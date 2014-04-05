<?php
/*
This is where the whereAt Asset Tracking magic happens!

The Triangulate function triangulates the signals based on the unit coordinates and returns the label x, y positions and the accuracy.
Input:
$data = array(
	array($unitx, $unity, $signalstrength),
	etc...
)
Note: coordinate grid must be square


Method: original model, based on two bases, the two units with max signal strength are selected
*/

function Triangulate($data) {
	$roundto = 0.001; //fraction of longest distance between units
	$margin = 0.15;
	
	//must have at least 3 measurements
	if (count($data) < 3) return false;
	
	//get round off factor
	$maxdis = 0;
	for ($a=0;$a<count($data);$a++)
	{
		for ($b=1;$b<count($data);$b++)
		{
			if ($b > $a)
			{
				$d = sqrt(pow($data[$a][0] - $data[$b][0], 2) + pow($data[$a][1] - $data[$b][1], 2));
				if ($d > $maxdis) $maxdis = $d;
			}
		}
	}
	$factor = $roundto * $maxdis;
	$factor = pow(10, floor(log10($factor)));
	
	//convert the signal strengths to relative distances
	$data = ConvertToRelativeDistance($data);
	
	//triangulation is based on three signals (in 2D), so do a combinations: take two as base and one third
	
	$signals = array();
	for ($i=count($data)-1;$i>=0;$i--)
	{
		$signals[] = $data[$i][2];
	}
	
	$maxi = GetMaxKey($signals);
	unset($signals[$maxi]);
	$nexti = GetMaxKey($signals);
	
	$results = array();
	for ($a=0;$a<count($data);$a++)
	{
		for ($b=0;$b<count($data);$b++)
		{
			if ($b == $nexti && $a == $maxi)
			{
				$otherdata = array();
				for ($d=0;$d<count($data);$d++)
				{
					if ($d != $a && $d != $b) $otherdata[] = $data[$d];
				}
				$tridata = array($data[$a], $data[$b], $otherdata);
				list($result, $base) = DoTriangulate($tridata, $factor, $roundto, $margin, $maxdis);
				//$base = ($base == 1) ? $b: $a;
				if ($result !== false)
				{
					//if (!isset($results[$base])) $results[$base] = array();
					foreach ($result as $res) $results[] = $res;
				}
			}
		}
	}
	$results = array(array_values($results));
	
	if (count($results) == 0) return false;
	
	print_r($results);
	
	foreach ($results as $i=>$subresults)
	{
		foreach ($subresults as $a=>$result)
		{
			$subresults[$a] = array($result);
		}
		$subresults = MergeSimilar($subresults, $margin);
		$results[$i] = array();
		foreach ($subresults as $subresult) $results[$i][] = $subresult[0];
	}
	
	//$results = MergeSimilar($results, $margin);
	
	print_r($results);
	
	$combinations = GetAllCombinations($results);
	
	$solutions = array();
	foreach ($combinations as $combination)
	{
		$result = array();
		foreach ($combination as $item)
		{
			$i = $item[0];
			$a = $item[1];
			$result[] = $results[$i][$a];
		}
		$solution = GetSolution($result, $maxdis, $factor);
		$solution[] = $solution[2] * GetTotalDisFactor($data, $solution[0], $solution[1], $maxdis);
		$solutions[] = $solution;
	}
	
	print_r($solutions);
	
	$minval = false;
	$mini = false;
	foreach ($solutions as $i=>$solution)
	{
		if ($solution[3] < $minval || $minval === false)
		{
			$minval = $solution[3];
			$mini = $i;
		}
	}
	
	$solution = $solutions[$mini];
	unset($solution[3]);
	
	//return data
	return $solution;
}

function DoTriangulate($data, $factor, $roundto, $margin, $maxdis) {
	$nsteps = 360;
	$maxdratio = 5;
	$maxdsolratio = .25;
	
	$otherunits = $data[2];
	$nunits = 2 + count($otherunits);
	
	//first relative distance should be > 1
	if ($data[1][2] < 1)
	{
		$tmp = $data[1];
		$data[1] = $data[0];
		$data[0] = $tmp;
		$base = 1;
	}
	else
		$base = 0;
	
	//normalize to first unit
	$ref = $data[0][2];
	for ($i=0;$i<2;$i++) $data[$i][2] = $data[$i][2] / $ref;
	foreach ($otherunits as $i=>$otherunit) $otherunits[$i][2] = $otherunit[2] / $ref;
	
	//triangulate
	$a = $data[1][2];
	$fvec = array($data[1][0] - $data[0][0], $data[1][1] - $data[0][1]);
	$f = sqrt(pow($fvec[0], 2) + pow($fvec[1], 2));
	
	$a3 = array();
	$gvec3 = array();
	$g3 = array();
	$beta3 = array();
	foreach ($otherunits as $otherunit)
	{
		$a3[] = $otherunit[2];
		
		$gvec3val = array($otherunit[0] - $data[0][0], $otherunit[1] - $data[0][1]);
		$g3[] = sqrt(pow($gvec3val[0], 2) + pow($gvec3val[1], 2));
		$beta3[] = atan2($fvec[1], $fvec[0]) - atan2($gvec3val[1], $gvec3val[0]);
		$gvec3[] = $gvec3val;
	}
	
	$basecoords = array();
	$afracs3 = array();
	for ($i=0;$i<$nsteps;$i++)
	{
		$th = ((2 * pi()) / $nsteps) * $i;
		@$d = abs($f / ($a*sqrt(1 - ((pow(sin($th), 2))/(pow($a, 2)))) + cos($th)));
		if (!is_nan($d) && $d != NULL)
		{
			if ($d / $f <= $maxdratio)
			{
				$d3s = array();
				$afrac3s = array();
				$delta3s = array();
				foreach ($otherunits as $b=>$otherunit)
				{
					$d3 = sqrt(pow($d, 2) + pow($g3[$b], 2) - 2*$d*$g3[$b]*cos($th + $beta3[$b])); //cos rule
					$afrac3 = ($d3 / $d) / $a3[$b];
					$delta3 = abs($a3[$b] * $d - $d3);
					
					if ($afrac3 == 0)
					{
						$afrac3s = false;
					}
					else
					{
						if ($afrac3 < 1 && $afrac3 > 0) $afrac3 = 1 / $afrac3;
						
						if ($afrac3s !== false) $afrac3s[] = $afrac3;
					}
					$d3s[] = $d3;
					$delta3s[] = $delta3;
				}
				
				if ($afrac3s === false)
					$afrac3 = 0;
				else
					$afrac3 = array_sum($afrac3s) / count($afrac3s);
				$delta3 = array_sum($delta3s) / count($delta3s);
				
				$totaldis = $d + $a*$d + array_sum($d3s);
				$disfactor = GetTotalDisFactorByDis($totaldis, $maxdis, $nunits);
				$afrac3 *= $disfactor;
				
				$e = $d * sin($th);
				$b = $d * cos($th);
				$basecoords[] = array($th, $d, $b, $e, $afrac3, $delta3);
				$afracs3[] = $afrac3;
			}
		}
	}
	
	//print_r($afracs3);
	
	//$min3 = min($afracs3);
	//$max3 = max($afracs3);
	//$min4 = min($afracs4);
	//$max4 = max($afracs4);
	
	echo "Base1: ({$data[0][0]}, {$data[0][1]}; {$data[0][2]}), base2: ({$data[1][0]}, {$data[1][1]}; {$data[1][2]})\n";
	//echo "Min/max: $min3, $max3, $min4, $max4\n";
	
	//find closest solutions
	$closest3 = FindClosest($afracs3, $margin, $basecoords);
	//$closest4 = FindClosest($afracs4, $margin, $basecoords);
	
	if ($closest3 === false /*|| $closest4 === false*/) return false;
	
	$phi = atan2($fvec[1], $fvec[0]);
	$highestfactor = false;
	$sols = array();
	foreach ($closest3 as $closest)
	{
		$coord3 = array($closest[3], $closest[4]);
		
		$sol = array();
		$sol[0] = $coord3[0] * cos($phi) - $coord3[1] * sin($phi);
		$sol[1] = $coord3[0] * sin($phi) + $coord3[1] * cos($phi);
		$sol[0] += $data[0][0];
		$sol[1] += $data[0][1];
		
		$sol[] = $closest[5];
		$sol[] = 1 / $closest[1];
		
		$sol[0] = round($sol[0] / $factor) * $factor;
		$sol[1] = round($sol[1] / $factor) * $factor;
		$sol[2] = round($sol[2] / $factor) * $factor;
		$sol[3] = round($sol[3] / $roundto) * $roundto;
		
		if ($sol[3] > $highestfactor || $highestfactor === false) $highestfactor = $sol[3];
		
		$sols[] = $sol;
	}
	
	foreach ($sols as $i=>$sol)
	{
		if ($highestfactor - $sol[3] > $margin) unset($sols[$i]);
	}
	$sols = array_values($sols);
	
	//var_dump($sols);
	
	return array($sols, $base);
}

function FindClosest($fracs, $margin, $basecoords) {
	$closest = array();
	
	foreach ($fracs as $i=>$frac)
	{
		$x = $basecoords[$i][1];
		$y = $basecoords[$i][2];
		echo "$frac, ($x, $y)\n";
	}
	echo "\n\n";
	
	//find closest solutions
	$withinmargin = false;
	$leastval = false;
	$maxval = false;
	$leasti = false;
	foreach ($fracs as $i=>$val)
	{
		if ($val > 0)
		{
			if ($val < 1) $val = 1 / $val;
			if ($val < $leastval || $leastval === false)
			{
				$leastval = $val;
				$leasti = $i;
			}
		}
	}
	$closest[] = array($leasti, $leastval);
	
	if (count($closest) == 0) return false;
	
	//add distance and some more data
	foreach ($closest as $i=>$closedata)
	{
		$closest[$i][] = $basecoords[$closedata[0]][1];
		$closest[$i][] = $basecoords[$closedata[0]][2];
		$closest[$i][] = $basecoords[$closedata[0]][3];
		$closest[$i][] = $basecoords[$closedata[0]][5];
	}
	
	return $closest;
}

function ConvertToRelativeDistance($data) {
	$sig = 2;
	
	//convert to number from log
	foreach ($data as $i=>$unitdata)
	{
		$unitdata[] = pow(10, ($unitdata[2] / 10)); //dB to abs
		$data[$i] = $unitdata;
	}
	
	//convert to relative strength
	$refstrength = $data[0][3];
	foreach ($data as $i=>$unitdata)
	{
		$unitdata[] = $unitdata[3] / $refstrength;
		$data[$i] = $unitdata;
	}
	
	//assuming strength ~ 1/d^f
	$f = 1; //this should come from empirical measurements
	foreach ($data as $i=>$unitdata)
	{
		$unitdata[] = round(pow(10, $sig) / pow($unitdata[4], $f)) / pow(10, $sig);
		$data[$i] = $unitdata;
	}
	
	$newdata = array();
	foreach ($data as $unitdata) $newdata[] = array($unitdata[0], $unitdata[1], $unitdata[5]);
	
	return $newdata;
}

function GetAllCombinations($data) {
	$indexes = array();
	for ($i=0;$i<count($data);$i++)
	{
		$index = array();
		for ($a=0;$a<count($data[$i]);$a++)
		{
			$index[] = array($i, $a);
		}
		$indexes[] = $index;
	}
	$combinations = GetNextRowCombinations($indexes, 0);
	return $combinations;
}

function GetNextRowCombinations($indexes, $startrow) {
	if ($startrow == count($indexes) - 1)
	{
		$combinations = array();
		foreach ($indexes[$startrow] as $index)
		{
			$combinations[] = array($index);
		}
		return $combinations;
	}
	else
	{
		$combinations = array();
		$nextcombinations = GetNextRowCombinations($indexes, $startrow + 1);
		foreach ($indexes[$startrow] as $index)
		{
			foreach ($nextcombinations as $nextcombination)
			{
				$combination = array($index);
				foreach ($nextcombination as $combinationitem) $combination[] = $combinationitem;
				$combinations[] = $combination;
			}
		}
		return $combinations;
	}
}

function GetTotalDisFactor($data, $x, $y, $maxdis) {
	$d = 0;
	foreach ($data as $unitdata)
		$d += sqrt(pow($unitdata[0] - $x, 2) + pow($unitdata[1] - $y, 2));
	
	$n = count($data);
	
	return GetTotalDisFactorByDis($d, $maxdis, $n);
}
function GetTotalDisFactorByDis($d, $maxdis, $n) {
	if ($d < $maxdis * $n)
		$factor = 1;
	else
		$factor = $d / ($maxdis * $n);
	return $factor;
}

function GetSolution($results, $maxdis, $factor) {
	list($avg, $var, $den) = WeightedAvg($results);
	
	$avg[2] *= $den;
	
	foreach ($avg as $i=>$val) $avg[$i] = round($val / $factor) * $factor;
	foreach ($var as $i=>$val) $var[$i] = round($val / $factor) * $factor;
	
	$x = $avg[0];
	$y = $avg[1];
	$acc = (($var[0] + $var[1]) / 2) + $avg[2];
	
	return array($x, $y, $acc);
}

function WeightedAvg($data) {
	$wfactor = 1;
	
	$num = array();
	$d2 = array();
	$den = 0;
	for ($i=0;$i<count($data[0])-1;$i++) $num[] = 0;
	for ($i=0;$i<count($data[0])-1;$i++) $d2[] = 0;
	
	foreach ($data as $line)
	{
		$w = pow($line[count($line)-1], $wfactor);
		for ($i=0;$i<count($line)-1;$i++)
		{
			$num[$i] += $line[$i] * $w;
		}
		$den += $w;
	}
	
	$avg = array();
	foreach ($num as $numer) $avg[] = $numer / $den;
	
	foreach ($data as $line)
	{
		$w = pow($line[count($line)-1], $wfactor);
		for ($i=0;$i<count($line)-1;$i++)
		{
			$d = ($line[$i] - $avg[$i]);
			$d2[$i] += abs($d) * $w;
		}
	}
	
	$var = array();
	foreach ($d2 as $d2num) $var[] = $d2num / $den;
	
	return array($avg, $var, $den);
}

function MergeSimilar($results, $margin) {
	//add weight tracking
	foreach ($results as $i=>$result) $results[$i] = array($result, 1);
	
	//merge
	$found = true;
	while ($found == true)
	{
		$found = false;
		for ($i=0;$i<count($results);$i++)
		{
			for ($a=0;$a<count($results);$a++)
			{
				if ($i != $a)
				{
					$similar = IsSimilar($results[$i][0], $results[$a][0]);
					if ($similar == true)
					{
						$found = true;
						unset($results[$a]);
						$results[$i][1]++;
						$results = array_values($results);
						break 2;
					}
				}
			}
		}
	}
	
	//find highest factor
	$highestfactor = false;
	foreach ($results as $subresults)
	{
		if ($subresults[1] > $highestfactor || $highestfactor === false) $highestfactor = $subresults[1];
	}
	
	//normalize factors
	foreach ($results as $i=>$subresults)
	{
		$factor = $subresults[1];
		$subresults = $subresults[0];
		foreach ($subresults as $a=>$result)
		{
			$w = $result[count($result)-1];
			$w = $w * sqrt($factor / $highestfactor);
			$subresults[$a][count($result)-1] = $w;
		}
		$results[$i] = $subresults;
	}
	
	//find higest weight
	$highestw = false;
	foreach ($results as $subresults)
	{
		foreach ($subresults as $result)
		{
			$w = $result[count($result)-1];
			if ($w > $highestw || $highestw === false) $highestw = $w;
		}
	}
	
	//print_r($results);
	
	//filter results outside margin
	foreach ($results as $i=>$subresults)
	{
		foreach ($subresults as $a=>$result)
		{
			if ($highestw - $result[count($result)-1] > $margin) unset($subresults[$a]);
		}
		$subresults = array_values($subresults);
		if (count($subresults) == 0)
			unset($results[$i]);
		else
			$results[$i] = $subresults;
	}
	$results = array_values($results);
	
	return $results;
}

function IsSimilar($a, $b) {
	return (serialize($a) == serialize($b));
}

function GetMaxKey($arr) {
	$maxval = false;
	$maxkey = false;
	foreach ($arr as $key=>$val)
	{
		if ($val > $maxval || $maxval === false)
		{
			$maxval = $val;
			$maxkey = $key;
		}
	}
	return $maxkey;
}
?>
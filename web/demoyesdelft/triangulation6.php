<?php
/*
This is where the whereAt Asset Tracking magic happens!

The Triangulate function triangulates the signals based on the unit coordinates and returns the label x, y positions and the accuracy.
Input:
$data = array(
	array($unitx, $unity, $averagesignalstrength, $minsignalstrength, $maxsignalstrength),
	etc...
)
Note: coordinate grid must be square


Method: optimization problem finding the minimum deviation from measured relative strengths, using other method of finding minimum than method 5
*/

function Triangulate($data) {
	$distancefrac = 0.001; //fraction of longest distance between units
	$margin = 0.15;
	
	//must have at least 3 measurements
	if (count($data) < 3) return false;
	
	//get round off factor
	$minx = false;
	$miny = false;
	$maxx = false;
	$maxy = false;
	for ($a=0;$a<count($data);$a++)
	{
		if ($data[$a][0] < $minx || $minx === false) $minx = $data[$a][0];
		if ($data[$a][1] < $miny || $miny === false) $miny = $data[$a][1];
		if ($data[$a][0] > $maxx || $maxx === false) $maxx = $data[$a][0];
		if ($data[$a][1] > $maxy || $maxy === false) $maxy = $data[$a][1];
		/*for ($b=1;$b<count($data);$b++)
		{
			if ($b > $a)
			{
				$d = sqrt(pow($data[$a][0] - $data[$b][0], 2) + pow($data[$a][1] - $data[$b][1], 2));
				if ($d > $maxdis) $maxdis = $d;
			}
		}*/
	}
	$maxdis = max($maxx - $minx, $maxy - $miny);
	$gridstep = $distancefrac * $maxdis;
	$roundto = pow(10, round(log10($gridstep)));
	
	//convert the signal strengths to relative distances
	$data = ConvertToRelativeDistance($data);
	
	$strongestval = false;
	$strongesti = false;
	foreach ($data as $key=>$val)
	{
		if ($val[2] < $strongestval || $strongestval === false)
		{
			$strongestval = $val[2];
			$strongesti = $key;
		}
	}
	//print_r($data);
	
	//establish search bounds
	$factor = 3;
	$dx = $maxx - $minx;
	$dy = $maxy - $miny;
	$avgx = ($maxx + $minx) / 2;
	$avgy = ($maxy + $miny) / 2;
	$bounds = array($avgx - $dx * $factor * 0.5, $avgy - $dy * $factor * 0.5, $avgx + $dx * $factor * 0.5, $avgy + $dy * $factor * 0.5);
	
	/*print_r($bounds);
	
	$exclude = array(array(-200, 0, 200, 200));
	print_r($exclude);
	
	print_r(GetBounds($bounds, $exclude));
	exit();*/
	
	//start searching
	$steps = 50;
	$xstep = $dx / ($steps - 1);
	$ystep = $dy / ($steps - 1);
	
	$start = microtime(true);
	$minima = FindMinima($data, $gridstep, $roundto, $bounds, $strongesti);
	$diff = (microtime(true) - $start) * 1000;
	//echo "\nSearch time: $diff ms\n\n";
	
	global $counter, $t, $t1, $t2, $t3;
	$avg = ($t / $counter) * 1000;
	$avg1 = ($t1 / $counter) * 1000;
	$avg2 = ($t2 / $counter) * 1000;
	$avg3 = ($t3 / $counter) * 1000;
	//echo "Called GetValue $counter times with an average execution time of $avg ms (1: $avg1 ms, 2: $avg2 ms, 3: $avg3 ms), total: ".($t * 1000)." ms\n\n";
	
	//merge similar
	$found = true;
	while ($found == true)
	{
		$found = false;
		for ($i=0;$i<count($minima);$i++)
		{
			for ($a=0;$a<count($minima);$a++)
			{
				if ($i != $a)
				{
					if (serialize($minima[$i]) == serialize($minima[$a]))
					{
						$found = true;
						unset($minima[$a]);
						$minima = array_values($minima);
						break 2;
					}
				}
			}
		}
	}
	
	//print_r($minima);
	
	//find result with minimum accuracy distance
	$minval = false;
	$minkey = false;
	foreach ($minima as $key=>$val)
	{
		if ($val[2] < $minval || $minval === false)
		{
			$minval = $val[2];
			$minkey = $key;
		}
	}
	
	$minimum = $minima[$minkey];
	
	//$minimum[2] = round($minimum[2] / $roundto) * $roundto;
	
	return $minimum;
}

function FindMinima($data, $gridstep, $roundto, $bounds /*minx, miny, maxx, maxy*/, $strongesti, $minval = false) {
	$steps = 20;
	
	$stepx = ($bounds[2] - $bounds[0]) / ($steps - 1);
	$stepy = ($bounds[3] - $bounds[1]) / ($steps - 1);
	
	$return = ($stepx < $gridstep || $stepy < $gridstep) ? true: false;
	
	//echo "Starting search between ($bounds[0] - $bounds[2]),($bounds[1] - $bounds[3]) with step size: $stepx, $stepy ($steps steps), minval: $minval\n";
	
	$values = array();
	for ($i=0;$i<$steps;$i++)
	{
		$values[$i] = array();
		$x = $bounds[0] + $i * $stepx;
		for ($a=0;$a<$steps;$a++)
		{
			$y = $bounds[1] + $a * $stepy;
			
			$val = GetValue($data, $x, $y, $strongesti);
			$values[$i][$a] = $val;
		}
	}
	
	$minima = array();
	$excludebounds = array();
	$buffer = array();
	for ($i=0;$i<$steps;$i++)
	{
		for ($a=0;$a<$steps;$a++)
		{
			$minx = false;
			$miny = false;
			$maxx = false;
			$maxy = false;
			
			$val = $values[$i][$a];
			if ($val > $minval && $minval !== false) continue;
			
			$surrounding = array();
			for ($b=-1;$b<=1;$b++)
			{
				if (isset($values[$i+$b]))
				{
					$x = $bounds[0] + ($i+$b) * $stepx;
					if ($x < $minx || $minx === false) $minx = $x;
					if ($x > $maxx || $maxx === false) $maxx = $x;
				}
				for ($c=-1;$c<=1;$c++)
				{
					if (isset($values[$i+$b][$a+$c]))
					{
						$y = $bounds[1] + ($a+$c) * $stepy;
						if ($y < $miny || $miny === false) $miny = $y;
						if ($y > $maxy || $maxy === false) $maxy = $y;
					}
					if ($b != 0 || $c != 0)
					{
						if (isset($values[$i+$b][$a+$c]))
						{
							$surrounding[] = $values[$i+$b][$a+$c];
						}
					}
				}
			}
			$min = min($surrounding);
			
			if ($val <= $min)
			{
				//echo "$val, surrounding: $min\n";
				if ($return == false)
				{
					//echo "Found minima between ($minx, $miny),($maxx, $maxy) continuing search\n";
					$nextbounds = array($minx, $miny, $maxx, $maxy);
					$nextbounds = GetBounds($nextbounds, $excludebounds, $roundto);
					if ($nextbounds !== false)
					{
						$excludebounds[] = $nextbounds;
						$minval = $val;
						$buffer[] = array($data, $gridstep, $roundto, $nextbounds, $strongesti, $minval);
					}
				}
				else
				{
					//echo "Found minima at ($x, $y): $val\n";
					$minval = $val;
					$x = $bounds[0] + $i * $stepx;
					$y = $bounds[1] + $a * $stepy;
					$minima[] = array($x, $y, $val);
				}
			}
		}
	}
	//echo "\n";
	if ($return == false)
	{
		foreach ($buffer as $finddata)
		{
			//if ($finddata[5] > $minval) continue;
			$finddata[5] = $minval;
			
			$extraminima = call_user_func_array("FindMinima", $finddata);
			foreach ($extraminima as $extraminimum)
			{
				if ($extraminimum[2] < $minval) $minval = $extraminimum[2];
				$minima[] = $extraminimum;
			}
		}
	}
	
	/*foreach ($minima as $i=>$minimum)
	{
		foreach ($minimum as $key=>$val) $minima[$i][$key] = round($val / $roundto) * $roundto;
	}*/
	
	return $minima;
}

function GetBounds($bounds, $exclude, $roundto) {
	$newbounds = $bounds;
	
	$allgood = false;
	while ($allgood == false)
	{
		$allgood = true;
		
		$minx = round($newbounds[0] / $roundto) * $roundto;
		$miny = round($newbounds[1] / $roundto) * $roundto;
		$maxx = round($newbounds[2] / $roundto) * $roundto;
		$maxy = round($newbounds[3] / $roundto) * $roundto;
		
		//echo "$minx, $miny, $maxx, $maxy\n";
		
		if ($miny >= $maxy || $minx >= $maxx) return false;
		
		foreach ($exclude as $ebounds)
		{
			$minxe = round($ebounds[0] / $roundto) * $roundto;
			$minye = round($ebounds[1] / $roundto) * $roundto;
			$maxxe = round($ebounds[2] / $roundto) * $roundto;
			$maxye = round($ebounds[3] / $roundto) * $roundto;
			
			//echo "$minx, $miny, $maxx, $maxy, $minxe, $minye, $maxxe, $maxye\n";
			
			if (($minx < $minxe && ($miny < $minye || $maxy > $maxye)) || ($maxx > $maxxe && ($miny < $minye || $maxy > $maxye))) continue;
			
			if ($minx >= $minxe && $maxx <= $maxxe)
			{
				if ($miny < $maxye && $maxy > $maxye)
				{
					$allgood = false;
					$newbounds[1] = $ebounds[3];
				}
				if ($maxy > $minye && $miny < $minye)
				{
					$allgood = false;
					$newbounds[3] = $ebounds[1];
				}
				if ($maxy <= $maxye && $miny >= $minye) return false;
				break;
			}
			elseif ($miny >= $minye && $maxy <= $maxye)
			{
				if ($minx < $maxxe && $maxx > $maxxe)
				{
					$allgood = false;
					$newbounds[0] = $ebounds[2];
				}
				if ($maxx > $minxe && $minx < $minxe)
				{
					$allgood = false;
					$newbounds[2] = $ebounds[0];
				}
				if ($maxx <= $maxxe && $minx >= $minxe) return false;
				break;
			}
		}
	}
	
	return $newbounds;
}

$counter = 0;
$t = 0;
$t1 = 0;
$t2 = 0;
$t3 = 0;
function GetValue($data, $x, $y, $strongesti) {
	$start = microtime(true);
	global $counter, $t, $t1, $t2, $t3;
	$counter++;
	//get distances
	$ds = array();
	$n = count($data);
	for ($i=0;$i<$n;$i++)
	{
		$a = $data[$i][0] - $x;
		$b = $data[$i][1] - $y;
		$ds[] = sqrt($a*$a + $b*$b);
	}
	$end1 = microtime(true);
	$t1 += $end1 - $start;
	
	//take each measurement as a reference and then check the deviations for all other measurements
	$deltas = array();
	/*for ($i=0;$i<$n;$i++)
	{*/
		$i = $strongesti;
		
		$d = $ds[$i];
		$aref = $data[$i][2];
		
		for ($a=0;$a<$n;$a++)
		{
			if ($i != $a)
			{
				$dunit = $ds[$a];
				$aunit = $data[$a][2] / $aref;
				$aunitmin = $data[$a][3] / $aref;
				$aunitmax = $data[$a][4] / $aref;
				
				$delta = abs($aunit * $d - $dunit);
				$deltamin = abs($aunitmin * $d - $dunit);
				$deltamax = abs($aunitmax * $d - $dunit);
				$delta = max($delta, $deltamin, $deltamax);
				$deltas[] = $delta;
			}
		}
	//}
	$end2 = microtime(true);
	$t2 += $end2 - $end1;
	
	$delta = array_sum($deltas) / count($deltas);
	
	$end3 = microtime(true);
	$t3 += $end3 - $end2;
	$t += $end3 - $start;
	return $delta;
}

function GetDerivative($data, $x, $y, $gridstep) {
	$factor = 100;
	
	$gridstep = $gridstep / $factor;
	$d = $gridstep / 2;
	
	$xup = GetValue($data, $x + $d, $y);
	$xdown = GetValue($data, $x - $d, $y);
	
	$yup = GetValue($data, $x, $y + $d);
	$ydown = GetValue($data, $x, $y - $d);
	
	$xderiv = ($xup - $xdown) / ($d * 2);
	$yderiv = ($yup - $ydown) / ($d * 2);
	
	//echo "$xup, $xdown, $yup, $ydown, $d, $xderiv, $yderiv\n";
	
	return array($xderiv, $yderiv);
}

function ConvertToRelativeDistance($data) {
	$sig = 2;
	
	//convert to number from log
	foreach ($data as $i=>$unitdata)
	{
		$unitdata[] = pow(10, ($unitdata[2] / 10)); //dB to abs
		$unitdata[] = pow(10, ($unitdata[3] / 10)); //dB to abs
		$unitdata[] = pow(10, ($unitdata[4] / 10)); //dB to abs
		$data[$i] = $unitdata;
	}
	
	//convert to relative strength
	$refstrength = $data[0][5];
	foreach ($data as $i=>$unitdata)
	{
		$unitdata[] = $unitdata[5] / $refstrength;
		$unitdata[] = $unitdata[6] / $refstrength;
		$unitdata[] = $unitdata[7] / $refstrength;
		$data[$i] = $unitdata;
	}
	
	//assuming strength ~ 1/d^f
	$f = 1; //this should come from empirical measurements
	foreach ($data as $i=>$unitdata)
	{
		$unitdata[] = round(pow(10, $sig) / pow($unitdata[8], $f)) / pow(10, $sig);
		$unitdata[] = round(pow(10, $sig) / pow($unitdata[9], $f)) / pow(10, $sig);
		$unitdata[] = round(pow(10, $sig) / pow($unitdata[10], $f)) / pow(10, $sig);
		$data[$i] = $unitdata;
	}
	
	$newdata = array();
	foreach ($data as $unitdata) $newdata[] = array($unitdata[0], $unitdata[1], $unitdata[11], $unitdata[12], $unitdata[13]);
	
	return $newdata;
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
?>
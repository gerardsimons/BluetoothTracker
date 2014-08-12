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


Method: optimization problem finding the minimum deviation from measured relative strengths
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
	
	$start = microtime(true);
	
	//start searching
	$steps = 2;
	$xstep = $dx / ($steps - 1);
	$ystep = $dy / ($steps - 1);
	
	/*$minima = array();
	for ($i=0;$i<$steps;$i++)
	{
		$x = $minx + $xstep * $i;
		for ($a=0;$a<$steps;$a++)
		{
			$y = $miny + $ystep * $a;
			$minima[] = FindMinimum($data, $x, $y, $gridstep, $roundto, $bounds);
			//$val = GetValue($data, $x, $y);
			//GetDerivative($data, $x, $y, $gridstep);
			//echo "$x, $y, $val\n\n";
		}
	}*/
	
	//print_r(FindMinimum($data, $avgx, $avgy, $gridstep, $bounds));
	
	$totalx = 0;
	$totaly = 0;
	$totaln = 0;
	foreach ($data as $unitdata)
	{
		$totalx += $unitdata[0];
		$totaly += $unitdata[1];
		$totaln++;
	}
	$startx = $totalx / $totaln;
	$starty = $totaly / $totaln;
	$minima = array();
	$minima[] = FindMinimum($data, $startx, $starty, $gridstep, $roundto, $bounds, $strongesti);
	
	/*$minima = array();
	for ($i=0;$i<count($data);$i++)
	{
		$minima[] = FindMinimum($data, $data[$i][0], $data[$i][1], $gridstep, $roundto, $bounds, $strongesti);
		/*for ($a=0;$a<count($data);$a++)
		{
			if ($a > $i)
			{
				$startx = ($data[$i][0] + $data[$a][0]) / 2;
				$starty = ($data[$i][1] + $data[$a][1]) / 2;
				$minima[] = FindMinimum($data, $startx, $starty, $gridstep, $roundto, $bounds, $strongesti);
			}
		}*/
	//}
	//foreach ($data as $unitdata) $minima[] = FindMinimum($data, $unitdata[0], $unitdata[1], $gridstep, $roundto, $bounds, $strongesti);
	
	/*$minima = array();
	for ($i=0;$i<$steps;$i++)
	{
		$x = $minx + $xstep * $i;
		for ($a=0;$a<$steps;$a++)
		{
			$y = $miny + $ystep * $a;
			$minimum = FindMinimum($data, $x, $y, $gridstep, $roundto, $bounds, $strongesti);
			$minima[] = $minimum;
		}
	}*/
	
	$diff = (microtime(true) - $start) * 1000;
	echo "\nSearch time: $diff ms\n\n";
	
	global $counter, $t, $t1, $t2, $t3;
	$avg = ($t / $counter) * 1000;
	$avg1 = ($t1 / $counter) * 1000;
	$avg2 = ($t2 / $counter) * 1000;
	$avg3 = ($t3 / $counter) * 1000;
	echo "Called GetValue $counter times with an average execution time of $avg ms (1: $avg1 ms, 2: $avg2 ms, 3: $avg3 ms), total: ".($t * 1000)." ms\n\n";
	
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
	
	$solution = $minima[$minkey];
	
	$solution[2] = round($solution[2] / $roundto) * $roundto;
	
	return $solution;
}

function FindMinimum($data, $startx, $starty, $gridstep, $roundto, $bounds /*minx, miny, maxx, maxy*/, $strongesti) {
	//multiplication factor for faster (but unstable?) convergence
	$factor = (1 / $gridstep);
	
	//initial values
	$x = $startx;
	$y = $starty;
	$prevx = $x;
	$prevy = $y;
	$been = array();
	
	//do the search
	$stop = false;
	while ($stop == false)
	{
		$deriv = GetDerivative($data, $x, $y, $gridstep, $strongesti);
		
		//echo "$x, $y, $deriv[0], $deriv[1]\n";
		
		$x += -$deriv[0] * $factor;
		$y += -$deriv[1] * $factor;
		
		if ($x < $bounds[0]) $x = $bounds[0];
		if ($y < $bounds[1]) $y = $bounds[1];
		if ($x > $bounds[2]) $x = $bounds[2];
		if ($y > $bounds[3]) $y = $bounds[3];
		
		$deltax = round(abs($x - $prevx) / $roundto) * $roundto;
		$deltay = round(abs($y - $prevy) / $roundto) * $roundto;
		
		if ($deltax == 0 && $deltay == 0) $stop = true;
		
		$xrounded = round($x / $gridstep) * $gridstep;
		$yrounded = round($y / $gridstep) * $gridstep;
		$key = "$xrounded:$yrounded";
		if (isset($been[$key]))
		{
			//echo "Resetting factor from $factor to ";
			$factor /= 10;
			//echo "$factor\n";
			$been = array();
		}
		else
			$been[$key] = true;
		
		$prevx = $x;
		$prevy = $y;
	}
	
	//get output data
	$x = round($x / $roundto) * $roundto;
	$y = round($y / $roundto) * $roundto;
	$acc = GetValue($data, $x, $y, $strongesti);
	//echo "Minimum at ($x, $y): $acc\n\n";
	return array($x, $y, $acc);
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

function GetDerivative($data, $x, $y, $gridstep, $strongesti) {
	$factor = 100;
	
	$gridstep = $gridstep / $factor;
	$d = $gridstep / 2;
	
	$xup = GetValue($data, $x + $d, $y, $strongesti);
	$xdown = GetValue($data, $x - $d, $y, $strongesti);
	
	$yup = GetValue($data, $x, $y + $d, $strongesti);
	$ydown = GetValue($data, $x, $y - $d, $strongesti);
	
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
		$unitdata[] = round(pow(10, $sig) / pow($unitdata[8], 1/$f)) / pow(10, $sig);
		$unitdata[] = round(pow(10, $sig) / pow($unitdata[9], 1/$f)) / pow(10, $sig);
		$unitdata[] = round(pow(10, $sig) / pow($unitdata[10], 1/$f)) / pow(10, $sig);
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
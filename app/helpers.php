<?php

function array_collect($array, $collectOn)
{
	$collection = [];

	if ( ! is_null($array))
	{
		foreach($array as $key => $value)	
		{
			$inCollection = ((is_array($value) and isset($value[$collectOn])) or (is_object($value) and isset($value->{$collectOn})));

			if ($inCollection)
			{
				$collection[$value[$collectOn]][] = $value;
			}
		}
	}

	return $collection;
}

function array_random($array)
{
	// Reset the keys
	$values = array_values($array);

	$length = count($values);

	if ($length == 0)
	{
		return null;
	}
	else if ($length == 1)
	{
		return $values[0];
	}
	else
	{
		return $values[mt_rand(0, $length - 1)];
	}
}

function carbon_intervalforhumans($carbon)
{
	$labels = array(
		'y' => 'Year', 
		'm' => 'Month', 
		'd' => 'Day', 
		'h' => 'Hour', 
		'i' => 'Minute', 
		's' => 'Second', 
	);

	$arr = [];

	$labelKeys = array_keys($labels);

	foreach($labelKeys as $key)
	{
		$diffFunc = 'diffIn'.$labels[$key].'s';
		$subFunc = 'sub'.$labels[$key].'s';
		$arr[$key] = $carbon->{$diffFunc}();
		$carbon = $carbon->{$subFunc}($arr[$key]);
	}

	$arr = array_filter($arr);
	$arrKeys = array_keys($arr);

	$mappedArr = array_map(function($value, $key) use ($labels)
		{
			return number_format($value).' '.Str::plural($labels[$key], $value);
		}, $arr, $arrKeys);

	return implode(' ', $mappedArr);
}
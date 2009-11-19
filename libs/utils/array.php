<?php
/**
 * Insert value at position of array
 * 
 * @param array $array
 * @param object $value
 * @param integer $position
 * @return array
 */
function array_insert($array, $value, $position) {
	$buf = array_splice($array,$position);
    $array[] = $value;
    $array = array_merge($array,$buf);
  
    return $array;
}

/**
 * Distinct merge two arrays
 * 
 * @param array $to
 * @param array $from
 * @return array
 */
function array_merge_recursive_distinct_test( &$to, &$from ) {

	foreach ( $from as $from_key => $from_value ) {
		if ( is_numeric($from_key) ) {
			if ( !is_array($to) )
				$to = array($to);
						
			if ( ($pos = array_search($from_value, $to)) !== false )
				$to[$pos] = $from_value; 
			else
				array_push($to, $from_value);
		}
		elseif ( is_array($from_value) && array_key_exists($from_key, $to) )
			array_merge_recursive_distinct_test($to[$from_key], $from_value);
		else
			$to[$from_key] = $from_value;			
	}			
			
	return $to;
}
?>
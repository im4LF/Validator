<?php
/**
 * Convert object to array
 * 
 * @param object $object
 * @return array
 */
function object2array( &$object ) {
	$array = (array) $object;
	$object_class = get_class($object);
	$res = array();
	
	foreach ( $array as $key => $value ) {
		$filtered_key = $key;
		if ( strpos($key, "\0*\0") !== false )
			$filtered_key = str_replace("\0*\0", '', $key).':protected';
		elseif ( strpos($key, "\0$object_class\0" ) === 0 )
			$filtered_key = str_replace("\0$object_class\0", '', $key).':private';
			
		$res[$filtered_key] =& $array[$key];
	}
	
	return $res;
}
?>
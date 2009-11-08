<?php
function parse_post(){
	/*$data = array();
	foreach($_POST as $key => $value){
		$keys = explode('_', $key, 3);
		$key = implode('.', $keys);
		$data[$key] = $value;
	}
	return $data;*/
	return json_decode(file_get_contents("php://input"), true);
}

function starts_with($string, $search) {
    return (strncmp($string, $search, strlen($search)) == 0);
}
function end_with( $str, $sub ) {
return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

/**
 * Merges any number of arrays of any dimensions, the later overwriting
 * previous keys, unless the key is numeric, in whitch case, duplicated
 * values will not be added.
 *
 * The arrays to be merged are passed as arguments to the function.
 *
 * @access public
 * @return array Resulting array, once all have been merged
 */
function array_merge_replace_recursive() {
    // Holds all the arrays passed
    $params = & func_get_args ();
   
    // First array is used as the base, everything else overwrites on it
    $return = array_shift ( $params );
   
    // Merge all arrays on the first array
    foreach ( $params as $array ) {
        foreach ( $array as $key => $value ) {
            // Numeric keyed values are added (unless already there)
            if (is_numeric ( $key ) && (! in_array ( $value, $return ))) {
                if (is_array ( $value )) {
                    $return [] = $this->array_merge_replace_recursive ( $return [$$key], $value );
                } else {
                    $return [] = $value;
                }
               
            // String keyed values are replaced
            } else {
                if (isset ( $return [$key] ) && is_array ( $value ) && is_array ( $return [$key] )) {
                    $return [$key] = $this->array_merge_replace_recursive ( $return [$$key], $value );
                } else {
                    $return [$key] = $value;
                }
            }
        }
    }
   
    return $return;
}

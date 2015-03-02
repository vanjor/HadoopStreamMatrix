<?php

require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($args) != 1) {
	$reducer = new PmatrixRowDicReducer();
	$reducer -> main();
}

class PmatrixRowDicReducer {

	function __construct() {
	}

	/**
	 * input format
	 * $value's dimension<TAB>$key
	 * output format
	 * $key<TAB>$value's dimension
	 * 
	 * the $key is sorted by dimension desc
	 */
	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($sorted_key, $actual_key) = explode("\t", $line);
			echo $actual_key, "\t", $sorted_key, PHP_EOL;
		}
	}

}

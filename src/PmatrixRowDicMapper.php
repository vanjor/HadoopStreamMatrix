<?php

require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$mapper = new PmatrixRowDicMapper();
	$mapper -> main();
}

class PmatrixRowDicMapper {

	function __construct() {
	}

	/**
	 * input format
	 * $key<TAB>$value
	 * output format
	 * $value's dimension<TAB>$key
	 */
	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($key, $value) = explode("\t", $line);
			$value = json_decode($value, "r");
			$value_length = count($value);
			echo $value_length, "\t", $key, PHP_EOL;
		}
	}

}

<?php

require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$mapper = new PmatrixMultiplicationPostMergeMapper();
	$mapper -> main();
}

class PmatrixMultiplicationPostMergeMapper {
	function __construct() {
	}

	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($key_row, $key_column, $value) = explode("\t", $line);
			$key_row = json_decode($key_row, "r");
			$key_column = json_decode($key_column, "r");
			$new_key = json_encode(array($key_row['k'], $key_column['k']));
			echo $new_key, "\t", $value, PHP_EOL;
		}
	}

}

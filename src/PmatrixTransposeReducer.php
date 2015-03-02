<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$reduce = new PmatrixTransposeReducer();
	$reduce -> main();
}

class PmatrixTransposeReducer {

	function __construct() {
	}

	function main() {
		$current_row = null;
		$current_column_list = array();

		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($row, $column, $value) = explode("\t", $line);

			if (isset($current_row) && $current_row != $row) {
				$this -> process_single_row($current_row, $current_column_list);
				unset($current_column_list);
			}

			$current_row = $row;
			$current_column_list[$column] = $value;
		}
		$this -> process_single_row($current_row, $current_column_list);
	}

	function process_single_row($current_row, $current_column_list) {
		$size = count($current_column_list);
		if ($size > 0) {
			$norm2_sum = 0;
			echo $current_row, "\t",  json_encode($current_column_list), PHP_EOL;
		}
	}

}

<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$reducer = new PmatrixRowVectorizeReducer();
	$reducer -> main();
}

class PmatrixRowVectorizeReducer {
	function __construct() {
	}

	function main() {
		$current_key = null;
		$current_vector = array();
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($row, $column, $value) = explode(chr(9), $line);
			if ($current_key == null) {
				$current_key = $row;
				$current_vector[$column] = $value;
			} else if ($current_key == $row) {
				$current_vector[$column] = $value;
			} else {
				$this -> print_result($current_key, $current_vector);
				$current_key = $row;
				unset($current_vector);
			}
		}
		$this -> print_result($current_key, $current_vector);
	}

	function print_result($current_key, $current_vector) {
		if ($current_key != null) {
			arsort($current_vector);
			echo $current_key, "\t",   json_encode($current_vector), PHP_EOL;
		}
	}

}

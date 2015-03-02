<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$mapper = new PmatrixSelfMultiplicationReducer();
	$mapper -> main();
}

class PmatrixSelfMultiplicationReducer {
	private $min_support = 0.000001;
	private $max_support = 1;
	function __construct($min_support = null,$max_support = null) {
	}

	function main() {
		$current_key = null;
		$row_vector = null;
		$column_vector = null;
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($key, $value) = explode("\t", $line);
			$value = json_decode($value, "r");
			if ($current_key == null) {
				$current_key = $key;
				$row_vector = $value;
			} else if ($current_key == $key) {
				if ($row_vector == null) {
					PmatrixLogger::warn("PmatrixSelfMultiplicationReducer encounter 1 vector for {$key}");
					PmatrixLogger::counter("debug","PmatrixSelfMultiplicationReducer_vector_1",1);
					$current_key = null;
					$row_vector = null;
					$column_vector = null;
					continue;
				} else if ($column_vector == null) {
					$column_vector = $value;
				} else {
					PmatrixLogger::warn("PmatrixSelfMultiplicationReducer encounter 3 vector for {$key}");
					PmatrixLogger::counter("debug","PmatrixSelfMultiplicationReducer_vector_3",1);
					$current_key = null;
					$row_vector = null;
					$column_vector = null;
					continue;
				}
			} else {
				$result = $this -> vector_multiplication($row_vector, $column_vector);
				$this -> print_result($current_key, $result);
				$current_key = $key;
				$row_vector = $value;
				$column_vector = null;
			}
		}
		if ($current_key != null) {
			$result = $this -> vector_multiplication($row_vector, $column_vector);
			$this -> print_result($current_key, $result);
		}
	}

	function print_result($current_key, $value) {
		if ($value > $this -> min_support) {
			$keys = json_decode($current_key, "r");
			PmatrixLogger::counter("debug", "PmatrixSelfMultiplicationReducer_normal_reduce_output", 1);
			echo $keys[0], "\t", $keys[1], "\t", $value, PHP_EOL;
		} else {
			PmatrixLogger::counter("debug", "PmatrixSelfMultiplicationReducer_bellow_mini_supporter", 1);
		}
	}

	function vector_multiplication(&$row_vector, &$column_vector) {
		$result = 0;
		$row_size = count($row_vector);
		$column_size = count($column_vector);
		if ($row_size < $column_size) {
			foreach ($row_vector as $key => $value) {
				if (isset($column_vector[$key])) {
					$result += $column_vector[$key] * $value;
				}
			}
		} else {
			foreach ($column_vector as $key => $value) {
				if (isset($row_vector[$key])) {
					$result += $row_vector[$key] * $value;
				}
			}
		}
		return $result;
	}

}

<?php

require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$mapper = new PmatrixMultiplicationPreSplitMapper($argv[1]);
	$mapper -> main();
}

class PmatrixMultiplicationPreSplitMapper {
	private $split_size = 500;

	function __construct($split_size = null) {
		if ($split_size != null && is_numeric($split_size) && $split_size > 0) {
			$this -> split_size = $split_size;
		}
	}

	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($key, $value) = explode("\t", $line);
			$value = json_decode($value, "r");
			$value_length = count($value);
			PmatrixLogger::counter("debug", "src_key", 1);
			if ($value_length <= $this -> split_size) {
				PmatrixLogger::counter("debug", "result_key", 1);
				PmatrixLogger::counter("debug", "result_key_single", 1);
				$main_key = $key;
				$part = 0;
				$random = rand(100, 10000);
				$new_key = array("r" => $random, "k" => $key, "p" => $part);
				echo json_encode($new_key), "\t",  json_encode($value), PHP_EOL;
			} else {
				$part = 0;
				while (!empty($value)) {
					$sub_value = array();
					foreach ($value as $it_key => $it_value) {
						$sub_value[$it_key] = $it_value;
						unset($value[$it_key]);
						if (count($sub_value) >= $this -> split_size) {
							break;
						}
					}
					$random = rand(100, 10000);
					$new_key = array("r" => $random, "k" => $key, "p" => $part++);
					PmatrixLogger::counter("debug", "result_key", 1);
					echo json_encode($new_key), "\t",  json_encode($sub_value), PHP_EOL;
				}
			}
		}
	}

}

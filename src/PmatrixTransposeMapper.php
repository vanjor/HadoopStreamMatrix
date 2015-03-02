<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$mapper = new PmatrixTransposeMapper();
	$mapper -> main();
}

class PmatrixTransposeMapper {

	function __construct() {
	}

	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($key, $vector) = explode(chr(9), $line);
			$vector = json_decode($vector, "r");
			$this -> transpose($key, $vector);
		}
	}

	private function transpose($key, $vector) {
		foreach ($vector as $sub_key => $value) {
			echo $sub_key, "\t", $key, "\t", $value, PHP_EOL;
		}
	}

}

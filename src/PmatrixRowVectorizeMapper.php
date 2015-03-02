<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$mapper = new PmatrixRowVectorizeMapper();
	$mapper -> main();
}

class PmatrixRowVectorizeMapper {
	private $method = 'row';
	private $min_support = 0.001;
	function __construct($row_or_column = null) {
		if ($row_or_column == 'row' || $row_or_column == 'column') {
			$this -> method = $row_or_column;
		}
	}

	function main() {
		// input comes from STDIN (standard input)
		while (($line = fgets(STDIN)) !== false) {
			// split the line into words while removing any empty string
			$line = trim($line, PHP_EOL);
			list($row, $column, $value) = explode(chr(9), $line);
			if ($value < $this -> min_support) {
				PmatrixLogger::counter("debug","PmatrixRowVectorizeMapper_filter",1);
				continue;
			}else{
				PmatrixLogger::counter("debug","PmatrixRowVectorizeMapper_pass",1);
			}
			if ($this -> method == 'row') {
				echo $row, "\t", $column, "\t", $value, PHP_EOL;
			} else if ($this -> method == 'column') {
				echo $column, "\t", $row, "\t", $value, PHP_EOL;
			}

		}
	}

}

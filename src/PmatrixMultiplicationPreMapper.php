<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	$mapper = new PmatrixMultiplicationPreMapper($argv[1]);
	$mapper -> main();
}

class PmatrixMultiplicationPreMapper {
	private $matrix_a_or_b;
	private $key_dic_set;
	function __construct($matrix_a_or_b) {
		if ($matrix_a_or_b == 'A'||$matrix_a_or_b == 'B') {
			$this -> matrix_a_or_b = $matrix_a_or_b;
		} else{
			PmatrixLogger::error(get_class($this)." receive wrong parameter matrix_a_or_b, value:".$matrix_a_or_b );
			exit(1); 
	    }
	}

	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($row_key, $vector_value) = explode("\t", $line);
			echo $this->matrix_a_or_b,"\t",$row_key,"\t",$vector_value,PHP_EOL;
		}
	}

}

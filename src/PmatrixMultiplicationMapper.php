<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");
require_once (dirname(__FILE__) . "/PmatrixDicLoader.php");

if (count($argv) != 1) {
	$mapper = new PmatrixMultiplicationMapper($argv[1], $argv[2]);
	$mapper -> main();
}

class PmatrixMultiplicationMapper {
	private $map_a_dic_name;
	private $map_b_dic_name;
	private $map_a_dic_set;
	private $map_b_dic_set;
	function __construct($map_a_dic_name, $map_b_dic_name) {
		$this -> map_a_dic_name = $map_a_dic_name;
		$this -> map_b_dic_name = $map_b_dic_name;
		$this -> map_a_dic_set = PmatrixDicLoader::loadDic($this -> map_a_dic_name);
		$this -> map_b_dic_set = PmatrixDicLoader::loadDic($this -> map_b_dic_name);
		PmatrixLogger::info("PmatrixMultiplicationMapper load map_a_dic_set:{$map_a_dic_name} count:" . count($this -> map_a_dic_set));
		PmatrixLogger::info("PmatrixMultiplicationMapper load map_b_dic_set:{$map_b_dic_name} count:" . count($this -> map_b_dic_set));
	}

	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($matrix_type, $key, $vector_value) = explode("\t", $line);
			if ($matrix_type === 'A') {
				foreach ($this->map_b_dic_set as $sub_key => $sub_key_value) {
					PmatrixLogger::counter("debug","PmatrixMultiplicationMapper_A",1);
					echo json_encode(array($key, $sub_key)), "\t", $vector_value, PHP_EOL;
				}
			} else if ($matrix_type === 'B') {
				foreach ($this->map_a_dic_set as $sub_key => $sub_key_value) {
					PmatrixLogger::counter("debug","PmatrixMultiplicationMapper_B",1);
					echo json_encode(array($sub_key, $key)), "\t", $vector_value, PHP_EOL;
				}
			}else{
				PmatrixLogger::counter("debug","PmatrixMultiplicationMapper_notAB",1);
			}
		}
	}

}

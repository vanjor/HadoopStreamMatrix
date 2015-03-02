<?php
require_once (dirname(__FILE__) . "/PmatrixLogger.php");
require_once (dirname(__FILE__) . "/PmatrixDicLoader.php");

if (count($argv) != 1) {
	$mapper = new PmatrixSelfMultiplicationMapper($argv[1]);
	$mapper -> main();
}


class PmatrixSelfMultiplicationMapper {
	private $key_dic_name;
	private $key_dic_set;
	function __construct($key_dic_name) {
		$this -> key_dic_name = $key_dic_name;
		$this -> key_dic_set = PmatrixDicLoader::loadDic($this -> key_dic_name);
		PmatrixLogger::info("PmatrixSelfMultiplicationMapper load key_dic:{$key_dic_name} count:" . count($this -> key_dic_set));
	}

	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($key, $value) = explode("\t", $line);
			foreach ($this->key_dic_set as $sub_key => $sub_key_value) {
				echo json_encode(array($key, $sub_key)), "\t", $value, PHP_EOL;
				echo json_encode(array($sub_key, $key)), "\t", $value, PHP_EOL;
			}
		}
	}

}

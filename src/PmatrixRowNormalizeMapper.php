<?php

require_once (dirname(__FILE__) . "/PmatrixLogger.php");

if (count($argv) != 1) {
	if(count($argv)==4){
		$mapper = new PmatrixRowNormalizeMapper($argv[1], $argv[2], $argv[3]);
		$mapper -> main();
	}else if(count($argv)==8){
		$mapper = new PmatrixRowNormalizeMapper($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7]);
		$mapper -> main();
	}
	
}

class PmatrixRowNormalizeMapper {
	private $df_min_support = 2;
	private $df_max_support = -1;

	private $all_tf_min_support = 0.00001;
	private $all_tf_max_support = -1;
	private $single_tf_min_support = 0.000001;
	private $single_tf_max_support = -1;

	private $norm = 2;

	/**
	 * $norm : 归一化方式
	 * $df_min_support，不为空的纬度min格式
	 * 
	 * $all_tf_min_support,全部纬度值和的min值
	 * 
	 * $single_tf_min_support 单个纬度min值
	 */
	function __construct($norm = null, $df_min_support = null, $df_max_support = null, $all_tf_min_support = null, $all_tf_max_support = null, $single_tf_min_support = null, $single_tf_max_support = null) {
		// if (($df_min_support != null && !is_numeric($df_min_support)) || ($df_max_support != null && !is_numeric($df_max_support))) {
		// PmatrixLogger::error("util_normalize_mapper encounter not numberic parameters df_min_support:{$df_min_support}\tdf_max_support:{$df_max_support}");
		// exit ;
		// } else if ($df_min_support != null && $df_min_support < 0 && $df_min_support != -1) {
		// PmatrixLogger::error("util_normalize_mapper encounter error df_min_support:{$df_min_support}");
		// exit ;
		// } else if ($df_max_support != null && $df_max_support <= 0 && $df_max_support != -1) {
		// PmatrixLogger::error("util_normalize_mapper encounter error df_max_support:{$df_max_support}");
		// exit ;
		// } else if ($df_min_support != null && $df_max_support != null && $df_max_support != -1 && $df_max_support < $df_min_support) {
		// PmatrixLogger::error("util_normalize_mapper encounter error df_min_support>df_max_support:{$df_min_support}>{$df_max_support}");
		// exit ;
		// } else if ($norm != null && (!is_numeric($norm) || $norm <= 0)) {
		// PmatrixLogger::error("util_normalize_mapper encounter error parameter norm:{$norm}");
		// exit ;
		// }
		$this -> norm = ($norm == null) ? $this -> norm : $norm;
		$this -> df_min_support = ($df_min_support == null) ? $this -> df_min_support : $df_min_support;
		$this -> df_max_support = ($df_max_support == null) ? $this -> df_max_support : $df_max_support;
		$this -> all_tf_min_support = ($all_tf_min_support == null) ? $this -> all_tf_min_support : $all_tf_min_support;
		$this -> all_tf_max_support = ($all_tf_max_support == null) ? $this -> all_tf_max_support : $all_tf_max_support;
		$this -> single_tf_min_support = ($single_tf_min_support == null) ? $this -> single_tf_min_support : $single_tf_min_support;
		$this -> single_tf_max_support = ($single_tf_max_support == null) ? $this -> single_tf_max_support : $single_tf_max_support;
		$separator = "\n\t\t";
		PmatrixLogger::info("PmatrixRowNormalizeMapper using paramters" . $separator . "norm:" . $this -> norm . $separator . $df_min_support . "df_min_support:" . $this -> df_min_support . $separator . $df_max_support . "df_max_support:" . $this -> df_max_support . $separator . $all_tf_min_support . "all_tf_min_support:" . $this -> all_tf_min_support . $separator . $all_tf_max_support . "all_tf_max_support:" . $this -> all_tf_max_support . $separator . $single_tf_min_support . "single_tf_min_support:" . $this -> single_tf_min_support . $separator . $single_tf_max_support . "single_tf_max_support:" . $this -> single_tf_max_support);
	}

	function main() {
		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line, PHP_EOL);
			list($key, $vector) = explode(chr(9), $line);
			$vector = json_decode($vector, "r");
			if (!$this -> filter($vector)) {
				PmatrixLogger::counter("debug", "support_filter", 1);
				continue;
			}
			$vector = $this -> normalize($vector);
			if ($vector == null) {
				PmatrixLogger::counter("debug", "normalize_filter", 1);
				continue;
			}
			echo $key, "\t",       json_encode($vector), PHP_EOL;
		}
	}

	private function filter(&$vector) {
		foreach ($vector as $key=>$single_value) {
			if ($single_value < $this -> single_tf_min_support) {
				PmatrixLogger::counter("debug", "remove_single_support_filter_single_tf_min", 1);
				unset($vector[$key]);
			} else if ($this -> single_tf_max_support != -1 && $single_value > $this -> single_tf_max_support) {
				PmatrixLogger::counter("debug", "remove_single_support_filter_single_tf_max", 1);
				unset($vector[$key]);
			}
		}
		
		$dimension = count($vector);
		if ($dimension < $this -> df_min_support) {
			PmatrixLogger::counter("debug", "support_filter_df_min", 1);
			return false;
		} else if ($this -> df_max_support != -1 && $dimension > $this -> df_max_support) {
			PmatrixLogger::counter("debug", "support_filter_df_max", 1);
			return false;
		}
		$all_sum = array_sum($vector);
		if ($all_sum < $this -> all_tf_min_support) {
			PmatrixLogger::counter("debug", "support_filter_all_tf_min", 1);
			return false;
		} else if ($this -> all_tf_max_support != -1 && $all_sum > $this -> all_tf_max_support) {
			PmatrixLogger::counter("debug", "support_filter_all_tf_max", 1);
			return false;
		}
		return true;
	}

	private function normalize($vector) {
		$norm_vector = array();
		$sum = 0;
		foreach ($vector as $key => $value) {
			if ($value == 0) {
				continue;
			}
			$value = pow($value, $this -> norm);
			$norm_vector[$key] = $value;
			$sum += $value;
		}
		if ($sum == 0) {
			return null;
		}
		foreach ($norm_vector as $key => &$value) {
			$value = pow($value * 1.0 / $sum, 1.0 / $this -> norm);
		}
		return $norm_vector;
	}

}

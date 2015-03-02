<?php

require_once (dirname(__FILE__) . "/PmatrixLogger.php");
require_once (dirname(__FILE__) . "/PmatrixUtil.php");

if (count($argv) != 1) {
    $reducer = new PmatrixMultiplicationPostMergeReducer();
    $reducer -> main();
}

class PmatrixMultiplicationPostMergeReducer {
    
    function __construct() {
    }

    function main() {
        $current_key = null;
        $current_value = 0;

        while (($line = fgets(STDIN)) !== false) {
            $line = trim($line, PHP_EOL);
            list($key, $value) = explode("\t", $line);
            if ($current_key == null) {
                $current_key = $key;
                $current_value = $value;
            } else if ($current_key == $key) {
                $current_value += $value;
            } else {
                $this -> print_result($current_key, $current_value);
                $current_key = $key;
                $current_value = $value;
            }
        }
        $this -> print_result($current_key, $current_value);
    }

    function print_result($key, $value) {
        if ($key != null) {
            $keys = json_decode($key);
            echo $keys[0], "\t", $keys[1], "\t", $value, PHP_EOL;
        }
    }

}

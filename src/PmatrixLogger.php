<?php

class PmatrixLogger {

    static function error($msg, $file = null) {
        file_put_contents("php://stderr", "ERROR:\t" . $msg . "\t" . $file . PHP_EOL);
    }

    static function info($msg, $file = null) {
        file_put_contents("php://stderr", "INFO:\t" . $msg . "\t" . $file . PHP_EOL);
    }

    static function warn($msg, $file = null) {
        file_put_contents("php://stderr", "WARN:\t" . $msg . "\t" . $file . PHP_EOL);
    }

    static function counter($group, $key, $value) {
        file_put_contents('php://stderr', "reporter:counter:{$group},{$key},{$value}\n");
    }
}

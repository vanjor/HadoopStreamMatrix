<?php
require_once (dirname(__FILE__) . "/FileReaderIterator.php");

class PmatrixDicLoader {
    /**
     * Load dictionary
     * return dictionary map, key=>value, value is df count etc. for key
     */
    public static function loadDic($file_or_dir) {
        $fileReader = new FileReaderIterator($file_or_dir);
        $fileReader -> setSkipFileOrDirPatterns("/^_|^\./u");
        $dic = array();
        foreach ($fileReader as $line) {
            list($key, $value) = explode("\t", $line);
            $dic[$key] = $value;
        }
        return $dic;
    }

}

<?php
/*
 * FileReaderIterator
 * Read File or Files under Directory line by line in recursively
 *
 * Iterator reference http://php.net/manual/zh/class.iterator.php
 * Usage demo:
 * $reader = new FileReaderIterator("/tmp");
 * foreach($reader as $line){
 *    echo $line,"\n";
 * }
 */

class FileReaderIterator implements Iterator {
    private $filePath = null;
    private $fileList = null;

    private $totalLineCount = 0;
    private $position = 0;

    private $fileListLeft = null;
    private $currentFilePath = null;
    private $currentFileOp = null;
    private $currentLine = null;

    private $skipFileOrDirPattern = null;
    private $mark = false;
    private $recursive = false;

    /**
     * $filePath str for the file path|directory path
     */
    public function __construct($filePath) {
        $this -> filePath = $filePath;
    }

    /**
     * $patterns_array array of str regex patterns, e.g.\/.*id.*|.*test.*\/i
     * the file for dir which match the patterns will be skipped.
     */
    public function setSkipFileOrDirPatterns($regPattern) {
        $this -> skipFileOrDirPattern = $regPattern;
    }

    /**
     * travel dir recursive, default is false
     */
    public function setRecursive($isRecursive) {
        $this -> recursive = $isRecursive;
    }

    function rewind() {
        $this -> fileList = self::listFiles($this -> filePath, $this -> recursive, $this -> skipFileOrDirPattern);
        $this -> fileListLeft = $this -> fileList;
        $this -> next();
    }

    function current() {
        return $this -> currentLine;
    }

    function key() {
        return $this -> position;
    }

    function next() {
        if ($this -> currentFileOp == null) {
            if (!empty($this -> fileListLeft)) {
                $this -> currentFilePath = array_pop($this -> fileListLeft);
                $this -> currentFileOp = fopen($this -> currentFilePath, "r");
                $this -> next();
            } else {
                // this is the final quiting
                $this -> currentLine = null;
                return;
            }
        } else if (feof($this -> currentFileOp)) {
            fclose($this -> currentFileOp);
            $this -> currentFilePath = null;
            $this -> currentFileOp = null;
            $this -> mark = true;
            $this -> next();
        } else {
            $this -> currentLine = fgets($this -> currentFileOp);
            // read files to the end
            if ($this -> currentLine === false) {
                $this -> next();
            } else {
                $this -> currentLine = str_replace(PHP_EOL, '', $this -> currentLine);
            }
            $this -> position++;
        }
    }

    function valid() {
        return $this -> currentLine !== null;
    }

    public static function listFiles($fileOrDirPath, $recursive = false, $skipFileOrDirPattern = null) {
        $fileArray = array();
        if (is_file($fileOrDirPath)) {
            if ($skipFileOrDirPattern == null || preg_match($skipFileOrDirPattern, pathinfo($fileOrDirPath, PATHINFO_BASENAME), $matches) === 0) {
                $fileArray[] = $fileOrDirPath;
            }
        } else if (is_dir($fileOrDirPath)) {
            $dh = opendir($fileOrDirPath);
            $tmpFileArray = array();
            while (($subFile = readdir($dh)) !== false) {
                // skip linux like file dir
                if ($subFile == '.' || $subFile == '..') {
                    continue;
                } else {
                    $subFilePath = $fileOrDirPath . DIRECTORY_SEPARATOR . $subFile;
                    if (!$recursive && is_dir($subFilePath)) {
                        // skipped
                    } else {
                        $tmpFileArray[] = $fileOrDirPath . DIRECTORY_SEPARATOR . $subFile;
                    }
                }
            }
            closedir($dh);
            foreach ($tmpFileArray as $subFile) {
                $rs = self::listFiles($subFile, $recursive, $skipFileOrDirPattern);
                $fileArray = array_merge($fileArray, $rs);
            }
        }
        return $fileArray;
    }
}

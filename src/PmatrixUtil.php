<?php

class PmatrixUtil {
	
	static function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	static function endsWith($haystack, $needle) {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

}

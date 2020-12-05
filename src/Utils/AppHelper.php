<?php

namespace ProductsImporter\Utils;

class AppHelper
{
	/**
	 * returns actual Date
	 * @return string
	 * @throws \Exception
	 */
	public static function getActualDate()
	{
		$date = new \DateTime('now');
		return $date->format('Y-m-d H:i:s');
	}

	public static function slug($title, $separator = '-')
	{
		// Convert all dashes/underscores into separator
		$flip = $separator === '-' ? '_' : '-';
		$title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
		// Replace @ with the word 'at'
		$title = str_replace('@', $separator . 'at' . $separator, $title);
		// Remove all characters that are not the separator, letters, numbers, or whitespace.
		$title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));
		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
		return trim($title, $separator);
	}

    /**
     * @param $haystack
     * @param $needle
     * @param  bool  $caseSensitive
     * @return bool
     */
    public static function contains($haystack, $needle, $caseSensitive = false) {
        if ($caseSensitive) {
            return (strpos($haystack, $needle) !== false);
        } else {
            return (stripos($haystack, $needle) !== false);
        }
    }
}

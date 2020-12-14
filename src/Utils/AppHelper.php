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
    $unwanted_array = ['ś' => 's', 'ą' => 'a', 'ć' => 'c', 'ç' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ź' => 'z', 'ż' => 'z',
                       'Ś' => 's', 'Ą' => 'a', 'Ć' => 'c', 'Ç' => 'c', 'Ę' => 'e', 'Ł' => 'l', 'Ń' => 'n', 'Ó' => 'o', 'Ź' => 'z', 'Ż' => 'z']; // Polish letters
    $str = strtr($title, $unwanted_array);

    $slug = strtolower(trim(preg_replace('/[\s-]+/', $separator,
      preg_replace('/[^A-Za-z0-9-]+/', $separator, preg_replace('/[&]/', 'and',
        preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $separator));

    return $slug;

  }

  /**
   * @param $haystack
   * @param $needle
   * @param bool $caseSensitive
   * @return bool
   */
  public static function contains($haystack, $needle, $caseSensitive = false)
  {
    if ($caseSensitive) {
      return (strpos($haystack, $needle) !== false);
    } else {
      return (stripos($haystack, $needle) !== false);
    }
  }
}

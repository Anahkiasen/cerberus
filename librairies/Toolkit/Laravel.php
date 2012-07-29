<?php
/**
 *
 * Laravel
 *
 * A suite of helpers for Laravel
 */

namespace Cerberus\Toolkit;

class Laravel
{
  public static function translate($key, $fallback = null)
  {
    if(!$fallback) $fallback = $key;

    return
      \Lang::line($key)->get(null,
      \Lang::line('validation.attributes.'.$key)->get(null,
      $fallback));
  }
}
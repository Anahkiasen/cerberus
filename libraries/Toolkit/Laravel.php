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

    // Search for the key itself
    $translation = \Lang::line($key)->get(null, '');

    // If not found, search in the field attributes
    if(!$translation) $translation =
      \Lang::line('validation.attributes.'.$key)->get(null,
      $fallback);

    return ucfirst($translation);
  }

  public static function paginate($object, $perPage = 20)
  {
    $pagination = \Paginator::make($object, $object->count(), $perPage);
    $object     = $pagination->results->for_page($pagination->page, $perPage)->get();

    return array($object, $pagination);
  }
}
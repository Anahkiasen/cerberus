<?php
namespace Cerberus\Models;

use \Eloquent;

class Elegant extends Eloquent
{
  // Attributes ---------------------------------------------------- /

  public function __toString()
  {
    return (string) $this->name;
  }
}

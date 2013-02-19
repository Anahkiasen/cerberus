<?php
namespace Cerberus;

use DB;
use Seeder;
use Underscore\Types\Arrays;
use Underscore\Types\String;

class Plant extends Seeder
{
  /**
   * Run the Seeder
   */
  public function run()
  {
    $data = $this->getSeeds();
    $data = array_values($data);

    $this->seed($data);
  }

  /**
   * Insert data into the database
   *
   * @param array $data Data
   */
  protected function seed($seeds)
  {
    $table = $this->getTableName();
    $model = $this->getModelName();

    // Format data
    $seeds = Arrays::each($seeds, function($seed) use ($model) {
      $seed = new $model($seed);
      return $seed->getAttributes();
    });

    // Slice for SQLite
    $slicer = floor(999 / sizeof($data[0]));
    $slices = array_chunk($data, $slicer);
    foreach ($slices as $slice) {
      DB::table($table)->insert($slice);
    }

    // Print number of seeds
    // $element = String::remove(get_called_class(), 'Seeder');
    // print sizeof($data).' '.lcfirst($element).' seeded successfully'.PHP_EOL;
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the name of the matching Model
   *
   * @return string
   */
  protected function getModelName()
  {
    return String::from(get_called_class())
      ->remove('Seeder')
      ->singular()
      ->obtain();
  }

  /**
   * Get the name of the matching table
   *
   * @return string
   */
  protected function getTableName()
  {
    return String::from(get_called_class())
      ->remove('Seeder')
      ->lower()
      ->obtain();
  }
}

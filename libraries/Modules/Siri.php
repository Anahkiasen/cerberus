<?php
/**
 *
 * Siri
 *
 * Transforms actions, objects and results
 * into readable sentences
 */
namespace Cerberus\Modules;

class Siri
{
  /**
   * The current message in instance
   * @var Siri
   */
  private static $message;

  /**
   * The different parts of the sentence being created
   * @var array
   */
  private $sentence = array();

  /**
   * Builds a restful message
   *
   * @param  string  $page   The current page
   * @param  string  $object The object's name
   * @param  string  $verb   The CRUD verb
   * @param  boolean $string The state of the action (failed or succeeded)
   * @param  string  $accord An accord to append the verb
   * @return string          A text message
   */
  public static function restful($page, $object, $verb, $state = true, $accord = null)
  {
    $bool  = $state ? 'success' : 'error';

    static::$message = new static();
    static::$message->noun($page);
    if($object) static::$message->subject($object);
    static::$message->state($bool)->verb($verb);

    return \Alert::$bool(static::$message, false);
  }

  /**
   * Fetch and display a message from session
   *
   * @return string A message in an alert
   */
  public static function displayMessage()
  {
    if(\Session::has('message')) {
      return \Session::get('message');
    }
  }

  /**
   * Accord a verb to its subject
   *
   * @param  string $subject A subject
   * @param  string $verb    A verb
   * @return string          An accorded subject
   */
  public static function accord($subject, $verb)
  {
    if(!$subject) return $verb;

    $language = \Config::get('application.language');
    switch($language) {
      case 'fr':
        if(in_array($subject, array('categories'))) {
          $verb .= 'e';
        }
        break;
    }

    return $verb;
  }
  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// CORE METHODS ////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Add a noun to the sentence
   *
   * @param  string $noun A noun
   */
  public function noun($noun)
  {
    $this->sentence['noun'] = __('cerberus::siri.nouns.'.$noun);

    return $this;
  }

  /**
   * Add a subject to the sentence
   *
   * @param  string $subject A subject
   */
  public function subject($subject)
  {
    $this->sentence['subject'] = '&laquo; ' .$subject. ' &raquo;';

    return $this;
  }

  /**
   * Change the state of the sentence, its outcome
   *
   * @param  string $state A state (success/error)
   */
  public function state($state)
  {
    $this->sentence['state'] = __('cerberus::siri.state.'.$state);

    return $this;
  }

  /**
   * Add a verb to the sentence
   *
   * @param  string $verb A verb
   */
  public function verb($verb)
  {
    $verb = __('cerberus::siri.verbs.'.$verb);
    $verb = static::accord(array_get($this->sentence, 'subject'), $verb);

    $this->sentence['verb'] = $verb;

    return $this;
  }

  /**
   * Renders the complete sentence
   *
   * @return string A sentence
   */
  public function __toString()
  {
    return implode(' ', $this->sentence);
  }
}
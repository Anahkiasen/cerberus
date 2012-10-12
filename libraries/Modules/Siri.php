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
   * An array of female nouns
   * @var array
   */
  private static $female = array(
    'category',
  );

  /**
   * Vowels
   * @var array
   */
  private static $vowels = array(
    'a', 'e', 'i', 'o', 'u', 'y',
  );

  /**
   * The different parts of the sentence being created
   * @var array
   */
  private $sentence = array();

  /**
   * The untranslated noun for helpers
   * @var string
   */
  private $noun = null;

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
    static::$message->noun($page, 'the');
    if($object) static::$message->subject($object);
    static::$message->state($bool)->verb($verb);

    return \Alert::$bool(static::$message, false);
  }

  /**
   * Creates an "Add a [something]" message
   *
   * @param string $noun The base noun
   */
  public static function add($noun)
  {
    static::$message = new static();
    static::$message->verb('add')->noun($noun, 'a');

    return static::$message->__toString();
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

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Checks if a noun is male or female
   *
   * @param  string  $noun A noun
   * @return boolean
   */
  public static function isFemale($noun)
  {
    $noun = \Str::singular($noun);

    return in_array($noun, static::$female);
  }

  /**
   * Check if a word starts with a vowel
   *
   * @param  string  $word A word
   * @return boolean
   */
  public static function startsWithVowel($word)
  {
    $letter = substr($word, 0, 1);

    return in_array($letter, static::$vowels);
  }

  /**
   * Check if a word ends with a vowel
   *
   * @param  string  $word A word
   * @return boolean
   */
  public static function endsWithVowel($word)
  {
    $letter = substr($word, -1);

    return in_array($letter, static::$vowels);
  }

  /**
   * Get the current language
   *
   * @return string The language in use
   */
  public static function lang()
  {
    return \Config::get('application.language');
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////////// RULES ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Accord an article to its noun
   *
   * @param  string $noun    A noun
   * @param  string $article An article
   * @return string          An accorded article
   */
  public static function accordArticle($noun, $article)
  {
    switch(static::lang()) {
      case 'fr':
        if(static::startsWithVowel($noun)) $article = substr($article, 0, -1)."'";
        break;
      case 'en':
        if(static::startsWithVowel($noun)) $article .= 'n';
        break;
    }

    return $article;
  }

  /**
   * Accord a verb to its noun
   *
   * @param  string $noun A noun
   * @param  string $verb A verb
   * @return string       An accorded verb
   */
  public static function accordVerb($noun, $verb)
  {
    switch(static::lang()) {
      case 'fr':
        if(static::isFemale($noun)) $verb .= 'e';
        break;
    }

    return $verb;
  }

  /**
   * Conjugate a verb according to a noun
   *
   * @param  string $noun The noun
   * @param  string $verb The verb
   * @return string       The conjugated verb
   */
  public static function conjugate($noun, $verb)
  {
    switch(static::lang()) {
      case 'fr':
        $verb = substr($verb, 0, -2).'é';
        $verb = static::accordVerb($noun, $verb);
        break;
      case 'en':
        if(!static::endsWithVowel($verb)) $verb .= 'e';
        $verb .= 'd';
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
   * @param  string $noun     A noun
   * @param  boolean $article Whether an article should be prepended
   */
  public function noun($noun, $article = null)
  {
    // Get noun
    $this->noun = $noun;
    $noun = __('cerberus::siri.nouns.'.$noun);

    if($article) {

      // Get the right article
      $sex = static::isFemale($this->noun) ? 'female' : 'male';
      $article = __('cerberus::siri.articles.'.$article.'.'.$sex);
      $article = static::accordArticle($this->noun, $article);

      // Add space
      $article .= ' ';
    }

    $this->sentence['noun'] = $article.$noun;

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

    // Conjugates if a noun precedes the verb
    if($this->noun) {
      $verb = static::conjugate($this->noun, $verb);
    }

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
    return ucfirst(implode(' ', $this->sentence));
  }
}
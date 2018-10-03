<?php

namespace Phing\Behat;

/**
 * Class FilterProfile. Represents a Behat CLI filter.
 *
 * @package Phing\Behat
 */
class FilterProfile extends \DataType {

  /**
   * The profile name.
   *
   * @var string
   *   The profile name.
   */
  protected $name;

  /**
   * The filter's tags.
   *
   * @var string
   *   The filter's tags.
   */
  protected $tags;

  /**
   * Set the profile name.
   *
   * @param string $str
   *   The profiles's name.
   *
   * @return self
   *   Return itself.
   */
  public function setName($str) {
    $this->name = (string) $str;
    return $this;
  }

  /**
   * Set the filter's tags.
   *
   * @param string $str
   *   The filter's $tags.
   *
   * @return self
   *   Return itself.
   */
  public function setTags($str) {
    $this->tags = trim($str, ', ');
    return $this;
  }

  /**
   * Get the profile's name.
   *
   * @return string
   *   The filter's name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set the filter's value from a text element.
   *
   * @param string $str
   *   The value of the text element.
   *
   * @return self
   *   Return itself.
   */
  public function addText($str) {
    return $this->setTags((string) $str);
  }

  /**
   * Get the filter's tags.
   *
   * @return array
   *   The filter's tags.
   */
  public function getText() {
    if (!$this->tags || strpos($this->tags, ',') === false) {
      return [];
    }

    return explode(',', $this->tags);
  }

}

<?php

namespace Drupal\maybe;

/**
 * Defines a wrapper class to call object methods without possibly causing exceptions.
 */
class Maybe {

  // The starting object on which we will be calling methods.
  private $object;

  // Create a new Maybe object and save the object we are acting on.
  function __construct($object = null) {
    $this->object = $object;
  }

  // Return the resulting object or value after all the methods have been called.
  function return() {
    return $this->object;
  }

  // Intercept any method called on the Maybe object other than return().
  function __call($method, $args) {

    // If we have an array, assume that we want to call the method on the first item.
    if(is_array($this->object)) {
      $this->object = reset($this->object);
      $this->__call($method, $args);
    }

    // If we have an object we want to call the desired method on it.
    else if (is_object($this->object)) {
      // Stop entities from throwing an error if you try to get a field they don't have.
      if ($method == 'get' && method_exists($this->object, 'hasField')) {
        if (!call_user_func_array(array($this->object,'hasField'), $args)) {
          $this->object = null;
          return $this;
        }
      }
      // Call the method and save the resulting object or value, or null if the function doesn't exist.
      $this->object = method_exists($this->object, $method) ? call_user_func_array(array($this->object, $method), $args) : null;
    }

    // Return the updated Maybe object so additional methods can be chained onto it.
    return $this;
  }
}

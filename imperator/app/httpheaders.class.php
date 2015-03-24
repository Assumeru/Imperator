<?php
namespace imperator;

class HttpHeaders {
	private $headers;

	/**
	 * Constructs a header object from a header string.
	 * 
	 * @param string $headers An HTTP header
	 */
	public function __construct($headers) {
		$lines = explode("\n", $headers);
		$headers = array();
		foreach($lines as $line) {
			$args = explode(':', $line,2);
			if(count($args) > 1) {
				$key = strtolower(trim($args[0]));
				if(!isset($headers[$key])) {
					$headers[$key] = array();
				}
				$values = explode(',', $args[1]);
				$values = array_map('trim', $values);
				$headers[$key] = array_merge($headers[$key], $values);
			}
		}
		$this->headers = $headers;
	}

	/**
	 * Checks if a key equals a value (case insensitive).
	 * 
	 * @param string $key The key to check
	 * @param string $value The value to equal
	 * @return bool True if the key's value is equal to the given value
	 */
	public function keyEquals($key, $value) {
		if(!empty($this->headers[$key])) {
			foreach($this->headers[$key] as $headerValue) {
				if(strcasecmp($headerValue, $value) === 0) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Checks if a key contains part of a value (case insensitive).
	 * 
	 * @param string $key The key to check
	 * @param string $value The value to equal
	 * @return bool True if the key's value contains the given value
	 */
	public function keyContains($key, $value) {
		if(!empty($this->headers[$key])) {
			foreach($this->headers[$key] as $headerValue) {
				if(strpos($headerValue, $value) !== false) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns the values for a given key.
	 * 
	 * @param string $key The key to retreive
	 * @return null|array An array of values or null if the key does not exist
	 */
	public function get($key) {
		if(isset($this->headers[$key])) {
			return $this->headers[$key];
		}
		return null;
	}

	/**
	 * Returns the first value for a given key.
	 * 
	 * @param string $key The key to retreive
	 * @return null|string The value of the key or null if the key does not exist
	 */
	public function getSingleton($key) {
		$value = $this->get($key);
		if(is_array($value)) {
			return $value[0];
		}
		return $value;
	}
}
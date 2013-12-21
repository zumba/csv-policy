<?php
namespace Zumba\CsvPolicy\Rule;

/**
 * CsvPolicy AbstractRule Class
 *
 * @abstract
 */
abstract class AbstractRule {

	/**
	 * List of values handed to the rule
	 *
	 * @access protected
	 * @var array
	 */
	protected $tokens = [];

	/**
	 * Get the error message regarding this rule
	 *
	 * Is passed the currently offending value
	 *
	 * @access public
	 * @param $input string
	 * @return string
	 */
	public function getErrorMessage($input) {
		return $input . ' is invalid.';
	}

	/**
	 * Returns the inputs that have been validated against
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	public function getTokens() {
		return $this->tokens;
	}

	/**
	 * Checks if the input has been parsed before
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function isUnique($input){
		$sumOccurrences = function($v, $n){
			return $v + (int)($n === $this->scalar);
		};
		return array_reduce($this->tokens, $sumOccurrences->bindTo((object)$input), 0) <= 1;
	}

	/**
	 * Store the input values and call the abstract validationLogic method
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function validate($input) {
		$this->tokens[] = $input;
		return $this->validationLogic($input);
	}

	/**
	 * Implement to enforce validation logic for this rule.
	 *
	 * @abstract
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	abstract public function validationLogic($input);
}
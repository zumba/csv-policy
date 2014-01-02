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
	 * Creates and increments a token key
	 *
	 * @access protected
	 * @param string $input
	 * @return void
	 */
	protected function addToken($input){
		if (empty($this->tokens[$input])){
			$this->tokens[$input] = 0;
		}
		$this->tokens[$input]++;
	}

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
		return array_keys($this->tokens);
	}

	/**
	 * Checks if the input has been parsed before
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function isUnique($input){
		return empty($this->tokens[$input]) || $this->tokens[$input] <= 1;
	}

	/**
	 * Store the input values and call the abstract validationLogic method
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function validate($input) {
		$this->addToken($input);
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
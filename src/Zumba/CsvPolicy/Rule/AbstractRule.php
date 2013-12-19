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
    protected $tokens = array();

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
     * @access protected
     * @param mixed $input
     * @return boolean
     */
	protected function isUnique($input){

		// $input has already been stored in the tokens array, so we need to
		// reduce it to ensure that there is only one occurance,
		// instead of using something like array_unique, for eaxample.
		return array_reduce($this->tokens, function($v, $n) use ($input) {
			return $v + (int)($n === $input);
		}, 0) === 1;
	}

    /**
     * Store the input values
     *
     * @access public
     * @param mixed $input
     * @return void
     */
    public function validate($input) {
        $this->tokens[] = $input;
    }

    /**
     * Get the error message regarding this rule
     *
     * Is passed the currently offending value
     *
     * @abstract
     * @access public
     * @param $input string
     * @return string
     */
    abstract public function getErrorMessage($input);
}
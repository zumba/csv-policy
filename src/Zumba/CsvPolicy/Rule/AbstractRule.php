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
        $sumOccurrences = function($v, $n){
            return $v + (int)($n === $this->scalar);
        };
		return array_reduce($this->tokens, $sumOccurrences->bindTo((object)$input), 0) === 1;
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
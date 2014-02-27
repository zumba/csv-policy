<?php
namespace Zumba\CsvPolicy;

/**
 * CsvPolicy Rule Class
 */
class Rule {

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
	 * Loop through any behavior traits and executes them
	 *
	 * @access protected
	 * @param mixed $input
	 * @return boolean
	 */
	protected function behaviorLogic($input){
		$valid = true;
		$behaviors = class_uses($this);
		if (!empty($behaviors)){
			foreach($behaviors as $trait){
				$method = $this->parseBehaviorMethod($trait);
				$valid = $this->$method($input);
				if (!$valid){
					break;
				}
			}
		}
		return $valid;
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
	 * Returns a method name to call from the behavior
	 *
	 * @access protected
	 * @param string $trait A fully qualified behavior trait string
	 * @return string
	 * @throws LogicException If a behavior doesn't have a method matching it's name
	 */
	protected function parseBehaviorMethod($trait){
		$parts = explode('\\', $trait);
		$method = strtolower(array_pop($parts));
		if (!method_exists($trait, $method)) {
			throw new \LogicException("Behavior $trait does not have a method named $method");
		}
		return $method;
	}

	/**
	 * Validate an input against the rule.
	 *
	 * Stores the input values as tokens, calls validationLogic, then validates against behaviors.
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function validate($input) {
		$this->addToken($input);
		return $this->validationLogic($input) && $this->behaviorLogic($input);
	}

	/**
	 * Override to enforce validation logic for this rule.
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function validationLogic($input) {
		return true;
	}
}
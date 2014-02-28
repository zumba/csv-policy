<?php
namespace Zumba\CsvPolicy;

/**
 * CsvPolicy Rule Class
 */
class Rule {

	/**
	 * Numerically indexed list of behavior methods to call in behavior logic
	 *
	 * @access protected
	 * @var array
	 */
	protected $behaviorMethods = [];

	/**
	 * List of values handed to the rule
	 *
	 * @access protected
	 * @var array
	 */
	protected $tokens = [];

	/**
	 * Rule constructor
	 *
	 * @access public
	 * @param void
	 * @constructor
	 */
	public function __construct(){
		$this->parseBehaviorTraits();
	}

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
		$methods = $this->behaviorMethods;
		if (!empty($methods)){
			foreach($methods as $method){
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
	 * Returns the name of the method from a ReflectionMethod instance
	 *
	 * @access protected
	 * @param ReflectionMethod $image
	 * @return ReflectionClass
	 * @see Zumba\CsvPolicy\Rule::parseBehaviorTraits
	 */
	protected function getReflectionMethodName(\ReflectionMethod $image){
		return $image->name;
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
	 * Parses the rule for behavior traits and cahces the trait methods.
	 *
	 * @access protected
	 * @param void
	 * @return void
	 */
	protected function parseBehaviorTraits(){
		if (empty($this->behaviorMethods)){
			$traits = (new \ReflectionClass($this))->getTraits();
			if (!empty($traits)){
				$getName = [$this, 'getReflectionMethodName'];
				foreach($traits as $reflection){
					$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
					$this->behaviorMethods += array_map($getName, $methods);
				}
			}
		}
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
<?php
namespace Zumba\CsvPolicy\Behavior;

/**
 * CsvPolicy Unique Behavior
 */
trait Unique {

	/**
	 * Checks if the input has been parsed before
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function csvPolicyIsUnique($input){
		return empty($this->tokens[$input]) || $this->tokens[$input] <= 1;
	}
}
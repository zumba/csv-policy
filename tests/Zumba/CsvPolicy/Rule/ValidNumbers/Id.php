<?php
namespace Zumba\CsvPolicy\Rule\ValidNumbers;

use \Zumba\CsvPolicy\Rule\AbstractRule;

/**
 * Id Rule class used in tests
 */
class Id extends AbstractRule {

	/**
	 * Verifies that id column is numeric and unique
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function validationLogic($input) {
		return $this->isUnique($input);
	}
}
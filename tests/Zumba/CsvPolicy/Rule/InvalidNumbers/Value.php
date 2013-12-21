<?php
namespace Zumba\CsvPolicy\Rule\InvalidNumbers;

use \Zumba\CsvPolicy\Rule\AbstractRule;

/**
 * Value Rule class used in tests
 */
class Value extends AbstractRule {

	/**
	 * Verifies that value column is numeric
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function validationLogic($input) {
		return is_numeric($input);
	}
}
<?php
namespace Zumba\CsvPolicy\Rule\InvalidNumbers;

use \Zumba\CsvPolicy\Rule\AbstractRule;

/**
 * Text Rule class used in tests
 */
class Text extends AbstractRule {

	/**
	 * Verifies that text column is not empty
	 *
	 * @access public
	 * @param mixed $input
	 * @return boolean
	 */
	public function validationLogic($input) {
		return !empty($input);
	}
}
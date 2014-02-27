<?php
namespace Zumba\CsvPolicy\Rule\ValidNumbers;

use \Zumba\CsvPolicy\Rule;

/**
 * Value Rule class used in tests
 */
class Value extends Rule {

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
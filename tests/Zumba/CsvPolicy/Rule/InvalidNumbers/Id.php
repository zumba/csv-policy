<?php
namespace Zumba\CsvPolicy\Rule\InvalidNumbers;

use \Zumba\CsvPolicy\Rule;

/**
 * Id Rule class used in tests
 */
class Id extends Rule {

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
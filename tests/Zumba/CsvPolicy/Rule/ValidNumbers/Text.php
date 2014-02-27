<?php
namespace Zumba\CsvPolicy\Rule\ValidNumbers;

use \Zumba\CsvPolicy\Rule;

/**
 * Text Rule class used in tests
 */
class Text extends Rule {

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
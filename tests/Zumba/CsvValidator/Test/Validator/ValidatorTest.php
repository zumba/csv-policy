<?php

namespace Zumba\CsvValidator\Test\Validator;

use \Zumba\CsvValidator\Test\TestCase,
	\Zumba\CsvValidator\Validator;

/**
 * @group validator
 */
class ValidatorTest extends TestCase {

	public function testGetErrorsReturnsArray() {
		$validator = new Validator();
		$this->assertTrue(is_array($validator->getErrors()));
	}
}
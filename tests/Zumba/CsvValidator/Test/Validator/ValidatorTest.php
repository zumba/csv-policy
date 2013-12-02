<?php

namespace Zumba\CsvValidator\Test\Validator;

use \Zumba\CsvValidator\Test\TestCase,
	\Zumba\CsvValidator\Validator;

/**
 * @group validator
 */
class ValidatorTest extends TestCase {

	public function setUp() {
		$this->lib = new Validator();
	}

	public function testGetErrorsReturnsArray() {
		$this->assertTrue(is_array($this->lib->getErrors()));
	}

	public function testValidationFailsIfFileDoesNotExist() {
		$valid = $this->lib->isValid(FIXTURE_PATH . '/fake.csv');
		$this->assertFalse($valid, 'Validator::isValid should fail if file does not exist');
		$this->assertNotEmpty($this->lib->getErrors(), 'Validator should report an error if the file does not exist');
	}

	public function testValidCsv() {
		$valid = $this->lib->isValid(FIXTURE_PATH . '/valid.csv');
		$this->assertTrue($valid, 'Validator::isValid should pass if csv is valid');
	}

	public function testSetDelimiter() {
		$this->lib->config(array('delimiter' => ';'));
		$valid = $this->lib->isValid(FIXTURE_PATH . '/semicolon.csv');
		$this->assertTrue($valid, 'Validator should be able to configure the delimiter');
	}

	public function testSetEnclosure() {
		$this->lib->config(array('enclosure' => '|'));
		$valid = $this->lib->isValid(FIXTURE_PATH . '/pipe_quotes.csv');
		$this->assertTrue($valid, 'Validator should be able to configure the enclosure');
	}

	public function testSetEscape() {
		$this->lib->config(array('escape' => ':'));
		$valid = $this->lib->isValid(FIXTURE_PATH . '/colon_escape.csv');
		$this->assertTrue($valid, 'Validator should be able to configure the escape character');
	}

	public function testCanDefineRequiredFields() {
		$this->lib->config(array('requiredFields' => array('column_one')));
		$valid = $this->lib->isValid(FIXTURE_PATH . '/valid.csv');
		$this->assertTrue($valid, 'Validator should pass if required fields are present');

		$this->lib->config(array('requiredFields' => array('fake_column')));
		$valid = $this->lib->isValid(FIXTURE_PATH . '/valid.csv');
		$this->assertFalse($valid, 'Validator should fail if required fields are not present');
	}
}
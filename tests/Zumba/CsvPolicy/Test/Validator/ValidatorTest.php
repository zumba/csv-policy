<?php

namespace Zumba\CsvPolicy\Test\Validator;

use \Zumba\CsvPolicy\Test\TestCase,
	\Zumba\CsvPolicy\Validator;

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
		$this->lib->config(['delimiter' => ';']);
		$valid = $this->lib->isValid(FIXTURE_PATH . '/semicolon.csv');
		$this->assertTrue($valid, 'Validator should be able to configure the delimiter');
	}

	public function testSetEnclosure() {
		$this->lib->config(['enclosure' => '|']);
		$valid = $this->lib->isValid(FIXTURE_PATH . '/pipe_quotes.csv');
		$this->assertTrue($valid, 'Validator should be able to configure the enclosure');
	}

	public function testSetEscape() {
		$this->lib->config(['escape' => ':']);
		$valid = $this->lib->isValid(FIXTURE_PATH . '/colon_escape.csv');
		$this->assertTrue($valid, 'Validator should be able to configure the escape character');
	}

	public function testCanDefineRequiredFields() {
		$this->lib->config(['requiredFields' => ['column_one']]);
		$valid = $this->lib->isValid(FIXTURE_PATH . '/valid.csv');
		$this->assertTrue($valid, 'Validator should pass if required fields are present');

		$this->lib->config(['requiredFields' => ['fake_column']]);
		$valid = $this->lib->isValid(FIXTURE_PATH . '/valid.csv');
		$this->assertFalse($valid, 'Validator should fail if required fields are not present');
	}
}
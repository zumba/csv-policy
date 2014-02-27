<?php

namespace Zumba\CsvPolicy\Test\Behavior;

use \Zumba\CsvPolicy\Test\TestCase;

/**
 * Class used in tests
 */
class UniqueMock extends \Zumba\CsvPolicy\Rule {
	use \Zumba\CsvPolicy\Behavior\Unique;
}

/**
 * @group rule
 */
class UniqueTest extends TestCase {

	public function setUp() {
		$this->rule = new UniqueMock();
	}

	public function testIsUnique(){
		// nothing has been validated yet, so it is unique
		$this->assertTrue($this->rule->unique('a'));

		// only one copy exists upon first validation
		$this->assertTrue($this->rule->validate('a'));

		// second validation fails
		$this->assertFalse($this->rule->validate('a'));

		// direct call now also reveals non-unique
		$this->assertFalse($this->rule->unique('a'));
	}
}
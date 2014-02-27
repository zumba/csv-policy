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
		// only one copy exists upon first validation
		$this->assertTrue($this->rule->validate('a'));

		// second validation fails
		$this->assertFalse($this->rule->validate('a'));
	}
}
<?php

namespace Zumba\CsvPolicy\Test\Rule\AbstractRuleTest;

use \Zumba\CsvPolicy\Test\TestCase;

/**
 * @group rule
 */
class AbstractRuleTest extends TestCase {

	public function setUp() {
		$this->rule = $this->getMock('\\Zumba\\CsvPolicy\\Rule\\AbstractRule', array('validationLogic'));
	}

	public function testValidateMethodTracksTokens() {
		$this->rule->validate('a');
		$this->rule->validate('b');
		$this->rule->validate('c');

		$this->assertEquals(['a', 'b', 'c'], $this->rule->getTokens());
	}

	public function testIsUnique(){
		$this->assertTrue($this->rule->isUnique('a'));

		$this->rule->validate('a');
		$this->assertTrue($this->rule->isUnique('a'));

		$this->rule->validate('a');
		$this->assertFalse($this->rule->isUnique('a'));

	}

	public function testValidateCallsValidationLogic(){
		$once = $this->atLeastOnce();
		$return = $this->returnValue(true);
		$this->rule->expects($once)->method('validationLogic')->will($return);

		$this->rule->validate('a');
	}

	public function testGetErrorMessageReturnsString(){
		$this->assertTrue(gettype($this->rule->getErrorMessage('explode')) === 'string');
	}
}
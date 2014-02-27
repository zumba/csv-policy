<?php
namespace Zumba\CsvPolicy\Rule\ValidNumbers;

use \Zumba\CsvPolicy\Rule;

/**
 * Id Rule class used in tests
 */
class Id extends Rule {
	use \Zumba\CsvPolicy\Behavior\Unique;
}
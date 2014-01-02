# CsvPolicy

![travis-ci](https://api.travis-ci.org/zumba/csv-policy.png)

**CsvPolicy** is a simple, policy-based validation library for CSV files.  Create rules for columns in a CSV file and the validator will load them, parse the file, and report rule violations.

*CsvPolicy requires PHP >= 5.4*

## Example
Suppose you have the following business requirements for a CSV file named `products.csv` that will be uploaded to your application:

1. It must contain the following column names: `id`, `value`
2. It must contain at least one of the following columns: `upc`, `code`, `stock`
3. The `id` column's values must be unique.
4. The `value` column must only contain numeric values

CsvPolicy allows you to model these rules as classes.  Required and optional fields are handled by the `Validator` class directly, so we only need to define rules for the `id` column and the `value` column.

The folowing rule will ensure that the values in the `id` field are unique:
```php
namespace Zumba\CsvPolicy\Rule\Products;

class Id extends \Zumba\CsvPolicy\Rule\AbstractRule {
    public function getErrorMessage($input) {
		return 'Id must be unique.  Duplicate found: ' . $input;
	}

	public function validationLogic($input) {
		// AbstractRule::isUnique is a built-in checker that your rule can use
		return $this->isUnique($input);
	}
}
```

Implementing the `AbstractRule::validationLogic` method will define the policy for that column.  The validator will use this method to check all of the values in the CSV for the column.
```php
namespace Zumba\CsvPolicy\Rule\Products;

class Value extends \Zumba\CsvPolicy\Rule\AbstractRule {
	public function getErrorMessage($input) {
		return 'Value must only contain numeric values. Non-numeric value found: ' . $input;
	}
	public function validationLogic($input) {
		return is_numeric($input);
	}
}
```

Finally, you configure your validator and use it to check if the CSV file is valid:
```php
$validator = new \Zumba\CsvPolicy\Validator();
$validator->config([
	'rulesPath' => './path/to/custom/rules'
	'requiredFields' => [

		// string field names are always required
		'id', 'value',

		// arrays of field names indicate that at least one field is required, but not all
		['upc', 'code', 'stock']
	]
]);

$valid = $validator->isValid('./path/to/products.csv');
if (!$valid){
    // errors is an array of rules violations
	$errors = $validator->getErrors();
}
```
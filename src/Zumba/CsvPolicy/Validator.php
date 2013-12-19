<?php

namespace Zumba\CsvPolicy;

use Doctrine\Common\Inflector\Inflector;

/**
 * CsvPolicy Validation Class
 *
 * Based loosely on these:
 *  * https://github.com/javilumbrales/csv_file_validation
 *  * https://github.com/goodby/csv
 */
class Validator {

	/**
	 * Collection of column indexes
	 *
	 * @access protected
	 * @var array
	 */
	protected $columnIndexes = [];

	/**
	 * Delimiter character
	 *
	 * @access protected
	 * @var string
	 */
	protected $delimiter = ',';

	/**
	 * Enclosure character
	 *
	 * @access protected
	 * @var string
	 */
	protected $enclosure = '"';

	/**
	 * Collection of validation errors
	 *
	 * @access protected
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Escape character
	 *
	 * @access protected
	 * @var array
	 */
	protected $escape = '\\';

	/**
	 * Collection of required fields
	 *
	 * @access protected
	 * @var array
	 */
	protected $requiredFields = [];

	/**
	 * Collection of rules objects
	 *
	 * @access protected
	 * @var array
	 */
	protected $rules = [];

	/**
	 * The location of the rule class files.
	 *
	 * This should be an absolute path to the directory containing your `CsvPolicy/Rule`
	 * directory, without a trailing slash. For example, the path for the following rule file:
	 *
	 * /Users/you/project/app/Lib/CsvPolicy/Rule/SomeFile/SomeRule.php
	 *
	 * should be:
	 *
	 * /Users/you/project/app/Lib
	 *
	 * @var string
	 */
	protected $rulesPath = '';

	/**
	 * Validator Class constructor
	 *
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		$this->config($config);
	}

	/**
	 * Iterates over the csv, checking rules
	 *
	 * @access protected
	 * @param string $file
	 * @return void
	 */
	protected function analyze($file){
		$handle = fopen($file, 'r');
		$delimiter = $this->delimiter;
		$enclosure = $this->enclosure;
		$escape = $this->escape;

		//Parse the first row, instantiate all the validators
		$row = $this->parseFirstRow($this->fgetcsv($handle));
		if(empty($this->errors)) {

			$this->loadRules($row, $file);
			while(($data = $this->fgetcsv($handle)) !== false) {
				while(($params = each($data))) {
					$this->checkRule($params);
					if (!empty($this->errors)){
						break 2;
					}
				}
			}
		}
		fclose($handle);
	}

	/**
	 * Verifies that required fields are all present and logs errors if missing.
	 *
	 * @access protected
	 * @param array $row
	 * @return void
	 */
	protected function checkRequiredFields(array $row){
		$required = $this->requiredFields;

		// Fields that must all be present
		$and = array_filter($required, 'is_string');

		// Fields where at least one must be present
		$or = array_filter($required, 'is_array');

		/**
		 * The following block checks if required fields are all present
		 * and logs any errors errors
		 */
		if (
			// number of fields is less than the required count
			count($row) < count($required) ||

			// $or fields are required, but not present
			(($orFieldsExist = !empty($or)) && !$this->orFieldsValid($or, $row)) ||

			// remaining fields are not present
			count(array_intersect($and, $row)) !== count($and)
		){
			$this->logMissingRequiredFields($row, $and, $or);
		}
	}

	/**
	 * Checks if a rule for the $params['key'] exists and validates.
	 *
	 * Logs errors from the rule if invalid.
	 *
	 * @access protected
	 * @param array $params ['key' => ?, 'value' => ?]
	 * @return void
	 */
	protected function checkRule(array $params){
		$value = trim($params['value']);
		$key = $params['key'];
		if(isset($this->rules[$key])) {
			$rule = $this->rules[$key];
		 	if (!$rule->validate($value)){
				$this->errors[] = $rule->getErrorMessage($value);
			}
		}
	}

	/**
	 * Configuration method
	 *
	 * options:
	 * * string delimiter
	 * * string enclosure
	 * * string escape
	 * * array requiredFields
	 *
	 * @access public
	 * @param array $options
	 * @return void
	 */
	public function config($options) {
		foreach($options as $key => $config){
			$method = 'set' . Inflector::classify($key);
			if (method_exists($this, $method)) {
				$this->$method($config);
			}
		}
	}

	/**
	 * Given a file pointer resource, return the next row from the file
	 *
	 * @access public
	 * @param Resource $handle
	 * @return array|null|false
	 * @throws \InvalidArgumentException If $handle is not a valid resource
	 */
	public function fgetcsv($handle){
		$result = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape);
		if ($result === null){
			throw new \InvalidArgumentException('File pointer resource used in fgetcsv is invalid');
		}
		return $result;
	}

	/**
	 * Return the array of errors
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Checks a CSV file for validity based on defined policies.
	 *
	 * Stops on the first violation
	 *
	 * @access public
	 * @param string $file Full path
	 * @return boolean
	 */
	public function isValid($file) {
		if (file_exists($file)){
			$this->analyze($file);
		} else {
			$this->errors[] = 'File ' . $file . ' does not exist.';
		}

		return empty($this->errors);
	}

	/**
	 * Instantiates and loads a single rule into the Validator::$rules array
	 *
	 * @access protected
	 * @param int $key
	 * @param string $Rule A fully qualified class name
	 * @return void
	 */
	protected function loadRule($key, $Rule){
		if(class_exists($Rule)) {
			$this->rules[$key] = new $Rule();
		} else {
			$this->errors[] = sprintf('Rule file found, but could not load rule class: "%s".', $Rule);
		}
	}

	/**
	 * Loads all of the rule validators
	 *
	 * @access protected
	 * @param array $row
	 * @param string $file
	 * @return void
	 */
	protected function loadRules(array $row, $file){
		$info = pathinfo($file);
		$namespace = Inflector::classify($info['filename']);
		$rulesPath = $this->rulesPath;

		foreach ($row as $key => $value) {
			$name = Inflector::classify($value);
			$relativePath = "/Zumba/CsvPolicy/Rule/$namespace/$name";
			$filename = $rulesPath . $relativePath . '.php';
			if (file_exists($filename)){
				require_once $filename;
				$Rule = str_replace('/', '\\', $relativePath);
				$this->loadRule($key, $Rule);
			}
			$this->columnIndexes[$key] = $value;
		}
	}

	/**
	 * Logs missing required fields
	 *
	 * @access protected
	 * @param array $row
	 * @param array $and
	 * @param array $or
	 * @return void
	 */
	protected function logMissingRequiredFields(array $row, array $and = [], array $or = []) {
		if (!empty($and)){
			$required = implode('", "', array_diff($and, $row));
			if (!empty($required)){
				$this->errors[] = sprintf(
					'The following missing columns are required: "%s".',
					$required
				);
			}
		}
		if(!empty($or)){
			$logOrError = function($fields) use ($row){
				$diff = array_diff($fields, $row);
				if (!count($diff)){
					$this->errors[] = sprintf(
						'At least one of the following columns is required: "%s".',
						implode($diff, '", "')
					);
				}
			};
			array_walk($or, $logOrError->bindTo($this));
		}
	}

	/**
	 * Normalizes the data in a row.
	 *
	 * @access protected
	 * @param array $row
	 * @return array
	 */
	protected function normalizeRow(array $row) {
		return array_filter(array_map('trim', array_map('strtolower', $row)));
	}

	/**
	 * Checks if arrays of fields in `$or` have at least one value present in `$fields`.
	 *
	 * @access protected
	 * @param array $or
	 * @param array $fields
	 * @return boolean
	 */
	protected function orFieldsValid(array $or, array $fields) {
		$valid = true;
		foreach($or as $requiredFields){
			$valid = count(array_intersect($requiredFields, $fields)) > 0;
			if (!$valid){
				break;
			}
		}
		return $valid;
	}

	/**
	 * Parses the first row
	 *
	 * Checks for duplicate column names and ensures all required fields are present
	 *
	 * @param array $data
	 * @access protected
	 * @return array $row normalized
	 */
	protected function parseFirstRow(array $row) {
		$row = $this->normalizeRow($row);

		$duplicateKeys = array_diff_key($row, array_unique($row));

		if(!empty($duplicateKeys)) {
			$duplicateKeys = implode($duplicateKeys, '", "');
			$this->errors[] = sprintf('The following columns are duplicated: "%s".', $duplicateKeys);
		}

		if(empty($this->errors)) {
			$this->checkRequiredFields($row);
		}
		return $row;
	}

	/**
	 * Sets the delimiter
	 *
	 * @access public
	 * @param string $delimiter
	 * @return \Zumba\CsvPolicy\Validator instance
	 */
	public function setDelimiter($delimiter = ','){
		$this->delimiter = $delimiter;
		return $this;
	}

	/**
	 * Sets the enclosure
	 *
	 * @access public
	 * @param string $enclosure
	 * @return \Zumba\CsvPolicy\Validator instance
	 */
	public function setEnclosure($enclosure = '"'){
		$this->enclosure = $enclosure;
		return $this;
	}

	/**
	 * Sets the escape
	 *
	 * @access public
	 * @param string $escape
	 * @return \Zumba\CsvPolicy\Validator instance
	 */
	public function setEscape($escape = '\\'){
		$this->escape = $escape;
		return $this;
	}

	/**
	 * Sets the required fields
	 *
	 * @access public
	 * @param array $requiredFields
	 * @return \Zumba\CsvPolicy\Validator instance
	 */
	public function setRequiredFields(array $requiredFields = []){
		$this->requiredFields = $requiredFields;
		return $this;
	}

	/**
	 * Sets the rules path
	 *
	 * @access public
	 * @param String $path
	 * @return \Zumba\CsvPolicy\Validator instance
	 */
	public function setRulesPath($path) {
		$this->rulesPath = $path;
		return $this;
	}
}
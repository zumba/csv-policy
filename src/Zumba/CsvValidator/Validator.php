<?php

namespace CsvValidator;

/**
 * Csv Validation Class
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
	protected $columnIndexes = array();

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
	protected $errors = array();

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
	protected $requiredFields = array();

	/**
	 * Collection of rules objects
	 *
	 * @access protected
	 * @var array
	 */
	protected $rules = array();

	/**
	 * The location of the rule class files.
	 *
	 * This should be an absolute path to the directory containing your `CsvValidator/Rule`
	 * directory, without a trailing slash. For example, the path for the following rule file:
	 *
	 * /Users/you/project/app/Lib/CsvValidator/Rule/SomeFile/SomeRule.php
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
	public function __construct(array $config = array()) {
		$this->config($config);
	}

	/**
	 * Iterates over the csv, checking rules
	 *
	 * @access  protected
	 * @param string $file
	 * @return [type]
	 */
	protected function analyze($file){
		$handle = fopen($file, 'r');
		$delimiter = $this->delimiter;
		$enclosure = $this->enclosure;
		$escape = $this->escape;

		//Parse the first row, instantiate all the validators
		$row = $this->parseFirstRow(fgetcsv($handle, 0, $delimiter, $enclosure, $escape));
		if(empty($this->errors)) {

			$this->loadRules($row, $file);

			$columnCount = count($this->columnIndexes);

			while(($data = fgetcsv($handle, 0, $delimiter)) !== false) {

				$errors = array();
				foreach ($data as $key => $value) {
					if($key >= $columnCount) {
						break;
					}
					$value = trim($value);
					if(isset($this->rules[$key]) && !$this->rules[$key]->validate($value)){
						$this->errors[] = $this->rules[$key]->getErrorMessage($value);
					}
				}
				if (!empty($this->errors)){
					break;
				}
			}
		}
		fclose($handle);
	}

	/**
	 * Configuration method
	 *
	 * options:
	 * * delimiter string
	 * * enclosure string
	 * * escape string
	 * * requiredFields array
	 *
	 * @access public
	 * @param array $options
	 * @return
	 */
	public function config($options) {
		foreach($options as $key => $config){
			$method = 'set' . \Inflector::camelize($key);
			if (method_exists($this, $method)) {
				$this->$method($config);
			}
		}
	}

	/**
	 * Return the array of errors
	 *
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Exits if/when an error is found
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
	 * Loads all of the rule validators
	 *
	 * @access protected
	 * @param array $row
	 * @param string $file
	 * @return void
	 */
	protected function loadRules(array $row, $file){
		$info = pathinfo($file);
		$namespace = \Inflector::classify($info['filename']);
		foreach ($row as $key => $value) {
			$name = \Inflector::classify($value);
			$filename = implode('/', array(
				$this->rulesPath, 'CsvValidator', 'Rule', $namespace, $name . '.php'
			));
			if (file_exists($filename)){
				require_once $filename;
				$Rule = implode('\\',  array('\\CsvValidator\\Rule', $namespace, $name));
				if(class_exists($Rule)) {
					$this->rules[$key] = new $Rule();
				}
			}
			$this->columnIndexes[$key] = $value;
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
			$required = $this->requiredFields;
			$columnCount = count($row);
			$requiredCount = count($required);

			// Fields that must all be present
			$and = array_filter($required, function($element){ return !is_array($element); });

			// Fields where at least one must be present
			$or = array_filter($required, function($element){ return is_array($element); });

			// The following large condition checks if required fields are not
			// present and logs the errors
			if (
				// number of fields is less than the required count
				$columnCount < $requiredCount ||

				// $or fields are required, but not present
				(!empty($or) && !$this->orFieldsValid($or, $row)) ||

				// remaining fields are not present
				count(array_intersect($and, $row)) !== count($and)
			){
				$required = implode(array_diff($and, $row), '", "');
				if (!empty($required)){
					$this->errors[] = sprintf(
						'The following missing columns are required: "%s".',
						$required
					);
				}
				foreach($or as $fields){
					if (count(array_intersect($fields, $row)) === 0){
						$required = implode(array_diff($fields, $row), '", "');
						$this->errors[] = sprintf(
							'At least one of the following columns is required: "%s".',
							$required
						);
					}
				}
			}
		}
		return $row;
	}

	/**
	 * Sets the delimiter
	 *
	 * @access public
	 * @param string $delimiter
	 * @return \CsvValidator\Validator current instance
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
	 * @return \CsvValidator\Validator current instance
	 */
	public function setEnclosure($enclosure = ','){
		$this->enclosure = $enclosure;
		return $this;
	}

	/**
	 * Sets the escape
	 *
	 * @access public
	 * @param string $escape
	 * @return \CsvValidator\Validator current instance
	 */
	public function setEscape($escape = ','){
		$this->escape = $escape;
		return $this;
	}

	/**
	 * Sets the required fields
	 *
	 * @access public
	 * @param array $requiredFields
	 * @return \CsvValidator\Validator current instance
	 */
	public function setRequiredFields(array $requiredFields = array()){
		$this->requiredFields = $requiredFields;
		return $this;
	}

	/**
	 * Sets the rules path
	 *
	 * @access public
	 * @param String $path
	 * @return \CsvValidator\Validator current instance
	 */
	public function setRulesPath($path) {
		$this->rulesPath = $path;
		return $this;
	}
}
<?php
namespace Helpers\View\Helper;

use Cake\Utility\Hash;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

class ResultHelper extends Helper
{
	protected $_fields = [];

    public $helpers = [
		'Number'
	];

	/**
	 * @todo Cache
	 *
	 * @param string $source
	 * @return string
	 */
    protected function _fields($source)
    {
		if(!isset($this->_fields[$source])) {
			$table = TableRegistry::get($source);
			$this->_fields[$source] = [];

			foreach ($table->schema()->columns() as $column) {
				$this->_fields[$source][$column] = $table->schema()->columnType($column);
			}
		}

        return $this->_fields[$source];
    }

	/**
	 *
	 * @param Entity $result
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public function type(Entity $result, $path, array $params = array())
	{
		// TODO
		//$primaryKey = 'id';
		//$primaryKey = $table->primaryKey();
		if(!isset($params['type'])) {
			$fields = $this->_fields($result->source());
			if (!empty($fields)) {
				$type = Hash::get($fields, $path);
			} else {
				$type = 'string';
			}
		} else {
			$type = $params['type'];
		}

		return $type;
	}

	/**
	 *
	 * @param Entity $result
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public function value(Entity $result, $path, array $params = array())
	{
		$type = $this->type($result, $path, $params);

		$value = Hash::get($result, $path);
		if($value !== null) {
			switch($type) {
				case 'integer':
					$value = $this->Number->format($value);
					break;
			}

			$value = (string)$value;
		}

		return $value;
	}

	/**
	 *
	 * @param integer $value
	 * @return array
	 */
	protected function _integerExtra($value)
	{
		$extra = [];

		if($value === 0) {
			$extra[] = 'zero';
		} else {
			$extra[] = $value > 0 ? 'positive' : 'negative';
		}

		return $extra;
	}

	/**
	 *
	 * @param string $value
	 * @return string
	 */
	protected function _stringExtra($value)
	{
		$extra = [];

		if($value === '') {
			$extra[] = 'empty';
		}

		return $extra;
	}

	/**
	 *
	 * @param boolean $value
	 * @return string
	 */
	protected function _booleanExtra($value)
	{
		$extra = [];

		if($value === true) {
			$extra[] = 'true';
		} else {
			$extra[] = 'false';
		}

		return $extra;
	}

	/**
	 *
	 * @todo classes (data type null|true|false|positive|negative|future|past|today)
	 *
	 * @param Entity $result
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public function extra(Entity $result, $path, array $params = array())
	{
		$extra = array();

		$type = $this->type($result, $path, $params);
		$value = Hash::get($result, $path);

		if($value === null) {
			$extra[] = 'null';
		} else {
			switch($type) {
				case 'boolean':
					$extra = array_merge($extra, $this->_booleanExtra($value));
					break;
				case 'integer':
					$extra = array_merge($extra, $this->_integerExtra($value));
					break;
				case 'string':
					$extra = array_merge($extra, $this->_stringExtra($value));
					break;
			}
		}

		return implode(' ', $extra);
	}
}
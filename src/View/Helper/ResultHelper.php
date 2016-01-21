<?php
namespace Helpers\View\Helper;

use Cake\Utility\Hash;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

class ResultHelper extends Helper
{
	protected $_fields = [];

    public $helpers = [
		'Number'
	];

	//@todo Cache
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

	public function type($result, $path, array $params = array())
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

	public function value($result, $path, array $params = array())
	{
		$type = $this->type($result, $path, $params);

		$value = Hash::get($result, $path);
		switch($type) {
			case 'integer':
				$value = $this->Number->format($value);
				break;
			default:
				$value = $value;
		}

		return $value;
	}

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

	protected function _stringExtra($value)
	{
		$extra = [];

		if($value === '') {
			$extra[] = 'empty';
		}

		return $extra;
	}

	public function extra($result, $path, array $params = array())
	{
		$extra = array();

		$type = $this->type($result, $path, $params);
		$value = Hash::get($result, $path);

		if($value === null) {
			$extra[] = 'null';
		} else {
			switch($type) {
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
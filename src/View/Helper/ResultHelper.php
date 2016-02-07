<?php
/**
 * Source code for the ResultHelper class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\View\Helper;

use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * This class provides utility function for working with result entities.
 */
class ResultHelper extends Helper
{

    /**
     * A list of field types grouped by table name
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Helpers used by this helper
     *
     * @var array
     */
    public $helpers = ['Number'];

    /**
     * Return the fields and their types for a given table
     *
     * @todo Cache
     *
     * @param string $source The name of the table
     * @return string
     */
    protected function _fields($source)
    {
        if (!isset($this->_fields[$source])) {
            $table = TableRegistry::get($source);
            $this->_fields[$source] = [];

            foreach ($table->schema()->columns() as $column) {
                $this->_fields[$source][$column] = $table->schema()->columnType($column);
            }
        }

        return $this->_fields[$source];
    }

    /**
     * Returns the type of a field extracted from the result entity by a given path.
     *
     * @param Entity $result The entity to get the field type from
     * @param string $path The path to the field
     * @param array $params Extra parameters, the "type" can can be used to force the type
     * @return string
     */
    public function type(Entity $result, $path, array $params = [])
    {
        // TODO
        //$primaryKey = 'id';
        //$primaryKey = $table->primaryKey();
        $type = Hash::get($params, 'type');
        if ($type === null) {
            $fields = (array)$this->_fields($result->source());
            $type = Hash::get($fields, $path);
        }

        return $type !== null ? $type : 'string';
    }

    /**
     * Returns a formatted value extracted from the result entity by a given path.
     *
     * @param Entity $result The entity to extract the value from
     * @param string $path The path to the value
     * @param array $params Extra parameters, the "type" can can be used to force the type
     * @return string
     */
    public function value(Entity $result, $path, array $params = [])
    {
        $type = $this->type($result, $path, $params);

        $value = Hash::get($result, $path);
        if ($value !== null) {
            switch ($type) {
                case 'integer':
                    $value = $this->Number->format($value);
                    break;
            }

            $value = (string)$value;
        }

        return $value;
    }

    /**
     * Returns extra class(es) for a given integer value.
     *
     * @param int $value The given value
     * @return array
     */
    protected function _integerExtra($value)
    {
        $extra = [];

        if ($value === 0) {
            $extra[] = 'zero';
        } else {
            $extra[] = $value > 0 ? 'positive' : 'negative';
        }

        return $extra;
    }

    /**
     * Returns extra class(es) for a given string value.
     *
     * @param string $value The given value
     * @return string
     */
    protected function _stringExtra($value)
    {
        $extra = [];

        if ($value === '') {
            $extra[] = 'empty';
        }

        return $extra;
    }

    /**
     * Returns extra class(es) for a given boolean value.
     *
     * @param bool $value The given value
     * @return string
     */
    protected function _booleanExtra($value)
    {
        $extra = [];

        if ($value === true) {
            $extra[] = 'true';
        } else {
            $extra[] = 'false';
        }

        return $extra;
    }

    /**
     * Returns extra class(es) for a given timestamp value.
     *
     * @param bool $value The given value
     * @return string
     */
    protected function _timestampExtra($value)
    {
        $extra = [];
        $today = Time::today();
        $tomorrow = Time::tomorrow();

        if ($value < $today) {
            $extra[] = 'past';
        } elseif ($value >= $today && $value < $tomorrow) {
            $extra[] = 'today';
        } else {
            $extra[] = 'future';
        }

        return $extra;
    }

    /**
     * Returns extra classes from the result entity by a given path.
     *
     * @todo classes (data type null|true|false|positive|negative|future|past|today)
     *
     * @param Entity $result The entity to get the extra from
     * @param string $path The path to the etra
     * @param array $params Extra parameters, the "type" can can be used to force the type
     * @return string
     */
    public function extra(Entity $result, $path, array $params = [])
    {
        $extra = [];

        $type = $this->type($result, $path, $params);
        $value = Hash::get($result, $path);

        if ($value === null) {
            $extra[] = 'null';
        } else {
            $method = "_{$type}Extra";
            if (method_exists($this, $method)) {
                $extra = array_merge($extra, $this->{$method}($value));
            }
        }

        return implode(' ', $extra);
    }
}

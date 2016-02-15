<?php
/**
 * Source code for the Hash utility class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\Utility;

/**
 * The Hash class extends Cake's Hash class with some more utility functions.
 */
class Hash extends \Cake\Utility\Hash
{
    /**
     * Returns an array with only the specified keys, containing the default value
     * when the key is not in the array.
     *
     * @todo Improve (performance):
     * @see http://stackoverflow.com/a/4260168
     * @see php.net/manual/en/function.array-filter.php#example-5511
     *
     *
     * @param array $array The original array
     * @param array $keys The keys that will be in the resulting array
     * @param mixed $default The value to use when the requested key is not found
     * @return array
     */
    public static function filterByKeys(array $array, array $keys, $default = null)
    {
        $result = [];

        foreach ($keys as $key) {
            if (strpos($key, '.') === false) {
                $result[$key] = isset($array[$key]) ? $array[$key] : $default;
            } else {
                $result = self::insert($result, $key, self::get($array, $key));
            }
        }

        return $result;
    }

    /**
     * Returns the key's value from the array or the default value if the key is
     * not present, then removes the key from the array.
     * If $key is an array then the result will be an array containing all keys
     * consumed from the $array.
     *
     * @param array $array The original array
     * @param mixed $key The path(s) to be extracted
     * @param mixed $default The default value to return if the key doesn't exist
     * @return mixed
     */
    public static function consume(array &$array, $key, $default = null)
    {
        if (is_array($key)) {
            $keys = $key;
            $result = [];

            foreach ($keys as $key) {
                $result[$key] = self::consume($array, $key, $default);
            }
        } else {
            $result = self::get($array, $key, $default);

            if (strpos($key, '.') === false) {
                unset($array[$key]);
            } else {
                $array = self::remove($array, $key);
            }
        }

        return $result;
    }
}

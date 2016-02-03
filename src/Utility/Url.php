<?php
/**
 * Source code for the Url class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\Utility;

use Cake\Routing\Router;
use Cake\Utility\Hash;

/**
 * Utility class used for URL parsing.
 */
abstract class Url
{

    /**
     * Cache for parsed URL strings.
     *
     * @var array
     */
    protected static $_cache = [];

    /**
     * Returns an URL parsed in a CakePHP array or the original string if it
     * could not be parsed.
     *
     * @param array|string $url The URL to be parsed
     * @return array|string
     */
    public static function parse($url)
    {
        if (is_string($url) && strpos($url, '/') === 0) {
            try {
                if (preg_match('/^(.*)#(.*)/', $url, $matches)) {
                    $url = $matches[1];
                    $hash = $matches[2];
                } else {
                    $hash = null;
                }

                $result = Router::parse($url);

                $pass = (array)Hash::get($result, 'pass');
                unset($result['pass']);

                $result = $result + $pass;
                if ($hash !== null) {
                    $result += ['#' => $hash];
                }
            } catch (Exception $e) {
                $result = $url;
            }
        } else {
            $result = $url;
        }

        return $result;
    }
}

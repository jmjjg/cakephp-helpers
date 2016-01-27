<?php
namespace Helpers\Utility;

use Cake\Routing\Router;
use Cake\Utility\Hash;

abstract class Url
{
	/**
	 *
	 * @var array
	 */
	protected static $_cache = [];

	/**
	 * @todo plugin, prefix
	 * @todo ?&=
	 *
	 * @param array|string $url
	 * @return array|string
	 */
	public static function parse($url)
	{
		if(is_string($url) && strpos($url, '/') === 0)
		{
			try {
				if(preg_match( '/^(.*)#(.*)/', $url, $matches)) {
					$url = $matches[1];
					$hash = $matches[2];
				} else {
					$hash = null;
				}

				$result = Router::parse($url);
				$pass = (array)Hash::get($result, 'pass');
				unset($result['pass']);

				$result = $result + $pass;
				if($hash !== null) {
					$result += ['#' => $hash];
				}

			} catch(Exception $e) {
				$result = $url;
			}
		} else {
			$result = $url;
		}

		return $result;
	}
}
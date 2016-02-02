<?php
/**
 *
 */
namespace Helpers\Test\TestCase\Utility;

use Cake\TestSuite\TestCase;
use Helpers\Utility\Url;

/**
 * php ../composer.phar require --dev phpunit/phpunit
 * vendor/bin/phpunit plugins/Helpers/tests/TestCase/Utility/UrlTest
 */
class UrlTest extends TestCase
{

    public function testParse()
    {
        $result = Url::parse('/Groups/index/');
        $expected = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index'];
        $this->assertEquals($expected, $result);
    }

    public function testParseParams()
    {
        $result = Url::parse('/Groups/view/1');
        $expected = ['plugin' => null, 'controller' => 'Groups', 'action' => 'view', '1'];
        $this->assertEquals($expected, $result);
    }

    public function testParseGetParams()
    {
        $result = Url::parse('/Groups/index/?sort=id');
        $expected = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index', '?' => ['sort' => 'id']];
        $this->assertEquals($expected, $result);

        $result = Url::parse('/Groups/index/1?sort=id#foo');
        $expected = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index', '1', '?' => ['sort' => 'id'], '#' => 'foo'];
        $this->assertEquals($expected, $result);
    }

    public function testParseExternalUrl()
    {
        $result = Url::parse('http://github.com');
        $expected = 'http://github.com';
        $this->assertEquals($expected, $result);

        $result = Url::parse('mailto:foo@bar.com');
        $expected = 'mailto:foo@bar.com';
        $this->assertEquals($expected, $result);
    }
}

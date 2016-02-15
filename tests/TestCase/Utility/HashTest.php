<?php
/**
 * Source code for the HashTest unit test class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\Test\TestCase\Utility;

use Cake\TestSuite\TestCase;
use Helpers\Utility\Hash;

/**
 * The HashTest class unit tests the Helpers\Utility\Hash class.
 */
class HashTest extends TestCase
{

    /**
     * Tests that the filterByKeys method returns the right array.
     *
     * @covers Helpers\Utility\Hash::filterByKeys
     * @return void
     */
    public function testFilterByKeys()
    {
        $array = ['bar' => 'baz', 'controller' => 'Groups', 'action' => 'index'];
        $allowed = ['plugin', 'controller', 'action'];
        $expected = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index'];

        $result = Hash::filterByKeys($array, $allowed);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that the filterByKeys method returns the right array even for unset
     * keys.
     *
     * @covers Helpers\Utility\Hash::filterByKeys
     * @return void
     */
    public function testFilterByKeysDefault()
    {
        $array = ['bar' => 'baz', 'controller' => 'Groups', 'action' => 'index'];
        $allowed = ['plugin', 'controller', 'action', 0];
        $expected = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index', 0 => null];

        $result = Hash::filterByKeys($array, $allowed);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that the filterByKeys method returns the right array even when it
     * uses a CakePHP path.
     *
     * @covers Helpers\Utility\Hash::filterByKeys
     * @return void
     */
    public function testFilterByKeysDeepKey()
    {
        $config = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index', '?' => ['sort' => 'created', 'direction' => 'asc']];
        $result = Hash::filterByKeys($config, ['controller', '?.sort']);
        $this->assertEquals(['controller' => 'Groups', '?' => ['sort' => 'created']], $result);
    }

    /**
     * Tests that the consume method returns and removes the key value when present.
     *
     * @covers Helpers\Utility\Hash::consume
     * @return void
     */
    public function testConsume()
    {
        $config = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index'];
        $result = Hash::consume($config, 'controller');
        $this->assertEquals('Groups', $result);
        $this->assertEquals(['plugin' => null, 'action' => 'index'], $config);
    }

    /**
     * Tests that the consume method returns the default key and removes nothing
     * when absent.
     *
     * @covers Helpers\Utility\Hash::consume
     * @return void
     */
    public function testConsumeDefault()
    {
        $config = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index'];
        $result = Hash::consume($config, 'foo', 666);
        $this->assertEquals(666, $result);
        $this->assertEquals(['plugin' => null, 'controller' => 'Groups', 'action' => 'index'], $config);
    }

    /**
     * Tests that the consume method returns the right value when the key is
     * present and uses a CakePHP path.
     *
     * @covers Helpers\Utility\Hash::consume
     * @return void
     */
    public function testConsumeDeepKey()
    {
        $config = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index', '?' => ['sort' => 'created', 'direction' => 'asc']];
        $result = Hash::consume($config, '?.sort');
        $this->assertEquals('created', $result);
        $this->assertEquals(['plugin' => null, 'controller' => 'Groups', 'action' => 'index', '?' => ['direction' => 'asc']], $config);
    }

    /**
     * Tests that the consume method returns an array of elements when called
     * with an array of keys.
     *
     * @covers Helpers\Utility\Hash::consume
     * @return void
     */
    public function testConsumeArray()
    {
        $config = ['plugin' => null, 'controller' => 'Groups', 'action' => 'index'];
        $result = Hash::consume($config, ['controller', 0]);

        $this->assertEquals(['controller' => 'Groups', 0 => null], $result);
        $this->assertEquals(['plugin' => null, 'action' => 'index'], $config);
    }
}

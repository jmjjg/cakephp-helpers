<?php
/**
 * Source code for the ResultHelperTest unit test class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\Test\TestCase\View\Helper;

use Cake\Network\Request;
use Cake\Network\Session;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Helpers\View\Helper\ResultHelper;

/**
 *
 */
class ResultHelperTest extends TestCase
{

    /**
     * Fixtures used.
     *
     * @var array
     */
    public $fixtures = [
        'core.Groups',
        'core.Uuiditems'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Groups = TableRegistry::get('Groups');
        $this->Uuiditems = TableRegistry::get('Uuiditems');

        $this->View = new View();
        $session = new Session();
        $this->View->request = new Request(['session' => $session]);
        $this->Result = new ResultHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->View, $this->Result);
    }

    /**
     * Test the type method
     *
     * @return void
     */
    public function testType()
    {
        $group = $this->Groups->get(1);

        $result = $this->Result->type($group, 'id');
        $expected = 'integer';
        $this->assertEquals($expected, $result);

        $result = $this->Result->type($group, 'title');
        $expected = 'string';
        $this->assertEquals($expected, $result);

        $uuiditem = $this->Uuiditems->get('481fc6d0-b920-43e0-a40d-6d1740cf8569');

        $result = $this->Result->type($uuiditem, 'published');
        $expected = 'boolean';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test the value method
     *
     * @return void
     */
    public function testValue()
    {
        $group = $this->Groups->get(1);

        $result = $this->Result->value($group, 'id');
        $expected = '1';
        $this->assertEquals($expected, $result);

        $result = $this->Result->value($group, 'title');
        $expected = 'foo';
        $this->assertEquals($expected, $result);

        $uuiditem = $this->Uuiditems->get('481fc6d0-b920-43e0-a40d-6d1740cf8569');

        $result = $this->Result->value($uuiditem, 'published');
        $expected = false;
        $this->assertEquals($expected, $result);
    }

    /**
     * Test the extra method
     *
     * @return void
     */
    public function testExtra()
    {
        $group = $this->Groups->get(1);

        $result = $this->Result->extra($group, 'id');
        $expected = 'positive';
        $this->assertEquals($expected, $result);

        $result = $this->Result->extra($group, 'title');
        $expected = '';
        $this->assertEquals($expected, $result);

        $uuiditem = $this->Uuiditems->get('481fc6d0-b920-43e0-a40d-6d1740cf8569');

        $result = $this->Result->extra($uuiditem, 'published');
        $expected = 'false';
        $this->assertEquals($expected, $result);
    }
}

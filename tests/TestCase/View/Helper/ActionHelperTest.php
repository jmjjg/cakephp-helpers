<?php
/**
 * Source code for the ActionHelperTest unit test class from the Helpers CakePHP 3 plugin.
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
use Helpers\View\Helper\ActionHelper;

/**
 *
 */
class ActionHelperTest extends TestCase
{
    /**
     * Fixtures used.
     *
     * @var array
     */
    public $fixtures = [ 'core.Uuiditems'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Uuiditems = TableRegistry::get('Uuiditems');

        $this->View = new View();
        $session = new Session();
        $this->View->request = new Request(['session' => $session]);

        $this->Action = new ActionHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Action, $this->View, $this->Uuiditems);
    }

    /**
     * Tests the title method
     *
     * @covers Helpers\View\Helper\ActionHelper::title
     */
    public function testTitle()
    {
        $this->assertEquals(
            'View group « {{name}} » (#{{id}})',
            $this->Action->title('Groups', 'view')
        );
    }

    /**
     * Tests the title method
     *
     * @covers Helpers\View\Helper\ActionHelper::confirm
     */
    public function testConfirm()
    {
        $this->assertEquals(
            'Are you sure you want to delete the group « {{name}} » (#{{id}})',
            $this->Action->confirm('Groups', 'delete')
        );
    }

    /**
     * Tests the text method
     *
     * @covers Helpers\View\Helper\ActionHelper::text
     */
    public function testText()
    {
        $this->assertEquals('View', $this->Action->text(['url' => ['controller' => 'Groups', 'action' => 'view', 1]]));
        $this->assertEquals('Details', $this->Action->text(['url' => ['controller' => 'Groups', 'action' => 'view', 1], 'text' => 'Details']));

        $this->assertEquals('john.doe@example.com', $this->Action->text(['url' => 'mailto:john.doe@example.com']));
        $this->assertEquals('John Doe', $this->Action->text(['url' => 'mailto:john.doe@example.com', 'text' => 'John Doe']));

        $this->assertEquals('http://www.example.com/', $this->Action->text(['url' => 'http://www.example.com/']));
        $this->assertEquals('Example', $this->Action->text(['url' => 'http://www.example.com/', 'text' => 'Example']));
    }

    /**
     * Tests the params and link methods.
     *
     * @covers Helpers\View\Helper\ActionHelper::link
     * @covers Helpers\View\Helper\ActionHelper::params
     * @covers Helpers\View\Helper\ActionHelper::_translate
     */
    public function testParamsLink()
    {
        $uuiditem = $this->Uuiditems->get('481fc6d0-b920-43e0-a40d-6d1740cf8569');

        // 1. POST link with confirm message
        $this->assertEquals(
            "<form name=\"post_0000000000000000000000\" style=\"display:none;\" method=\"post\" action=\"/uuiditems/delete/481fc6d0-b920-43e0-a40d-6d1740cf8569\"><input type=\"hidden\" name=\"_method\" value=\"POST\"/></form><a href=\"#\" title=\"Delete uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)\" onclick=\"if (confirm(&quot;Are you sure you want to delete the uuiditem \u00ab Item 1 \u00bb (#481fc6d0-b920-43e0-a40d-6d1740cf8569)&quot;)) { document.post_0000000000000000000000.submit(); } event.returnValue = false; return false;\">Delete</a>",
            preg_replace(
                '/post_[^"\.]+/m',
                'post_0000000000000000000000',
                $this->Action->link($this->Action->params($uuiditem, '/Uuiditems/delete/{{id}}', [ 'confirm' => true, 'type' => 'post']))
            )
        );

        // 2 GET link
        $this->assertEquals(
            "<a href=\"http://www.example.com/481fc6d0-b920-43e0-a40d-6d1740cf8569\">http://www.example.com/481fc6d0-b920-43e0-a40d-6d1740cf8569</a>",
            $this->Action->link($this->Action->params($uuiditem, 'http://www.example.com/{{id}}'))
        );

        // 3 Mailto GET link
        $this->assertEquals(
            "<a href=\"mailto:481fc6d0-b920-43e0-a40d-6d1740cf8569@example.com\">481fc6d0-b920-43e0-a40d-6d1740cf8569@example.com</a>",
            $this->Action->link($this->Action->params($uuiditem, 'mailto:{{id}}@example.com'))
        );
    }
}

<?php
/**
 * Source code for the ResultsTableHelperTest unit test class from the Helpers CakePHP 3 plugin.
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
use Helpers\Test\TestCase\View\Helper\CommonHelperTestTrait;
use Helpers\View\Helper\ResultsTableHelper;

/**
 *
 */
class ResultsTableHelperTest extends TestCase
{
    use CommonHelperTestTrait;

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

        $this->ResultsTable = new ResultsTableHelper($this->View);

        $this->ResultsTable->Paginator->request = new Request();
        $this->addPaginationParams($this->ResultsTable->Paginator->request);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->ResultsTable, $this->View, $this->Uuiditems, $this->Groups);
    }

    /**
     * Test the index method
     *
     * @covers Helpers\View\Helper\ResultsTableHelper::table
     * @covers Helpers\View\Helper\ResultsTableHelper::tbody
     * @covers Helpers\View\Helper\ResultsTableHelper::tbodyCell
     * @covers Helpers\View\Helper\ResultsTableHelper::thead
     * @covers Helpers\View\Helper\ResultsTableHelper::_actionCells
     * @return void
     */
    public function testIndex()
    {
        $groups = $this->Groups->find()->all();

        $result = $this->ResultsTable->table(
            $groups,
            [
                'id',
                'title'
            ]
        );
        $expected = '<table class="results_set"><thead><tr><th><a href="/?sort=id&amp;direction=asc">Id</a></th>
<th><a href="/?sort=title&amp;direction=asc">Title</a></th>
</tr></thead><tbody><tr><td class="data integer positive">1</td>
<td class="data string ">foo</td>
</tr><tr><td class="data integer positive">2</td>
<td class="data string ">bar</td>
</tr></tbody></table>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test the index method with links
     *
     * @covers Helpers\View\Helper\ResultsTableHelper::table
     * @covers Helpers\View\Helper\ResultsTableHelper::tbody
     * @covers Helpers\View\Helper\ResultsTableHelper::thead
     * @covers Helpers\View\Helper\ResultsTableHelper::_actionCells
     * @return void
     */
    public function testIndexWithLinks()
    {
        $uuiditems = $this->Uuiditems->find()->limit(1)->all();

        $result = $this->ResultsTable->table(
            $uuiditems,
            [
                'id',
                'published',
                'name',
                '/Uuiditems/view/{{id}}',
                '/Uuiditems/edit/{{id}}',
                '/Uuiditems/delete/{{id}}' => [
                    'confirm' => true,
                    'type' => 'post'
                ],
                'http://www.example.com/{{id}}',
                'mailto:{{id}}@example.com'
            ]
        );
        $expected = '<table class="results_set"><thead><tr><th><a href="/?sort=id&amp;direction=asc">Id</a></th>
<th><a href="/?sort=published&amp;direction=asc">Published</a></th>
<th><a href="/?sort=name&amp;direction=asc">Name</a></th>
<th class="actions" colspan="5">Actions</th>
</tr></thead><tbody><tr><td class="data uuid ">481fc6d0-b920-43e0-a40d-6d1740cf8569</td>
<td class="data boolean false"></td>
<td class="data string ">Item 1</td>
<td class="action  uuiditems view"><a href="/uuiditems/view/481fc6d0-b920-43e0-a40d-6d1740cf8569" title="View uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)">View</a></td>
<td class="action  uuiditems edit"><a href="/uuiditems/edit/481fc6d0-b920-43e0-a40d-6d1740cf8569" title="Edit uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)">Edit</a></td>
<td class="action  uuiditems delete"><form name="post_0000000000000000000000" style="display:none;" method="post" action="/uuiditems/delete/481fc6d0-b920-43e0-a40d-6d1740cf8569"><input type="hidden" name="_method" value="POST"/></form><a href="#" title="Delete uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)" onclick="if (confirm(&quot;Are you sure you want to delete the uuiditem \u00ab Item 1 \u00bb (#481fc6d0-b920-43e0-a40d-6d1740cf8569)&quot;)) { document.post_0000000000000000000000.submit(); } event.returnValue = false; return false;">Delete</a></td>
<td class="action "><a href="http://www.example.com/481fc6d0-b920-43e0-a40d-6d1740cf8569">http://www.example.com/481fc6d0-b920-43e0-a40d-6d1740cf8569</a></td>
<td class="action "><a href="mailto:481fc6d0-b920-43e0-a40d-6d1740cf8569@example.com">481fc6d0-b920-43e0-a40d-6d1740cf8569@example.com</a></td>
</tr></tbody></table>';
        $result = preg_replace('/post_[^"\.]+/m', 'post_0000000000000000000000', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the tbodyCell method for the different cell types.
     *
     * @covers Helpers\View\Helper\ResultsTableHelper::tbodyCell
     * @covers Helpers\View\Helper\ResultsTableHelper::_cellType
     * @covers Helpers\View\Helper\ResultsTableHelper::tbodyLinkCell
     * @covers Helpers\View\Helper\ResultsTableHelper::tbodyDataCell
     */
    public function testTbodyCell()
    {
        // Group
        $group = $this->Groups->get(1);

        // 1. Integer
        // 1.1 Positive integer
        $this->assertEquals(
            "<td class=\"data integer positive\">1</td>\n",
            $this->ResultsTable->tbodyCell($group, 'id')
        );

        // 1.2 Negative integer
        $group->id = -1;
        $this->assertEquals(
            "<td class=\"data integer negative\">-1</td>\n",
            $this->ResultsTable->tbodyCell($group, 'id')
        );

        // 1.3 Zero
        $group->id = 0;
        $this->assertEquals(
            "<td class=\"data integer zero\">0</td>\n",
            $this->ResultsTable->tbodyCell($group, 'id')
        );

        // 2. String
        // 2.1 Normal string
        $this->assertEquals(
            "<td class=\"data string \">foo</td>\n",
            $this->ResultsTable->tbodyCell($group, 'title')
        );

        // 2.2 Empty string
        $group->title = '';
        $this->assertEquals(
            "<td class=\"data string empty\"></td>\n",
            $this->ResultsTable->tbodyCell($group, 'title')
        );

        // Uuiditem
        $uuiditem = $this->Uuiditems->get('481fc6d0-b920-43e0-a40d-6d1740cf8569');

        // 3. Uuid
        $this->assertEquals(
            "<td class=\"data uuid \">481fc6d0-b920-43e0-a40d-6d1740cf8569</td>\n",
            $this->ResultsTable->tbodyCell($uuiditem, 'id')
        );

        // 4. Boolean
        // 4.1 Boolean false
        $this->assertEquals(
            "<td class=\"data boolean false\"></td>\n",
            $this->ResultsTable->tbodyCell($uuiditem, 'published')
        );

        // 5. Link
        $this->assertEquals(
            "<td class=\"action  uuiditems view\"><a href=\"/uuiditems/view/481fc6d0-b920-43e0-a40d-6d1740cf8569\" title=\"View uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)\">View</a></td>\n",
            $this->ResultsTable->tbodyCell($uuiditem, '/Uuiditems/view/{{id}}')
        );
    }
}

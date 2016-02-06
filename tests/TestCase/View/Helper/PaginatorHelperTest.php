<?php
/**
 * Source code for the PaginatorHelperTest unit test class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\Test\TestCase\View\Helper;

use Cake\Network\Request;
use Cake\Network\Session;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Helpers\View\Helper\PaginatorHelper;

/**
 *
 */
class PaginatorHelperTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->View = new View();
        $session = new Session();
        $this->View->request = new Request(['session' => $session]);

        $this->Paginator = new PaginatorHelper($this->View);
        $this->Paginator->request = new Request();
        $this->Paginator->request->addParams([
            'paging' => [
                'Articles' => [
                    'page' => 1,
                    'current' => 1,
                    'count' => 1,
                    'prevPage' => false,
                    'nextPage' => false,
                    'pageCount' => 1,
                    'perPage' => 10,
                    'sort' => null,
                    'direction' => null,
                    'limit' => null,
                ],
                'Groups' => [
                    'page' => 2,
                    'current' => 21,
                    'count' => 56,
                    'prevPage' => true,
                    'nextPage' => true,
                    'pageCount' => 5,
                    'perPage' => 10,
                    'sort' => null,
                    'direction' => null,
                    'limit' => null,
                ]
            ]
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->View, $this->Paginator);
    }

    /**
     * Test PaginatorHelper::first() overwritten and original code.
     *
     * @covers Helpers\View\Helper\PaginatorHelper::first
     * @covers Helpers\View\Helper\PaginatorHelper::__construct
     * @return void
     */
    public function testFirst()
    {
        $result = $this->Paginator->first();
        $expected = '<li class="first disabled"><a href="">&lt;&lt; first</a></li>';
        $this->assertEquals($expected, $result);

        $result = $this->Paginator->first('<< first', ['model' => 'Groups']);
        $expected = '<li class="first"><a href="/">&lt;&lt; first</a></li>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test PaginatorHelper::last() overwritten and original code.
     *
     * @covers Helpers\View\Helper\PaginatorHelper::last
     * @covers Helpers\View\Helper\PaginatorHelper::__construct
     * @return void
     */
    public function testLast()
    {
        $result = $this->Paginator->last();
        $expected = '<li class="last disabled"><a href="">last &gt;&gt;</a></li>';
        $this->assertEquals($expected, $result);

        $result = $this->Paginator->last('last >>', ['model' => 'Groups']);
        $expected = '<li class="last"><a href="/?page=5">last &gt;&gt;</a></li>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test PaginatorHelper::numbers() overwritten and original code.
     *
     * @covers Helpers\View\Helper\PaginatorHelper::numbers
     * @covers Helpers\View\Helper\PaginatorHelper::__construct
     * @return void
     */
    public function testNumbers()
    {
        $result = $this->Paginator->numbers();
        $expected = '<li class="active"><a href="">1</a></li>';
        $this->assertEquals($expected, $result);

        $result = $this->Paginator->numbers(['model' => 'Groups']);
        $expected = '<li><a href="/">1</a></li><li class="active"><a href="">2</a></li><li><a href="/?page=3">3</a></li><li><a href="/?page=4">4</a></li><li><a href="/?page=5">5</a></li>';
        $this->assertEquals($expected, $result);
    }
}

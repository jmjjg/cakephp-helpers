<?php
namespace Helpers\Test\TestCase\View\Helper;

use Cake\Network\Request;
use Cake\Network\Session;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Helpers\View\Helper\ResultsSetHelper;
use Cake\View\View;

/**
 * php ../composer.phar require --dev phpunit/phpunit
 * sudo -u apache vendor/bin/phpunit plugins/Helpers/tests/TestCase/View/Helper/ResultsSetHelperTest
 */
class ResultsSetHelperTest extends TestCase
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
        $this->ResultsSet = new ResultsSetHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->ResultsSet, $this->View, $this->Uuiditems, $this->Groups);
    }

	/**
	 * Test the index method
	 *
	 * @return void
	 */
    public function testIndex()
    {
		$groups = $this->Groups->find()->all();

        $result = $this->ResultsSet->index(
			$groups,
			[
				'id',
				'title'
			]
		);
		$expected = '<div class="index "><table class="results_set"><thead><tr><th><a href="/?sort=id&amp;direction=asc">Id</a></th>
<th><a href="/?sort=title&amp;direction=asc">Title</a></th>
</tr></thead><tbody><tr><td class="data integer positive">1</td>
<td class="data string ">foo</td>
</tr><tr><td class="data integer positive">2</td>
<td class="data string ">bar</td>
</tr></tbody></table></div>';
        $this->assertEquals($expected, $result);
		/*$expectedHtml = [
			'div' => ['class' => 'index '],
				'table' => ['class' => 'results_set'],
					'thead' => true,
						'tr' => true,
							'th' => true,
								'a' => ['href' => '/?sort=id&amp;direction=asc'],
									'Id',
								'/a',
							'/th',
							'th' => true,
								'a' => ['href' => '/?sort=title&amp;direction=asc'],
//									'Title',
//								'/a',
//							'/th',
//						'/tr',
//					'/thead',
		];
		debug($result);
		$this->assertHtml($expectedHtml, $result);*/
	}


	/**
	 * Test the index method with links
	 *
	 * @return void
	 */
    public function testIndexWithLinks()
	{
		$uuiditems = $this->Uuiditems->find()->limit(1)->all();

        $result = $this->ResultsSet->index(
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
				]
			]
		);
		$expected = '<div class="index "><table class="results_set"><thead><tr><th><a href="/?sort=id&amp;direction=asc">Id</a></th>
<th><a href="/?sort=published&amp;direction=asc">Published</a></th>
<th><a href="/?sort=name&amp;direction=asc">Name</a></th>
<th class="actions" colspan="3">Actions</th>
</tr></thead><tbody><tr><td class="data uuid ">481fc6d0-b920-43e0-a40d-6d1740cf8569</td>
<td class="data boolean false"></td>
<td class="data string ">Item 1</td>
<td class="action  uuiditems view"><a href="/uuiditems/view/481fc6d0-b920-43e0-a40d-6d1740cf8569" title="View uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)">View</a></td>
<td class="action  uuiditems edit"><a href="/uuiditems/edit/481fc6d0-b920-43e0-a40d-6d1740cf8569" title="Edit uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)">Edit</a></td>
<td class="action  uuiditems delete"><form name="post_0000000000000000000000" style="display:none;" method="post" action="/uuiditems/delete/481fc6d0-b920-43e0-a40d-6d1740cf8569"><input type="hidden" name="_method" value="POST"/></form><a href="#" title="Delete uuiditem « Item 1 » (#481fc6d0-b920-43e0-a40d-6d1740cf8569)" onclick="if (confirm(&quot;Are you sure you want to delete the uuiditem \u00ab Item 1 \u00bb (#481fc6d0-b920-43e0-a40d-6d1740cf8569)&quot;)) { document.post_0000000000000000000000.submit(); } event.returnValue = false; return false;">Delete</a></td>
</tr></tbody></table></div>';
		$result = preg_replace('/post_[^"\.]+/m', 'post_0000000000000000000000', $result);
        $this->assertEquals($expected, $result);
    }

	/**
	 * Test the index method
	 *
	 * @return void
	 */
    public function testIndexEmpty()
    {
		$groups = $this->Groups->find()->where(['id' => 0])->all();

        $result = $this->ResultsSet->index(
			$groups,
			[
				'id',
				'title'
			]
		);
		$expected = '<p class="notice">No record was found</p>';
        $this->assertEquals($expected, $result);
    }
}

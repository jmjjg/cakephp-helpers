<?php
/**
 * Source code for the CommonHelperTestTrait unit test trait from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\Test\TestCase\View\Helper;

use Cake\Network\Request;

/**
 * This trait provides a function that adds common pagination params to a request.
 */
trait CommonHelperTestTrait
{
    /**
     * Add common pagination parameters to the request.
     *
     * @param Cake\Network\Request $request
     */
    public function addPaginationParams(Request &$request)
    {
        $request->addParams([
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
}

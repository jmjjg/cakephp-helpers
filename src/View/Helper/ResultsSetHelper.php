<?php
/**
 * Source code for the ResultsSetHelper class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\View\Helper;

use Cake\ORM\ResultSet;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Sample usage:
 * $this->Html->css('Helpers.extra', ['block' => true]);
 * echo $this->ResultsSet->index(
 *     $groups,
 *     [
 *         'id',
 *         'name',
 *         'created',
 *         'modified',
 *         '/Groups/view/{{id}}' => [
 *             // INFO: set to false to disable auto title
 *             //'title' => __('View group « {{name}} » (#{{id}})')
 *         ],
 *         '/Groups/edit/{{id}}' => [
 *             // INFO: set to false to disable auto title
 *             //'title' => __('Edit group « {{name}} » (#{{id}})')
 *         ],
 *         '/Groups/delete/{{id}}' => [
 *             // INFO: set to false to disable auto title
 *             //'title' => __('Delete group « {{name}} » (#{{id}})'),
 *             'type' => 'post',
 *             // INFO: don't set or set to false to disable auto confirm message
 *             //'confirm' => __('Are you sure you want to delete the group « {{name}} » (# {{id}})?')
 *             'confirm' => true
 *         ],
 *     ]
 * );
 */
class ResultsSetHelper extends Helper
{

    use StringTemplateTrait;

    /**
     * A "td" containing an "a"
     */
    const CELL_TYPE_LINK = 'link';

    /**
     * A "td" containing data
     */
    const CELL_TYPE_DATA = 'data';

    /**
     * Classic <a href /> GET link
     */
    const LINK_TYPE_GET = 'href';

    /**
     * Looks like a link but is a mini POST form
     */
    const LINK_TYPE_POST = 'post';

    /**
     * Helpers used by this helper
     *
     * @var array
     */
    public $helpers = [
        'Form',
        'Html',
        'Paginator' => [
            'className' => 'Helpers.Paginator'
        ],
        'Result' => [
            'className' => 'Helpers.Result'
        ],
        'ResultsTable' => [
            'className' => 'Helpers.ResultsTable'
        ]
    ];

    /**
     * The default config, to merge with the parent's
     *
     * @var array
     */
    protected $_defaultConfig = [
        'options' => [],
        'templates' => [
            'index' => '<div class="index {{model}}">{{pagination}}{{table}}{{pagination}}</div>',
            'empty' => '<p class="notice">{{message}}</p>',
            'paginationCounter' => '<p>{{counter}}</p>',
            'paginationLinks' => '<ul class="pagination">{{first}}{{prev}}{{numbers}}{{next}}{{last}}</ul>',
            'pagination' => '<div class="paginator">{{counter}}{{links}}</div>'
        ],
        'messages' => [
            'empty' => 'No record was found',
            'first' => '<< first',
            'previous' => '< previous',
            'next' => 'next >',
            'last' => 'last >>'
        ]
    ];

    /**
     * Utility method to get the template by name.
     *
     * @param string $template The name of the template
     * @param array $data The optional data to send to the templater
     * @return string
     */
    protected function _template($template, array $data = [])
    {
        return $this->templater()->format($template, $data);
    }

    /**
     * Returns an <index> containing a <table>, <pagination> and the <model> name
     * if there are results or an <empty> <message>.
     *
     * @param ResultSet $results A list of Entity results
     * @param array $paths A list of call paths
     * @param array $params The only used key is "message"
     * @return string
     */
    public function index(ResultSet $results, array $paths, array $params = [])
    {
        if (count($results) > 0) {
            $indexData = [
                'table' => $this->ResultsTable->table($results, $paths),
                'pagination' => $this->pagination(),
                'model' => Inflector::underscore($this->Paginator->defaultModel())
            ];
            $index = $this->_template('index', $indexData);
        } else {
            $message = Hash::get($params, 'message');
            $indexData = [
                'message' => null !== $message ? $message : $this->config('messages.empty')
            ];
            $index = $this->_template('empty', $indexData);
        }

        return $index;
    }

    /**
     * If there is at least one page of results, returns a <pagination> block
     * which contains a list of navigation <links> (<paginationLinks>) and a
     * <paginationCounter>.
     *
     * @fixme disabledTitle
     * @see Cake\View\Helper\PaginatorHelper::prev()
     * @see Cake\View\Helper\PaginatorHelper::$options
     *
     * @param array $options @see Helpers\View\Helper\PaginatorHelper Available
     *  keys are "model", ...
     * @return string
     */
    public function pagination(array $options = [])
    {
        $paginationLinksData = [
            'first' => $this->Paginator->first($this->config('messages.first'), $options),
            'prev' => $this->Paginator->prev($this->config('messages.previous'), $options),
            'numbers' => $this->Paginator->numbers($options),
            'next' => $this->Paginator->next($this->config('messages.next'), $options),
            'last' => $this->Paginator->last($this->config('messages.last'), $options)
        ];
        $links = $this->_template('paginationLinks', $paginationLinksData);

        $paginationData = [
            'counter' => $this->_template('paginationCounter', ['counter' => $this->Paginator->counter()]),
            'links' => $links
        ];
        return $this->_template('pagination', $paginationData);
    }
}

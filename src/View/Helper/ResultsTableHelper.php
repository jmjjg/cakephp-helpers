<?php
/**
 * Source code for the ResultsTableHelper class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\View\Helper;

use Cake\ORM\Entity;
use Cake\ORM\ResultSet;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;
use Helpers\Utility\Hash;
use Helpers\Utility\Url;

/**
 * The Helpers.ResultsTableHelper makes displaying the results of find or paginate
 * fast, including links.
 *
 * @todo Cell types: input
 *
 * @see Helpers\View\Helper\ResultsSetHelper for sample usage.
 */
class ResultsTableHelper extends Helper
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
     * Helpers used by this helper
     *
     * @var array
     */
    public $helpers = [
        'Action' => [
            'className' => 'Helpers.Action'
        ],
        'Form',
        'Html',
        'Paginator' => [
            'className' => 'Helpers.Paginator'
        ],
        'Result' => [
            'className' => 'Helpers.Result'
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
            "table" => "<table class=\"results_set\">{{thead}}{{tbody}}</table>",
            "thead" => "<thead>{{theadRows}}</thead>",
            "theadRows" => "<tr>{{theadCells}}</tr>",
            "theadCell" => "<th>{{theadCell}}</th>\n",
            "theadActionCell" => "<th class=\"actions\" colspan=\"{{count}}\">{{message}}</th>\n",
            "tbody" => "<tbody>{{tbodyRows}}</tbody>",
            "tbodyRows" => "<tr>{{tbodyCells}}</tr>",
            "tbodyDataCell" => "<td class=\"data {{type}} {{extra}}\">{{data}}</td>\n",
            "tbodyLinkCell" => "<td class=\"action {{extra}}\">{{link}}</td>\n"
        ]
    ];

    /**
     * Returns an array of actions that are only at the end of the paths.
     *
     * @param array $paths A list of call paths
     * @return array
     */
    protected function _actionCells(array $paths)
    {
        $result = [];
        $done = false;

        $paths = array_reverse(array_keys(Hash::normalize($paths)));
        foreach ($paths as $path) {
            if ($done === false) {
                if ($this->_cellType($path) === self::CELL_TYPE_LINK) {
                    $result[] = $path;
                } else {
                    $done = true;
                }
            }
        }

        return array_reverse($result);
    }

    /**
     * Returns a classic thead with theadCell sort links and a theadActionCell
     * with a {{count}} rowspan.
     *
     * @param array $paths A list of call paths
     * @return string
     */
    public function thead(array $paths)
    {
        $theadCells = '';
        foreach (Hash::normalize($paths) as $path => $params) {
            if ($this->_cellType($path) === self::CELL_TYPE_DATA) {
                $theadCell = $this->Paginator->sort($path, (array)$params);
                $theadCells .= $this->templater()->format('theadCell', ['theadCell' => $theadCell]);
            }
        }

        //INFO: actions uniquement à la fin
        $actions = $this->_actionCells($paths);
        if (count($actions) > 0) {
            $theadCells .= $this->templater()->format(
                'theadActionCell',
                [
                    'count' => count($actions),
                    'message' => __('Actions')
                ]
            );
        }

        $theadRows = $this->templater()->format('theadRows', ['theadCells' => $theadCells]);
        return $this->templater()->format('thead', ['theadRows' => $theadRows]);
    }

    /**
     * Returns a <tbodyDataCell> containing <data>, <type> and <extra> values
     * extract by $path from the result.
     *
     * @param Entity $result An Entity result
     * @param string $path A cell path
     * @param array $params Set "extra" key to false to disable extra info
     * @return string
     */
    public function tbodyDataCell(Entity $result, $path, array $params = [])
    {
        $params += ['extra' => true];
        $extra = Hash::consume($params, 'extra');

        return $this->templater()->format(
            'tbodyDataCell',
            [
                'data' => h($this->Result->value($result, $path, $params)),
                'type' => $this->Result->type($result, $path, $params),
                'extra' => $extra === true ? $this->Result->extra($result, $path, $params) : null
            ]
        );
    }

    /**
     * Returns a "td" cell containing a link (provided by ActionHelper::link)
     * with the translated path as maybe a parsable CakePHP URL.
     *
     * @param Entity $result An Entity result
     * @param string $path A cell path
     * @param array $params Keys are title, confirm, type, extra, used as
     *  FormHelper::postLink() and HtmlHelper::link() params.
     * @return string
     */
    public function tbodyLinkCell(Entity $result, $path, array $params = [])
    {
        $params = $this->Action->params($result, $path, $params);

        $foo = Hash::filterByKeys($params, ['text', 'url', 'type', 'extra']);

        return $this->templater()->format(
            'tbodyLinkCell',
            [
                'link' => $this->Action->link($params),
                'type' => $foo['type'],
                'extra' => $foo['extra'] === true && is_array($foo['url']) ? Inflector::underscore("{$foo['url']['plugin']} {$foo['url']['controller']} {$foo['url']['action']}") : null
            ]
        );
    }

    /**
     * Returns the type of a cell: a link cell (CELL_TYPE_LINK) or a data cell
     * (CELL_TYPE_DATA).
     *
     * @param string $path A cell path
     * @return string
     */
    protected function _cellType($path)
    {
        // Link cell
        if (strpos($path, '/') === 0 || preg_match('/^(mailto|(ht|f)tps{0,1}):/', $path)) {
            return self::CELL_TYPE_LINK;
        // Data cell
        } else {
            return self::CELL_TYPE_DATA;
        }
    }

    /**
     * Returns a body cell based on its type (CELL_TYPE_LINK or CELL_TYPE_DATA).
     *
     * @param Entity $result An Entity result
     * @param string $path A cell path
     * @param array $params Used in tbodyLinkCell and tbodyDataCell calls
     * @return string
     */
    public function tbodyCell(Entity $result, $path, array $params = [])
    {
        $cell = null;

        switch ($this->_cellType($path)) {
            case self::CELL_TYPE_LINK:
                $cell = $this->tbodyLinkCell($result, $path, $params);
                break;
            case self::CELL_TYPE_DATA:
                $cell = $this->tbodyDataCell($result, $path, $params);
                break;
        }

        return $cell;
    }

    /**
     * Returns a <tbody> containing <tbodyRows> containing <tbodyCells>.
     *
     * @param ResultSet $results A list of Entity results
     * @param array $paths A list of call paths
     * @return string
     */
    public function tbody(ResultSet $results, array $paths)
    {
        $tbodyRows = '';

        foreach ($results as $result) {
            $tbodyCells = '';
            foreach (Hash::normalize($paths) as $path => $params) {
                $tbodyCells .= $this->tbodyCell($result, $path, (array)$params);
            }

            $tbodyRows .= $this->templater()->format('tbodyRows', ['tbodyCells' => $tbodyCells]);
        }

        return $this->templater()->format('tbody', ['tbodyRows' => $tbodyRows]);
    }

    /**
     * Returns a <table> containing a <thead> and a <tbody>.
     *
     * @param ResultSet $results A list of Entity results
     * @param array $paths A list of call paths
     * @return string
     */
    public function table(ResultSet $results, array $paths)
    {
        return $this->templater()->format(
            'table',
            [
                'thead' => $this->thead($paths),
                'tbody' => $this->tbody($results, $paths)
            ]
        );
    }
}

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
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;
use Helpers\Utility\Url;

/**
 * The Helpers.ResultsTableHelper makes displaying the results of find or paginate
 * fast, including links.
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
     * Params to be merged by the _params() method, using keys.
     *
     * @var array
     */
    /*protected $_paramsDefaults = [
        'tbodyLinkCell' => [
            'extra' => true,
            'title' => true,
            'confirm' => false,
            'type' => self::LINK_TYPE_GET
        ]
    ];*/

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
        //TODO: URL, classes
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
        $addExtra = false === isset($params['extra']) || !empty($params['extra']);

        return $this->templater()->format(
            'tbodyDataCell',
            [
                'data' => h($this->Result->value($result, $path, $params)),
                'type' => $this->Result->type($result, $path, $params),
                'extra' => $addExtra ? $this->Result->extra($result, $path, $params) : null
            ]
        );
    }

    /**
     * Utility function to translate a string with values from the entity using
     * the templater.
     *
     * @param Entity $result An Entity result
     * @param string $string The string to translate
     * @return string
     */
    protected function _translate(Entity $result, $string)
    {
        $this->templater()->remove('_link');
        $this->templater()->add(['_link' => $string]);
        return $this->templater()->format('_link', $result->toArray());
        //$this->templater()->remove('_link');
    }

    /**
     * Returns params + defaults in _paramsDefaults using key.
     *
     * @param string $key The key for default params
     * @param array $params The params to complete
     * @return array
     */
    /*protected function _params($key, array $params = [])
    {
        return $params + (array)$this->_paramsDefaults[$key];
    }*/

    /**
     * Returns a "td" cell containing a link (heither LINK_TYPE_GET or
     * LINK_TYPE_POST) with the translated path as maybe a parsable CakePHP URL.
     *
     * @param Entity $result An Entity result
     * @param string $path A cell path
     * @param array $params Keys are title, confirm, type, extra, used as
     *  FormHelper::postLink() and HtmlHelper::link() params.
     * @return string
     */
    public function tbodyLinkCell(Entity $result, $path, array $params = [])
    {
//        debug($this->_params(__FUNCTION__, $params));
        // TODO: pre process, + headers
        /* /* @todo <th class="actions"><?= __('Actions') ?></th> + */
        /* <td class="actions">
          <?= $this->Html->link(__('View'), ['action' => 'view', $result->id]) ?>
          <?= $this->Html->link(__('Edit'), ['action' => 'edit', $result->id]) ?>
          <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $result->id], ['confirm' => __('Are you sure you want to delete # {0}?', $result->id)]) ?>
          </td> */
// TODO: cache ?
        $url = Url::parse($this->_translate($result, $path)); // TODO: or $params['url'] + translate ? / method params ?

        if (is_array($url)) {
            if (!isset($params['title']) || in_array($params['title'], [true, null], true)) {
                $params['title'] = __(
                    sprintf(
                        '%s %s « {{name}} » (#{{id}})',
                        Inflector::humanize($url['action']),
                        mb_convert_case(Inflector::classify($url['controller']), MB_CASE_LOWER)
                    )
                );
            }

            if (isset($params['confirm']) && in_array($params['confirm'], [true, null], true)) {
                $params['confirm'] = __(
                    sprintf(
                        'Are you sure you want to %s the %s « {{name}} » (#{{id}})',
                        mb_convert_case(Inflector::humanize($url['action']), MB_CASE_LOWER),
                        mb_convert_case(Inflector::humanize(Inflector::singularize($url['controller'])), MB_CASE_LOWER)
                    )
                );
            }

            $text = __(Inflector::camelize($url['action']));
        } else {
            $text = $url;
            if (strpos($text, 'mailto:') === 0) {
                $text = substr($text, 7);
            }
        }

        $type = Hash::get($params, 'type') === self::LINK_TYPE_POST ? self::LINK_TYPE_POST : self::LINK_TYPE_GET;
        unset($params['type']);

        foreach (['confirm', 'title'] as $key) {
            if (isset($params[$key])) {
                $params[$key] = $this->_translate($result, $params[$key]);
            }
        }

        $addExtra = false === isset($params['extra']) || !empty($params['extra']);

        return $this->templater()->format(
            'tbodyLinkCell',
            [
                'link' => $type === self::LINK_TYPE_POST ? $this->Form->postLink($text, $url, $params) : $this->Html->link($text, $url, $params),
                'type' => $type,
                'extra' => $addExtra && is_array($url) ? Inflector::underscore("{$url['plugin']} {$url['controller']} {$url['action']}") : null
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
        // TODO input cell
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

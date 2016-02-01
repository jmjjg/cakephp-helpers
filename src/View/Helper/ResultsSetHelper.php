<?php
namespace Helpers\View\Helper;

use Cake\Utility\Hash;
use Cake\ORM\Entity;
use Cake\ORM\ResultSet;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;
use Helpers\Utility\Url;

/**
 * Sample usage:
 * $this->Html->css('Helpers.extra', ['block' => true]);
 * echo $this->ResultsSet->index(
 * 	$groups,
 * 	[
 * 		'id',
 * 		'name',
 * 		'created',
 * 		'modified',
 * 		'/Groups/view/{{id}}' => [
 * 			// INFO: set to false to disable auto title
 * 			//'title' => __('View group « {{name}} » (#{{id}})')
 * 		],
 * 		'/Groups/edit/{{id}}' => [
 * 			// INFO: set to false to disable auto title
 * 			//'title' => __('Edit group « {{name}} » (#{{id}})')
 * 		],
 * 		'/Groups/delete/{{id}}' => [
 * 			// INFO: set to false to disable auto title
 * 			//'title' => __('Delete group « {{name}} » (#{{id}})'),
 * 			'type' => 'post',
 * 			// INFO: don't set or set to false to disable auto confirm message
 * 			//'confirm' => __('Are you sure you want to delete the group « {{name}} » (# {{id}})?')
 * 			'confirm' => true
 * 		],
 * 	]
 * );
 */
class ResultsSetHelper extends Helper
{
	use StringTemplateTrait;

	const CELL_TYPE_LINK = 'link';
	const CELL_TYPE_DATA = 'data';

	const LINK_TYPE_GET = 'href';
	const LINK_TYPE_POST = 'post';

    public $helpers = [
//		'Url',
		'Form',
		'Html',
//		'Number',
		'Paginator' => [
			'className' => 'Helpers.Paginator'
		],
		'Result' => [
			'className' => 'Helpers.Result'
		]
	];

    protected $_defaultConfig = [
        'options' => [],
        'templates' => [
            'table' => '<table class="results_set">{{thead}}{{tbody}}</table>',
            'thead' => '<thead>{{theadRows}}</thead>',
			'theadRows' => '<tr>{{theadCells}}</tr>',
			'theadCell' => '<th>{{theadCell}}</th>',
			'theadActionCell' => '<th class="actions" colspan="{{count}}">{{message}}</th>',
			'tbody' => '<tbody>{{tbodyRows}}</tbody>',
			'tbodyRows' => '<tr>{{tbodyCells}}</tr>',
			'tbodyDataCell' => '<td class="data {{type}} {{extra}}">{{data}}</td>',
			'tbodyLinkCell' => '<td class="action {{extra}}">{{link}}</td>',
			'index' => '<div class="index {{model}}">{{pagination}}{{table}}{{pagination}}</div>',
			'empty' => '<p class="notice">{{message}}</p>',
			// Pagination
			'paginationCounter' => '<p>{{counter}}</p>',
			'paginationLinks' => '<ul class="pagination">{{links}}</ul>',
			'pagination' => '<div class="paginator">{{counter}}{{links}}</div>'
        ]
    ];


	/**
	 *
	 * @var array
	 */
	protected $_paramsDefaults = [
		'tbodyLinkCell' => [
			'extra' => true,
			'title' => true,
			'confirm' => false,
			'type' => self::LINK_TYPE_GET
		]
	];

	/**
	 * Returns an array of actions that are only at the end of the paths.
	 *
	 * @param array $paths
	 * @return array
	 */
	protected function _actionCells(array $paths)
	{
		$result = [];
		$done = false;

		$paths = array_reverse(array_keys(Hash::normalize($paths)));
		foreach($paths as $path) {
			if($done === false) {
				if($this->cellType($path) === self::CELL_TYPE_LINK) {
					$result[] = $path;
				} else {
					$done = true;
				}
			}
		}

		return array_reverse($result);
	}

	/**
	 *
	 * @todo actions cell (theadCell)
	 *
	 * @param array $paths
	 * @return string
	 */
	public function thead(array $paths)
	{
		$theadCells = '';
		foreach(Hash::normalize($paths) as $path => $fieldParams) {
			if ($this->cellType($path) === self::CELL_TYPE_DATA) {
				$theadCell = $this->Paginator->sort($path, (array)$fieldParams);
				$theadCells .= $this->templater()->format('theadCell', ['theadCell' => $theadCell])."\n";
			}
		}

		//INFO: actions uniquement à la fin
		//TODO: URL, classes
		$actions = $this->_actionCells($paths);
		if(count($actions) > 0) {
			$theadCells .= $this->templater()->format(
				'theadActionCell',
				[
					'count' => count($actions),
					'message' => __('Actions')
				])."\n";
		}

		$theadRows = $this->templater()->format('theadRows', ['theadCells' => $theadCells]);
		return $this->templater()->format('thead', ['theadRows' => $theadRows]);
	}

	/**
	 *
	 * @param Entity $result
	 * @param string $path
	 * @param array $fieldParams Set "extra" key to false to disable extra info
	 * @return string
	 */
	public function tbodyDataCell(Entity $result, $path, array $fieldParams = [])
	{
		$addExtra = false === isset($fieldParams['extra']) || !empty($fieldParams['extra']);

		return $this->templater()->format(
			'tbodyDataCell',
			[
				'data' => h($this->Result->value($result, $path, $fieldParams)),
				'type' => $this->Result->type($result, $path, $fieldParams),
				'extra' => $addExtra ? $this->Result->extra($result, $path, $fieldParams) : null
			]
		)."\n";
	}

	/**
	 * Utility function to translate a string with values from the entity using
	 * the templater.
	 *
	 * @param Entity $result
	 * @param string $string
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
	 * @param string $key
	 * @param array $params
	 * @return array
	 */
	protected function _params($key, array $params = [])
	{
		return $params + (array)$this->_paramsDefaults[$key];
	}

	/**
	 *
	 * @param Entity $result
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public function tbodyLinkCell(Entity $result, $path, array $params = [])
	{
//		debug($this->_params(__FUNCTION__, $params));
		// TODO: pre process, + headers
		/*/* @todo <th class="actions"><?= __('Actions') ?></th> +*/
		/*<td class="actions">
			<?= $this->Html->link(__('View'), ['action' => 'view', $result->id]) ?>
			<?= $this->Html->link(__('Edit'), ['action' => 'edit', $result->id]) ?>
			<?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $result->id], ['confirm' => __('Are you sure you want to delete # {0}?', $result->id)]) ?>
		</td>*/
// TODO: cache ?
		$url = Url::parse($this->_translate($result, $path)); // TODO: or $params['url'] + translate ? / method params ?

		if(is_array($url)) {
			if(!isset($params['title']) || in_array($params['title'], [true, null], true)) {
				$params['title'] =  __(
					sprintf(
						'%s %s « {{name}} » (#{{id}})',
						Inflector::humanize($url['action']),
						mb_convert_case(Inflector::classify($url['controller']), MB_CASE_LOWER)
					)
				);
			}

			if(isset($params['confirm']) && in_array($params['confirm'], [true, null], true)) {
				$params['confirm'] =  __(
					sprintf(
						'Are you sure you want to %s the %s « {{name}} » (#{{id}})',
						mb_convert_case(Inflector::humanize($url['action']),MB_CASE_LOWER),
						mb_convert_case(Inflector::humanize(Inflector::singularize($url['controller'])),MB_CASE_LOWER)
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

		$type = Hash::get( $params, 'type' ) === self::LINK_TYPE_POST ? self::LINK_TYPE_POST : self::LINK_TYPE_GET;
		unset($params['type']);

		foreach(['confirm', 'title'] as $key) {
			if(isset($params[$key])) {
				$params[$key] = $this->_translate($result, $params[$key]);
			}
		}

		$addExtra = false === isset($params['extra']) || !empty($params['extra']);

		return $this->templater()->format(
			'tbodyLinkCell',
			[
				'link' => $type === self::LINK_TYPE_POST
					? $this->Form->postLink($text, $url, $params)
					: $this->Html->link($text, $url, $params),
				'type' => $type,
				'extra' => $addExtra && is_array($url)
					? Inflector::underscore("{$url['plugin']} {$url['controller']} {$url['action']}")
					: null
			]
		)."\n";
	}

	/**
	 *
	 * @param string $path
	 * @return string
	 */
	public function cellType($path)
	{
		// TODO input cell
		// Link cell
		if (strpos($path, '/') === 0 || preg_match('/^(mailto|(ht)tps{0,1}):/', $path)) {
			return self::CELL_TYPE_LINK;
		// Data cell
		} else {
			return self::CELL_TYPE_DATA;
		}
	}

	/**
	 *
	 * @param Entity $result
	 * @param string $path
	 * @param array $fieldParams
	 * @return string
	 */
	public function tbodyCell(Entity $result, $path, array $fieldParams = [])
	{
		$cell = null;

		switch($this->cellType($path))
		{
			case self::CELL_TYPE_LINK:
				$cell = $this->tbodyLinkCell($result, $path, $fieldParams);
				break;
			case self::CELL_TYPE_DATA:
				$cell = $this->tbodyDataCell($result, $path, $fieldParams);
				break;
		}

		return $cell;
	}

	/**
	 *
	 * @param ResultSet $results
	 * @param array $paths
	 * @return string
	 */
	public function tbody(ResultSet $results, array $paths)
	{
		$tbodyRows = '';

		foreach ($results as $result) {
			$tbodyCells = '';
			foreach (Hash::normalize($paths) as $path => $fieldParams) {
				$tbodyCells .= $this->tbodyCell($result, $path, (array)$fieldParams);
			}

			$tbodyRows .= $this->templater()->format('tbodyRows', ['tbodyCells' => $tbodyCells]);
		}

		return $this->templater()->format('tbody', ['tbodyRows' => $tbodyRows]);
	}

	/**
	 *
	 * @param ResultSet $results
	 * @param array $paths
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

	/**
	 *
	 * @param ResultSet $results
	 * @param array $paths
	 * @param array $params
	 * @return string
	 */
	public function index(ResultSet $results, array $paths, array $params = [])
	{
		if(count($results) > 0)
		{
			$index = $this->templater()->format(
				'index',
				[
					'table' => $this->table($results, $paths),
					'pagination' => $this->pagination(),
					'model' => Inflector::underscore($this->Paginator->defaultModel())
				]
			);

		} else {
			// TODO: i18n (domain, function) + attribute
			$message = __( Hash::get($params, 'message') );
			$index = $this->templater()->format(
				'empty',
				[
					'message' => $message !== null ? $message : 'No record was found'
				]
			);
		}

		return $index;
	}

	/**
	 *
	 * @param array $options
	 * @return string
	 */
	public function pagination( array $options = array() )
	{
		if($this->Paginator->param('count') < 1) {
			return null;
		}

		// TODO: i18n + attribute
		$links = $this->Paginator->first(__('<< first'), $options)
			.$this->Paginator->prev(__('< previous'), $options)
			.$this->Paginator->numbers($options)
			.$this->Paginator->next(__('next >'), $options)
			.$this->Paginator->last(__('last >>'), $options);

		$counter = $this->templater()->format('paginationCounter', ['counter' => $this->Paginator->counter()]);
		$links = $this->templater()->format('paginationLinks', ['links' => $links]);

		return $this->templater()->format(
			'pagination',
			[
				'counter' => $counter,
				'links' => $links
			]
		);
	}
}
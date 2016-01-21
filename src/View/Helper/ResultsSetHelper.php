<?php
namespace Helpers\View\Helper;

use Cake\Utility\Hash;
use Cake\ORM\Entity;
use Cake\ORM\ResultSet;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Usage
 *	$this->Html->css('Helpers.extra', ['block' => true]);
 *	echo $this->ResultsSet->index(
 *		$groups,
 *		[
 *			'id',
 *			'name',
 *			'created',
 *			'modified',
 *			'/Groups/view/{{id}}',
 *			'/Groups/edit/{{id}}',
 *			'/Groups/delete/{{id}}' => [
 *				'type' => 'post',
 *				'confirm' => __('Are you sure you want to delete the group « {{name}} » (# {{id}})?')
 *			],
 *		]
 *	);
 */
class ResultsSetHelper extends Helper
{
	use StringTemplateTrait;

	const CELL_LINK = 'link';
	const CELL_DATA = 'data';

	const LINK_A = 'a';
	const LINK_POST = 'post';

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
            'table' => '<table class="results_set">{{thead}}{{tfoot}}{{tbody}}</table>',
            'thead' => '<thead>{{theadRows}}</thead>',
			'theadRows' => '<tr>{{theadCells}}</tr>',
			'theadCell' => '<th>{{theadCell}}</th>',
			'theadActionCell' => '<th class="actions" colspan="{{count}}">{{message}}</th>',
			'tbody' => '<tbody>{{tbodyRows}}</tbody>',
			'tbodyRows' => '<tr>{{tbodyCells}}</tr>',
			//TODO: classes (data type null|true|false|positiove|negative|future|past|today)
			'tbodyDataCell' => '<td class="data {{type}} {{extra}}">{{data}}</td>',
			'tbodyLinkCell' => '<td class="action {{extra}}">{{link}}</td>',
			'index' => '<div class="index {{model}}">{{pagination}}{{table}}{{pagination}}</div>',
			'empty' => '<p class="notice">{{$message}}</p>',
			// Pagination
			'paginationCounter' => '<p>{{counter}}</p>',
			'paginationLinks' => '<ul class="pagination">{{links}}</ul>',
			'pagination' => '<div class="paginator">{{counter}}{{links}}</div>'
        ]
    ];

	// INFO: uniquement celles qui se trouvent à la fin
	protected function _actionCells(array $paths)
	{
		$result = [];
		$done = false;

		$paths = array_reverse(array_keys(Hash::normalize($paths)));
		foreach($paths as $path) {
			if($done === false) {
				if($this->cellType($path) === self::CELL_LINK) {
					$result[] = $path;
				} else {
					$done = true;
				}
			}
		}

		return array_reverse($result);
	}

	//@todo actions cell (theadCell)
	public function thead(array $paths)
	{
		$theadCells = '';
		foreach(Hash::normalize($paths) as $path => $fieldParams) {
			if ($this->cellType($path) === self::CELL_DATA) {
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

	// INFO paramètre pour désactiver les extra ($fieldParams['extra'])
	public function tbodyDataCell(Entity $result, $path, array $fieldParams = array())
	{
		$addExtra = false === isset($fieldParams['extra']) && !empty($fieldParams['extra']);

		return $this->templater()->format(
			'tbodyDataCell',
			[
				'data' => h($this->Result->value($result, $path, $fieldParams)),
				'type' => $this->Result->type($result, $path, $fieldParams),
				'extra' => $addExtra ? $this->Result->extra($result, $path, $fieldParams) : null
			]
		)."\n";
	}

	protected function _translate(Entity $result, $string)
	{
		$this->templater()->remove('_link');
		$this->templater()->add(['_link' => $string]);
		return $this->templater()->format('_link', $result->toArray());
	}

	/**
	 *
	 * @todo plugin, prefix
	 *
	 * @param Entity $result
	 * @param type $path
	 * @param array $fieldParams
	 * @return type
	 */
	protected function _link(Entity $result, $path)
	{
		$url = $this->_translate($result, $path);

		if (preg_match('/^\/(?<controller>[^\/]+)\/(?<action>[^\/]+)\/(?<extra>.*)$/', $url, $matches)) {
			$url = ['plugin' => null, 'controller' => $matches['controller'], 'action' => $matches['action']] + explode('/', $matches['extra']);
		} else {
			$url = $path;
		}
		$this->templater()->remove('_link');

		return $url;
	}

	public function tbodyLinkCell(Entity $result, $path, array $fieldParams = array())
	{
		// TODO: pre process, + headers
		/*/* @todo <th class="actions"><?= __('Actions') ?></th> +*/
		/*<td class="actions">
			<?= $this->Html->link(__('View'), ['action' => 'view', $result->id]) ?>
			<?= $this->Html->link(__('Edit'), ['action' => 'edit', $result->id]) ?>
			<?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $result->id], ['confirm' => __('Are you sure you want to delete # {0}?', $result->id)]) ?>
		</td>*/

		$url = $this->_link($result, $path);
		$text = is_array($url) ? __(Inflector::camelize($url['action'])) : $url;
		$type = Hash::get( $fieldParams, 'type' ) === self::LINK_POST ? self::LINK_POST : self::LINK_A;
		unset($fieldParams['type']);
		$addExtra = false === isset($fieldParams['extra']) && !empty($fieldParams['extra']);

		if(isset($fieldParams['confirm'])) {
			$fieldParams['confirm'] = $this->_translate($result, $fieldParams['confirm']);
		}

		return $this->templater()->format(
			'tbodyLinkCell',
			[
				'link' => $type === self::LINK_POST ? $this->Form->postLink($text, $url, $fieldParams) : $this->Html->link($text, $url, $fieldParams),
				'type' => $type,
				'extra' => $addExtra && is_array($url) ? Inflector::underscore("{$url['plugin']} {$url['controller']} {$url['action']}") : null
			]
		)."\n";
	}

	public function cellType($path)
	{
		// Link cell
		if (strpos($path, '/') === 0) {
			return self::CELL_LINK;
		// Data cell
		} else {
			return self::CELL_DATA;
		}
		// TODO input cell
	}

	public function tbodyCell(Entity $result, $path, array $fieldParams = array())
	{
		$cell = null;

		switch($this->cellType($path))
		{
			case self::CELL_LINK:
				$cell = $this->tbodyLinkCell($result, $path, $fieldParams);
				break;
			case self::CELL_DATA:
				$cell = $this->tbodyDataCell($result, $path, $fieldParams);
				break;
		}

		return $cell;
	}

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

	public function index(ResultSet $results, array $paths, array $params = array())
	{
		if(!empty($results))
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
			// TODO: i18n + attribute
			$message = __( Hash::get($params, 'message') || 'No record was found' );
			$index = $this->templater()->format('empty', ['message' => $message]);
		}

		return $index;
	}

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
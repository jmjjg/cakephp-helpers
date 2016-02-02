<?php
/**
 *
 */
namespace Helpers\View\Helper;

use Cake\View\Helper;
use Cake\View\Helper\PaginatorHelper as CakePaginatorHelper;
use Cake\View\View;

/**
 *
 */
class PaginatorHelper extends CakePaginatorHelper
{

    protected $_defaultTemplates = [
        'firstDisabled' => '<li class="first disabled"><a href="{{url}}">{{text}}</a></li>',
        'lastDisabled' => '<li class="last disabled"><a href="{{url}}">{{text}}</a></li>',
        'numberDisabled' => '<li class="active"><a href="{{url}}">{{text}}</a></li>',
        'counterPages' => 'Page {{page}} ou of {{pages}}, showing {{current}} records out of {{count}}'
    ];

    public function __construct(View $View, array $config = [])
    {
        $this->_defaultConfig['templates'] = array_merge(
            $this->_defaultConfig['templates'],
            $this->_defaultTemplates
        );
//debug(__FILE__);
        // TODO: (test from) config file
        parent::__construct($View, $config);
//        $this->config( 'templates', $this->_defaultConfig['templates'] );
    }

    public function first($first = '<< first', array $options = [])
    {
        if ($this->hasPrev() === false) {
            return $this->templater()->format('firstDisabled', [
                        'url' => null,
                        'text' => h($first)
            ]);
        } else {
            return parent::first($first, $options);
        }
    }

    public function last($last = 'last >>', array $options = [])
    {
        if ($this->hasNext() === false) {
            return $this->templater()->format('lastDisabled', [
                        'url' => null,
                        'text' => h($last)
            ]);
        } else {
            return parent::last($last, $options);
        }
    }

    public function numbers(array $options = [])
    {
        if ($this->param('page') === 1 && $this->param('pageCount') === 1) {
            return $this->templater()->format('numberDisabled', [
                        'url' => null,
                        'text' => 1
            ]);
        } else {
            parent::numbers($options);
        }
    }
}

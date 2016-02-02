<?php
/**
 *
 */
namespace Helpers\View\Helper;

use Cake\View\Helper\PaginatorHelper as CakePaginatorHelper;
use Cake\View\View;

/**
 *
 */
class PaginatorHelper extends CakePaginatorHelper
{

    /**
     * Default templates that will be merged.
     *
     * @var array
     */
    protected $_defaultTemplates = [
        'firstDisabled' => '<li class="first disabled"><a href="{{url}}">{{text}}</a></li>',
        'lastDisabled' => '<li class="last disabled"><a href="{{url}}">{{text}}</a></li>',
        'numberDisabled' => '<li class="active"><a href="{{url}}">{{text}}</a></li>',
        'counterPages' => 'Page {{page}} ou of {{pages}}, showing {{current}} records out of {{count}}'
    ];

    /**
     * Constructor. Overridden to merge templates.
     *
     * @param View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
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

    /**
     * Overwritten. Now returns an unclickable first link even when there is no
     * previous page.
     *
     * @param string|int $first if string use as label for the link. If numeric, the number of page links
     *   you want at the beginning of the range.
     * @param array $options An array of options.
     * @return string numbers string.
     */
    public function first($first = '<< first', array $options = [])
    {
        if ($this->hasPrev() === false) {
            return $this->templater()->format(
                'firstDisabled',
                [
                    'url' => null,
                    'text' => h($first)
                ]
            );
        } else {
            return parent::first($first, $options);
        }
    }

    /**
     * Overwritten. Now returns an unclickable last link even when there is no
     * next page.
     *
     * @param string|int $last if string use as label for the link, if numeric print page numbers
     * @param array $options Array of options
     * @return string numbers string.
     */
    public function last($last = 'last >>', array $options = [])
    {
        if ($this->hasNext() === false) {
            return $this->templater()->format(
                'lastDisabled',
                [
                    'url' => null,
                    'text' => h($last)
                ]
            );
        } else {
            return parent::last($last, $options);
        }
    }

    /**
     * Overwritten. Now returns an unclickable number link even when there is only
     * one page.
     *
     * @param array $options Options for the numbers.
     * @return string numbers string.
     */
    public function numbers(array $options = [])
    {
        if ($this->param('page') === 1 && $this->param('pageCount') === 1) {
            return $this->templater()->format(
                'numberDisabled',
                [
                    'url' => null,
                    'text' => 1
                ]
            );
        } else {
            parent::numbers($options);
        }
    }
}

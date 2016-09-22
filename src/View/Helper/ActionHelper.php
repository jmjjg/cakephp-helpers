<?php
/**
 * Source code for the ActionHelper class from the Helpers CakePHP 3 plugin.
 *
 * @author Christian Buffin
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Helpers\View\Helper;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;
use Helpers\Utility\Hash;
use Helpers\Utility\Url;

/**
 * The Helpers.ActionHelper makes displaying links easy.
 *
 * @see Helpers\View\Helper\ResultsSetHelper for sample usage.
 */
class ActionHelper extends Helper
{

    use StringTemplateTrait;

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
        'Html'
    ];

    /**
     * Default parameters
     *
     * @var array
     */
    protected $_defaults = [
        'url' => null,
        'extra' => true,
        'title' => true,
        'confirm' => false,
        'type' => self::LINK_TYPE_GET
    ];

    /**
     * Live cache for the title and confirm methods.
     *
     * @var array
     */
    protected $_cache = [
        'title' => [],
        'confirm' => []
    ];

    /**
     * Utility function to format a string with values from the entity using
     * the templater.
     *
     * @param Entity $result An Entity result
     * @param string $string The string to translate
     * @return string
     */
    protected function _format(Entity $result, $string)
    {
        $this->templater()->remove('_link');
        $this->templater()->add(['_link' => $string]);
        return $this->templater()->format('_link', $result->toArray());
    }

    /**
     * Returns a link for the params (use the params() method before).
     *
     * @param array $params The link parameters.
     * @return string
     */
    public function link(array $params)
    {
        $local = Hash::consume($params, ['text', 'url', 'type', 'extra']);

        return $local['type'] === self::LINK_TYPE_POST
            ? $this->Form->postLink($local['text'], $local['url'], $params)
            : $this->Html->link($local['text'], $local['url'], $params);
    }

    /**
     * Returns the default title msgid for a given controller and action pair.
     *
     * @todo Remove because unnecessary: use a custom class to provide translation
     *
     * @param string $controller The name of the controller
     * @param string $action The name of the action
     * @return string
     */
    public function title($controller, $action)
    {
        $key = "{$controller}.{$action}";

        if (!isset($this->_cache[__FUNCTION__][$key])) {
            $this->_cache[__FUNCTION__][$key] = __(
                sprintf(
                    '%s %s « {{name}} » (#{{id}})',
                    Inflector::humanize($action),
                    mb_convert_case(Inflector::classify($controller), MB_CASE_LOWER)
                )
            );
        }

        return $this->_cache[__FUNCTION__][$key];
    }

    /**
     * Returns the default confirm msgid for a given controller and action pair.
     *
     * @todo Remove because unnecessary: use a custom class to provide translation
     *
     * @param string $controller The name of the controller
     * @param string $action The name of the action
     * @return string
     */
    public function confirm($controller, $action)
    {
        $key = "{$controller}.{$action}";

        if (!isset($this->_cache[__FUNCTION__][$key])) {
            $this->_cache[__FUNCTION__][$key] = __(
                sprintf(
                    'Are you sure you want to %s the %s « {{name}} » (#{{id}})',
                    mb_convert_case(Inflector::humanize($action), MB_CASE_LOWER),
                    mb_convert_case(Inflector::humanize(Inflector::singularize($controller)), MB_CASE_LOWER)
                )
            );
        }

        return $this->_cache[__FUNCTION__][$key];
    }

    /**
     * Returns the link text for the given parameters.
     *
     * @fixme ? Remove because unnecessary: use a custom class to provide translation
     *
     * @param array $params The params; used keys are text and url
     * @return string
     */
    public function text(array $params)
    {
        if (isset($params['text'])) {
            $text = $params['text'];
        } else {
            if (is_array($params['url'])) {
                $text = __(Inflector::camelize($params['url']['action']));
            } else {
                $text = $params['url'];
                if (strpos($text, 'mailto:') === 0) {
                    $text = substr($text, 7);
                }
            }
        }

        return $text;
    }

    /**
     * Returns the params to be used by \Helpers\View\Helper\ActionHelper, completing
     * params for a given $path (URL) using the Entity as a data source.
     *
     * @param Entity $result The entity from which data can be read
     * @param string $path The URL, can use {{data}} extracted from the entity
     * @param array $params The original parameters to be completed
     * @return array
     */
    public function params(Entity $result, $path, array $params = [])
    {
        $params += $this->_defaults;

        $params['url'] = in_array($params['url'], [true, null], true)
            ? Url::parse($this->_format($result, $path))
            : $params['url'];

        if (is_array($params['url'])) {
            if ($params['title'] === true) {
                $params['title'] = $this->title($params['url']['controller'], $params['url']['action']);
            }

            if ($params['confirm'] === true) {
                $params['confirm'] = $this->confirm($params['url']['controller'], $params['url']['action']);
            }
        } else {
            unset($params['title'], $params['confirm']);
        }

        $params['text'] = $this->text($params);

        foreach (['confirm', 'title'] as $key) {
            if (isset($params[$key])) {
                $params[$key] = $this->_format($result, $params[$key]);
            }
        }

        return $params;
    }
}

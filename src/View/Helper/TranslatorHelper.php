<?php
namespace Helpers\View\Helper;

use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Helpers\Utility\Url;
use Translator\Utility\TranslatorsRegistry;

/**
 * The TranslatorHelper makes a bridge between the Translator plugin and the
 * Helpers plugin.
 */
class TranslatorHelper extends Helper
{
    protected $_translators = [];

    public function translator($name = null)
    {
        $className = null === $name ? TranslatorsRegistry::defaultTranslator() : $name;

        if (false === isset($this->_translators[$className])) {
            $this->_translators[$className] = TranslatorsRegistry::getInstance()->get($className);
        }

        return $this->_translators[$className];
    }

    public function params(array $params = [])
    {
        return $params + ['name' => null];
    }

    public function label($path, array $cell = [], array $params = [])
    {
        if (false === isset($cell['label'])) {
            $params = $this->params($params);
            $translator = $this->translator($params['name']);

            $cell['label'] = $translator->translate($path);
        }

        return $cell;
    }

    public function parse($path)
    {
        $data = Url::parse($path);

        $result = ['entity' => mb_convert_case(Inflector::singularize($data['controller']), MB_CASE_LOWER)];
        $result['action']['middle'] = Inflector::singularize($data['action']);
        $result['action']['start'] = mb_convert_case($result['action']['middle'], MB_CASE_TITLE);

        return $result;
    }

    public function action($path, array $cell = [], array $params = [])
    {
        $params = $this->params($params);
        $translator = $this->translator($params['name']);

        if (false === isset($cell['text'])) {
            $cell['text'] = $translator->translate($path);
        }

        $title = false === isset($cell['title']) || in_array($cell['title'], [null, true], true);
        $confirm = true === isset($cell['confirm']) && true === $cell['confirm'];

        if ($title || $confirm) {
            $parsed = $this->parse($path);

            if (true === $title) {
                $singular = "{$parsed['action']['start']} {$parsed['entity']} « {{name}} » (#{{id}})";
                $cell['title'] = $translator->translate($singular);
            }
            if (true === $confirm) {
                $singular = "Really {$parsed['action']['middle']} {$parsed['entity']} « {{name}} » (#{{id}})?";
                $cell['confirm'] = $translator->translate($singular);
            }
        }

        return $cell;
    }

    const TYPE_ACTION = 'action';

    const TYPE_LABEL = 'label';

    public function type($path)
    {
        if ('/' === $path[0]) {
            return self::TYPE_ACTION;
        }

        return self::TYPE_LABEL;
    }

    public function index(array $cells, array $params = [])
    {
        $params = $this->params($params);
        $translator = $this->translator($params['name']);
        $cells = Hash::normalize($cells);

        foreach ($cells as $path => $cell) {
            $type = $this->type($path);

            if (self::TYPE_ACTION === $type) {
                $cells[$path] = $this->action($path, (array)$cell, $params);
            } else {
                $cells[$path] = $this->label($path, (array)$cell, $params);
            }
        }

        return $cells;
    }
}

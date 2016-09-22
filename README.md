# cakephp-helpers

## Usage
```php
$this->Html->css('Helpers.extra', ['block' => true]);

// @see plugin Translator
$this->ResultsSet->config(
    'messages',
    [
        'first' => $translator->translate('<< first'),
        'prev' => $translator->translate('<< Previous'),
        'next' => $translator->translate('Next >>'),
        'last' => $translator->translate('last >>'),
        'empty' => $translator->translate('No record was found'),
        'counter' => $translator->translate('Page {{page}} out of {{pages}}, showing {{current}} records out of {{count}}')
    ]
);

echo $this->ResultsSet->index(
    $groups,
    $this->Translator->index(
        [
            'id',
            'name',
            'created',
            'modified',
            '/Groups/view/{{id}}' => [
                // INFO: set to false to disable auto title
                //'title' => __('View group « {{name}} » (#{{id}})')
            ],
            '/Groups/edit/{{id}}' => [
                // INFO: set to false to disable auto title
                //'title' => __('Edit group « {{name}} » (#{{id}})')
            ],
            '/Groups/delete/{{id}}' => [
                // INFO: set to false to disable auto title
                //'title' => __('Delete group « {{name}} » (#{{id}})'),
                'type' => 'post',
                // INFO: don't set or set to false to disable auto confirm message
                //'confirm' => __('Are you sure you want to delete the group « {{name}} » (# {{id}})?')
                'confirm' => true
            ],
        ]
    )
);
```

### Unit tests
```bash
php ../composer.phar require --dev phpunit/phpunit

sudo -u apache vendor/bin/phpunit plugins/Helpers

sudo -u apache vendor/bin/phpunit plugins/Helpers/tests/TestCase/View/Helper/PaginatorHelperTest
```

### Code quality
```bash
sudo bash -c "( rm logs/*.log ; rm -r logs/quality ; rm tmp/cache/models/myapp* ; rm tmp/cache/persistent/myapp* )" ; \
sudo -u apache ant quality -f plugins/Helpers/vendor/Jenkins/build.xml

wget http://localhost:8080/jnlpJars/jenkins-cli.jar

java -jar jenkins-cli.jar -s http://localhost:8080/ create-job "CakePHP 3 plugin Helpers" < plugins/Helpers/vendor/Jenkins/jobs/CakePHP3-Helpers-Plugin.xml
java -jar jenkins-cli.jar -s http://localhost:8080/ create-job "CakePHP 3 plugin Helpers Quality" < plugins/Helpers/vendor/Jenkins/jobs/CakePHP3-Helpers-Plugin-Quality.xml
```

## Credits
Uses a subset of [Famfamfam's Silk icon set](http://www.famfamfam.com/lab/icons/silk/), see README file in webroot/img/famfamfam_silk_icons
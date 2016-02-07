# cakephp-helpers

## Usage
```php
$this->Html->css('Helpers.extra', ['block' => true]);
echo $this->ResultsSet->index(
	$groups,
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
```

## Credits
Uses a subset of [Famfamfam's Silk icon set](http://www.famfamfam.com/lab/icons/silk/), see README file in webroot/img/famfamfam_silk_icons
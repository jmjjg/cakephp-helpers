# cakephp-helpers

# Usage
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
			//'title' => __('View group « {{name}} » (# {{id}})')
		],
		'/Groups/edit/{{id}}' => [
			// INFO: set to false to disable auto title
			//'title' => __('Edit group « {{name}} » (# {{id}})')
		],
		'/Groups/delete/{{id}}' => [
			// INFO: set to false to disable auto title
			//'title' => __('Delete group « {{name}} » (# {{id}})'),
			'type' => 'post',
			// INFO: don't set or set to false to disable auto confirm message
			//'confirm' => __('Are you sure you want to delete the group « {{name}} » (# {{id}})?')
			'confirm' => true
		],
	]
);
```

## Credits
Uses a subset of [Famfamfam's Silk icon set](http://www.famfamfam.com/lab/icons/silk/), see README file in webroot/img/famfamfam_silk_icons
<?php ?>
<!-- File: plugins/recacher/View/Cacher Queries/multiselect_dnstracking.ctp -->
<div class="top">
	<h1><?php echo __('Assign %s to %s', __('Recaching'), __('Cacher Queries')); ?></h1>
</div>
<div class="center">
	<div class="posts form">
	<?php echo $this->Form->create('CacherQuery', array('url' => array('action' => 'multiselect_recaching')));?>
	    <fieldset>
	        <legend><?php echo __('Assign %s to %s', __('Recaching'), __('Cacher Queries')); ?></legend>
	    	<?php
				echo $this->Form->input('recache', array(
					'label' => __('Recaching?'),
					'options' => array(
						0 => __('No'),
						1 => __('Yes'),
					),
				));
	    	?>
	    </fieldset>
	<?php echo $this->Form->end(__('Update')); ?>
	</div>
<?php
if(isset($selected_cacher_queries) and $selected_cacher_queries)
{
	$details = array();
	foreach($selected_cacher_queries as $selected_cacher_query)
	{
		$details[] = array('name' => __('Cacher Query: '), 'value' => $selected_cacher_query);
	}
	echo $this->element('Utilities.details', array(
			'title' => __('Selected Cacher Queries. Count: %s', count($selected_cacher_queries)),
			'details' => $details,
		));
}
?>
</div>

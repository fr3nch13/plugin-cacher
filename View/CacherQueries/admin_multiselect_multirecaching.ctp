<?php 
// File: plugins/recacher/View/CacherQueries/multiselect_multidnstracking.ctp

// content
$th = array(
	'CacherQuery.query_key' => array('content' => __('Cacher Query Key'), 'options' => array('sort' => 'CacherQuery.query_key')),
	'recache_select' => array('content' => __('Select Recache'), 'options' => array('class' => 'actions')),
	'CacherQuery.recache' => array('content' => __('Recache?'), 'options' => array('sort' => 'CacherQuery.recache')),
);

$td = array();
foreach ($cacher_queries as $i => $cacher_query)
{
	$recache = $this->Wrap->yesNo($cacher_query['CacherQuery']['recache']);
	$actions = $this->Form->input('CacherQuery.'.$i.'.id', array('type' => 'hidden', 'value' => $cacher_query['CacherQuery']['id']));
	$actions .= $this->Form->input('CacherQuery.'.$i.'.recache', array(
		'div' => false,
		'label' => false,
		'options' => array(0 => __('No'), 1 => __('Yes')),
		'selected' => $cacher_query['CacherQuery']['recache'],
	));

	$td[$i] = array(
		$cacher_query['CacherQuery']['query_key'],
		array(
			$actions,
			array('class' => 'actions'),
		),
		$recache,
	);
}

$before_table = false;
$after_table = false;

if($td)
{
	$before_table = $this->Form->create('CacherQuery', array('url' => array('action' => 'multiselect_multirecaching')));
	$after_table = $this->Form->end(__('Update'));
}

echo $this->element('Utilities.page_index', array(
	'page_title' => __('Select %s for these %s', __('Recaching'), __('Cacher Queries')),
	'use_search' => false,
	'th' => $th,
	'td' => $td,
	'before_table' => $before_table,
	'after_table' => $after_table,
));
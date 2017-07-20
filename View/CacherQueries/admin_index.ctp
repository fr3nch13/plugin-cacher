<?php 
// File: app/View/CacherQueries/admin_index.ctp

$page_options = array(
	$this->Html->link(__('Show All'), array(0)),
	$this->Html->link(__('Show Only Recaching'), array('yes')),
	$this->Html->link(__('Show Not Recaching'), array('no')),
);

// content
$th = array(
	'actions' => array('content' => __('Actions'), 'options' => array('class' => 'actions')),
	'CacherQuery.path' => array('content' => __('Path'), 'options' => array('sort' => 'CacherQuery.path')),
	'CacherQuery.model_id' => array('content' => __('Model ID'), 'options' => array('sort' => 'CacherQuery.model_id')),
	'CacherQuery.model_name' => array('content' => __('Model Name'), 'options' => array('sort' => 'CacherQuery.model_name')),
	'CacherQuery.model_use' => array('content' => __('Model to Use'), 'options' => array('sort' => 'CacherQuery.model_use')),
	'CacherQuery.query_key' => array('content' => __('Query Key'), 'options' => array('sort' => 'CacherQuery.query_key')),
	'CacherQuery.recache' => array('content' => __('Recache?'), 'options' => array('sort' => 'CacherQuery.recache')),
	'CacherQuery.querytime' => array('content' => __('Last Recache Time'), 'options' => array('sort' => 'CacherQuery.querytime')),
	'CacherQuery.recacheerror' => array('content' => __('Error?'), 'options' => array('sort' => 'CacherQuery.recacheerror')),
	'CacherQuery.recached' => array('content' => __('Last Recached'), 'options' => array('sort' => 'CacherQuery.recached')),
	'CacherQuery.lastran' => array('content' => __('Last Ran'), 'options' => array('sort' => 'CacherQuery.lastran')),
	'CacherQuery.requested' => array('content' => __('Last Requested'), 'options' => array('sort' => 'CacherQuery.requested')),
	'CacherQuery.created' => array('content' => __('Created'), 'options' => array('sort' => 'CacherQuery.created')),
	'multiselect' => true,
);

$td = array();
foreach ($cacher_queries as $i => $cacher_query)
{
	$actions = $this->Html->link(__('View'), array('action' => 'view', $cacher_query['CacherQuery']['id']));
	$actions .= $this->Html->link(__('Delete'),array('action' => 'delete', $cacher_query['CacherQuery']['id']),array('confirm' => 'Are you sure?'));
	
	$td[$i] = array(
		array(
			$actions,
			array('class' => 'actions'),
		),
		$this->Html->link($cacher_query['CacherQuery']['path'], $cacher_query['CacherQuery']['path']),
		$cacher_query['CacherQuery']['model_id'],
		$cacher_query['CacherQuery']['model_name'],
		$cacher_query['CacherQuery']['model_use'],
		$cacher_query['CacherQuery']['query_key'],
		array(
			$this->Html->link($this->Wrap->yesNo($cacher_query['CacherQuery']['recache']), array('action' => 'toggle', 'recache', $cacher_query['CacherQuery']['id']), array('confirm' => 'Are you sure?')), 
			array('class' => 'actions'),
		),
//		$this->Wrap->niceSeconds($cacher_query['CacherQuery']['querytime']),
		$cacher_query['CacherQuery']['querytime'],
		$this->Wrap->yesNo($cacher_query['CacherQuery']['recacheerror']),
		$this->Wrap->niceTime($cacher_query['CacherQuery']['recached']),
		$this->Wrap->niceTime($cacher_query['CacherQuery']['lastran']),
		$this->Wrap->niceTime($cacher_query['CacherQuery']['requested']),
		$this->Wrap->niceTime($cacher_query['CacherQuery']['created']),
		'multiselect' => $cacher_query['CacherQuery']['id'],
	);
}

echo $this->element('Utilities.page_index', array(
	'page_title' => __('%s %s', $lookup_type, __('Cacher Queries')),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
	// multiselect options
	'use_multiselect' => true,
	'multiselect_options' => array(
		'recaching' => __('Modify Recaching - All'),
		'multirecaching' => __('Modify Recaching - Invidual'),
		'delete' => __('Delete'),
	),
	'multiselect_referer' => array(
		'admin' => true,
		'controller' => 'cacher_queries',
		'action' => 'index',
		'plugin' => 'cacher',
	),
));
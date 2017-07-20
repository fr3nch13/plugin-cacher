<?php 
// File: app/View/CacherQueries/view.ctp

$page_options = array();
$page_options[] = $this->Form->postLink(__('Delete'),array('action' => 'delete', $cacher_query['CacherQuery']['id']), array('confirm' => 'Are you sure?'));

$details = array();
$details[] = array('name' => __('Key'), 'value' => $cacher_query['CacherQuery']['query_key']);
$details[] = array('name' => __('Model ID'), 'value' => $cacher_query['CacherQuery']['model_id']);
$details[] = array('name' => __('Model Name'), 'value' => $cacher_query['CacherQuery']['model_name']);
$details[] = array('name' => __('Model Alias'), 'value' => $cacher_query['CacherQuery']['model_alias']);
$details[] = array('name' => __('Model To Use'), 'value' => $cacher_query['CacherQuery']['model_use']);
$details[] = array('name' => __('Path'), 'value' => $this->Html->link($cacher_query['CacherQuery']['path'], $cacher_query['CacherQuery']['path']));
$details[] = array('name' => __('Last Requested'), 'value' => $this->Wrap->niceTime($cacher_query['CacherQuery']['requested']));
$details[] = array('name' => __('Recache?'), 'value' => $this->Wrap->yesNo($cacher_query['CacherQuery']['recache']));
$details[] = array('name' => __('Last Ran'), 'value' => $this->Wrap->niceTime($cacher_query['CacherQuery']['lastran']));
$details[] = array('name' => __('Last Recached'), 'value' => $this->Wrap->niceTime($cacher_query['CacherQuery']['recached']));
$details[] = array('name' => __('Cache Time'), 'value' => $this->Wrap->niceTime($cacher_query['CacherQuery']['fstat']['ctime_nice']));
$details[] = array('name' => __('Error?'), 'value' => $this->Wrap->yesNo($cacher_query['CacherQuery']['recacheerror']));
$details[] = array('name' => __('Last Error Msg'), 'value' => $cacher_query['CacherQuery']['recacheerrormsg']);
$details[] = array('name' => __('Created'), 'value' => $this->Wrap->niceTime($cacher_query['CacherQuery']['created']));



$stats = array();

$tabs = array();

$tabs[] = array(
	'key' => 'query',
	'title' => __('Query'),
	'content' => $this->Wrap->descView(print_r(unserialize($cacher_query['CacherQuery']['query']), true)),
);

echo $this->element('Utilities.page_view', array(
	'page_title' => __('Cacher Query: %s', $cacher_query['CacherQuery']['query_key']),
	'page_options' => $page_options,
	'details_title' => __('Details'),
	'details' => $details,
	'stats' => $stats,
	'tabs_id' => 'tabs',
	'tabs' => $tabs,
));
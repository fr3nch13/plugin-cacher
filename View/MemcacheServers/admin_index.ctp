<?php 
// File: plugins/cacher/View/MemcacheServers/admin_index.ctp

$page_options = array(
	$this->Html->link(__('Update Stats for All'), array('action' => 'refresh')),
);

// content
$th = array(
	'actions' => array('content' => __('Actions'), 'options' => array('class' => 'actions')),
	'MemcacheServer.host' => array('content' => __('Host'), 'options' => array('sort' => 'MemcacheServer.host')),
	'MemcacheServer.uptime' => array('content' => __('Uptime - Last time Memcache was started'), 'options' => array('sort' => 'MemcacheServer.uptime')),
	'MemcacheServer.total_items' => array('content' => __('Total Items Stored'), 'options' => array('sort' => 'MemcacheServer.total_items')),
	'MemcacheServer.limit_maxbytes' => array('content' => __('Max Bytes for Storage'), 'options' => array('sort' => 'MemcacheServer.limit_maxbytes')),
	'MemcacheServer.bytes_read' => array('content' => __('Total Bytes Read'), 'options' => array('sort' => 'MemcacheServer.bytes_read')),
	'MemcacheServer.bytes_written' => array('content' => __('Total Bytes Written'), 'options' => array('sort' => 'MemcacheServer.bytes_written')),
	'MemcacheServer.total_connections' => array('content' => __('Total Opened Connections'), 'options' => array('sort' => 'MemcacheServer.total_connections')),
	'MemcacheServer.modified' => array('content' => __('Last Updated'), 'options' => array('sort' => 'MemcacheServer.modified')),
	'MemcacheServer.created' => array('content' => __('Created'), 'options' => array('sort' => 'MemcacheServer.created')),
	'MemcacheServer.version' => array('content' => __('Memcache Version'), 'options' => array('sort' => 'MemcacheServer.version')),
);

$td = array();
foreach ($memcache_servers as $i => $memcache_server)
{
	$actions = $this->Html->link(__('View'), array('action' => 'view', $memcache_server['MemcacheServer']['id']));
//	$actions .= $this->Html->link(__('Delete'),array('action' => 'delete', $memcache_server['MemcacheServer']['id']),array('confirm' => 'Are you sure?'));
	
	$td[$i] = array(
		array(
			$actions,
			array('class' => 'actions'),
		),
		$this->Html->link($memcache_server['MemcacheServer']['host'], array('action' => 'view', $memcache_server['MemcacheServer']['id'])),
		$this->Wrap->niceSeconds($memcache_server['MemcacheServer']['uptime']). ' ('. $memcache_server['MemcacheServer']['uptime']. ')',
		$this->Wrap->niceNumber($memcache_server['MemcacheServer']['total_items']),
		$this->Wrap->formatBytes($memcache_server['MemcacheServer']['limit_maxbytes']). ' ('. $memcache_server['MemcacheServer']['limit_maxbytes']. ')',
		$this->Wrap->formatBytes($memcache_server['MemcacheServer']['bytes_read']). ' ('. $memcache_server['MemcacheServer']['bytes_read']. ')',
		$this->Wrap->formatBytes($memcache_server['MemcacheServer']['bytes_written']). ' ('. $memcache_server['MemcacheServer']['bytes_written']. ')',
		$this->Wrap->niceNumber($memcache_server['MemcacheServer']['total_connections']),
		$this->Wrap->niceTime($memcache_server['MemcacheServer']['modified']),
		$this->Wrap->niceTime($memcache_server['MemcacheServer']['created']),
		$memcache_server['MemcacheServer']['version'],
		'multiselect' => $memcache_server['MemcacheServer']['id'],
	);
}

echo $this->element('Utilities.page_index', array(
	'page_title' => __('Memcache Servers'),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
/*
	// multiselect options
	'use_multiselect' => true,
	'multiselect_options' => array(
		'recaching' => __('Modify Recaching - All'),
		'multirecaching' => __('Modify Recaching - Invidual'),
		'delete' => __('Delete'),
	),
	'multiselect_referer' => array(
		'admin' => true,
		'controller' => 'memcache_servers',
		'action' => 'index',
		'plugin' => 'cacher',
	),
*/
));
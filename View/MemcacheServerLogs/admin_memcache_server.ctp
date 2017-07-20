<?php 
// File: plugins/cacher/View/MemcacheServerLogs/admin_index.ctp

$page_options = array(
);

// content
$th = array(
	'MemcacheServerLog.uptime' => array('content' => __('Uptime - Last time Memcache was started'), 'options' => array('sort' => 'MemcacheServerLog.uptime')),
	'MemcacheServerLog.total_items' => array('content' => __('Total Items Stored'), 'options' => array('sort' => 'MemcacheServerLog.total_items')),
	'MemcacheServerLog.get_hits' => array('content' => __('Items Requested/Found'), 'options' => array('sort' => 'MemcacheServerLog.get_hits')),
	'MemcacheServerLog.get_misses' => array('content' => __('Items Requested/Not Found'), 'options' => array('sort' => 'MemcacheServerLog.get_misses')),
	'MemcacheServerLog.evictions' => array('content' => __('Items Evicted'), 'options' => array('sort' => 'MemcacheServerLog.evictions')),
	'MemcacheServerLog.limit_maxbytes' => array('content' => __('Max Bytes for Storage'), 'options' => array('sort' => 'MemcacheServerLog.limit_maxbytes')),
	'MemcacheServerLog.bytes' => array('content' => __('Bytes Used for Storage'), 'options' => array('sort' => 'MemcacheServerLog.limit_maxbytes')),
	'MemcacheServerLog.bytes_read' => array('content' => __('Total Bytes Read'), 'options' => array('sort' => 'MemcacheServerLog.bytes_read')),
	'MemcacheServerLog.bytes_written' => array('content' => __('Total Bytes Written'), 'options' => array('sort' => 'MemcacheServerLog.bytes_written')),
	'MemcacheServerLog.cmd_get' => array('content' => __('Retrieval Requests'), 'options' => array('sort' => 'MemcacheServerLog.cmd_get')),
	'MemcacheServerLog.cmd_set' => array('content' => __('Storage Requests'), 'options' => array('sort' => 'MemcacheServerLog.cmd_set')),
	'MemcacheServerLog.curr_connections' => array('content' => __('Current Open Connections'), 'options' => array('sort' => 'MemcacheServerLog.curr_connections')),
	'MemcacheServerLog.total_connections' => array('content' => __('Total Opened Connections'), 'options' => array('sort' => 'MemcacheServerLog.total_connections')),
	'MemcacheServerLog.created' => array('content' => __('Created'), 'options' => array('sort' => 'MemcacheServerLog.created')),
);

/*

array('name' => __('Retrieval Requests'), 'value' => $memcache_server['MemcacheServer']["cmd_get"]),
array('name' => __('Storage Requests'), 'value' => $memcache_server['MemcacheServer']["cmd_set"]),
*/

$td = array();
foreach ($memcache_server_logs as $i => $memcache_server_log)
{
	$percCacheHit=0;
	if($memcache_server_log['MemcacheServerLog']["cmd_get"])
		$percCacheHit=((real)$memcache_server_log['MemcacheServerLog']["get_hits"]/ (real)$memcache_server_log['MemcacheServerLog']["cmd_get"] *100);
	$percCacheHit=round($percCacheHit,3);
	$percCacheMiss=100-$percCacheHit;

	$td[$i] = array(
		$this->Wrap->niceSeconds($memcache_server_log['MemcacheServerLog']['uptime']). ' ('. $memcache_server_log['MemcacheServerLog']['uptime']. ')',
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['total_items']),
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['get_hits']). ' ('. $percCacheHit. '%)',
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['get_misses']). ' ('. $percCacheMiss. '%)',
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['evictions']),
		$this->Wrap->formatBytes($memcache_server_log['MemcacheServerLog']['limit_maxbytes']). ' ('. $memcache_server_log['MemcacheServerLog']['limit_maxbytes']. ')',
		$this->Wrap->formatBytes($memcache_server_log['MemcacheServerLog']['bytes']). ' ('. $memcache_server_log['MemcacheServerLog']['bytes']. ')',
		$this->Wrap->formatBytes($memcache_server_log['MemcacheServerLog']['bytes_read']). ' ('. $memcache_server_log['MemcacheServerLog']['bytes_read']. ')',
		$this->Wrap->formatBytes($memcache_server_log['MemcacheServerLog']['bytes_written']). ' ('. $memcache_server_log['MemcacheServerLog']['bytes_written']. ')',
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['cmd_get']),
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['cmd_set']),
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['curr_connections']),
		$this->Wrap->niceNumber($memcache_server_log['MemcacheServerLog']['total_connections']),
		$this->Wrap->niceTime($memcache_server_log['MemcacheServerLog']['created']),
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
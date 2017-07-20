<?php 
// File: plugins/cacher/View/CacherQueries/admin_memcache_stats.ctp
$page_options = array(
	$this->Html->link(__('Update Stats'), array('action' => 'refresh', $memcache_server['MemcacheServer']['id'])),
);

$details_blocks = array();
$multi_stats = array();

$details_blocks[1][1] = array(
	'title' => __('General Details'),
	'details' => array(
		array('name' => __('Host'), 'value' => $memcache_server['MemcacheServer']['host']),
		array('name' => __('Port'), 'value' => $memcache_server['MemcacheServer']['port']),
		array('name' => __('Memcache Version'), 'value' => $memcache_server['MemcacheServer']['version']),
		array('name' => __('Libevent Version'), 'value' => $memcache_server['MemcacheServer']['libevent']),
		array('name' => __('Process ID'), 'value' => $memcache_server['MemcacheServer']['pid']),
	),
);

$details_blocks[1][2] = array(
	'title' => __('Time Stats'),
	'details' => array(
		array('name' => __('Uptime'), 'value' => $this->Wrap->niceSeconds($memcache_server['MemcacheServer']['uptime']). ' ('. $memcache_server['MemcacheServer']['uptime']. ')'),
		array('name' => __('Accum User Time'), 'value' => $memcache_server['MemcacheServer']["rusage_user"]),
		array('name' => __('Accum System Time'), 'value' => $memcache_server['MemcacheServer']["rusage_system"]),
		array('name' => __('Last Updated'), 'value' => $this->Wrap->niceTime($memcache_server['MemcacheServer']['modified'])),
		array('name' => __('First Added'), 'value' => $this->Wrap->niceTime($memcache_server['MemcacheServer']['created'])),
	),
);

// calculations
$percCacheHit=0;
if($memcache_server['MemcacheServer']["cmd_get"])
	$percCacheHit=((real)$memcache_server['MemcacheServer']["get_hits"]/ (real)$memcache_server['MemcacheServer']["cmd_get"] *100);
$percCacheHit=round($percCacheHit,3);
$percCacheMiss=100-$percCacheHit;


$multi_stats[] = array(
	'title' => __('Items'),
	'stats' => array(
		array('name' => __('Total Items Stored'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["total_items"])),
		array('name' => __('Items Requested &amp; Found'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["get_hits"]), 'tip' => $percCacheHit. '%'),
		array('name' => __('Items Requested &amp; Not Found'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["get_misses"]), 'tip' => $percCacheMiss. '%'),
		array('name' => __('Total Items Evicted'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["evictions"])),
	),
);

$multi_stats[] = array(
	'title' => __('Memory'),
	'stats' => array(
		array('name' => __('Max Bytes allowed for Storage'), 'value' => $this->Wrap->formatBytes($memcache_server['MemcacheServer']["limit_maxbytes"]), 'tip' => __('In Bytes: '. $memcache_server['MemcacheServer']["limit_maxbytes"])),
		array('name' => __('Bytes Used for Storage'), 'value' => $this->Wrap->formatBytes($memcache_server['MemcacheServer']["bytes"]), 'tip' => __('In Bytes: '. $memcache_server['MemcacheServer']["bytes"])),
		array('name' => __('Total Bytes Read'), 'value' => $this->Wrap->formatBytes($memcache_server['MemcacheServer']["bytes_read"]), 'tip' => __('In Bytes: '. $memcache_server['MemcacheServer']["bytes_read"])),
		array('name' => __('Total Bytes Written'), 'value' => $this->Wrap->formatBytes($memcache_server['MemcacheServer']["bytes_written"]), 'tip' => __('In Bytes: '. $memcache_server['MemcacheServer']["bytes_written"])),
	),
);

$multi_stats[] = array(
	'title' => __('Connections'),
	'stats' => array(
		array('name' => __('Total Opened Connections'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["total_connections"])),
		array('name' => __('Current Open Connections'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["curr_connections"])),
		array('name' => __('Retrieval Requests'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["cmd_get"])),
		array('name' => __('Storage Requests'), 'value' => $this->Wrap->niceNumber($memcache_server['MemcacheServer']["cmd_set"])),
	),
);

$tabs = array();
$tabs[] = array(
	'key' => 'logs',
	'title' => __('Past Stats'),
	'url' => array('controller' => 'memcache_server_logs', 'action' => 'memcache_server', $memcache_server['MemcacheServer']['id']),
);

$caches = array();
foreach($cache_configs as $cache_name => $cache_settings)
{
	$caches[] = array('name' => $cache_name, 'value' => $this->Wrap->niceSeconds($cache_settings['duration']));
}

$tabs[] = array(
	'key' => 'caches',
	'title' => __('Cache Configs using this Server'),
	'content' => $this->element('Utilities.details', array(
				'title' => __('Name and Expiration'),
				'options' => false,
				'details' => $caches,
			)),
);

echo $this->element('Utilities.page_view_columns', array(
	'page_title' => __('Memcache Stats for %s:%s', $memcache_server['MemcacheServer']['host'], $memcache_server['MemcacheServer']['port']),
	'page_options' => $page_options,
	'details_blocks' => $details_blocks,
	'multi_stats' => $multi_stats,
	'tabs_id' => 'tabs',
	'tabs' => $tabs,
));
<?php

// for looking information from dnsdbapi
CakeLog::config('cacher', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('cacher'),
	'file' => 'cacher.log',
));

	Cache::config('slowQueries', array(
		'engine' => 'Memcache',
		'duration' => 1800, // half hour
		'prefix' => 'slowQueries',
		'mask' => 0777,
		'compress' => false,
	)); 

	Cache::config('recacherLocks', array(
		'engine' => 'Memcache',
		'duration' => 1800, // half hour
		'prefix' => 'recacherLocks',
		'mask' => 0777,
	)); 
	
if(!Configure::read('Cacher.sql_threshold'))
{
	Configure::write('Cacher.sql_threshold', 0); // if not set, set to 0
}
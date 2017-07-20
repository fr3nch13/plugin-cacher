<?php

App::uses('AppModel', 'Model');
/**
 * CacheManager Model
 *
 * Provides access to the CacheSource for the cron script to refresh the caches
 */
class CacheManager extends AppModel 
{
	public $useTable = false;
	
	public $actsAs = array(
		'Cacher.Cache' => array(
			'config' => 'slowQueries',
			'gzip' => false,
		),
	);
}
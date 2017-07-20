<?php

App::uses('AppModel', 'Model');
/**
 * MemcacheServerLog Model
 *
 */
class MemcacheServerLog extends AppModel 
{
	public $belongsTo = array(
		'MemcacheServer' => array(
			'className' => 'MemcacheServer',
			'foreignKey' => 'memcache_server_id',
		),
	);
	
	public $actsAs = array(
		'Utilities.Common', 
		'Utilities.Shell',
	);
	
	// define the fields that can be searched
	public $searchFields = array(
		'MemcacheServerLog.host',
	);
}
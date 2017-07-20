<?php
/**
 * Cache data source class.
 *
 * @copyright     Copyright 2010, Jeremy Harris
 * @link          http://42pixels.com
 * @package       cacher
 * @subpackage    cacher.models.behaviors
 */

/**
 * Includes
 */
App::uses('Folder', 'Utility');
App::uses('DataSource', 'Model/Datasource');

/**
 * CacheSource datasource
 *
 * Gets find results from cache instead of the original datasource. The cache
 * is stored under CACHE/cacher.
 *
 * @package       cacher
 * @subpackage    cacher.models.datasources
 */
class CacheSource extends DataSource {

/**
 * Stored original datasource for fallback methods
 *
 * @var DataSource
 */
	public $source = null;
	
	public $forceRefresh = false;
	
	// placeholder for the CacherQuery model which the paming is being moved to
	public $CacherQuery = false;
	
	// if the query was actually submitted, not pulled from cache
	public $queried = false; 
	
	// if true, then the last ran query was cached/recached
	public $recached = false;
	
	// track if there was an error
	public $recache_error = false;
	public $recache_error_msg = false;
	
	public $CacheEngine = false;
	
	public $recache_threshold = 0; // set in the app config in the frontend

/**
 * Constructor
 *
 * Sets default options if none are passed when the datasource is created and
 * creates the cache configuration. If a `config` is passed and is a valid
 * Cache configuration, CacheSource uses its settings
 *
 * ### Extra config settings
 * - `original` The name of the original datasource, i.e., 'default' (required)
 * - `config` The name of the Cache configuration to use. Uses 'default' by default
 * - other settings required by DataSource...
 *
 * @param array $config Configure options
 */
	public function __construct($config = array()) {
		$config = array_merge(array('config' => 'default'), $config);
		parent::__construct($config);

		if(Configure::read('Cache.disable') === true) {
			return;
		}
		if(!isset($this->config['original'])) {
			throw new CakeException('Missing name of original datasource.');
		}
		if(!Cache::isInitialized($this->config['config'])) {
			Cache::config($this->config['config'], array(
				'engine' => 'File',
				'duration'=> 600,
				'prefix' => $this->config['config'],
				'mask' => 0777,
			)); 
			//throw new CacheException(sprintf('Missing cache configuration for "%s".', $this->config['config']));
		}
		
		// set the threshold
		if(!$this->recache_threshold = Configure::read('Cacher.sql_threshold'))
		{
			$this->recache_threshold = 0;
		}

		$this->source = ConnectionManager::getDataSource($this->config['original']);
		
		// cache garbage collection
		Cache::gc($this->config['config']);
	}

/**
 * Redirects calls to original datasource methods. Needed if the `Cacher.Cache` 
 * behavior is attached before other behaviors that use the model's datasource methods.
 * 
 * @param string $name Original db source function name
 * @param array $arguments Arguments
 * @return mixed
 */
	public function __call($name = null, $arguments = array()) {
		return call_user_func_array(array($this->source, $name), $arguments);
	}
	
/**
 * Reads from cache if it exists. If not, it falls back to the original
 * datasource to retrieve the data and cache it for later
 *
 * @param Model $Model
 * @param array $queryData
 * @return array Results
 * @see DataSource::read()
 */
	public function read(Model $Model, $queryData = array(), $recursive = null) 
	{ 
		// track if we're recaching
		$recaching = false;
		if(isset($queryData['recaching']))
		{
			$recaching = true;
			unset($queryData['recaching']);
		}
		
		// get the cache fstat info
		$recach_fstat = false;
		if(isset($queryData['recach_fstat']))
		{
			$recach_fstat = true;
			unset($queryData['recach_fstat']);
		}
		
		if(!isset($queryData['recursive']))
			$queryData['recursive'] = $Model->recursive;
		
		$this->_resetSource($Model);
		$key = $this->_key($Model, $queryData);
		
		$recached = $this->recached = false;
		$queried = $this->queried = false;
		
		$requested = false;
		if(!$recaching)
		{
			$requested = date('Y-m-d H:i:s');
		}
		
		$querytime = 0;
		
		$results = Cache::read($key, $this->config['config']);
		
		if($results === false) 
		{
			$recaching_data = array();
			$querytime_start = microtime(true);
			$querytime = 0;
			
			if($recaching)
			{	
				$this->recache_error = $recache_error = false;
				$this->recache_error_msg = $recache_error_msg = false;
				
				try
				{
					$results = $this->source->read($Model, $queryData);
					
					$queried = $this->queried = true;
					$querytime_end = microtime(true);
					$querytime = $querytime_end - $querytime_start;
					$recaching_data['querytime'] = $querytime;
				}
				catch (Exception $e) 
				{
					$this->recache_error = $recache_error = true;
					$this->recache_error_msg = $recache_error_msg = $e->getMessage();
				}
				
				$recaching_data['recacheerror'] = $recache_error;
				
				if($recache_error)
				{
					$recaching_data['recacheerrormsg'] = $recache_error_msg;
				}
			}
			else
			{
				$recaching_data['requested'] = $requested;
				
				$results = $this->source->read($Model, $queryData);
				
				$queried = $this->queried = true;
				$querytime_end = microtime(true);
				$querytime = $querytime_end - $querytime_start;
				$recaching_data['querytime'] = $querytime;
			}
			
			$cacheme = true;
			
			if($querytime < $this->recache_threshold)
			{
				$cacheme = false;
			}
			
			// if it meets the criteria to be cached
			if($cacheme)
			{
				if(isset($this->config['gzip'])) {
					Cache::write($key, gzcompress(serialize($results)), $this->config['config']);
				} else {
					Cache::write($key, $results, $this->config['config']);
				}
				
				$recached = $this->recached = true;
				$this->_map($Model, $key, $queryData, $recaching_data, $recaching, $queried, $recached);
				
				// updated with the _map, don't need to do it again
				$requested = false;
			}
		} 
		else 
		{
			// uncompress data from cache
			if(isset($this->config['gzip'])) {
				$results = unserialize(gzuncompress($results));
			}
		}
		
		// if we're here, this query was requested outside of recaching 
		if($requested)
		{
			$this->_update_requested($Model, $key, $requested);
		}
		
		return $results;
	}
	
	public function fstat(Model $Model, $key = false)
	{
		if(!$cache_settings = Cache::settings($this->config['config']))
		{
			return false;
		}
		
		$results = array(
			'cache_settings' => $cache_settings,
			'atime' => false,
			'atime_nice' => false,
			'mtime' => false,
			'mtime_nice' => false,
			'ctime' => false,
			'ctime_nice' => false,
		);
		
		return $results;
		
		if(!$this->CacheEngine and isset($cache_settings['engine']) and $cache_settings['engine'])
		{
			list($plugin, $class) = pluginSplit($cache_settings['engine'], true);
			$cacheClass = $class . 'Engine';
			App::import($cacheClass, $plugin . 'Cache/Engine');
			$this->CacheEngine = new $cacheClass();
			$this->CacheEngine->init($cache_settings);
		}
		
		$results = array(
			'cache_settings' => $cache_settings,
			'atime' => false,
			'atime_nice' => false,
			'mtime' => false,
			'mtime_nice' => false,
			'ctime' => false,
			'ctime_nice' => false,
		);
		
		
		if($fstats = $this->CacheEngine->fstat($key))
		{
			$results['atime'] = (isset($fstats['atime'])?$fstats['atime']:false);
			$results['mtime'] = (isset($fstats['mtime'])?$fstats['mtime']:false);
			$results['ctime'] = (isset($fstats['ctime'])?$fstats['ctime']:false);
			$results['atime_nice'] = ($results['atime']?date('Y-m-d H:i:s', $results['atime']):false);
			$results['mtime_nice'] = ($results['mtime']?date('Y-m-d H:i:s', $results['mtime']):false);
			$results['ctime_nice'] = ($results['ctime']?date('Y-m-d H:i:s', $results['ctime']):false);
		}
		
		return $results;
		
	}
	
	
	public function deleteRecache(Model $Model, $key = false)
	{
		if($key)
		{
			return Cache::delete($key, $this->config['config']);
		}
		return false;
	}

/*
 * Refreshes the cache for the queries that are marked for recaching
 * @param Model $Model
 */
	public function recache(Model $Model, $modelName = false, $queryData = array())
	{
		if(!isset($this->{$modelName}))
		{
			list($plugin, $className) = pluginSplit($modelName, true, null);
			if($plugin) $plugin = "{$plugin}.";
			App::import('Model', "{$plugin}{$modelName}");
			$this->{$modelName} = new $modelName(null, Inflector::tableize($className));
		}
		
		$queryData['recaching'] = true;
		
		return $this->read($this->{$modelName}, $queryData);
	}

/*
 * Clears the cache for a specific model and rewrites the map. Pass query to
 * clear a specific query's cached results
 *
 * @param array $query If null, clears all for this model
 * @param Model $Model The model to clear the cache for
 */
	public function clearModelCache(Model $Model, $clear_map) {
		$keys = $this->_get_map($Model, true);
		
		if(empty($keys)) {
			return;
		}
		
		foreach ($keys as $map_id => $cacheKey) {
			Cache::delete($cacheKey, $this->config['config']);
		}
		
		if($clear_map)
		{
			$this->_clear_map($Model, array_keys($keys));
		}
	}

/**
 * Hashes a query into a unique string and creates a cache key
 *
 * @param Model $Model The model
 * @param array $query The query
 * @return string
 */
	protected function _key(Model $Model, $query = array()) {
		$query = array_merge(
			array(
				'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null, 'recursive' => $Model->recursive,
				'offset' => null, 'order' => null, 'page' => null, 'group' => null, 'callbacks' => true
			),
			(array)$query
		);
		
		// remove some un needed keys for the proper cache key
		$removeMe = array('recache', 'cacher', 'cacher_path');
		foreach($removeMe as $removeField)
		{
			if(isset($query[$removeField])) unset($query[$removeField]);
		}
		ksort($query);
		
		$gzip = (isset($this->config['gzip'])) ? '_gz' : '';
		$queryHash = md5(serialize($query));
		$sourceName = $this->source->configKeyName;
		return Inflector::underscore($sourceName).'_'.Inflector::underscore($Model->alias).'_'.$queryHash.$gzip;
	}
	
/*
 * get the map of cached queries
 */
	protected function _get_map(Model $Model, $justKeys = false)
	{
		$this->_loadCacherQueryModel($Model);
		
		$results = $this->CacherQuery->getMap($Model->name, $Model->id, $justKeys);
		
		return $results;
	}
	
	protected function _clear_map(Model $Model, $ids = array())
	{
		if(empty($ids)) return true;
		
		$this->_loadCacherQueryModel($Model);
		
		return $this->CacherQuery->clearMap($ids);
	}
	
/**
 * Creates a cache map (used for tracking cache keys)
 * 
 * @param Model $Model
 * @param string $key 
 */
	protected function _map(Model $Model, $query_key = null, $queryData = array(), $recaching_data = array(), $recaching = false, $queried = false, $recached = false) 
	{
		if(!$query_key) return false;
		
		$this->_loadCacherQueryModel($Model);
		
		$recache = false;
		$model_id = (isset($Model->id)?$Model->id:0);
		$model_name = $Model->name;
		$model_alias = $Model->alias;
		$model_use = $Model->name;
		$lastran = date('Y-m-d H:i:s');
		$lastrecached = date('Y-m-d H:i:s');
		$path = $cacher_path = '';
		
		if(isset($queryData['recache']) and $queryData['recache'])
		{
			$recache = true;
			$recached = date('Y-m-d H:i:s');
			if(is_array($queryData['recache']))
			{
				if(isset($queryData['recache']['model_id'])) $model_id = $queryData['recache']['model_id'];
				if(isset($queryData['recache']['model_name'])) $model_name = $queryData['recache']['model_name'];
				if(isset($queryData['recache']['model_alias'])) $model_alias = $queryData['recache']['model_alias'];
				if(isset($queryData['recache']['model_use'])) $model_use = $queryData['recache']['model_use'];
				if(isset($queryData['recache']['lastran'])) $lastran = $queryData['recache']['lastran'];
				if(isset($queryData['recache']['path'])) $path = $queryData['recache']['path'];
			}
		}
		if(isset($queryData['cacher_path']))
		{
			$path = $queryData['cacher_path'];
		}
		
		$add_data = array(
			'model_name' => $model_name,
			'model_alias' => $model_alias,
			'model_use' => $model_use,
			'query_key' => $query_key,
			'query' => serialize($queryData),
			'path' => $path,
		);
		
		$update_data = array(
			'model_id' => $model_id,
			'model_use' => $model_use,
			'recache' => $recache,
		);
		
		$update_data = array_merge($update_data, $recaching_data);
		
		// if the query was actually submitted, not just read from the cache
		if($queried)
		{
			$add_data['lastran'] = $lastran;
			$update_data['lastran'] = $lastran;
			
		}
		
		// if the query was actually recached
		if($recached)
		{
			$add_data['recached'] = $lastrecached;
			$update_data['recached'] = $lastrecached;
		}
		
		return $this->CacherQuery->checkAddUpdate($query_key, $add_data, $update_data);
	}
	
	protected function _update_requested(Model $Model, $query_key = null, $requested = false)
	{
		if(!$query_key) return false;
		
		$this->_loadCacherQueryModel($Model);
		
		return $this->CacherQuery->updateRequested($query_key, $requested);
	}
	
	protected function _loadCacherQueryModel(Model $Model)
	{
		// load up the CacherQuery Model
		if(!$this->CacherQuery)
		{
			App::import('Model', 'Cacher.CacherQuery');
			$this->CacherQuery = new CacherQuery();
		}
	}

/**
 * Resets the model's datasource to the original
 *
 * @param Model $Model The model
 * @return boolean
 */
	protected function _resetSource(Model $Model) {
		if(isset($Model->_useDbConfig)) {
			$this->source = ConnectionManager::getDataSource($Model->_useDbConfig);
		}
		return $Model->setDataSource(ConnectionManager::getSourceName($this->source));
	}

/**
 * Since Datasource has the method `describe()`, it won't be caught `__call()`.
 * This ensures it is called on the original datasource properly.
 * 
 * @param mixed $model
 * @return mixed 
 */
	public function describe($model) {
		if(method_exists($this->source, 'describe')) {
			return $this->source->describe($model);
		}
		return $this->describe($model);
	}
	
}
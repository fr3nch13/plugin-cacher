<?php

App::uses('AppModel', 'Model');
/**
 * CacherQuery Model
 *
 */
class CacherQuery extends AppModel 
{
	public $actsAs = array(
		'Utilities.Common', 
		'Utilities.Shell', 
		'Cacher.Cache' => array(
			'config' => 'slowQueries',
			'clearOnDelete' => false,
			'clearOnSave' => false,
		),
	);
	
	// define the fields that can be searched
	public $searchFields = array(
		'CacherQuery.model_name',
		'CacherQuery.model_alias',
		'CacherQuery.query_key',
		'CacherQuery.path',
	);
	
	public $toggleFields = array('recache');
	
	public $multiselectOptions = array('delete', 'recache');
	
	public $include_cache_info = false;
	
	public $delete_key = false;
	
	public function afterFind($results = array(), $primary = false)
	{
		foreach($results as $i => $result)
		{
			if(isset($result[$this->alias]) and $this->include_cache_info)
			{
				$results[$i][$this->alias]['fstat'] = $this->Cacher_fstat($result[$this->alias]['query_key']);
			}
		}
		return parent::afterFind($results, $primary);
	}
	
	public function beforeDelete($cascade = true)
	{
		$this->delete_key = $this->field('query_key');
		return true;
	}
	
	public function afterDelete()
	{
		if($this->delete_key)
		{
			$this->Cacher_deleteRecache($this->delete_key);
		}
		return true;
	}
	
	public function checkAddUpdate($query_key = false, $add_data = array(), $update_data = array())
	{
		if(!$query_key) return false;
		
		$id = false;
		
		if(!$id = $this->field('id', array('query_key' => $query_key)))
		{
			$this->create();
			if(!isset($add_data['query_key'])) $add_data['query_key'] = $query_key;
			$this->data = array_merge($add_data, $update_data);
		}
		else
		{
			$this->id = $id;
			$this->data = $update_data;
		}
			
		
		if($this->save($this->data))
		{
			$id = $this->id;
		}
		return $id;
	}
	
	public function updateRequested($query_key = false, $requested = false)
	{
		if(!$query_key) return false;
		if(!$requested) return false;
		
		return $this->updateAll(array($this->alias. '.requested' => "'".$requested."'"), array($this->alias. '.query_key' => $query_key));
	}
	
	public function multiselect_recaching($data = false, $multiselect_value = false)
	{
		if(!isset($data['multiple']))
		{
			$this->modelError = __('No % were selected', __('Cacher Queries'));
			return false;
		}
		
		// see if we can figure out where to send the user after the update
		$this->multiselectReferer = unserialize($data['CacherQuery']['multiselect_referer']);
		
		$this->updateAll(
			array($this->alias. '.recache' => $multiselect_value),
			array($this->alias. '.id' => array_keys($data['multiple']))
		);
		return true;
	}
	
	public function multiselect_multirecaching($sessionData = array(), $data = array())
	{
		
		// see if we can figure out where to send the user after the update
		$this->multiselectReferer = array();
		if(isset($sessionData['CacherQuery']['multiselect_referer']))
		{
			$this->multiselectReferer = unserialize($sessionData['CacherQuery']['multiselect_referer']);
		}
		
		if(isset($data[$this->alias]))
		{
			if(!$this->saveMany($data[$this->alias]))
			{
				$this->modelError = __('Unalbe to update the %s.', __('Cacher Queries'));
				return false;
			}
		}
		return true;
	}
	
	public function getMap($model_name = false, $model_id = false, $justKeys = false)
	{
		$conditions = array();
		if($model_name)
		{
			$conditions['model_name'] = $model_name;
		}
		if($model_id)
		{
			$conditions['model_id'] = $model_id;
		}
		
		if($justKeys)
		{
			return $this->find('list', array(
				'fields' => array('id', 'query_key'),
				'conditions' => $conditions,
			));
		}
		
		return $this->find('all', array(
			'conditions' => $conditions,
		));
	}
	
/*
 * removes map items with the unique ids in the array
 */
	public function clearMap($ids = array())
	{
		if(empty($ids)) return false;
		
		return $this->deleteAll(array('id' => $ids));
	}
	
/*
 * Used to refresh the mapped queries that are marked for recaching
 */
	public function recache()
	{
		$time_start = microtime(true);
		$queries = $this->find('all', array(
			'conditions' => array(
				'recache' => true,
			),
			'order' => array(
				'recached' => 'asc',
//				'lastran' => 'asc', // will be used to track if the cache still needs caching
			),
			'limit' => 5,
		));
		
		$this->shellOut(__('Found %s queries to try to recache.', count($queries)), 'cacher');
		
		if(!$recache_threshold = Configure::read('Cacher.sql_threshold'))
		{
			$recache_threshold = 0;
		}
		
		$this->shellOut(__('Cacher threshold set to: %s', $recache_threshold), 'cacher');
		
		$i = 0;
		foreach($queries as $query)
		{
			// check if this query is locked, if so, go to the next one
			if(Cache::read($query[$this->alias]['query_key'], 'recacherLocks'))
			{
				continue;
			}
			
			$i++;
			$querytime_start = microtime(true);
			$this->shellOut(__('Start recache %s of %s queries - key: %s - path: %s - last recache: %s', $i, count($queries), $query[$this->alias]['query_key'], $query[$this->alias]['path'], $query[$this->alias]['recached']), 'cacher');
			
			// set the lock on this query
			Cache::write($query[$this->alias]['query_key'], 'locked', 'recacherLocks');
			
			if(!$model_use = $query[$this->alias]['model_use'])
			{
				$model_use = $query[$this->alias]['model_alias'];
			}
			
			$this->Cacher_recache($model_use, unserialize($query[$this->alias]['query']));
			
			$querytime_end = microtime(true);
			$querytime = $querytime_end - $querytime_start;
			
			// it was actually queried, not just read from the cache
			if($this->Cacher_isQueried())
			{
				if($recache_threshold)
				{
					if($querytime < $recache_threshold)
					{
						// remove it from the recache map
						$this->delete($query[$this->alias]['id']);
						$this->shellOut(__('Deleted Recache Query - key: %s - took %s seconds to run - threshold %s', $query[$this->alias]['query_key'], $querytime, $recache_threshold), 'cacher');
					}
				}
			}
			
			Cache::delete($query[$this->alias]['query_key'], 'recacherLocks');
			$this->shellOut(__('Complete recache %s of %s queries - took %s seconds - key: %s - path: %s', $i, count($queries), $querytime, $query[$this->alias]['query_key'], $query[$this->alias]['path']), 'cacher');
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$this->shellOut(__('Actually recached %s of %s queries - took %s seconds', $this->Cacher_getRecacheCount(), count($queries), $time), 'cacher');
		$this->shellOut(__('Actual queries submitted %s of %s queries - took %s seconds', $this->Cacher_getQueriedCount(), count($queries), $time), 'cacher');
	}
	
	public function cleanup()
	{
		// removes queries from the list that haven't been accessed in 2 weeks.
		// this seems to be a good timeframe to consider the cache stale
		// don't dascade, do run callbacks
		return $this->deleteAll(array($this->alias.'.requested <' => date('Y-m-d H:i:s', strtotime('-2 weeks'))), false, true);
	}
	
	protected function _loadModel($model_name = false)
	{
		if(!$model_name) return false;
		
		// load up the CacherQuery Model
		if(!isset($this->Models[$model_name]))
		{
			App::import('Model', $model_name);
			$this->Models[$model_name] = new $model_name();
		}
		if(isset($this->Models[$model_name]) and $this->Models[$model_name])
		{
			return true;
		}
		return false;
	}
}
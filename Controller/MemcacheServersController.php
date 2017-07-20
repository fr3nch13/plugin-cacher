<?php

App::uses('CacherAppController', 'Cacher.Controller');

class MemcacheServersController extends CacherAppController 
{
	public function admin_index() 
	{
		$this->Prg->commonProcess();
		
		$conditions = array();
		
		$this->MemcacheServer->recursive = 0;
		$this->paginate['order'] = array('MemcacheServer.created' => 'desc');
		$this->paginate['conditions'] = $this->MemcacheServer->conditions($conditions, $this->passedArgs); 
		
		$memcache_servers = $this->paginate();
		$this->set('memcache_servers', $memcache_servers);
	}
	
	public function admin_refresh($server_id = false)
	{
		if($server_id)
		{
			$server_id = array($server_id);
		}
		
		if($this->MemcacheServer->updateServersStats(false, $server_id))
		{
			$this->Session->setFlash(__('%s have been updated.', __('Memcache Servers')));
		}
		else
		{
			$this->Session->setFlash(__('Unable to update %s.', __('Memcache Servers')));
		}
		return $this->redirect($this->referer());
	}
	
	public function admin_view($id = null) 
	{
		if(!$memcache_server = $this->MemcacheServer->read(null, $id))
		{
			throw new NotFoundException(__('Unknown %s', __('Memcache Server')));
		}
		
		$this->set('memcache_server', $memcache_server);
		
		$this->set('cache_configs', $this->MemcacheServer->configsUsingMemcache($memcache_server['MemcacheServer']['host'], $memcache_server['MemcacheServer']['port']));
	}
}
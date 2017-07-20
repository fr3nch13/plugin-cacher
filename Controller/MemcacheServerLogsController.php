<?php

App::uses('CacherAppController', 'Cacher.Controller');

class MemcacheServerLogsController extends CacherAppController 
{
	public function admin_memcache_server($id = null) 
	{
		if(!$memcache_server = $this->MemcacheServerLog->MemcacheServer->read(null, $id))
		{
			throw new NotFoundException(__('Unknown %s', __('Memcache Server')));
		}
		
		$this->set('memcache_server', $memcache_server);
		
		$this->Prg->commonProcess();
		
		$conditions = array(
			'MemcacheServerLog.memcache_server_id' => $id,
		);
		
		$this->paginate['order'] = array('MemcacheServerLog.created' => 'desc');
		$this->paginate['conditions'] = $this->MemcacheServerLog->conditions($conditions, $this->passedArgs); 
		
		$memcache_server_logs = $this->paginate();
		$this->set('memcache_server_logs', $memcache_server_logs);
	}
}
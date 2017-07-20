<?php

App::uses('CacherAppController', 'Cacher.Controller');

class CacherQueriesController extends CacherAppController 
{
//
	public function admin_index($recache = false) 
	{
		$this->Prg->commonProcess();
		
		$conditions = array();
		
		$lookup_type = __('All');
		if($recache)
		{
			$exclude = false;
			if($recache == 'yes') 
			{
				$lookup_type = __('ONLY Recaching');
				$conditions['CacherQuery.recache'] = 1;
			}
			elseif($recache == 'no') 
			{
				$lookup_type = __('NOT Recaching');
				$conditions['CacherQuery.recache'] = 0;
			}
		}
		$this->set('lookup_type', $lookup_type);
		
		$this->CacherQuery->recursive = 0;
		$this->paginate['order'] = array('CacherQuery.created' => 'desc');
		$this->paginate['conditions'] = $this->CacherQuery->conditions($conditions, $this->passedArgs); 
		
		$this->CacherQuery->include_cache_info = true;
		
		$cacher_queries = $this->paginate();
		$this->set('cacher_queries', $cacher_queries);
	}
//
	public function admin_view($id = null) 
	{
		$this->CacherQuery->id = $id;
		if(!$this->CacherQuery->exists()) 
		{
			return $this->redirect(array('action' => 'index'));
		}
		
		$this->CacherQuery->include_cache_info = true;
		
		$this->CacherQuery->recursive = 0;
		$this->set('cacher_query', $this->CacherQuery->read(null, $id));
	}
	
	public function admin_multiselect()
	{
	/*
	 * batch manage multiple items
	 */
		if(!$this->request->is('post'))
		{
			throw new MethodNotAllowedException();
		}
		
		// forward to a page where the user can choose a value
		$redirect = false;
		if(isset($this->request->data['multiple']))
		{
			$ids = array();
			foreach($this->request->data['multiple'] as $id => $selected) { if($selected) $ids[] = $id; }
			$this->request->data['multiple'] = $this->CacherQuery->find('list', array(
				'fields' => array('CacherQuery.id', 'CacherQuery.id'),
				'conditions' => array('CacherQuery.id' => $ids),
				'recursive' => -1,
			));
		}
		
		if($this->request->data['CacherQuery']['multiselect_option'] == 'recaching')
		{
			$redirect = array('action' => 'multiselect_recaching');
		}
		elseif($this->request->data['CacherQuery']['multiselect_option'] == 'multirecaching')
		{
			$redirect = array('action' => 'multiselect_multirecaching');
		}
		if($redirect)
		{
			Cache::write('Multiselect_CacherQuery_'. AuthComponent::user('id'), $this->request->data, 'sessions');
			return $this->redirect($redirect);
		}
		
		if($this->CacherQuery->multiselect($this->request->data))
		{
			$this->Session->setFlash(__('The %s were updated.', __('Cacher Queries')));
			return $this->redirect($this->referer());
		}
		
		$this->Session->setFlash(__('The %s were NOT updated.', __('Cacher Queries')));
		return $this->redirect($this->referer());
	}
	
	public function admin_multiselect_recaching()
	{
		$sessionData = Cache::read('Multiselect_CacherQuery_'. AuthComponent::user('id'), 'sessions');
		if($this->request->is('post') || $this->request->is('put')) 
		{
			$multiselect_value = (isset($this->request->data['CacherQuery']['recache'])?$this->request->data['CacherQuery']['recache']:0);
			if($this->CacherQuery->multiselect_recaching($sessionData, $multiselect_value)) 
			{
				Cache::delete('Multiselect_CacherQuery_'. AuthComponent::user('id'), 'sessions');
				$this->Session->setFlash(__('The %s was updated for these %s.', __('Recaching'), __('Cacher Queries')));
				return $this->redirect($this->CacherQuery->multiselectReferer());
			}
			else
			{
				$this->Session->setFlash(__('The %s was NOT updated for these %s.', __('Recaching'), __('Cacher Queries')));
			}
		}
		
		$selected_cacher_queries = array();
		if(isset($sessionData['multiple']))
		{
			$selected_cacher_queries = $this->CacherQuery->find('list', array(
				'recursive' => -1,
				'conditions' => array(
					'CacherQuery.id' => $sessionData['multiple'],
				),
				'fields' => array('CacherQuery.id', 'CacherQuery.query_key'),
				'sort' => array('CacherQuery.cacher_key' => 'asc'),
			));
		}
		
		$this->set('selected_cacher_queries', $selected_cacher_queries);
		
		if(!$selected_cacher_queries)
		{
			Cache::delete('Multiselect_CacherQuery_'. AuthComponent::user('id'), 'sessions');
			$this->Session->setFlash(__('No %s were selected.', __('Cacher Queries')));
			return $this->redirect(unserialize($sessionData['CacherQuery']['multiselect_referer']));
		}
	}
	
	public function admin_multiselect_multirecaching()
	{
		$sessionData = Cache::read('Multiselect_CacherQuery_'. AuthComponent::user('id'), 'sessions');
		if($this->request->is('post') || $this->request->is('put')) 
		{
			if($this->CacherQuery->multiselect_multirecaching($sessionData, $this->request->data))
			{
				Cache::delete('Multiselect_CacherQuery_'. AuthComponent::user('id'), 'sessions');
				$this->Session->setFlash(__('The %s were updated.', __('Cacher Queries')));
				return $this->redirect($this->CacherQuery->multiselectReferer());
			}
			else
			{
				$this->Session->setFlash(__('The %s were NOT updated.', __('Cacher Queries')));
			}
		}

		$this->Prg->commonProcess();
		
		$conditions = array(
			'CacherQuery.id' => $sessionData['multiple'], 
		);
		
		$this->paginate['limit'] = count($sessionData['multiple']);
		$this->paginate['order'] = array('CacherQuery.created' => 'desc');
		$this->paginate['conditions'] = $this->CacherQuery->conditions($conditions, $this->passedArgs);
		$this->set('cacher_queries', $this->paginate());
	}
	
//
	public function admin_toggle($field = null, $id = null)
	{
	/*
	 * Toggle an object's boolean settings (like active)
	 */
		if($this->CacherQuery->toggleRecord($id, $field))
		{
			$this->Session->setFlash(__('The %s has been updated.', __('Cacher Query')));
		}
		else
		{
			$this->Session->setFlash($this->CacherQuery->modelError);
		}
		
		return $this->redirect($this->referer());
	}

//
	public function admin_delete($id = null) 
	{
		$this->CacherQuery->id = $id;
		if(!$this->CacherQuery->exists()) {
			throw new NotFoundException(__('Invalid %s', __('Cacher Query')));
		}
		if($this->CacherQuery->delete()) {
			$this->Session->setFlash(__('%s deleted', __('Cacher Query')));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('%s was not deleted', __('Cacher Query')));
		return $this->redirect(array('action' => 'index'));
	}
	
/// pages for viewing the memcache stats
	public function admin_memcache_stats()
	{
		$total_memcache_stats = $this->CacherQuery->totalMemcacheStats();
		$this->set('total_memcache_stats', $total_memcache_stats);
	}
}
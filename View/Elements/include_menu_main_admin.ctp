<?php ?>
<li><?php echo $this->Html->link(__('Cacher'), '#'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('Queries'), array('controller' => 'cacher_queries', 'action' => 'index', 'admin' => true, 'plugin' => 'cacher')); ?></li> 
		<li><?php echo $this->Html->link(__('Memcache Stats'), array('controller' => 'memcache_servers', 'action' => 'index', 'admin' => true, 'plugin' => 'cacher')); ?></li> 
	</ul>
</li>
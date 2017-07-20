<?php

App::uses('UtilitiesAppShell', 'Utilities.Console/Command');

class CacherShell extends UtilitiesAppShell
{
	// the models to use
	public $uses = array('Cacher.CacherQuery', 'Cacher.MemcacheServer');
	
	public function startup() 
	{
		$this->clear();
		$this->out('Cacher Shell');
		$this->hr();
		return parent::startup();
	}
	
	public function getOptionParser()
	{
	/*
	 * Parses out the options/arguments.
	 * http://book.cakephp.org/2.0/en/console-and-shells.html#configuring-options-and-generating-help
	 */
	
		$parser = parent::getOptionParser();
		
		$parser->description(__d('cake_console', 'The Cacher Shell used to run cron jobs common in all of the apps.'));
		
		$parser->addSubcommand('recache', array(
			'help' => __d('cake_console', 'Recaches queries marked for asynchronous cache updating.'),
			'parser' => array(
				'options' => array(
				),
			),
		));
		$parser->addSubcommand('cleanup', array(
			'help' => __d('cake_console', 'Removed queries that havent been accessed in awhile'),
			'parser' => array(
				'options' => array(
				),
			),
		));
		$parser->addSubcommand('memcache_stats_update', array(
			'help' => __d('cake_console', 'Polls configured memcache servers, gets their stats, and saves the information to the database.'),
			'parser' => array(
				'options' => array(
				),
			),
		));
		
		return $parser;
	}
	
	public function recache()
	{
		/////////// recaches tracked queries marked for recaching
		return $this->CacherQuery->recache();
	}
	
	public function cleanup()
	{
		/////////// email a list of validation errors
		return $this->CacherQuery->cleanup();
	}
	
	public function memcache_stats_update()
	{
		/////////// updates the memcache stats for each server it finds in the configured cache settings
		return $this->MemcacheServer->updateServersStats(true);
	}
	
}
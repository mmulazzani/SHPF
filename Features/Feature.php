<?php

namespace SHPF\Features;


use SHPF\Logger;

use SHPF\Checkers\CheckFailedException;

use SHPF\Checkers\CheckFailedInfo;

use SHPF\Checkers\Checker;

use SHPF\SHPF;

use \Exception;

abstract class Feature
{
	/*-------------------------------------------------------------------------*/
	// Members
	/*-------------------------------------------------------------------------*/
	
	/**
	 * @var Output
	 */
	protected $output;
	
	/**
	 * @var SHPF
	 */
	protected $shpf;
	
	/**
	 * @var IUserStore
	 */
	protected $userStore;
	
	/**
	 * Array of registered checkers
	 * @var array
	 */
	protected $checkers = array ();
	
	/**
	 * Security Level
	 * @var integer
	 */
	protected $securityLevel = 0;
	
	protected $name;
	
	
	/*-------------------------------------------------------------------------*/
	// Public
	/*-------------------------------------------------------------------------*/
	
	public function __construct ($name, SHPF $shpf)
	{
		$this->name = $name;
		$this->shpf = $shpf;
		$this->output = $shpf->getOutput();
		$this->userStore = $shpf->getUserStore();
	}
	
	
	/**
	 * Executes the feature
	 * @return bool
	 */
	public function run ()
	{
		return $this->runCheckers();
	}
	
	
	public function getName ()
	{
		return $this->name;
	}
	
	public function getCheckerByName ($name)
	{
		foreach ($this->checkers as $checker)
		{
			if ($checker->getName() == $name)
				return $checker;
		}
		
		return null;
	}
	
	/**
	 * Sets the security level
	 * @param integer $level
	 */
	public function setSecurityLevel ($level)
	{
		if (is_numeric ($level))
			$this->securityLevel = $level;
	}
	
	public function getSecurityLevel ()
	{
		return $this->securityLevel;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Protected
	/*-------------------------------------------------------------------------*/
	
	protected function runChecker (Checker $checker)
	{
		Logger::writeLine ('Running checker '. get_class ($checker));
		
		// Run check
		try
		{
			$success = $checker->run();
		}
		catch (Exception $ex)
		{
			//return new CheckFailedInfo ($checker, $this, $ex->getMessage());
			throw new CheckFailedException( new CheckFailedInfo ($checker, $this, $ex->getMessage()) );
		}
		
		// Check if failed
		if (!$success)
			throw new CheckFailedException( new CheckFailedInfo ($checker, $this) );
			//return new CheckFailedInfo ($checker, $this);
		
		return true;
	}
	
	protected function runCheckers ()
	{
		foreach ($this->checkers as $checker)
		{
			$success = $this->runChecker ($checker);
			
			// Not successful?
			if ($success !== true)
				return $success;
		}
		
		return true;
	}
	
	protected function addChecker (Checker $checker)
	{
		$this->checkers[] = $checker;
	}
}
<?php

namespace SHPF\Features;


use SHPF\Logger;

use SHPF\Checkers\CheckFailedException;

use SHPF\Checkers\CheckFailedInfo;

use SHPF\Checkers\Checker;

use SHPF\SHPF;

use \Exception;

/**
 * Abstract base class for a feature.
 * Provides basic structure for running checkers.
 * 
 * @author Thomas Unger
 *
 */
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
	
	/**
	 * Internal name
	 * @var string
	 */
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
	
	/**
	 * Gets the internal name
	 * @return string
	 */
	public function getName ()
	{
		return $this->name;
	}
	
	/**
	 * Returns the checker matching the internal name
	 * @param string $name Internal name
	 * @return Checker
	 */
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
	
	/**
	 * Returns the security level of the feature
	 * @return number
	 */
	public function getSecurityLevel ()
	{
		return $this->securityLevel;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Protected
	/*-------------------------------------------------------------------------*/
	
	/**
	 * Runs a certain checker
	 * @param Checker $checker The checker to run
	 * @throws CheckFailedException
	 * @return boolean
	 */
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
			throw new CheckFailedException( new CheckFailedInfo ($checker, $this, $ex->getMessage()) );
		}
		
		// Check if failed
		if (!$success)
			throw new CheckFailedException( new CheckFailedInfo ($checker, $this) );
		
		return true;
	}
	
	/**
	 * Runs all checkers of the feature
	 * @return boolean
	 */
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
	
	/**
	 * Adds a checker which will be run by the feature
	 * @param Checker $checker The checker to add
	 */
	protected function addChecker (Checker $checker)
	{
		$this->checkers[] = $checker;
	}
}
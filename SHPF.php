<?php

namespace SHPF;

require_once ('AutoLoader.php');

if (!defined ('SHPF_ROOT_WEB'))
	throw new \Exception ('SHPF_ROOT_WEB relative web path not set');

use SHPF\Checkers\AsynchronousChecker;
use SHPF\UserStore\SessionStore;
use SHPF\Checkers\CheckFailedInfo;
use SHPF\Checkers\CheckFailedException;
use SHPF\Features\Feature;
use SHPF\UserStore\IUserStore;
use SHPF\Output\Output;
use \ReflectionMethod;
use \ReflectionFunction;
use \ReflectionException;
use \Exception;

class SHPF
{
	/*-------------------------------------------------------------------------*/
	// Public members
	/*-------------------------------------------------------------------------*/
	
	public $raiseExceptionOnFailure = false;
	
	public $enableAsync = true;
	
	public $enableLogging = false;
	
	/*-------------------------------------------------------------------------*/
	// Private members
	/*-------------------------------------------------------------------------*/
	
	private $checkFailedHandler = null;
	
	private $features = array ();
	
	/**
	 * @var Output
	 */
	private $output;
	
	/**
	 * @var IUserStore
	 */
	private $userStore;
	
	
	/**
	 * Defines whether potential setup actions are valid (false) or 
	 * whether a successful check without setup must be achieved (true)
	 * 
	 * @var boolean
	 */
	private $enforceSuccess = false;
	
	/**
	 * 
	 * @var ICryptoProvider
	 */
	private $cryptoProvider = null;
	
	/*-------------------------------------------------------------------------*/
	// Public methods
	/*-------------------------------------------------------------------------*/
	
	public function __construct ($configFile = 'config.php')
	{
		//require_once ($configFile);
		
		Logger::writeLine('-----------------------');
		Logger::writeLine('SHPF created');
		
		$this->output = new Output($this);
		$this->userStore = new SessionStore();
	}
	
	public function addFeature (Feature $feature)
	{
		Logger::writeLine ('Added feature: '. get_class ($feature));
		
		$this->features[] = $feature;
	}
	
	
	public function run ($level = null)
	{
		Logger::setEnabled($this->enableLogging);
		
		//-----------------------------------------
		// Async
		//-----------------------------------------
		
		if ($_GET['async'])
		{
			try
			{
				$ret = $this->processAsyncCheckers();
				echo 1;
				Logger::writeLine ('SHPF async successful');
			}
			catch (CheckFailedException $ex)
			{
				echo 0;
				$this->raiseExceptionOnFailure = false;
				$this->onFailed ( $ex->getCheckFailedInfo() );
				
			}
			
			/*
			if ($ret instanceof CheckFailedInfo)
			{
				$this->onFailed($ret);
				return false;
			}*/
			
			//-----------------------------------------
			// Stop execution
			// Only one checker at a time
			//-----------------------------------------
			
			exit;
		}
		
		//-----------------------------------------
		// Run features
		//-----------------------------------------
		
		if ($level !== null)
			Logger::writeLine ('Running features with security level ' . $level .' or lower');
		
		foreach ($this->features as $feature)
		{
			// Check if the security level is set and the feature meets the requirements
			if ($level !== null && $feature->getSecurityLevel() > $level)
				continue;
			
			Logger::writeLine ('Running feature '. get_class ($feature));
			
			try
			{
				$ret = $feature->run ();
				//var_dump ($ret);
				/*if ($ret instanceof CheckFailedInfo)
				{
					$this->onFailed($ret);
					return false;
				}*/
			}
			catch (CheckFailedException $ex)
			{
				$this->onFailed ( $ex->getCheckFailedInfo() );
				return false;
			}
		}
		
		
		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		//$this->output->flushAll();
		
		
		Logger::writeLine ('SHPF successful');
		
		
		return true;
	}

	
	/*-------------------------------------------------------------------------*/
	// Public getters/setters
	/*-------------------------------------------------------------------------*/
	
	/**
	 * Returns the output handler
	 * @return Output
	 */
	public function getOutput ()
	{
		return $this->output;
	}
	
	/**
	 * Sets the output handler
	 * @param Output $output
	 */
	public function setOutput (Output $output)
	{
		$this->output = $output;
	}
	
	/**
	 * Returns the user store
	 * @return IUserStore
	 */
	public function getUserStore ()
	{
		return $this->userStore;
	}
	
	/**
	 * Sets the user store
	 * @param IUserStore $userStore
	 */
	public function setUserStore (IUserStore $userStore)
	{
		$this->userStore = $userStore;
	}
	
	/**
	 * Returns whether potential setup actions are valid (false) or 
	 * whether a successful check without setup must be achieved (true)
	 * 
	 * @return boolean
	 */
	public function getEnforceSuccess ()
	{
		return $this->enforceSuccess;
	}
	
	/**
	 * Sets whether potential setup actions are valid (false) or 
	 * whether a successful check without setup must be achieved (true)
	 * 
	 * @param boolean $enforceSuccess
	 */
	public function setEnforceSuccess ($enforceSuccess = true)
	{
		$this->enforceSuccess = $enforceSuccess;
	}
	
	
	public function setCheckFailedHandler ($functionName)
	{
		try
		{
			$srm = new ReflectionFunction($functionName);
			//$func = $srm->getClosure();
			$func = $functionName;
			$this->checkFailedHandler = $func;
			return true;
		} 
		catch (ReflectionException $e) 
		{
			echo $e->getMessage();
			return false;
		}		
	}
	
	
	public function setCryptoProvider (ICryptoProvider $cryptoProvider)
	{
		$this->cryptoProvider = $cryptoProvider;
	}
	
	/*-------------------------------------------------------------------------*/
	// Private methods
	/*-------------------------------------------------------------------------*/
	
	private function processAsyncCheckers ()
	{
		//-----------------------------------------
		// Check if async is enabled
		// If not, return success
		//-----------------------------------------
		
		if (!$this->enableAsync)
			return true;
		
		
		Logger::writeLine ('Running async checkers');
		
		//-----------------------------------------
		// Search feature
		//-----------------------------------------
		
		$feature = $this->getFeatureByName ( $_GET['async_feature'] );
		
		if ($feature === null)
			return false;
		
		//-----------------------------------------
		// Search checker
		//-----------------------------------------
		
		$checker = $feature->getCheckerByName( $_GET['async_checker'] );
		
		if ($checker === null)
			return false;
		
		if (! $checker instanceof AsynchronousChecker)
			return false;
		
		
		//-----------------------------------------
		// Check POST payload
		// for message, or if encrypted
		//-----------------------------------------
		
		// Default: take whole POST
		$data = $_POST;
		
		// Is there a JSON encoded message?
		if ($_POST['data'])
		{
			$data = $_POST['data'];
		}
		
		// Is there an encrypted message?
		else if ($_POST['encrypted'])
		{
			Logger::writeLine('Decrypting POST data');
			
			$crypted = $_POST['encrypted'];
			
			$crypted = base64_decode ($crypted);

			Logger::writeLine('Encrypted: '. print_r ($crypted, true));
			
			// Crypto Handler set?
			if ($this->cryptoProvider !== null)
			{
				try
				{
					$data = $this->cryptoProvider->decrypt($crypted);
				}
				catch (\Exception $ex)
				{
					throw new CheckFailedException( new CheckFailedInfo ($checker, $feature, $ex->getMessage()) );
				}
			}

			//Logger::writeLine('Error: '. json_last_error());

			//Logger::writeLine('Decrypted: '. print_r ($data, true));
		}
		
		$json = json_decode ($data, true);
		if ($json !== null)
			$data = $json;
		
		// Set data
		$checker->setPostData($data);
		
		//-----------------------------------------
		// Run it
		//-----------------------------------------
		
		Logger::writeLine ('Running checker '. get_class ($checker));
		
		// Run check
		try
		{
			$success = $checker->runAsync ();
		}
		catch (Exception $ex)
		{
			throw new CheckFailedException( new CheckFailedInfo ($checker, $feature, $ex->getMessage()) );
		}
		
		// Check if failed
		if (!$success)
			throw new CheckFailedException( new CheckFailedInfo ($checker, $feature) );
		
		return $success;
	}
	
	private function onFailed (CheckFailedInfo $info)
	{
		Logger::writeLine ('Failed: '. $info->toString());
	
		//-----------------------------------------
		// Check failed handler
		//-----------------------------------------
		
		if ($this->checkFailedHandler !== null)
		{
			$func = $this->checkFailedHandler;
			$func ($info);
		}
		
		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		$this->output->onFailed ();
		
		//-----------------------------------------
		// Exception
		//-----------------------------------------
		
		if ($this->raiseExceptionOnFailure)
			throw new CheckFailedException ($info); 
	}
	
	/**
	 * Returns a feature by a given name
	 * 
	 * @param string $name
	 * @return Feature
	 */
	private function getFeatureByName ($name)
	{
		foreach ($this->features as $feature)
		{
			if ($feature->getName() == $name)
				return $feature;
		}
		
		return null;
	}

}


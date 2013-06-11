<?php

namespace SHPF\UserStore;

/**
 * Uses the $_SESSION variable to access and store values
 * 
 * @author Thomas Unger
 *
 */
class SessionStore implements IUserStore
{
	/**
	 * Checks whether a session has been started, and if not, attempts to do so
	 * 
	 * @throws Exception
	 */
	public function __construct ()
	{
		// Does a session exist?
		if (strlen ( session_id () ) == 0)
		{
			/*
			// The following can be used to transport the SID in the URL
			ini_set("session.use_cookies",0);
			ini_set("session.use_only_cookies",0);
			ini_set("session.use_trans_sid",1);
			*/
			
			// Try start a new session
			$success = session_start ();
			
			if (! $success)
				throw new Exception ('Failed to start session for SessionStore');
		}
	}
	
	/**
	 * Gets a value from the store
	 *
	 * @param string $key 	Unique key for accessing a variable in the store
	 * @return mixed
	 */
	public function getValue ($key)
	{
		return $_SESSION[ $this->transformKey($key) ];
	}
	
	
	/**
	 * Sets a value in the store
	 *
	 * @param string $key 	Unique key for accessing a variable in the store
	 * @param mixed $value 	Value to store
	 */
	public function setValue ($key, $value)
	{
		$_SESSION[ $this->transformKey($key) ] = $value;
	}
	
	
	/**
	 * Returns whether a value for a key exists in the store
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasValue ($key)
	{
		return isset ($_SESSION[ $this->transformKey($key) ]);
	}
	
	
	/**
	 * Clears the store from all values
	 */
	public function clear ()
	{
		unset ($_SESSION);
	}
	
	
	/**
	 * Prepends an ID to the key to avoid collisions
	 * 
	 * @param string $key
	 */
	private function transformKey ($key)
	{
		return 'shpf_'. $key;
	}
}


?>
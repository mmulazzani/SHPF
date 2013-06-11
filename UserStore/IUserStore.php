<?php

namespace SHPF\UserStore;

/**
 * Interface for storing and accessing values in a variable store
 * 
 * @author Thomas Unger
 *
 */
interface IUserStore
{
	/**
	 * Gets a value from the store
	 * 
	 * @param string $key 	Unique key for accessing a variable in the store
	 * @return mixed
	 */
	public function getValue ($key);
	
	
	/**
	 * Sets a value in the store
	 * 
	 * @param string $key 	Unique key for accessing a variable in the store
	 * @param mixed $value 	Value to store
	 */
	public function setValue ($key, $value);
	
	
	
	/**
	 * Returns whether a value for a key exists in the store
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function hasValue ($key);
	
	
	/**
	 * Clears the store from all values
	 */
	public function clear ();
	
}


?>
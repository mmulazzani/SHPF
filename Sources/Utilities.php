<?php

namespace SHPF;


/**
 * A set of static utility functions used in the famework
 * 
 * @author Thomas Unger
 *
 */
class Utilities
{
	/**
	 * Returns the (relative) SHPF path
	 * 
	 * @return string
	 */
	public static function getSHPFPath ()
	{
		return SHPF_ROOT_WEB;
	}
	
	/**
	 * Rewrites a relative path by prepending the SHPF path
	 * 
	 * @param string $path
	 * @return string
	 */
	public static function getPath ($path)
	{
		$dirname = self::getSHPFPath();
		$filename = $dirname . $path;

		return $filename;
	}
	
	/**
	 * Returns the request URI including the protocol and server name
	 * @return string
	 */
	public static function getFullRequestURI ()
	{
		$protocol = 'http';
		
		if ($_SERVER['SERVER_PORT'] == 443)
			$protocol = 'https';
		
		$url = $protocol .'://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		
		return $url;
	}
	
	/**
	 * Makes the first letter in a string uppercase
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function strCapitalize ($str)
	{
		return strtoupper (substr ($str, 0, 1)) . substr ($str, 1);
	}
}
<?php

namespace SHPF\Checkers;


use SHPF\Logger;

use SHPF\Features\Feature;
use SHPF\SHPF;

/**
 * Abstract base class for a checker which awaits an asynchronous response from the client.
 * 
 * @author Thomas Unger
 *
 */
abstract class AsynchronousChecker extends Checker
{
	/**
	 * Contains the data sent via POST to the framework
	 * @var array
	 */
	protected $postData;
	
	/**
	 * Creates a new instance
	 * 
	 * @param unknown $name Internal name of the checker
	 * @param SHPF $shpf SHPF instance
	 * @param Feature $feature Parent feature
	 */
	public function __construct ($name, SHPF $shpf, Feature $feature)
	{
		parent::__construct($name, $shpf, $feature);
	}
	
	/**
	 * Returns the URL containing the query string, so that a client side response 
	 * is delegated automatically from SHPF to this checker.
	 * 
	 * @return string
	 */
	public function getAsyncQueryString ()
	{
		// Append SID if the SID was transported in the URL in this request
		if ($_GET[ session_name() ])
		{
			$appendSid = SID .'&';
		}
		
		return $_SERVER['SCRIPT_NAME'] .'?'. $appendSid .'async=1&async_feature=' . $this->feature->getName() .'&async_checker=' . $this->name;
	}
	
	/**
	 * Sets the post data for this checker
	 * 
	 * @param array $postData
	 */
	public function setPostData ($postData)
	{
		if (!is_array ($postData))
		{
			$json = json_decode ($postData, true);
			if ($json !== null)
				$postData = $json;
		}
		
		$this->postData = $postData;
		
		if (is_array ($postData) && count ($postData) > 0)
			Logger::writeLine(__CLASS__ .': post data: '. print_r ($postData, true));
	}
	
	/**
	 * Runs the asynchronous check and returns whether it was successful or not
	 * @return boolean
	 */
	public abstract function runAsync ();
}
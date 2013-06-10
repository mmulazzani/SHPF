<?php

namespace SHPF\Checkers;


use SHPF\Logger;

use SHPF\Features\Feature;
use SHPF\SHPF;

abstract class AsynchronousChecker extends Checker
{
	protected $postData;
	
	public function __construct ($name, SHPF $shpf, Feature $feature)
	{
		parent::__construct($name, $shpf, $feature);
	}
	
	
	public function getAsyncQueryString ()
	{
		if ($_GET[ session_name() ])
		{
			$appendSid = SID .'&';
		}
		
		return $_SERVER['SCRIPT_NAME'] .'?'. $appendSid .'async=1&async_feature=' . $this->feature->getName() .'&async_checker=' . $this->name;
	}
	
	
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
	
	public abstract function runAsync ();
}
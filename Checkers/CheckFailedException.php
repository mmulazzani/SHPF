<?php

namespace SHPF\Checkers;

use \Exception;

/**
 * Exception containing all info about a failed checker.
 * 
 * @author Thomas Unger
 *
 */
class CheckFailedException extends Exception
{
	private $info;
	
	public function __construct (CheckFailedInfo $info)
	{
		parent::__construct ();
		
		$this->info = $info;
	}
	
	public function __toString()
	{
		return __CLASS__ . ": {$this->info}\n";
	}
	
	public function getCheckFailedInfo ()
	{
		return $this->info;
	}
}

?>
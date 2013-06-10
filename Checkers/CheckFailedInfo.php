<?php

namespace SHPF\Checkers;


use SHPF\Features\Feature;

class CheckFailedInfo
{
	/**
	 * @var Checker
	 */
	public $checker;
	
	/**
	 * @var Feature
	 */
	public $feature;
	
	/**
	 * @var string
	 */
	public $message;
	
	
	public function __construct (Checker $checker, Feature $feature, $message = null)
	{
		$this->checker = $checker;
		$this->feature = $feature;
		$this->message = $message;
	}
	
	
	public function toString ()
	{
		return 'Failed at checker '. get_class ($this->checker) .' in feature '. get_class ($this->feature) .': '. $this->message;
	}
	
	
	public function __toString ()
	{
		return $this->toString();
	}
}
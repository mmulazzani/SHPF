<?php

namespace SHPF\Checkers;


use SHPF\Features\Feature;
use SHPF\IUserStore;
use SHPF\SHPF;

abstract class Checker
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
	 * The feature to which this checker belongs to
	 * @var Feature
	 */
	protected $feature;
	
	
	protected $name;
	
	
	/*-------------------------------------------------------------------------*/
	// Public
	/*-------------------------------------------------------------------------*/
	
	public function __construct ($name, SHPF $shpf, Feature $feature)
	{
		$this->name = $name;
		$this->feature = $feature;
		$this->shpf = $shpf;
		$this->output = $shpf->getOutput();
		$this->userStore = $shpf->getUserStore();
	}
	
	
	/**
	 * Runs the check and returns whether it was successful or not
	 * @return bool
	 */
	abstract public function run ();
	
	
	public function getName ()
	{
		return $this->name;
	}
}
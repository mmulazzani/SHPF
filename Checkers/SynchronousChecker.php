<?php

namespace SHPF\Checkers;


use SHPF\Features\Feature;
use SHPF\SHPF;

abstract class SynchronousChecker extends Checker
{
	public function __construct ($name, SHPF $shpf, Feature $feature)
	{
		parent::__construct($name, $shpf, $feature);
	}
}
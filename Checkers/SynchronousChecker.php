<?php

namespace SHPF\Checkers;


use SHPF\Features\Feature;
use SHPF\SHPF;

/**
 * Abstract base class for a checker which needs no asynchronous response from the client.
 *
 * @author Thomas Unger
 *
 */
abstract class SynchronousChecker extends Checker
{
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
}
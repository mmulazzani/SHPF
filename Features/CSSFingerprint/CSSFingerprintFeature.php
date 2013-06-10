<?php

namespace SHPF\Features\CSSFingerprint;

use SHPF\SecurityLevel;

use SHPF\SHPF;

use SHPF\Features\Feature;




class CSSFingerprintFeature extends Feature
{
	/**
	 * Timeout in seconds for asynchronous callbacks
	 * @var integer
	 */
	public $asyncTimeout = 30;
	
	/**
	 * Specifies whether to use the async callback timeout
	 * @var boolean
	 */
	public $enableAsyncTimeout = true;
	
	/**
	 * Specifies whether it is accepted that no javascript is enabled
	 * @var boolean
	 */
	public $allowNoJavascript = true;
	
	/**
	 * Maximum number of simultanous open requests from server to client
	 * @var integer
	 */
	public $maxOpenRequests = 3;
	
	/**
	 * Specifies whether each open request should be checked for expiration
	 * @var boolean
	 */
	public $checkTimeoutForEachRequest = true;

	/*-------------------------------------------------------------------------*/
	// Public
	/*-------------------------------------------------------------------------*/
	
	public function __construct (SHPF $shpf)
	{
		parent::__construct ('css_fingerprint', $shpf);
		
		$this->setSecurityLevel(SecurityLevel::MEDIUM);
		
		$this->addChecker (new CSSFingerprintChecker ($this->shpf, $this));

	}
}
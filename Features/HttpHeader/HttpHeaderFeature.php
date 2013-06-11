<?php

namespace SHPF\Features\HttpHeader;

use SHPF\SecurityLevel;

use SHPF\SHPF;

use SHPF\Features\Feature;



/**
 * Feature to collect and compare Http headers of the client request.
 * Also allows IP binding by using request information.
 * 
 * @author Thomas Unger
 *
 */
class HttpHeaderFeature extends Feature
{
	/**
	 * Use IP Address Binding of the client
	 * @var boolean
	 */
	public $checkIpAddress = true;
	
	/**
	 * Check User Agent string of the browser
	 * @var boolean
	 */
	public $checkUserAgent = true;
	
	/**
	 * Check Http Accept Header
	 * @var boolean
	 */
	public $checkHttpAccept = true;
	
	/**
	 * Check Http Accept Language Header
	 * @var boolean
	 */
	public $checkHttpAcceptLanguage = true;
	
	/**
	 * Check Http Accept Encoding Header
	 * @var boolean
	 */
	public $checkHttpAcceptEncoding = true;

	/**
	 * Check the order of the Http Headers
	 * @var boolean
	 */
	public $checkHttpHeaderOrder = true;
	
	/*-------------------------------------------------------------------------*/
	// Public
	/*-------------------------------------------------------------------------*/
	
	public function __construct (SHPF $shpf)
	{
		parent::__construct ('server_env', $shpf);
		
		$this->setSecurityLevel(SecurityLevel::LOW);
		
		$this->addChecker (new HttpHeaderChecker ($this->shpf, $this));
	}
	
	/**
	 * Enables/disables all available checks of this feature
	 * @param boolean $check
	 */
	public function setCheckAll ($check = true)
	{
		$this->checkComSpec = $check;
		$this->checkHttpAccept = $check;
		$this->checkHttpAcceptEncoding = $check;
		$this->checkHttpAcceptLanguage = $check;
		$this->checkIpAddress = $check;
		$this->checkHttpHeaderOrder = $check;
	}

}
<?php

namespace SHPF\Features\HttpHeader;

use SHPF\SecurityLevel;

use SHPF\SHPF;

use SHPF\Features\Feature;




class HttpHeaderFeature extends Feature
{
	public $checkIpAddress = true;
	public $checkUserAgent = true;
	public $checkHttpAccept = true;
	public $checkHttpAcceptLanguage = true;
	public $checkHttpAcceptEncoding = true;

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
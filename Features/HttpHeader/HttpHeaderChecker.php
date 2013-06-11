<?php

namespace SHPF\Features\HttpHeader;

use SHPF\Features\Feature;

use SHPF\SHPF;

use SHPF\Checkers\SynchronousChecker;

use \Exception;


/**
 * Checker to collect and compare Http headers of the client request.
 * Also allows IP binding by using request information.
 * 
 * @author Thomas Unger
 *
 */
class HttpHeaderChecker extends SynchronousChecker
{
	public function __construct (SHPF $shpf, Feature $feature)
	{
		parent::__construct('header', $shpf, $feature);
	}
	
	
	public function run ()
	{
		/*-------------------------------------------------------------------------*/
		// Extract interesting information
		/*-------------------------------------------------------------------------*/
		
		$remoteAddr = $_SERVER['REMOTE_ADDR'];
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$httpAccept = $_SERVER['HTTP_ACCEPT'];
		$httpAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$httpAcceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];

		
		/*-------------------------------------------------------------------------*/
		// Check existing values
		/*-------------------------------------------------------------------------*/
		
		//-----------------------------------------
		// IP Address
		//-----------------------------------------
		
		if ($this->feature->checkIpAddress && $this->userStore->hasValue ('serverRemoteAddr'))
		{
			if ($this->userStore->getValue ('serverRemoteAddr') != $remoteAddr)
				throw new Exception ('Server REMOTE_ADDR does not match');
		}
		
		//-----------------------------------------
		// User Agent
		//-----------------------------------------
		
		if ($this->feature->checkUserAgent && $this->userStore->hasValue ('serverUserAgent'))
		{
			if ($this->userStore->getValue ('serverUserAgent') != $userAgent)
				throw new Exception ('Server HTTP_USER_AGENT does not match');
		}
		
		//-----------------------------------------
		// HTTP ACCEPT
		//-----------------------------------------
		
		if ($this->feature->checkHttpAccept && $this->userStore->hasValue ('serverHttpAccept'))
		{
			if ($this->userStore->getValue ('serverHttpAccept') != $httpAccept)
				throw new Exception ('Server HTTP_ACCEPT does not match');
		}
		
		//-----------------------------------------
		// HTTP ACCEPT LANGUAGE
		//-----------------------------------------
		
		if ($this->feature->checkHttpAcceptLanguage && $this->userStore->hasValue ('serverHttpAcceptLanguage'))
		{
			if ($this->userStore->getValue ('serverHttpAcceptLanguage') != $httpAcceptLanguage)
				throw new Exception ('Server HTTP_ACCEPT_LANGUAGE does not match');
		}
		
		//-----------------------------------------
		// HTTP ACCEPT ENCODING
		//-----------------------------------------
		
		if ($this->feature->checkHttpAcceptEncoding && $this->userStore->hasValue ('serverHttpAcceptEncoding'))
		{
			if ($this->userStore->getValue ('serverHttpAcceptEncoding') != $httpAcceptEncoding)
				throw new Exception ('Server HTTP_ACCEPT_ENCODING does not match');
		}
		
		
		
		/*-------------------------------------------------------------------------*/
		// Set Values
		/*-------------------------------------------------------------------------*/
		
		$this->userStore->setValue ('serverRemoteAddr', $remoteAddr);
		$this->userStore->setValue ('serverUserAgent', $userAgent);
		$this->userStore->setValue ('serverHttpAccept', $httpAccept);
		$this->userStore->setValue ('serverHttpAcceptLanguage', $httpAcceptLanguage);
		$this->userStore->setValue ('serverHttpAcceptEncoding', $httpAcceptEncoding);


		
		
		
		/*-------------------------------------------------------------------------*/
		// HTTP Header Order
		/*-------------------------------------------------------------------------*/
		
		$headers = getallheaders ();
		
		if (is_array ($headers))
		{
			// Get keys, we only need to check the order
			$headerKeys = array_keys ($headers);
			
			// Filter out unwanted headers
			$importantHeaders = array ('Accept', 'Accept-Language', 'Accept-Encoding', 'Accept-Charset', 'User-Agent', 'Connection', 'Cache-Control');
			$headerKeys = array_intersect ($headerKeys, $importantHeaders);
			
			
			//-----------------------------------------
			// Compare value
			//-----------------------------------------
			
			if ($this->feature->checkHttpHeaderOrder && $this->userStore->hasValue ('serverHttpHeaderOrder'))
			{
				$savedOrder = $this->userStore->getValue ('serverHttpHeaderOrder');
				
				// Filter out values which are only in one of the arrays
				$savedOrder = array_intersect ($savedOrder, $headerKeys);
				$headerKeys = array_intersect ($headerKeys, $savedOrder);
				
				
				reset ($headerKeys);
				reset ($savedOrder);
				
				do
				{
					// RFC2616 (HTTP/1.1) states Header are case-insensitive, convert them to upper case
					$curA = strtoupper ( current ($headerKeys) );
					$curB = strtoupper ( current ($savedOrder) );

					if ($curA != $curB)
						throw new Exception ('HTTP Header Order does not match');

				}
				while (next ($headerKeys) !== false && next ($savedOrder) !== false);
			}
			else
			{
				//-----------------------------------------
				// Set values
				//-----------------------------------------
				
				$this->userStore->setValue ('serverHttpHeaderOrder', $headerKeys);
			}
		}
		
		
		return true;
	}
}
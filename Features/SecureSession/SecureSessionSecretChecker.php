<?php
/**
 * Ported from Session Lock by Ben Adida
 * https://github.com/benadida/sessionlock/blob/master/python/sessionlock.py
 *
 * @author Thomas Unger
 */

namespace SHPF\Features\SecureSession;

use SHPF\Utilities;

use SHPF\Features\Feature;

use SHPF\SHPF;

use SHPF\Checkers\SynchronousChecker;

use \Exception;


/**
 * Checker to validate Http Requests using shared secret
 * 
 * @author Thomas Unger
 *
 */
class SecureSessionSecretChecker extends SynchronousChecker
{
	public function __construct (SHPF $shpf, Feature $feature)
	{
		parent::__construct('secret_checker', $shpf, $feature);
	}
	
	
	public function run ()
	{

		//-----------------------------------------
		// Add required javascripts
		//-----------------------------------------
		
		$this->output->addJSFile ('Features/SecureSession/js/XMLHttpRequest.js');
		$this->output->addJSFile ('Features/SecureSession/js/parseuri.js');
		$this->output->addJSFile ('Features/SecureSession/js/date.js');
		$this->output->addJSFile ('Features/SecureSession/js/hmac.js');
		
		if ($this->output->includeJSLibrary)
			$this->output->addJSFile ('Output/js/mootools-more-1.4.0.1.js');
		
		$this->output->addJSFile ('Features/SecureSession/js/locksession.js');
		$this->output->addJSFile ('Features/SecureSession/js/shpf.failed.js');
		
		if (! $_REQUEST['ls_timestamp'] || ! $_REQUEST['ls_sig'])
			$this->output->addJSFile ('Features/SecureSession/js/shpf.redirect.js');
		
		if ($this->feature->useEncryption === true)
		{
			$this->output->addJSFile ('Features/SecureSession/js/2.5.3-crypto-sha1-hmac-pbkdf2-blockmodes-aes.js');
			$this->output->addJSFile ('Features/SecureSession/js/shpf.crypt.js');
		}

		
		$sessID = session_id ();
		
		//-----------------------------------------
		// Save last SID
		// If SID changes (session_regenerate_id), must be saved in localStorage as well
		//-----------------------------------------
		
		if (!$this->userStore->hasValue ('secureSession_lastSID'))
		{
			$this->userStore->setValue ('secureSession_lastSID', $sessID);
		}
		else if ($sessID != $this->userStore->getValue ('secureSession_lastSID'))
		{
			$oldSessionID = $this->userStore->getValue ('secureSession_lastSID');
			
			$js .= <<<END
		LockSession.init();
		LockSession.SESSIONID = '$oldSessionID';
		LockSession.load_token();
END;
			
			$this->userStore->setValue ('secureSession_lastSID', $sessID);
		}
		
		//-----------------------------------------
		// Set session id
		//-----------------------------------------

		$js .= <<<END
		LockSession.SESSIONID = '$sessID';
END;
		

		$this->output->outputJS ($js);
		
		
		
		/*-------------------------------------------------------------------------*/
		// Check if all required variables / parameters are there
		/*-------------------------------------------------------------------------*/
		
		$softFail = $this->softFail ();
		
		//-----------------------------------------
		// Server side shared secret
		//-----------------------------------------
		
		if (! $this->userStore->hasValue ('secureSession_shared') )
		{
			if (!$softFail && ($_REQUEST['ls_timestamp'] || $_REQUEST['ls_sig']))
				throw new Exception ('Invalid link. No session started.');
				
			return true;
		}
			
		
		$sharedSecret = $this->userStore->getValue ('secureSession_shared');


		//-----------------------------------------
		// GET parameters
		//-----------------------------------------
		
		if (! $_REQUEST['ls_timestamp'])
		{
			if (!$softFail)
				throw new Exception ('No timestamp');
			return true;
		}
			
		if (! $_REQUEST['ls_sig'])
		{
			if (!$softFail)
				throw new Exception ('No hmac');
			
			return true;
		}
			
		
		$clientHMAC = $_REQUEST['ls_sig'];

		
		//-----------------------------------------
		// Check timestamp
		//-----------------------------------------
		
		$time = @strtotime ($_REQUEST['ls_timestamp']);
		
		if ($this->userStore->hasValue ('secureSession_timediff'))
			$timediff = $this->userStore->getValue ('secureSession_timediff');
		else
			$timediff = 0;		
		
		$timeDelta = (time () - $timediff) - $time;
		
		if ($timeDelta < 0 || $timeDelta > $this->feature->syncEncryptedTimeout)
			throw new Exception ('Timeout: '. $timeDelta);
		
	
		//-----------------------------------------
		// Get URL to sign
		//-----------------------------------------

		unset ($_REQUEST['ls_sig']);
		
		// For some strange reason, there's a wrong session id in _REQUEST
		if (defined ('SID') && strlen (SID) > 0)
			$_REQUEST[ session_name() ] = session_id ();
		else
			unset ($_REQUEST[ session_name() ]);
		
		
		$keys = array_keys($_REQUEST);
		sort ($keys);
		
		
		$url = $_SERVER['SCRIPT_NAME'];
		$url .= '?';
		
		$params = '';
		
		foreach ($keys as $key)
		{
			if (strlen ($params) > 0)
				$params .= '&';
			
			$params .= urlencode($key) .'='. urlencode ($_REQUEST[ $key ]);
		}
		
		$urlToSign = $url . $params;
		
		
		
		
		//-----------------------------------------
		// Calculate HMAC
		//-----------------------------------------
		
		if (function_exists ('hash_hmac'))
			$hmac = hash_hmac ('sha1', $urlToSign, $sharedSecret);
		else
			$hmac = $this->hmac_sha1 ($sharedSecret, $urlToSign);

		
		//-----------------------------------------
		// Compare HMAC
		//-----------------------------------------
		
		if (!$softFail && $clientHMAC != $hmac)
			throw new Exception ('HMACs not matching');
		
		if (!$softFail && $this->feature->resetFailCountOnSuccess == true)
			$this->userStore->setValue ('secureSession_missingGETcount', 0);
		
		
		return true;
	}
	
	/**
	 * Counts failed requests and returns if another failed request is allowed
	 * @return boolean
	 */
	private function softFail ()
	{
		$count = $this->userStore->getValue ('secureSession_missingGETcount');
		
		if ($count === null)
			$count = 0;
		
		$count ++;
		
		$this->userStore->setValue ('secureSession_missingGETcount', $count);
		
		if ($count > $this->feature->allowedFailCount)
			return false;
		
		return true;
	}
	
	
	/**
	 * Generates HMAC using SHA1
	 * @param string $key
	 * @param string $data
	 * @return string
	 */
	private function hmac_sha1($key, $data)
	{
	    // Adjust key to exactly 64 bytes
	    if (strlen($key) > 64) {
	        $key = str_pad(sha1($key, true), 64, chr(0));
	    }
	    if (strlen($key) < 64) {
	        $key = str_pad($key, 64, chr(0));
	    }
	
	    // Outter and Inner pad
	    $opad = str_repeat(chr(0x5C), 64);
	    $ipad = str_repeat(chr(0x36), 64);
	
	    // Xor key with opad & ipad
	    for ($i = 0; $i < strlen($key); $i++) {
	        $opad[$i] = $opad[$i] ^ $key[$i];
	        $ipad[$i] = $ipad[$i] ^ $key[$i];
	    }
	
	    return sha1($opad.sha1($ipad.$data, true));
	}

}
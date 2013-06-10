<?php

namespace SHPF\Features\SecureSession;

use SHPF\SecurityLevel;

use SHPF\Logger;

use SHPF\ICryptoProvider;

use SHPF\SHPF;

use SHPF\Features\Feature;




class SecureSessionFeature extends Feature implements ICryptoProvider
{
	/**
	 * Specifies, whether crypto provider / encryption for asynchronous communication using the shared key should be used
	 * @var boolean
	 */
	public $useEncryption = true;
	
	/**
	 * Timeout in seconds of asynchronous, encrypted messages
	 * @var integer Timeout in seconds
	 */
	public $asyncEncryptedTimeout = 30;
	
	/**
	 * Timeout in seconds of received HMAC'd requests
	 * @var integer Timeout in seconds
	 */
	public $syncEncryptedTimeout = 300;
	
	/**
	 * Number of invalid consecutive requests, before session is killed
	 * @var integer
	 */
	public $allowedFailCount = 1;
	
	/**
	 * Reset the fail count timer, once a valid request is received
	 * @var boolean
	 */
	public $resetFailCountOnSuccess = true;
	
	
	
	private $secretChecker;
	private $secretNegotiation;
	
	/*-------------------------------------------------------------------------*/
	// Public
	/*-------------------------------------------------------------------------*/
	
	public function __construct (SHPF $shpf)
	{
		parent::__construct ('secure_url', $shpf);
		
		$this->setSecurityLevel(SecurityLevel::MEDIUM);
		
		$this->secretNegotiation = new SecureSessionSecretNegotiation ($this->shpf, $this);
		$this->addChecker ($this->secretNegotiation);
		
		
		$this->secretChecker = new SecureSessionSecretChecker ($this->shpf, $this);
		$this->addChecker ($this->secretChecker);
		
		//-----------------------------------------
		// Crypto Provider
		//-----------------------------------------
		
		if ($this->useEncryption === true)
			$shpf->setCryptoProvider($this);
	}
	
	
	/*-------------------------------------------------------------------------*/
	// CryptoProvider
	/*-------------------------------------------------------------------------*/
	

	private function checkMcrypt ()
	{
		if (!function_exists ('mcrypt_module_open') || ! MCRYPT_RIJNDAEL_128)
			throw new \Exception ('mcrypt required for encryption/decryption');
	}
	
	private function getSharedKey ()
	{
		return $this->userStore->getValue ('secureSession_shared');
	}
	
	private function getCryptoKey ()
	{
		$sharedKey = $this->getSharedKey();
		return $sharedKey;
		
		//$cryptoKey = sha1($sharedKey);
		
		//return $cryptoKey;
	}
	
	public function encrypt ($message)
	{
		throw new \Exception ('Not implemented');
		
		$this->checkMcrypt();
		
		$key = $this->getCryptoKey ();
		if (!$key)
			return $message;
		
		$td = mcrypt_module_open (MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		
		
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		
	
		
		mcrypt_generic_init($td, $key, $iv);
		$encrypted_data = mcrypt_generic($td, $message);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return $encrypted_data;
	}
	
	public function decrypt ($message)
	{
		$this->checkMcrypt();
	
		$key = $this->getCryptoKey ();
		if (!$key)
			return $message;
	
		$td = mcrypt_module_open (MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		//$this->cryptIV = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		
		$iv = substr($message, 0, 16); 
		$message = substr ($message, 16);
		
		
		//Logger::writeLine ('IV: '. $iv);
		
		$key = $this->PBKDF2($key, $iv, 1, 32); 
		
		mcrypt_generic_init($td, $key, $iv);
		$decrypted = mdecrypt_generic($td, $message);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		
		$decrypted = trim ($decrypted);
		
		
		//-----------------------------------------
		// Extract timestamp
		//-----------------------------------------
		
		$parts = explode ('|', $decrypted, 2);
		
		if (count ($parts) != 2)
			throw new \Exception ('Missing timestamp in decrypted message');
		
		$timestamp = $parts[0];
		$decrypted = $parts[1];
		
		//-----------------------------------------
		// Check timestamp
		//-----------------------------------------
		
		$time = @strtotime ($timestamp);
		
		$timeDelta = time () - $time;
		
		if ($timeDelta > $this->asyncEncryptedTimeout)
			throw new \Exception ($timestamp . ' =  ' . time () . ' = ' . $time .' - Timeout: '. $timeDelta);
		
		
		
		
		return $decrypted;
	}
	
	/**
	 * PHP PBKDF2 Implementation.
	 *
	 * For more information see: http://www.ietf.org/rfc/rfc2898.txt
	 *
	 * @param string $p             password
	 * @param string $s             salt
	 * @param integer $c            iteration count (use 1000 or higher)
	 * @param integer $dkl  derived key length
	 * @param string $algo  hash algorithm
	 * @return string                       derived key of correct length
	 */
	private function PBKDF2($p, $s, $c, $dkl, $algo = 'sha1') {
		$kb = ceil($dkl / strlen(hash($algo, null, true)));
		$dk = '';
		for($block = 1; $block <= $kb; ++$block) {
			$ib = $b = hash_hmac($algo, $s.pack('N', $block), $p, true);
			for($i = 1; $i < $c; ++$i)
				$ib ^= ($b = hash_hmac($algo, $b, $p, true));
			$dk.= $ib;
		}
		return substr($dk, 0, $dkl);
	
	}
	
	
	
	public function setPrime ($prime, $bits)
	{
		$this->secretNegotiation->setPrime ($prime, $bits);
	}

}
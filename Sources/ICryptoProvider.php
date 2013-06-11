<?php

namespace SHPF;

/**
 * Interface for a crypto provider. Defines encryption and decryption methods.
 * @author Thomas Unger
 */
interface ICryptoProvider
{
	/**
	 * Encrypts a message
	 * @param string $message Message to encrypt
	 * @return string Encrypted message
	 */
	public function encrypt ($message);
	
	/**
	 * Decrypts a message
	 * @param string $message Message to decrypt
	 * @return string Decrypted message
	 */
	public function decrypt ($message);
}
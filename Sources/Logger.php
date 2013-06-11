<?php


namespace SHPF;

/**
 * Logs messages to a file
 * 
 * @author Thomas Unger
 *
 */
class Logger
{
	/**
	 * Defines whether to start each log line with the current date and time (default true)
	 * @var boolean
	 */
	public static $prependDateTime = true;
	
	/**
	 * Defines whether logging is enabled
	 * @var boolean
	 */
	private static $enabled = false;
	
	/**
	 * Sets whether logging is enabled
	 * @param boolean $enabled
	 */
	public static function setEnabled ($enabled)
	{
		self::$enabled = $enabled;
	}
	
	/**
	 * Writes a new line to the log
	 * @param string $message The message to log
	 */
	public static function writeLine ($message)
	{
		if (!self::$enabled) return;
		
		self::write ($message ."\n");
	}
	
	/**
	 * Writes text to the log
	 * @param string $message The text to log
	 */
	public static function write ($message)
	{
		if (!self::$enabled) return;
		
		if (self::$prependDateTime === true)
			$message = @date (DATE_ISO8601) . ' ' . $message;
		
		try
		{
			$fp = fopen ('log.txt', 'a+');
			
			fputs ($fp, $message);
			fclose ($fp);
		}
		catch (Exception $x)
		{
		}
		
	}
}


?>
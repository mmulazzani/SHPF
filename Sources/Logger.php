<?php


namespace SHPF;


class Logger
{
	public static $prependDateTime = true;
	
	private static $enabled = false;
	
	public static function setEnabled ($enabled)
	{
		self::$enabled = $enabled;
	}
	
	public static function writeLine ($message)
	{
		if (!self::$enabled) return;
		
		self::write ($message ."\n");
	}
	
	
	public static function write ($message)
	{
		if (!self::$enabled) return;
		
		if (self::$prependDateTime === true)
			$message = @date (DATE_ISO8601) . ' ' . $message;
		
		try
		{
			/*if (!file_exists ('log.txt'))
			{
				$fp = fopen ('log.txt', 'a+');
				fclose ($fp);
			}*/
			
			$fp = fopen ('log.txt', 'a+');
			//rewind ($fp);
			fputs ($fp, $message);
			fclose ($fp);
		}
		catch (Exception $x)
		{
		}
		
	}
}


?>
<?php

namespace SHPF;



class Utilities
{
	public static function getSHPFPath ()
	{
		return SHPF_ROOT_WEB;
	}
	
	public static function getPath ($path)
	{
		$dirname = self::getSHPFPath();
		$filename = $dirname . $path;

		return $filename;
	}
	
	public static function getFullRequestURI ()
	{
		$protocol = 'http';
		
		if ($_SERVER['SERVER_PORT'] == 443)
			$protocol = 'https';
		
		$url = $protocol .'://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		
		return $url;
	}
	
	public static function strCapitalize ($str)
	{
		return strtoupper (substr ($str, 0, 1)) . substr ($str, 1);
	}
}
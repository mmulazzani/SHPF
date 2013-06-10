<?php

namespace SHPF;

class AutoLoader
{
	public static $baseDir;
	
	static private function tryIncludeFile ($filename)
	{
		$filename = self::$baseDir .'/'. $filename;
		//echo $filename."<br>";

		if (file_exists ($filename))
		{
			include ($filename);
			//echo 'Autloaded: ' . $filename ."<br>";
			return true;
		}

		return false;
	}

	static public function load ($name)
	{
		$parts = explode ('\\', $name);

		$lastpart = $parts[ count($parts) - 1 ];

		$filename = $lastpart . '.php';
		
		if (count ($parts) <= 2)
		{
			if (self::tryIncludeFile('Sources/'. $filename))
				return;
		}

		//-----------------------------------------
		// Look up in namespace directories
		//-----------------------------------------

		for ($i = 1; $i < count($parts) - 1; $i++)
			$path .= strtolower ($parts[ $i ]) . '/';
			
		if (self::tryIncludeFile($path . $filename))
			return;
	}
}

spl_autoload_register('SHPF\AutoLoader::load');
AutoLoader::$baseDir = dirname(__FILE__);

?>
<?php

namespace SHPF\Output;

use SHPF\Utilities;

use SHPF\SHPF;

class Output
{
	public $useScriptTags = true;
	
	/**
	 * @var SHPF
	 */
	protected $shpf;
	
	/**
	 * @var IUserStore
	 */
	protected $userStore;
	
	public $includeJSLibrary = true;
	
	private $libraryFlushed = false;
	
	
	protected $outputBuffer = array (
			
				'html'	=>	'',
				'js'	=>	'',
				'head'	=>	'',
			
			);
	
	
	/*-------------------------------------------------------------------------*/
	// Public
	/*-------------------------------------------------------------------------*/
	
	public function __construct (SHPF $shpf)
	{
		$this->shpf = $shpf;
		$this->userStore = $shpf->getUserStore();		


	}
	
	public function __destruct()
	{
		$this->flushAll();
	}
	
	
	public function outputJS ($output)
	{
		$this->outputBuffer[ 'js' ] .= $output;
	}
	
	public function outputHTML ($output)
	{
		$this->outputBuffer[ 'html' ] .= $output;
	}
	
	public function outputHead ($output)
	{
		$this->outputBuffer[ 'head' ] .= $output;
	}
	
	
	
	public function flushAll ($return = false)
	{
		$this->flushHead ($return);
		$this->flushHTML ($return);
		$this->flushJS ($return);
	}
	
	public function flushJS ($return = false)
	{
		if (empty ($this->outputBuffer['js']))
			return;
		
		if ($this->useScriptTags)
		{
			$this->outputBuffer[ 'js' ] = 
				'<script type="text/javascript">' . $this->outputBuffer[ 'js' ] . '</script>';
		}
		
		if (!$return)
			echo $this->outputBuffer[ 'js' ];
		else
			$retOutput = $this->outputBuffer[ 'js' ];
		
		$this->outputBuffer[ 'js' ] = '';
		
		if ($return)
			return $retOutput;
	}
	
	public function flushHTML ($return = false)
	{
		if (!$return)
			echo $this->outputBuffer[ 'html' ];
		else
			$retOutput = $this->outputBuffer[ 'html' ];
		
		$this->outputBuffer[ 'html' ] = '';
		
		if ($return)
			return $retOutput;
	}
	
	public function flushHead ($return = false)
	{
		if (!$this->libraryFlushed)
		{
			$this->libraryFlushed = true;
			
			$tmp = $this->flushHead (true);
			
			
			//-----------------------------------------
			// JS Library
			//-----------------------------------------
			
			if ($this->includeJSLibrary)
				$this->addJSFile('Output/js/mootools-core-1.4.5.js');
			
			
			//-----------------------------------------
			// Own Library
			//-----------------------------------------
			
			$this->addJSFile ('Features/Base/js/shpf.js');
				
		
		
			
			$this->outputHead ($tmp);
		}
		
		
		
		if (!$return)
			echo $this->outputBuffer[ 'head' ];
		else
			$retOutput = $this->outputBuffer[ 'head' ];
	
		$this->outputBuffer[ 'head' ] = '';
		
		if ($return)
			return $retOutput;
	}
	
	public function setIncludeJSLibrary ($include)
	{
		$this->includeJSLibrary = $include;
	}
	
	public function addJSFile ($path)
	{
		// Relativ?
		if (!strpos ($path, '://'))
			$path = Utilities::getPath ($path);
		
		$this->outputHead('<script type="text/javascript" src="'. $path .'"></script>');
	}
	
	
	public function onFailed ()
	{
		$this->outputJS ("SHPF.fireEvent('failed');");
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Private
	/*-------------------------------------------------------------------------*/

	
	
}
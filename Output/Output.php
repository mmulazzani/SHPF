<?php

namespace SHPF\Output;

use SHPF\Utilities;

use SHPF\SHPF;

/**
 * This class controls the output of the framework.
 * 
 * @author Thomas Unger
 *
 */
class Output
{
	/**
	 * Defines whether to use script tags when outputting the raw javascript code (default true)
	 * @var boolean
	 */
	public $useScriptTags = true;
	
	/**
	 * Defines whether to include the boxed MooTools library (version 1.4.5) (default true). 
	 * Can be set to false, but in that case a compatible library must be provided by the web application.
	 * @var boolean
	 */
	public $includeJSLibrary = true;
	
	
	/**
	 * Reference to the SHPF instance
	 * @var SHPF
	 */
	protected $shpf;
	
	/**
	 * Reference to the userstore
	 * @var IUserStore
	 */
	protected $userStore;

	
	/**
	 * The buffered output of the various output groups (html, js, head). These are emptied when calling the flushX methods.
	 * @var array
	 */
	protected $outputBuffer = array (
			
				'html'	=>	'',
				'js'	=>	'',
				'head'	=>	'',
			
			);
	
	/**
	 * Defines whether the javascript library has already been outputted
	 * @var boolean
	 */
	private $libraryFlushed = false;
	
	
	/*-------------------------------------------------------------------------*/
	// Public
	/*-------------------------------------------------------------------------*/
	
	public function __construct (SHPF $shpf)
	{
		$this->shpf = $shpf;
		$this->userStore = $shpf->getUserStore();		
	}

	/**
	 * Destructor. Flushes the buffered output if not yet done by the web application. 
	 * (results in content being flushed at the end of the page)
	 */
	public function __destruct()
	{
		$this->flushAll();
	}
	
	/**
	 * Outputs raw javascript code. Must be flushed so that it is sent to the browser.
	 * @param string $output
	 */
	public function outputJS ($output)
	{
		$this->outputBuffer[ 'js' ] .= $output;
	}
	
	/**
	 * Outputs HTML code. Must be flushed so that it is sent to the browser.
	 * @param string $output
	 */
	public function outputHTML ($output)
	{
		$this->outputBuffer[ 'html' ] .= $output;
	}
	
	/**
	 * Outputs HTML code which needs to be in the HTML header. Must be flushed so that it is sent to the browser.
	 * @param string $output
	 */
	public function outputHead ($output)
	{
		$this->outputBuffer[ 'head' ] .= $output;
	}
	
	
	/**
	 * Flushes all buffered output. The order is: head, html, js
	 * @param boolean $return If true, output is returned instead of being sent to the browser.
	 */
	public function flushAll ($return = false)
	{
		$this->flushHead ($return);
		$this->flushHTML ($return);
		$this->flushJS ($return);
	}
	
	/**
	 * Flushes buffered javascript output.
	 * @param string $return If true, output is returned instead of being sent to the browser.
	 * @return void|string
	 */
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
	
	/**
	 * Flushes buffered HTML output.
	 * @param string $return If true, output is returned instead of being sent to the browser.
	 * @return void|string
	 */
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
	
	/**
	 * Flushes buffered HTML head output. Includes SHPF javscript libraries on first call.
	 * 
	 * @param string $return If true, output is returned instead of being sent to the browser.
	 * @return void|string
	 */
	public function flushHead ($return = false)
	{
		//-----------------------------------------
		// First time only includes
		//-----------------------------------------
		
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
	
	/**
	 * Sets whether to include the boxed MooTools library 
	 * @param boolean $include
	 */
	public function setIncludeJSLibrary ($include)
	{
		$this->includeJSLibrary = $include;
	}
	
	/**
	 * Adds a javascript file to the HTML head includes.
	 * @param string $path
	 */
	public function addJSFile ($path)
	{
		// Relative?
		if (!strpos ($path, '://'))
			$path = Utilities::getPath ($path);
		
		$this->outputHead('<script type="text/javascript" src="'. $path .'"></script>');
	}
	
	/**
	 * Is called when a checker fails.
	 */
	public function onFailed ()
	{
		// Fire javascript failed event
		$this->outputJS ("SHPF.fireEvent('failed');");
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Private
	/*-------------------------------------------------------------------------*/

	
	
}
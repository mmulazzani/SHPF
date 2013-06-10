<?php

namespace SHPF\Features\CSSFingerprint;

use SHPF\Logger;

use SHPF\Utilities;

use SHPF\Features\Feature;

use SHPF\SHPF;

use SHPF\Checkers\AsynchronousChecker;

use \Exception;



class CSSFingerprintChecker extends AsynchronousChecker
{
	const TIMEOUT = 30;
	
	/**
	 * 1st element: CSS expression
	 * 2nd element: javascript property to check
	 * 3rd element: javascript; check property for browser prefixes 
	 */
	private $cssProperties = array (
			
		/*-------------------------------------------------------------------------*/
		// Recommended
		/*-------------------------------------------------------------------------*/

		/**
		 * CSS inline-block
		 * http://caniuse.com/inline-block
		 */
		array ('display:inline-block;', 'display', false),

		/**
		 * CSS min/max-width/height
		 * http://caniuse.com/minmaxwh
		 */
		array ('min-width:35px;', 'minWidth', false),
			
		/**
		 * CSS position:fixed
		 * http://caniuse.com/css-fixed
		 */
		array ('position:fixed;', 'position', false),
			
		/**
		 * CSS Table display
		 * http://caniuse.com/css-table
		 */
		array ('display:table-row;', 'display', false),
			
		/**
		 * CSS3 Opacity
		 * http://caniuse.com/css-opacity
		 */
		array ('opacity:0.5;', 'opacity', false),
			
		/**
		 * CSS3 Colors
		 * http://caniuse.com/css3-colors
		 */
		array ('background:hsla(56, 100%, 50%, 0.3);', 'background', false),

	
		/*-------------------------------------------------------------------------*/
		// Candidate Recommendation
		/*-------------------------------------------------------------------------*/
			
		/**
		 * CSS3 Box-sizing
		 * http://caniuse.com/css3-boxsizing
		 */	
		array ('box-sizing:border-box;', 'boxSizing', true),
			
		/**
		 * CSS3 Border-radius (rounded corners)
		 * http://caniuse.com/border-radius
		 */
		array ('border-radius:9px;', 'borderRadius', true),
			
		/**
		 * CSS3 Box-shadow
		 * http://caniuse.com/css-boxshadow
		 */
		array ('box-shadow: inset 4px 4px 16px 10px #000;', 'boxShadow', true),
			
		/**
		 * CSS3 Multiple column layout
		 * http://caniuse.com/multicolumn
		 */
		array ('column-count:4;', 'columnCount', true),
			
		/*-------------------------------------------------------------------------*/
		// Working Draft
		/*-------------------------------------------------------------------------*/

		/**
		 * CSS3 Transforms
		 * http://caniuse.com/transforms2d
		 */
		array ('transform:rotate(30deg);', 'transform', true),
			
		/**
		 * rem (root em) units
		 * http://caniuse.com/rem
		 */
		array ('font-size: 2rem;', 'fontSize', false),
			
		/**
		 * CSS3 Text-shadow
		 * http://caniuse.com/css-textshadow
		 */
		array ('text-shadow: 4px 4px 14px #969696;', 'textShadow', false),
			
		/**
		 * CSS Gradients
		 * http://caniuse.com/css-gradients
		 */	
		array ('background:{PREFIX}linear-gradient(left, red, blue 30%, green);', 'background', true),
			
		/**
		 * CSS3 Transitions
		 * http://caniuse.com/css-transitions
		 */
		array ('transition: background-color 2s linear 0.5s;', 'transition', true),
			
		/**
		 * CSS3 Animation
		 * http://caniuse.com/css-animation
		 */
		array ('animation: animationName 4s linear 1.5s infinite alternate none;', 'animation', true),
			
		/**
		 * CSS resize property
		 * http://caniuse.com/css-resize
		 */
		array ('resize:both;', 'resize', false),
			
		/**
		 * Flexible Box Layout Module
		 * http://caniuse.com/flexbox
		 */
		array ('box-orient:horizontal;', 'boxOrient', true),
			
		/**
		 * CSS3 3D Transforms
		 * http://caniuse.com/transforms3d
		 */
		array ('transform-style:preserve-3d;', 'transformStyle', true),
			
		/**
		 * Font feature settings
		 * http://caniuse.com/font-feature
		 */
		array ('font-feature-settings:"dlig=1,ss01=1";', 'fontFeatureSettings', true),
			
		/**
		 * calc() as CSS unit value
		 * http://caniuse.com/calc
		 */
		array ('width:{PREFIX}calc(25% - 1em);', 'width', true),
			
		/**
		 * CSS Hyphenation
		 * http://caniuse.com/css-hyphens
		 */
		array ('hyphens:auto;', 'hyphens', true),
		
		/**
		 * CSS3 object-fit/object-position
		 * http://caniuse.com/object-fit
		 */
		array ('object-fit:contain;', 'objectFit', true),
			
			
	);
	
	
	public function __construct (SHPF $shpf, Feature $feature)
	{
		parent::__construct('checker', $shpf, $feature);
	}
	
	public function run ()
	{
		//-----------------------------------------
		// Procedure:
		// 1) Check if JS postback is possible
		// 2) If not, always accept this checker
		// 3) If yes, run check
		//-----------------------------------------
		
		/*-------------------------------------------------------------------------*/
		// Initialize 
		/*-------------------------------------------------------------------------*/
		
		if (!$this->userStore->hasValue ('cssFingerprint_initialized'))
		{
			// Save time of first request
			$this->userStore->setValue ('cssFingerprint_initialized', time());
			
			//return true;
		}
		
		/*-------------------------------------------------------------------------*/
		// Check compatibility (if javascript postback is possible)
		/*-------------------------------------------------------------------------*/
		
		elseif (!$this->userStore->hasValue ('cssFingerprint_compatible') && $this->feature->allowNoJavascript)
		{
			//-----------------------------------------
			// Get init time and check if there is still
			// no answer from the browser
			// -> indicates that no Javascript is enabled
			//-----------------------------------------
			
			$initTime = $this->userStore->getValue ('cssFingerprint_initialized');
			
			if ($initTime + self::TIMEOUT < time ())
			{
				$this->userStore->setValue ('cssFingerprint_compatible', false);
				return true;
			}
			
			//return true;
		}
		
		/*-------------------------------------------------------------------------*/
		// Not compatible, accept always
		/*-------------------------------------------------------------------------*/
		
		elseif ($this->userStore->getValue ('cssFingerprint_compatible') === false)
			return true;
		
		
		/*-------------------------------------------------------------------------*/
		// Run check
		/*-------------------------------------------------------------------------*/
		
		//-----------------------------------------
		// Is one still active?
		//-----------------------------------------
		
		if ($this->feature->enableAsyncTimeout && 
				$this->userStore->hasValue ('cssFingerprint_lastRequest'))
		{
			$lastRequestTime = $this->userStore->getValue ('cssFingerprint_lastRequest');
				
			if ($lastRequestTime + $this->feature->asyncTimeout < time ())
				throw new Exception ('Timeout. No response from async callback.');
				
		}
		
	/*	
		//-----------------------------------------
		// HTML Output
		//-----------------------------------------
		
		$html = <<<END
		
	<div id="cssFingerprint" style="display:none; border-radius: 3px;"></div>	
		
END;
		
		
		$this->output->outputHTML ($html);
		
		
		//-----------------------------------------
		// Javascript Output
		//-----------------------------------------
		
		$js = <<<END
		
window.addEvent('domready', function()
{
    
	CSSFingerprint.run ('{$this->getAsyncQueryString()}');
	
});

END;
		
		$this->output->addJSFile ('Features/CSSFingerprint/js/cssfingerprint.js');
		
		$this->output->outputJS ($js);
		*/
		
		//-----------------------------------------
		// Get properties to check
		//-----------------------------------------
		
		// First time, we want all properties
		if (!$this->userStore->hasValue ('cssFingerprint_data'))
			$properties = $this->getAllCssProperties();
		else
		{
			// Get random set
			$properties = $this->getRandomCssProperties();
			
			// Get the keys of these properties
			// The keys point to the keys in the big css property definitions array
			$keys = array_keys ($properties);
			
			// Expand the lastRequestKeys array
			$lastRequestKeys = $this->userStore->getValue ('cssFingerprint_lastRequestKeys');
			if (!is_array ($lastRequestKeys))
				$lastRequestKeys = array ();
			
			//$lastRequestKeys[] = $keys;
			
			// Too many requests?
			if (count ($lastRequestKeys) > $this->feature->maxOpenRequests)
				throw new Exception ('Too many open css fingerprint requests');
			
			$keyRequest = array (
					
					'time'	=>	time(),
					'keys'	=>	$keys,
					
					);
			
			$lastRequestKeys[] = $keyRequest;
			
			$this->userStore->setValue ('cssFingerprint_lastRequestKeys', $lastRequestKeys);
			
		}
		
		//-----------------------------------------
		// Run the check
		//-----------------------------------------
		
		$this->runCheck($properties);
		
		
		//-----------------------------------------
		// Save time
		//-----------------------------------------
		
		if (!$this->userStore->hasValue ('cssFingerprint_lastRequest'))
			$this->userStore->setValue ('cssFingerprint_lastRequest', time());

		
		return true;
	}
	
	public function runAsync ()
	{

		/*-------------------------------------------------------------------------*/
		// Set compatibility to true
		/*-------------------------------------------------------------------------*/
		
		if (!$this->userStore->hasValue ('cssFingerprint_compatible'))
		{
			$this->userStore->setValue ('cssFingerprint_compatible', true);
		}
		
		
		/*-------------------------------------------------------------------------*/
		// Run check
		/*-------------------------------------------------------------------------*/
		
		//-----------------------------------------
		// Complete request
		//-----------------------------------------
		
		$this->userStore->setValue ('cssFingerprint_lastRequest', null);
		
		//-----------------------------------------
		// First time?
		//-----------------------------------------
		
		if (!$this->userStore->hasValue ('cssFingerprint_data'))
		{
			$this->userStore->setValue ('cssFingerprint_data', $this->postData);
			return true;
		}
		
		//-----------------------------------------
		// Compare
		//-----------------------------------------
		
		/*
		$lastData = $this->userStore->getValue ('cssFingerprint_data');		
		$curData = $this->postData;
		
		// Valid format? If not, what happened? Could not decrypt?
		if (!is_array ($this->postData))
			throw new Exception ('Invalid post data');
		
		
		// Element count
		if (count ($lastData) != count ($curData))
			throw new Exception ('Property count not equal');
		
		// Properties
		foreach ($lastData as $key => $elem)
		{
			if ($curData[ $key ] != $elem)
				throw new Exception ('Property mismatch: '. $key);
		}*/
		
		// Get the reference data whichs contains all properties
		$referenceData = $this->userStore->getValue ('cssFingerprint_data');
		
		// Get the keys which were sent to the client the last requests
		$expectedKeys = $this->userStore->getValue ('cssFingerprint_lastRequestKeys');
		
		
		$success = false;
		// Loop through all open request keys
		// The received values must match one of these sets
		foreach ($expectedKeys as $keyRequestKey => $keyRequest)
		{
			// Check timeout for each request?
			if ($this->feature->enableAsyncTimeout && $this->feature->checkTimeoutForEachRequest)
			{
				$time = $keyRequest['time'];
				
				if ($time + $this->feature->asyncTimeout < time ())
					throw new Exception ('Timeout. No response from one async callback of open requests.');
			}
			
			$hasDiff = false;
			
			// Loop through the keys in this set
			foreach ($keyRequest['keys'] as $key => $cssKey)
			{
				// The POST data is ordered, as is $key (numeric array)
				// Get the POST data on n-position
				$data = $this->postData[ $key ];
				
				// Get the according CSS reference data
				$cssData = $referenceData[ $cssKey ];
				
				// If the POST data is not an array, there is an error, exit
				if (!is_array ($data))
				{
					$hasDiff = true;
					break;
				}
				
				// Compare the arrays
				$diff = array_diff_assoc($data, $cssData);
				
				// If there is a difference ($diff contains differences as array elements)
				// exit
				if (count ($diff) > 0)
				{
					$hasDiff = true;
					break;
				}
			}
			
			// If there is no error, remove the successfully identified set of keys
			if (!$hasDiff)
			{
				unset ($expectedKeys[ $keyRequestKey ]);
				$this->userStore->setValue ('cssFingerprint_lastRequestKeys', $expectedKeys);
				
				$success = true;
				break;
			}
				//$i++;
			
			if ($success)
				break;
		}
		
		// Error
		if (!$success)
			throw new Exception ('Mismatching property data');
		
		
		return true;
	}
	
	
	private function runCheck (array $properties)
	{
		
		$cssPrefixes = array ('-webkit-','-moz-','-o-','-ms-','-khtml-');
		$javascriptPrefixes = array ('Webkit','Moz','O','ms','Khtml');
		
		
		$i = 0;
		$js = array ();
		
		// Output hidden div which contains all other divs
		$this->output->outputHTML ('<div style="display:none">');
		
		foreach ($properties as $prop)
		{
			$cssStyle = $prop[0];
			$javascriptProperty = $prop[1];
			$usePrefixes = $prop[2];
		
		
			$cssChecks = array ();
			$javascriptChecks = array ();
		
			// Replace quotes, because they cause problems
			$cssStyle = str_replace ('"', "'", $cssStyle);
		
			// If we want to use browser specific prefixes, we need to test all
			if ($usePrefixes)
			{
				// Do we have a custom prefix position?
				// That happens when the property itself doesn't need the prefix, but the value
				$customPrefix = strpos($cssStyle, '{PREFIX}') !== false;
		
				// First of all, use the property without prefix
				$cssChecks[] = str_replace ('{PREFIX}', '', $cssStyle);
				$javascriptChecks[] = str_replace ('{PREFIX}', '', $javascriptProperty);
				
				// Loop through css properties
				foreach ($cssPrefixes as $prefix)
				{
					if ($customPrefix)
						$cssChecks[] = str_replace ('{PREFIX}', $prefix, $cssStyle);
					else
						$cssChecks[] = $prefix . $cssStyle;
				}
		
				// Loop through javascript prefixes
				foreach ($javascriptPrefixes as $prefix)
				{
					if ($customPrefix)
						$javascriptChecks[] = str_replace ('{PREFIX}', $prefix, $javascriptProperty);
					else
						$javascriptChecks[] = $prefix . Utilities::strCapitalize($javascriptProperty);
				}
			}
			else
			{
				// No prefix needed, just use the definition
				
				$customPrefix = null;
				$cssChecks[] = $cssStyle;
				$javascriptChecks[] = $javascriptProperty;
			}
		
			
			$propJs = array ();
		
			foreach ($cssChecks as $key => $check)
			{
				$this->output->outputHTML ('<div id="cssCheck' . $i .'" style="'. $check .'"></div>');
		
				// Cache the JS checks in a seperate array
				$propJs[] = '"'. $javascriptChecks[$key] .'" in $("cssCheck'. $i .'").style';
				$propJs[] = '$("cssCheck'. $i .'").style.'. $javascriptChecks[$key];
		
				$i++;
			}
			
			// Cache this set of JS checks
			$js[] = $propJs;

		}
		
		// Close hidden div
		$this->output->outputHTML ('</div>');
		
		//-----------------------------------------
		// Javascript Output
		//-----------------------------------------
		
		// Format and send as Javascript JSON array
		// The browser evaluates the JSON array and therefore sends
		// an array of values in the format we want
		
		$jsJSON = '';
		
		foreach ($js as $j)
		{
			if (strlen ($jsJSON) > 0)
				$jsJSON .= ',';
			
			$jsJSON .= '[';
			$jsJSON .= join(',', $j);
			$jsJSON .= ']';
		}
		
		$jsJSON = '[' . $jsJSON .']';
		
		
		// Output the JSON array and async send code
		
		$jsOutput = <<<END
		window.addEvent('domready', function()
		{
			var test = $jsJSON;			
		
			CSSFingerprint.run ('{$this->getAsyncQueryString()}', test);
		
		});
END;
		
		$this->output->addJSFile ('Features/CSSFingerprint/js/cssfingerprint.js');
		
		$this->output->outputJS ($jsOutput);
		
	}
	
	

	/**
	 * Picks random entries of the different css property definitions
	 * The total amount of entries is specified by the first element of the property arrays
	 * 
	 * @return array Array of css property definitions
	 */
	private function getRandomCssProperties ()
	{
		//$recommended = array_rand($this->cssRecommended[1], $this->cssRecommended[0]);
		//$candidateRecommendation = array_rand($this->cssCandidateRecommendation[1], $this->cssCandidateRecommendation[0]);
		//$workingDraft = array_rand($this->cssWorkingDraft[1], $this->cssWorkingDraft[0]);
		
		//return array_merge ($recommended, $candidateRecommendation, $workingDraft);
		
		$keys = array_rand ($this->cssProperties, 3);
		
		$properties = array ();
		foreach ($keys as $key)
		{
			$properties[ $key ] = $this->cssProperties[ $key ];
		}
		return $properties;
	}
	
	/**
	 * Returns all CSS property definitions
	 * 
	 * @return array Array of CSS property definitions
	 */
	private function getAllCssProperties ()
	{
		//return array_merge ($this->cssRecommended[1], $this->cssCandidateRecommendation[1], $this->cssWorkingDraft[1]);
		return $this->cssProperties;
	}
}
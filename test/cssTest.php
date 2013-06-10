<!DOCTYPE html>
<html>
<head>



</head>
<body>

<?php

$css = array (

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
			
		// TODO: Border-Image nicht weil man ein bild braucht
			
		/**
		 * CSS3 Multiple column layout
* http://caniuse.com/multicolumn
*/
		array ('column-count:4;', 'columnCount', true),
			


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


echo '<textarea id="console" cols="100" rows="80"></textarea>';
$js = 'var log = function(type, msg) { document.getElementById("console").value += type + ": " + msg + "\n"; };';



$cssPrefixes = array ('-webkit-','-moz-','-o-','-ms-','-khtml-');
$javascriptPrefixes = array ('Webkit','Moz','O','Ms','Khtml');


$i = 0;

foreach ($css as $prop)
{
	$cssStyle = $prop[0];
	$javascriptProperty = $prop[1];
	$prefixes = $prop[2];
	
	
	$cssChecks = array ();
	$javascriptChecks = array ();
	
	
	$cssStyle = str_replace ('"', "'", $cssStyle);
	
	
	if ($prefixes)
	{
		$customPrefix = strpos($cssStyle, '{PREFIX}') !== false;
		
		$cssChecks[] = str_replace ('{PREFIX}', '', $cssStyle);
		foreach ($cssPrefixes as $prefix)
		{
			if ($customPrefix)
				$cssChecks[] = str_replace ('{PREFIX}', $prefix, $cssStyle);
			else
				$cssChecks[] = $prefix . $cssStyle; 
		}
		
		$javascriptChecks[] = str_replace ('{PREFIX}', '', $javascriptProperty);
		foreach ($javascriptPrefixes as $prefix)
		{
			if ($customPrefix)
			{
				$javascriptChecks[] = str_replace ('{PREFIX}', $prefix, $javascriptProperty);
			}
			else
				$javascriptChecks[] = $prefix . strtoupper (substr ($javascriptProperty, 0, 1)) . substr ($javascriptProperty, 1);
		}
	
		
	}
	else
	{
		$customPrefix = null;
		$cssChecks[] = $cssStyle;
		$javascriptChecks[] = $javascriptProperty;
	}
	
	
	
	
	
	echo '<div style="display:none">';
	
	foreach ($cssChecks as $key => $check)
	{
		echo '<div id="cssCheck' . $i .'" style="'. $check .'"></div>';
		
		$js .= 'log ("'. $javascriptChecks[$key] .'", "'. $javascriptChecks[$key] .'" in document.getElementById("cssCheck'. $i .'").style);' . "\n";
		$js .= 'log ("'. $check .'", document.getElementById("cssCheck'. $i .'").style.'. $javascriptChecks[$key] .');' . "\n";
		
		$i++;
	}
	
	echo '</div>';
	
	
}


echo '<script type="text/javascript">' . $js .'</script>';








?>

<style type="text/css">

body { 
font-size: 1em; 
background-color: hsla(0,100%,50%,0.2);
}

.test {
font-size: 1em; 
color: black;
}

</style>

<div class="test" id="testest"></div>


<script type="text/javascript">

var json = JSON.stringify (document.styleSheets[0].cssRules[0].style);




//alert(json);

//alert (document.styleSheets[0].cssRules[0].style.backgroundColor);
alert (window.getComputedStyle(document.getElementById('testest'), null).getPropertyValue('font-size'));

</script>



</body>
</html>
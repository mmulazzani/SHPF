<?php

// Create instance of SHPF
$shpf = new SHPF\SHPF ();

// Set a function as handler, when SHPF fails
$shpf->setCheckFailedHandler ('\failedHandler');

//-----------------------------------------
// Javascript failed handlers are also possible
// Only called if async checkers fail
//-----------------------------------------

/*
<script type="text/javascript">

	SHPF.addEvent ('failed', function (msg)
	{
		alert ("Failed!\n" + msg);
	});

</script>
*/

//-----------------------------------------
// Server Environment Feature
// - Checks server headers
//-----------------------------------------

// Create feature
$httpHeaderFeature = new SHPF\Features\HttpHeader\HttpHeaderFeature ($shpf);

// No config necessary

// Add feature
$shpf->addFeature ($httpHeaderFeature);


//-----------------------------------------
// Secure URL Feature
// - Negotiates secret between browser and server
// - Secures URLs and data with hash
// - Enables SHPF checker data encryption
//-----------------------------------------

// Create feature
$secureSessionFeature = new SHPF\Features\SecureSession\SecureSessionFeature ($shpf);

// Specifies if encryption for SHPF checker data shall be enabled
$secureSessionFeature->useEncryption = true;

// Add feature
$shpf->addFeature ($secureSessionFeature);


//-----------------------------------------
// CSS Fingerprint Feature
// - Checks CSS properties if they remain 
//   the same between requests
//-----------------------------------------

// Create feature
$cssFingerprintFeature = new SHPF\Features\CSSFingerprint\CSSFingerprintFeature ($shpf);

// Specifies if checker fails when no async response is received within a timeout period
$cssFingerprintFeature->asyncTimeout = 30;

// Add feature
$shpf->addFeature ($cssFingerprintFeature);



//-----------------------------------------
// SHPF config
//-----------------------------------------

// Specifies whether async checkers are used
$shpf->enableAsync = true;

// Specifies whether an Exception is thrown when a checker failes
$shpf->raiseExceptionOnFailure = true;


//-----------------------------------------
// Output config
//-----------------------------------------

// Specifies whether JS code is enclosed between <script> tags
$shpf->getOutput()->useScriptTags = true;

// Specifies whether packaged mootools core is included
$shpf->getOutput()->includeJSLibrary = true;

/*-------------------------------------------------------------------------*/
// Run SHPF
/*-------------------------------------------------------------------------*/

// Run SHPF
// Return value indicates success or failure
// If Exceptions are enabled, use try/catch to determine success or failure
$success = $shpf->run ();


//-----------------------------------------
// Control output
//-----------------------------------------

// Output all types
$shpf->getOutput()->flushAll();

// Or ...
// Output the different types as you need them

// Code can/should go to HTML <head>
$shpf->getOutput()->flushHead ();

// JS Code
$shpf->getOutput()->flushJS ();

// HTML
$shpf->getOutput()->flushHTML ();

// For all flushXXX() functions, a parameter $return can be given
// if you don't want to output, but rather have it as a return value

$head = $shpf->getOutput()->flushHead (true);


?>
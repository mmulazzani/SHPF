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

// Add feature
$shpf->addFeature ($httpHeaderFeature);



/* OPTIONAL: */

// Config for certain checks
$httpHeaderFeature->checkHttpAccept = true;
$httpHeaderFeature->checkHttpAcceptEncoding = true;
$httpHeaderFeature->checkHttpAcceptLanguage = true;
$httpHeaderFeature->checkIpAddress = true;
$httpHeaderFeature->checkUserAgent = true;
$httpHeaderFeature->checkHttpHeaderOrder = true;

// Enable/disable all checks
$httpHeaderFeature->setCheckAll (true);


//-----------------------------------------
// Secure Session Feature
// - Negotiates secret between browser and server
// - Secures URLs and data with hash
// - Enables SHPF checker data encryption
//-----------------------------------------

// Create feature
$secureSessionFeature = new SHPF\Features\SecureSession\SecureSessionFeature ($shpf);

// Add feature
$shpf->addFeature ($secureSessionFeature);



/* OPTIONAL: */

// Specifies if encryption for SHPF checker data shall be enabled
$secureSessionFeature->useEncryption = true;

// Timeout in seconds of asynchronous, encrypted messages
$secureSessionFeature->asyncEncryptedTimeout = 30;

// Timeout in seconds of received HMAC'd requests
$secureSessionFeature->syncEncryptedTimeout = 300;

// Number of invalid consecutive requests, before session is killed
$secureSessionFeature->allowedFailCount = 1;

// Reset the fail count timer, once a valid request is received
$secureSessionFeature->resetFailCountOnSuccess = true;




//-----------------------------------------
// CSS Fingerprint Feature
// - Checks CSS properties if they remain 
//   the same between requests
//-----------------------------------------

// Create feature
$cssFingerprintFeature = new SHPF\Features\CSSFingerprint\CSSFingerprintFeature ($shpf);

// Add feature
$shpf->addFeature ($cssFingerprintFeature);



/* OPTIONAL: */

// Timeout in seconds for asynchronous callbacks
$cssFingerprintFeature->asyncTimeout = 30;

// Specifies whether to use the async callback timeout
$cssFingerprintFeature->enableAsyncTimeout = true;

// Specifies whether it is accepted that no javascript is enabled
$cssFingerprintFeature->allowNoJavascript = true;

// Maximum number of simultanous open requests from server to client
$cssFingerprintFeature->maxOpenRequests = 3;

// Specifies whether each open request should be checked for expiration
$cssFingerprintFeature->checkTimeoutForEachRequest = true;



//-----------------------------------------
// SHPF config (OPTIONAL)
//-----------------------------------------

// Specifies whether async checkers are used
$shpf->enableAsync = true;

// Specifies whether an Exception is thrown when a checker failes
$shpf->raiseExceptionOnFailure = true;

// Defines whether logging is enabled. If yes, log messages are appended in a separate file (default false)
$shpf->enableLogging = false;


//-----------------------------------------
// Output config (OPTIONAL)
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
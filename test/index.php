<?php

define ('SHPF_ROOT_WEB', '/fh/master/shpf/');

include ('../SHPF/SHPF.php');


//-----------------------------------------
// Failed Handler for Framework
//-----------------------------------------

function failedHandler ($info)
{
	echo '<span style="font-size:2em; color:red">'. $info .'</span>';

	session_destroy();
	
	echo '<br/>Destroyed session';
}

//-----------------------------------------
// Disable Cookies, use only URL Parameters
//-----------------------------------------

/*
ini_set("session.use_cookies",0);
ini_set("session.use_only_cookies",0);
ini_set("session.use_trans_sid",1);
*/

session_start ();

//-----------------------------------------
// Clear Session Link Handler
// Destroys session
//-----------------------------------------

if ($_GET['clear_session'])
{
	session_destroy();
	$_SESSION['features_default'] = true;
	Header ('Location: index.php'. ( defined('SID') ? ('?' . SID) : ''));
	exit;
}


//-----------------------------------------
// Default activated features
//-----------------------------------------

if (!$_SESSION['features_default'])
{
	//$_SESSION['feature_env'] = 1;
// 	$_SESSION['feature_SecureSession'] = 1;
// 	$_SESSION['feature_css'] = 1;
	$_SESSION['features_default'] = true;
}

//-----------------------------------------
// Handler for form, determines which features to activate
//-----------------------------------------

if ($_POST['set_features'])
{
	$_SESSION['feature_env'] = $_POST['feature_env'];
	$_SESSION['feature_SecureSession'] = $_POST['feature_SecureSession'];
	$_SESSION['feature_css'] = $_POST['feature_css'];
}


//-----------------------------------------
// Create Framework Instance
//-----------------------------------------


$shpf = new SHPF\SHPF ();
$shpf->setCheckFailedHandler ('\failedHandler');

$shpf->enableLogging = true;


if ($_SESSION['feature_env'])
{

	$serverEnvFeature = new SHPF\Features\HttpHeader\HttpHeaderFeature ($shpf);

	
	$serverEnvFeature->checkHttpAccept = false;
	$serverEnvFeature->checkHttpAcceptEncoding = true;
	$serverEnvFeature->checkHttpAcceptLanguage = true;
	$serverEnvFeature->checkIpAddress = false;
	$serverEnvFeature->checkUserAgent = false;
	$serverEnvFeature->setCheckAll (true);
	
	$serverEnvFeature->checkHttpHeaderOrder = true;
	
	
	
	//$serverEnvFeature->setCheckAll (true);

	$shpf->addFeature ($serverEnvFeature);
}

if ($_SESSION['feature_SecureSession'])
	$shpf->addFeature (new SHPF\Features\SecureSession\SecureSessionFeature ($shpf));

if ($_SESSION['feature_css'])
	$shpf->addFeature (new SHPF\Features\CSSFingerprint\CSSFingerprintFeature ($shpf));




//-----------------------------------------
// Run checks
//-----------------------------------------

$ret = $shpf->run ();





?>

<html>
<head>
	<title>SHPF Test</title>
	
	<style type="text/css">
		body
		{
			font-family: Arial, Verdana, "Times New Roman";
			font-size: 0.9em;
		}
	</style>
</head>
<body>

	<h1>SHPF Test</h1>
	
	<p>SessionID is transported via URL for easy simulation of session hijacking.</p>
	
	<h2>Activated Features + SecureSession form test</h2>

<form method="post" action="index.php<?php if (defined('SID')) echo '?' . SID; ?>">

	<input type="checkbox" name="feature_env" id="feature_env" value="1" <?php if ($_SESSION['feature_env']) echo 'checked="checked"'; ?> /> 
	<label for="feature_env">Http Header</label>
	<br/>
	<input type="checkbox" name="feature_SecureSession" id="feature_SecureSession" value="1" <?php if ($_SESSION['feature_SecureSession']) echo 'checked="checked"'; ?> /> 
	<label for="feature_SecureSession">SecureSession (SessionLock)</label>
	<br/>
	<input type="checkbox" name="feature_css" id="feature_css" value="1" <?php if ($_SESSION['feature_css']) echo 'checked="checked"'; ?> /> 
	<label for="feature_css">CSS Fingerprint</label>
	<br/>
	
	<br/>
	
	<input type="hidden" name="set_features" value="1" />
	<input type="submit" value="Set features" />

</form>


	<h2>SecureSession link test</h2>


<?php 

//-----------------------------------------
// Testlink for SecureSession
//-----------------------------------------

echo '<a href="'. $_SERVER['SCRIPT_NAME'] .'">Test-URL</a>&nbsp;';
echo '<a href="#" >Anchor</a>';


//-----------------------------------------
// Write all framework output
//-----------------------------------------

$shpf->getOutput()->flushAll ();




//-----------------------------------------
// Async Hook
//-----------------------------------------

if (isset ($_GET['async_hook']))
	$_SESSION['asyncHook'] = $_GET['async_hook'];

$asyncHook = $_SESSION['asyncHook'];


//-----------------------------------------
// Session size and cleanup
// Needs to remove data from this test site
// in order to get raw framework data size
//-----------------------------------------


$sessionBackup = $_SESSION;
unset ($_SESSION['features_default']);
unset ($_SESSION['feature_env']);
unset ($_SESSION['feature_SecureSession']);
unset ($_SESSION['feature_css']);
unset ($_SESSION['asyncHook']);


$serializedSession = session_encode();
$serializedSessionSize = strlen ($serializedSession);


$session = $_SESSION;
$_SESSION = $sessionBackup;



?>

	<h2>POST Data</h2>
	
	<textarea cols="100" rows="10" wrap="off" style="white-space: nowrap; overflow: scroll;"><?php echo htmlentities(print_r($_POST, true));?></textarea>

	<h2>Async communication</h2>
	
	<p>Watch async communication (some browsers may not support this): <?php echo $asyncHook ? 'on' : 'off'; ?> <a href="index.php?async_hook=<?php echo $asyncHook ? 0 : 1; ?>">[toggle]</a></p>
	
	<textarea id="async" cols="100" rows="10" wrap="off" style="white-space: nowrap; overflow: scroll;"></textarea>
	
	<h2>$_SESSION size</h2>
	
	<p><?php echo number_format ($serializedSessionSize, 0, ',', '.'); ?> bytes <a href="index.php?clear_session=1">[clear]</a></p>
	
	<textarea cols="100" rows="10" wrap="off" style="white-space: nowrap; overflow: scroll;"><?php echo htmlentities(print_r($session, true));?></textarea>

<script type="text/javascript">

	SHPF.addEvent ('failed', function (msg)
	{
		alert ("Failed!\n" + msg);
	});

	SHPF._sendAsync = SHPF.sendAsync;
	SHPF.sendAsync = function (url, postPayload, encryptIfPossible)
	{
		var text = 'SHPF: ' +  url +"\n" + postPayload + "\n\n";
		$('async').value += text;
		SHPF._sendAsync (url, postPayload, encryptIfPossible);
	};


	<?php if ($asyncHook) { ?>

	// LOCKSESSION OVERRIDE
	// move the xmlhttprequest open method
    XMLHttpRequest.prototype._open = XMLHttpRequest.prototype.open;

    XMLHttpRequest.prototype.open = function(method, url, async, username, password) {
		var text = method + ': ' + url + "\n\n";
		$('async').value += text;
		
		return this._open(method,url,async,username,password);
    };

    <?php } ?>

</script>


</body>
</html>
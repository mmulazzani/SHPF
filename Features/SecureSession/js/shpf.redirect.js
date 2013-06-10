window.addEvent('domready', function()
{
	var url = document.location.href;
	
	// URL has no timestamp / hmac?
	if (LockSession.TOKEN && (url.indexOf ('ls_timestamp') < 0 || url.indexOf ('ls_sig') < 0))
	{
		document.location.href = LockSession.patch_url (url);
	}
});
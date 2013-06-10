SHPF.addEvent ('failed', function ()
{
	if (localStorage)
	{
		localStorage.setItem ('secureURL_shared', null);
	}
});
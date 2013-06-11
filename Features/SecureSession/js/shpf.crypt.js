SHPF.getSharedSecret = function ()
{
	return LockSession.TOKEN;
};

SHPF.getCryptoKey = function ()
{
	return SHPF.getSharedSecret();
};

SHPF.encrypt = function (message)
{
	var secret = SHPF.getCryptoKey ();
	
	if (!secret)
		return message;

	// Prepend date
	var timestamp = new Date().toISO8601(5);
	
	message = timestamp + "|" + message;

	var crypted = Crypto.AES.encrypt(message, secret, { mode: new Crypto.mode.CBC(Crypto.pad.ZeroPadding) });
	
	return crypted;
};

SHPF.decrypt = function (message)
{
	var secret = SHPF.getCryptoKey ();
	if (!secret)
		return message;
	
	var decrypted = Crypto.AES.decrypt(message, secret, { mode: new Crypto.mode.CBC(Crypto.pad.ZeroPadding) });

	return decrypted;
};
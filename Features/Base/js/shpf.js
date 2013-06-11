var SHPFClass = new Class ({
	
	Implements: Events,
	
	encrypt : null,
	decrypt : null,
	

	sendAsync: function (url, postPayload, encryptIfPossible)
	{
		if (!encryptIfPossible)
			encryptIfPossible = true;
			
		var shpf = this;
		
		var request = new Request ({ 
			url: url,
			onSuccess: function(responseText)
			{
				if (responseText != 1)
				{
					shpf.fireEvent ('failed', responseText ? responseText : '');
				}
			}
		});
		
		var message = postPayload;
		
		if (typeof (message) == 'array' || typeof (message) == 'object')
			message = JSON.encode (message);
		
		if (encryptIfPossible && SHPF.encrypt)
		{
			message = { encrypted: SHPF.encrypt (message) };
		}
		else
			message = { data: JSON.encode (message) };
		
		request.post (message);
	}

});

SHPF = new SHPFClass();
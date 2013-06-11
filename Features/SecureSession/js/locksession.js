//
// Javascript Library for LockSession
//
// Ben Adida (ben@eecs.harvard.edu)
//
// 20007-10-11
//

// requires hmac
// requires mootools

SSL_HOST = "https://localhost"

LockSession = {};

LockSession.SESSIONID = null;
LockSession.hasLocalStorage = false;
LockSession.TOKEN = null;

function hash_to_sorted_query_string(hash) {
	
    var params_hash = $H(hash);
    
    var keys = params_hash.getKeys().sort();

    var params_str = keys.map(function(k) {return k + '=' + encodeURIComponent(hash[k]);}).join('&');

    return params_str;
}

LockSession.receiveToken = function(token) {
    LockSession.TOKEN = token;

    LockSession.saveToken (token);
    
    LockSession.cleanup();
    LockSession.setupPage();
   
};

window.deliverToken = LockSession.receiveToken;
	
LockSession.getToken = function() {
    var iframe = document.createElement('iframe');
    iframe.src = SSL_HOST + '/locksession/token_get_ssl';
    iframe.id = 'locksession_frame';
    iframe.style.height=0;
    iframe.style.width=0;
    iframe.style.visibility = 'hidden';
    document.body.appendChild(iframe);
};

LockSession.cleanup = function() {
    var iframe = $('locksession_frame');
    if (iframe)
    	document.body.removeChild(iframe);
};

LockSession.saveToken = function (token)
{
	if (LockSession.hasLocalStorage)
	{
		var key = 'secureURL_shared';
		if (LockSession.SESSIONID != null && LockSession.SESSIONID.length > 0)
			key += LockSession.SESSIONID;
		
		localStorage.setItem (key, token);
	}
	else
	{
		document.location.href = document.location.href + "#[" + token + "]";
	}
};

LockSession.load_token = function () {
	
	if (LockSession.hasLocalStorage)
	{
		var key = 'secureURL_shared';
		if (LockSession.SESSIONID != null && LockSession.SESSIONID.length > 0)
			key += LockSession.SESSIONID;
		
		if (localStorage.getItem (key) != null)
		{
			LockSession.TOKEN = localStorage.getItem (key);
			
			return true;
		}
	}

	
    // get the fragment
    var fragment = document.location.hash.substring(1);

    // get the token out of there, there may be some other fragment
    var match = fragment.match(/\[(.*)\]/)

    if (!match) {
    	return false;
    }

    var token = match[1];

    var new_href = document.location.href.replace('[' + token + ']', '');
    LockSession.TOKEN = token;

    // for now don't change the URL
    //document.location.replace(new_href);
    
    return true;
};

LockSession.sign = function(str) {
    if (!LockSession.TOKEN) {
throw 'Oy, no token and trying to sign!';
    }

    return hex_hmac_sha1(LockSession.TOKEN, str);
};

// assumes an absolute url
LockSession.patch_url = function(url) {
    var parsed_current = parseUri(document.location);
    var parsed_next = parseUri(url);
    
    // If only fragment is different, do nothing
    //if ((parsed_current.path + parsed_current.query) == (parsed_next.path + parsed_next.query))
    //	return null;
    //if (url.location == document.url)

    // don't affect other sites
    //if (parsed_current.host != parsed_next.host) {
    //	return url;
    //}

    var new_url = parsed_next.path + '?';

    // timestamp
    parsed_next.queryKey['ls_timestamp'] = new Date().toISO8601(5);

    // parameter string
    var params_str = hash_to_sorted_query_string(parsed_next.queryKey);
    new_url += params_str;

    // the string to sign inludes a '?' no matter what. And the timestamp of course.
    var string_to_sign = new_url;

    new_url += '&ls_sig=' + encodeURIComponent(LockSession.sign(string_to_sign));

    // prepend the protocol and host and such
    new_url = parsed_next.protocol + '://' + parsed_next.authority + new_url;

    // append the existing hash and the token
    if (!LockSession.hasLocalStorage)
    	new_url += '#' + parsed_next.anchor + '[' + LockSession.TOKEN + ']';

    return new_url;
};

LockSession.patch_hrefs = function() {
    var anchors = document.getElementsByTagName('a');
	//var anchors = $$('a');

    var onclick = function(event) {
    	if (this._onclick != null)
    	{
    		var ret = this._onclick (event);
    		if (ret == false) return false;
    	}
    	
    	var url = LockSession.patch_url(this.href);
    	
    	if (url == null)
    		return false;
    	
    	document.location = url;
    	
    	return false;
    };

    // go through all of them
    for (var i=0; i<anchors.length; i++) {
    	if (anchors[i].onclick != null)
    		anchors[i]._onclick = anchors[i].onclick;
    	anchors[i].onclick = onclick;
    }
};

LockSession.form_submit = function(form) {
    // add the timestamp
    var ls_ts_input = form.getElementById(LockSession.FORM_INPUT_ID);
    if (!ls_ts_input) 
    {
		ls_ts_input = document.createElement('input');
		ls_ts_input.type= 'hidden';
		ls_ts_input.id = LockSession.FORM_INPUT_ID;
		form.appendChild(ls_ts_input);
    }

    ls_ts_input.name = 'ls_timestamp';
    ls_ts_input.value = new Date().toISO8601(5);

    var inputs = form.getElementsByTagName('input');

    var params = {};

    // go through the inputs, creating the string
    for (var i=0; i < inputs.length; i++) {
		// don't add the input if it has no name
		if (!inputs[i].name)
			continue;
		

		var inputName = inputs[i].name;
		var input = inputs[i];
		
		if (input.disabled)
			continue;

		if (input.type == 'checkbox' || input.type == 'radio')
		{
			if (input.checked)
				params[inputName] = input.value;
		}
		else
			params[inputName] = input.value;

    }

    var params_str = hash_to_sorted_query_string(params);

    var hmac_input = document.createElement('input');
    hmac_input.type = 'hidden';
    hmac_input.name = 'ls_sig';

    // get the normalized path
    hmac_input.value = LockSession.sign(parseUri(form.action).path + (params_str.length > 0 ? '?'+ params_str : ''));

    form.appendChild(hmac_input);
	

    if (!LockSession.hasLocalStorage)
    	form.action += '#' + '[' + LockSession.TOKEN + ']';
};

LockSession.patch_forms = function() {
    var forms = document.getElementsByTagName('form');
    
    for (var i=0; i <forms.length; i++) {
    	forms[i].onsubmit = function() {LockSession.form_submit(this);};
    }
};

LockSession.patch_ajax = function() {
	
	
	
    // move the xmlhttprequest open method
    XMLHttpRequest.prototype.__open = XMLHttpRequest.prototype.open;

    XMLHttpRequest.prototype.open = function(method, url, async, username, password) {
    	
		parsed_url = parseUri(url);
		
		/*if (parsed_url.path == '')
		{
			var parsed_current = parseUri(document.location);
			
			var url2 = parsed_current.directory + url;

			parsed_url = parseUri(url2);
			
		}*/
		
		
		var new_url = parsed_url.path + '?';
		
		// timestamp
		parsed_url.queryKey['ls_timestamp'] = new Date().toISO8601(5);
		
		// parameter string
		var params_str = hash_to_sorted_query_string(parsed_url.queryKey);
		new_url += params_str;
		
		// the string to sign inludes a '?' no matter what. And the timestamp of course.
		var string_to_sign = new_url;
		
		new_url += '&ls_sig=' + encodeURIComponent(LockSession.sign(string_to_sign));
		
		return this.__open(method,new_url,async,username,password);
    };
};

LockSession.setupPage = function() {
	
    LockSession.patch_hrefs();
    LockSession.patch_forms();
    LockSession.patch_ajax();
};

LockSession.init = function()
{

	LockSession.FORM_INPUT_ID = 'ls_timestamp';
	
	LockSession.hasLocalStorage = !!window.localStorage;


};




window.addEvent('domready', function()
{
	if (LockSession.TOKEN != null)
		return;
	
	LockSession.init();
	
	
    // try getting the token from the URL
    LockSession.load_token();

    // try going and getting the token
    if (LockSession.TOKEN) {
    		LockSession.setupPage();
    } else {
    	//LockSession.getToken();
    }
});
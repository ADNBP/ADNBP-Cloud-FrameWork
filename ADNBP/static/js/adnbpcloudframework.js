/*
* ADNBP Cloud FrameWork Auth JS 
* by hector lópez
*/

function autoSizeTextArea(ele,w,h) { ele.style.height = 'auto'; ele.style.height = h ; ele.style.width = w ;}
function sleep(milliseconds) { var start = new Date().getTime(); for (var i = 0; i < 1e7; i++) { if ((new Date().getTime() - start) > milliseconds){  break;  } } }
// KEEEP SESSION
var _adnbpKeepSession=0;  // Put a value > 0 and call keepSession to maintain the session. 120 (recommended)
function keepSession() { if(_adnbpKeepSession>0) {setInterval (calTokeepSession, _adnbpKeepSession);}}
function calTokeepSession() {http_request = new XMLHttpRequest();http_request.open('GET', "/CloudFrameWorkService/keepSession"); http_request.send(null);}

/**
 * parses and returns URI query parameters 
 * 
 * @param {string} param parm
 * @param {bool?} asArray if true, returns an array instead of a scalar 
 * @returns {Object|Array} 
 */
function getURIParameter(param, asArray) {
    return document.location.search.substring(1).split('&').reduce(function(p,c) {
        var parts = c.split('=', 2).map(function(param) { return decodeURIComponent(param); });
        if(parts.length == 0 || parts[0] != param) return (p instanceof Array) && !asArray ? null : p;
        return asArray ? p.concat(parts.concat(true)[1]) : parts.concat(true)[1];
    }, []);
}

/**
 * Check types of values 
 * 
 * @param {string} type values are: email
 * @param {multi-type} value to evaluate the value
 * @returns {boolean} 
 */
function formInputValidate(type,value) {
	var ret = false;
	switch(type) {
		case 'email':
		var exp= /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		if(value.match(exp))
			ret= true;
		break;
	}
	return ret;
}
// getURIParameter("id")  // returns the last id or null if not present
// getURIParameter("id", true) // returns an array of all ids

function sendToConsole(title,data) {
	console.log(title);
	console.log(data);
}

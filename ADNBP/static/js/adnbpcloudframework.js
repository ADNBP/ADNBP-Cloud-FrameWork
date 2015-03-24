
// HTML 5 W3

// FORM SUPPORT
function autoSizeTextArea(ele,w,h) { ele.style.height = 'auto'; ele.style.height = h ; ele.style.width = w ;}

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
// getURIParameter("id")  // returns the last id or null if not present
// getURIParameter("id", true) // returns an array of all ids

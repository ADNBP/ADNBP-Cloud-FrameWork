
// HTML 5 W3
var _adnbpCurrentGeoLocationByBrowser = 'did not get yet. Use: setGeoLocationByBrowser()'; //
var _adnbpCurrentGeoLocationByBrowserStatus = 'no init'; //
var _adnbpCurrentGeoLocationByBrowserLat = 0; //
var _adnbpCurrentGeoLocationByBrowserLong = 0; //
var _adnbpKeepSession=0;  // each 5 seconds


function setGeoLocationByBrowser() {
   if(_adnbpCurrentGeoLocationByBrowserStatus == 'no init') {
      _adnbpCurrentGeoLocationByBrowser="Requesting GeoLocation from the browser";
      _adnbpCurrentGeoLocationByBrowserStatus = 'init setGeoLocationByBrowser';
      
      if (navigator.geolocation) {
         _adnbpCurrentGeoLocationByBrowserStatus = 'calling navigator.geolocation';
        navigator.geolocation.getCurrentPosition(recordGeoLocationByBrowser);
      }
      else{
           _adnbpCurrentGeoLocationByBrowser="Geolocation is not supported by this browser.";
           _adnbpCurrentGeoLocationByBrowserStatus='not supported';
      }
   }
}

function autoSizeTextArea(ele,w,h)
{
   ele.style.height = 'auto';
   //var newHeight = (ele.scrollHeight > 32 ? ele.scrollHeight : 32);
   ele.style.height = h ;
   ele.style.width = w ;
}

function keepSession() {
    if(_adnbpKeepSession>0) {
        setInterval (calTokeepSession, _adnbpKeepSession);
    }
}

function calTokeepSession() {
    http_request = new XMLHttpRequest();
    http_request.open('GET', "/CloudFrameWorkService/keepSession");
    http_request.send(null);
    
}
  
function recordGeoLocationByBrowser(position) {
  _adnbpCurrentGeoLocationByBrowser="Latitude: " + position.coords.latitude + 
  "<br>Longitude: " + position.coords.longitude;
  _adnbpCurrentGeoLocationByBrowserLat = position.coords.latitude;
  _adnbpCurrentGeoLocationByBrowserLong = position.coords.longitude;
   _adnbpCurrentGeoLocationByBrowserStatus = 'ok';  

}

function getGeoLocationByBrowser() {
    return(_adnbpCurrentGeoLocationByBrowser);
}

function showGeoLocationByBrowserInGoogleMaps(div) {
    if(_adnbpCurrentGeoLocationByBrowserStatus=='no init') {
        alert('Use first setGeoLocationByBrowser();');
    } else {
        var mapOptions = {
          center: new google.maps.LatLng(_adnbpCurrentGeoLocationByBrowserLat, _adnbpCurrentGeoLocationByBrowserLong),
          zoom: 8
        };
        var map = new google.maps.Map(document.getElementById(div),
            mapOptions);
    }
    
}

// Services
app.service('AuthService', function($q, $http, USER_ROLES,API_URLS) {
  var LOCAL_TOKEN_KEY = 'yourTokenKey';

  var userData = {};
  var username = '';
  var lastUserName = '';
  var isAuthenticated = false;
  var role = '';
  var authToken;
  var fingerprint ='angularClient';
 
   var login = function(name, pw) {
  	lastUserName = name;  // Keep the last email used
    window.localStorage.setItem("lastUserName", lastUserName);
    console.log("stored lastUserName: "+lastUserName);
  	
    return $q(function(resolve, reject) {
    	if(name.length  < 1) reject('Login Failed.');
    	else {
    		var req = {
				 method: 'POST',
				 url: API_URLS.login,
				 data:  {user:name,password:pw, clientfingerprint:fingerprint}
				};
	    	$http(req).
			  success(function(data, status, headers, config) {
		        storeUserCredentials(name + '.yourServerToken');
		        resolve('Login success.');
			  	
			    // this callback will be called asynchronously
			    // when the response is available
			  }).
			  error(function(data, status, headers, config) {
			  	console.log(data);
			  	reject('Login Failed.');
			  });
		  }

    });
  };
 
 
 
  function loadUserCredentials() {
    var token = window.localStorage.getItem(LOCAL_TOKEN_KEY);
    lastUserName = window.localStorage.getItem("lastUserName");
    console.log("loaded lastUserName: "+lastUserName);
    if (token) {
      useCredentials(token);
    }
  }
 
  function storeUserCredentials(token) {
    window.localStorage.setItem(LOCAL_TOKEN_KEY, token);
    
    useCredentials(token);
  }
 
  function useCredentials(token) {
    username = token.split('.')[0];
    isAuthenticated = true;
    authToken = token;
 
    if (username == 'admin') {
      role = USER_ROLES.admin
    }
    if (username == 'user') {
      role = USER_ROLES.public
    }
 
    // Set the token as header for your requests!
    $http.defaults.headers.common['X-Auth-Token'] = token;
  }
 
  function destroyUserCredentials() {
    authToken = undefined;
    username = '';
    isAuthenticated = false;
    $http.defaults.headers.common['X-Auth-Token'] = undefined;
    window.localStorage.removeItem(LOCAL_TOKEN_KEY);
  }
 

  var logout = function() {
    destroyUserCredentials();
  };
 
  var isAuthorized = function(authorizedRoles) {
    if (!angular.isArray(authorizedRoles)) {
      authorizedRoles = [authorizedRoles];
    }
    return (isAuthenticated && authorizedRoles.indexOf(role) !== -1);
  };
 
  loadUserCredentials();
 
  return {
    login: login,
    logout: logout,
    isAuthorized: isAuthorized,
    isAuthenticated: function() {return isAuthenticated;},
    lastUserName: lastUserName,
    username: function() {return username;},
    role: function() {return role;}
  };
});
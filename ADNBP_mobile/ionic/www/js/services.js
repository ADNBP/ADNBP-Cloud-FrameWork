// Common models for all controllers
app.factory('ADNBP', function() {

	//window.localStorage.removeItem('ADBP_UserData');
	var userData = window.localStorage.getItem('ADBP_UserData');
	if(userData !== null) userData = JSON.parse(userData);
	else userData = {public:{data:{}},auth:{isAuth:false,data:{}}};
	
	
	var updateData = function () { 
		window.localStorage.setItem('ADBP_UserData',JSON.stringify(userData)); 
		console.log('saving: '+JSON.stringify(userData));
	};
	
	return {
		userData: userData.public.data,
		setKey: function (key,data) { userData.public.data[key] = data; updateData(); }, 
		getKey: function (key) { return (typeof userData.public.data[key] == 'undefined' )?null:userData.public.data[key];},
		
		userAuthData: userData.auth.data,
		isAuth: userData.auth.isAuth,
		signIn: function (user,token) { userData.auth.isAuth = true; userData.auth.data['user']=user; userData.auth.data['token']=token; updateData();},
		signOut: function() {userData.auth.data = {}; userData.auth.isAuth = false ; updateData();},
		setAuthKey: function (key,data) { userData.auth.data[key] = data; userData.auth.isAuth = true ; updateData(); }, 
		getAuthKey: function (key) { return (typeof userData.auth.data[key] == 'undefined' )?null:userData.auth.data[key];}
	};
});

// Auth Services
app.service('AuthService', function($q, $http, $rootScope,USER_ROLES,API_URLS) {
  var LOCAL_TOKEN_KEY = 'yourTokenKey';
  var LOCAL_USERDATA_KEY = 'UserData';

  var userData = {};
  var username = '';
  var lastUserName = '';
  var isAuthenticated = false;
  var role = '';
  var authToken;
  var fingerprint ='angularClient';
  var loginSemaphore = false;
 
   var login = function(name, pw) {
   	
   	if(!loginSemaphore) {
	  	lastUserName = name;  // Keep the last email used
	    window.localStorage.setItem("lastUserName", lastUserName);
    }
  	
    return $q(function(resolve, reject) {
    	if(loginSemaphore) {
    		reject('The proccess is still running.');
    	} else {
    		loginSemaphore = true;
    		destroyUserCredentials();
    		var req = {
				 method: 'POST',
				 url: API_URLS.loginUrl,
				 data:  {user:name,password:pw, clientfingerprint:fingerprint}
				};
			$rootScope.$broadcast('loading:show');	
	    	$http(req).
			  success(function(data, status, headers, config) {
			  	loginSemaphore = false;  // Allow new petitions
			  	window.localStorage.setItem(LOCAL_TOKEN_KEY, name + '.yourServerToken');
    			window.localStorage.setItem(LOCAL_USERDATA_KEY, JSON.stringify(data));
    			loadUserCredentials();
    			$rootScope.$broadcast('loading:hide');
		        resolve('Login success.');
			  	
			    // this callback will be called asynchronously
			    // when the response is available
			  }).
			  error(function(data, status, headers, config) {
			  	loginSemaphore = false;
			  	console.log('Error login.');
			  	console.log(config);
			  	$rootScope.$broadcast('loading:hide');
			  	reject('Login Failed.');
			  });
		  }

    });
  };
 
 
  function loadUserCredentials() {
  	
    authToken = window.localStorage.getItem(LOCAL_TOKEN_KEY);
    lastUserName = window.localStorage.getItem("lastUserName");
    console.log("loaded lastUserName: "+lastUserName);
    if (authToken) {
    	username = authToken.split('.')[0];
    	isAuthenticated = true;
	    userData = JSON.parse(window.localStorage.getItem(LOCAL_USERDATA_KEY));
	    $http.defaults.headers.common['X-Auth-Token'] = authToken;
	     role = USER_ROLES.admin;
	    console.log('User data loaded with token: '+authToken);
    }

  }
 
  function destroyUserCredentials() {
    authToken = undefined;
    username = '';
    isAuthenticated = false;
    userData = {};
    $http.defaults.headers.common['X-Auth-Token'] = undefined;
    window.localStorage.removeItem(LOCAL_TOKEN_KEY);
    window.localStorage.removeItem(LOCAL_USERDATA_KEY);
    console.log('User data destroyed');
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
    userData: userData,
    username: function() {return username;},
    role: function() {return role;}
  };
});

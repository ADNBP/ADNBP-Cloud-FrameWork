// Common models for all controllers
app.factory('ADNBP', function($rootScope) {
	// window.localStorage.removeItem('ADBP_UserData');
	var userData = window.localStorage.getItem('ADBP_UserData');
	if(userData !== null) userData = JSON.parse(userData);
	else userData = {public:{data:{}},auth:{isAuth:false,data:{}}};
	
	
	var updateData = function () { 
		window.localStorage.setItem('ADBP_UserData',JSON.stringify(userData)); 
		console.log('saving: '+JSON.stringify(userData));
		//$rootScope.$apply();
	};
	return {
		userData: userData,
		setKey: function (key,data) { userData.public.data[key] = data; updateData(); }, 
		getKey: function (key) { return (typeof userData.public.data[key] == 'undefined' )?null:userData.public.data[key];},
		isAuth: userData.auth.isAuth,
		setAuth: function (b) { userData.auth.isAuth = b;updateData();},
		signIn: function (user,data) { 
			userData.auth.isAuth = true; 
			userData.auth.data['user']=data; 
			updateData();
		},
		signOut: function() {if(userData.auth.isAuth) { userData.auth.data = {}; userData.auth.isAuth = false ;updateData();}},
		setAuthKey: function (key,data) { userData.auth.data[key] = data; userData.auth.isAuth = true ; updateData(); }, 
		getAuthKey: function (key) { return (typeof userData.auth.data[key] == 'undefined' )?null:userData.auth.data[key];},
		setMenu: function(menu) { userData.auth.data['menu']=menu;updateData();}
	};
});

// Auth Services
app.service('AuthService', function($q, $http,$rootScope,API_URLS) {

   var semaphore = false;
   var fingerprint = 'angularService';
   
   var authUser = function(name, pw) {
   	    $http.defaults.headers.common = {};
    	return $q(function(resolve, reject) {
	    	if(semaphore) {
	    		reject('The proccess is still running.');
	    	} else {
	    		semaphore = true;
	    		var req = {
					 method: 'POST',
					 withCredentials: true,
					 
					 url: API_URLS.credentials+'/signin',
					 data:  {user:name,password:pw}
					};
				$rootScope.$broadcast('loading:show');	
		    	$http(req).
				  success(function(ret, status, headers, config) {
				  	semaphore = false;  // Allow new petitions
	    			$rootScope.$broadcast('loading:hide');
	    			if(ret.success) {
			        	resolve(ret.data.user);
			        }else 
			        	reject(ret);
				  }).
				  error(function(ret, status, headers, config) {
				  	semaphore = false;
				  	$rootScope.$broadcast('loading:hide');
				  	reject(ret);
				  });
			  }

   		});
  };
  
  var logOut = function() {
  	  $http.get(API_URLS.credentials+'?logout');
  	  $http.defaults.headers.common = {};
  };
  
  var readMenu = function() {
    	return $q(function(resolve, reject) {
	    	if(semaphore) {
	    		reject('The proccess is still running.');
	    	} else {
	    		semaphore = true;
	    		var req = {
					 method: 'GET',
					 url: API_URLS.mobile+'/menu/ionic'
					};
				$rootScope.$broadcast('loading:show');	
		    	$http(req).
				  success(function(ret, status, headers, config) {
				  	semaphore = false;  // Allow new petitions
	    			$rootScope.$broadcast('loading:hide');
	    			if(ret.success)
			        	resolve(ret.data);
			        else 
			        	reject(ret);
				  }).
				  error(function(ret, status, headers, config) {
				  	semaphore = false;
				  	$rootScope.$broadcast('loading:hide');
				  	reject(ret);
				  });
			  }

   		});
  };
 
 
  return {
    authUser: authUser,
    logOut: logOut,
    readMenu: readMenu,
    semaphore: semaphore
  };
});

// Auth Services
app.service('OldAuthService', function($q, $http, $rootScope,USER_ROLES,API_URLS) {
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

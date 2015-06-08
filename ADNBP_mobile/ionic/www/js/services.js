// Common models for all controllers
app.factory('ADNBP', function($rootScope,$q,$http) {
	// window.localStorage.removeItem('ADBP_UserData');
	var userData = window.localStorage.getItem('ADBP_UserData');
	if(userData !== null) userData = JSON.parse(userData);
	else userData = {public:{data:{}},auth:{isAuth:false,data:{}}};
	
	
	var updateData = function () { 
		window.localStorage.setItem('ADBP_UserData',JSON.stringify(userData)); 
		console.log('saving: '+JSON.stringify(userData));
		//$rootScope.$apply();
	};
	
	var apiGet = function (apiUrl,params) {
		return $q(function(resolve, reject) {
			var req = {
				 method: 'GET',
				 withCredentials: true,
				 url: apiUrl,
				 data:  params
				};
			$rootScope.$broadcast('loading:show');	
	    	$http(req).
			  success(function(ret, status, headers, config) {
			  	semaphore = false;  // Allow new petitions
				$rootScope.$broadcast('loading:hide');
				console.log(ret);
				if(ret.success) {
		        	resolve(ret.data);
		        }else 
		        	reject(ret);
			  }).
			  error(function(ret, status, headers, config) {
			  	semaphore = false;
			  	$rootScope.$broadcast('loading:hide');
			  	reject(ret);
			  });
		 });
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
		setMenu: function(menu) { userData.auth.data['menu']=menu;updateData();},
		apiGet: apiGet
	};
});
// Auth Services
app.service('AuthService', function($q, $http,$rootScope,API_URLS) {
  var semaphore = false;
  var authUser = function(credentials) {
   	    $http.defaults.headers.common = {};
    	return $q(function(resolve, reject) {
	    	if(semaphore) {
	    		reject('The proccess is still running.');
	    	} else if(credentials.user =="" && credentials.provider=="") {
	    		reject('Missing user or provider in credentials');
	    	} else {
	    		semaphore = true;
	    		var req = {
					 method: 'POST',
					 withCredentials: true,
					 url: API_URLS.base+API_URLS.credentials+'/signin',
					 data:  credentials
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
  	  $http.get(API_URLS.base+API_URLS.credentials+'?logout');
  	  $http.defaults.headers.common = {};
  };
  // Read menu service
  var readMenu = function() {
    	return $q(function(resolve, reject) {
	    	if(semaphore) {
	    		reject('The proccess is still running.');
	    	} else {
	    		semaphore = true;
	    		var req = {
					 method: 'GET',
					 url: API_URLS.base+API_URLS.mobile+'/menu/ionic'
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
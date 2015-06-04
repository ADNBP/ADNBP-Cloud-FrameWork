// LOGIN
app.controller('LoginCtrl',function($scope,$state, $http,$ionicPopup, AuthService,ADNBP) {
	
	$scope.title = 'Template login.html';
	$scope.userData = ADNBP.userData;
	$scope.user = {username:ADNBP.getKey('lastUserName'),password:""};
	var semaphore = false;
	
	$scope.fbLogin = function () {
		$state.go('app.browse');
	};
	
	$scope.googleLogin = function () {
		$state.go('app.browse');
	};
	
	$scope.check = function() {
		
		//$http.defaults.headers.common.X_CF_SESSION_ID = ADNBP.getKey('session_id');
		
		$http.get('http://localhost:9080/api/cf_credentials').
		  success(function(data, status, headers, config) {
		  	console.log(data);
		  }).
		  error(function(data, status, headers, config) {
		    // called asynchronously if an error occurs
		    // or server returns response with an error status.
		  });
	};
	
	$scope.signIn = function (data) {
		if(semaphore) return false;
		ADNBP.signOut();
		if(data.username !='') {
			console.log('Trying to auth '+data.username);
			ADNBP.setKey('lastUserName',data.username);
			
			semaphore = true;
			AuthService.authUser(data.username,data.password)
			// OK
			.then(function(ret) {
			  ADNBP.signIn(data.username,ret);
			  $http.defaults.headers.common['X-CloudFramWork-AuthToken'] = ret.token;
			  semaphore = false;
			  $scope.readMenu();
			}, 
		    // ERR
		    function(err) {
			      semaphore = false;
			      var alertPopup = $ionicPopup.alert({
			        title: 'Login failed!',
			        template: 'Please check your credentials!'
			      });
		    });
		} 
	};

	$scope.readMenu = function () {
		if(semaphore) return false;
		if(!$scope.userData.auth.isAuth) return false;
		semaphore = true;
		AuthService.readMenu()
		// OK
		.then(function(data) {
		  semaphore = false;
	      $state.go('app.browse');
	      $scope.setMenuItems(data);
		}, 
	    // ERR
	    function(err) {
		      semaphore = false;
		      ADNBP.signOut();
		      var alertPopup = $ionicPopup.alert({
		        title: 'Reading menu Failed!',
		        template: 'Please check your credentials!'
		      });
	    });
	};
	

	
	if($scope.userData.auth.isAuth) $state.go('app.browse');

});

// http://blog.ionic.io/oauth-ionic-ngcordova/
app.controller("OauthExample", function($scope, $cordovaOauth) {
    $scope.googleLogin = function() {
        $cordovaOauth.google("CLIENT_ID_HERE", ["https://www.googleapis.com/auth/urlshortener", "https://www.googleapis.com/auth/userinfo.email"]).then(function(result) {
            console.log(JSON.stringify(result));
        }, function(error) {
            console.log(error);
        });
    };

});

// APP and Main menu
app.controller('AppCtrl', function($scope, $state,$ionicModal, $timeout,AuthService,ADNBP) {

  $scope.userData = ADNBP.userData;
  
  $scope.title = 'Menu';
  $scope.logoutMenu = {icon: 'ion-log-out',title:'Logout'};
  
  $scope.menuItems = [];
  $scope.setMenuItems = function (data) { $scope.menuItems = data;};
  
  /*
  $scope.readMenu = function  () {
	  	$scope.menuItems = [
	    { icon:"ion-search", title: 'Search', url: '#/app/search' },
	    { icon:"ion-ios-browsers", title: 'Browse', url: '#/app/browse' },
	    { icon:"ion-play", title: 'Playlist', url: '#/app/playlists' },
	    { icon:"ion-gear-a", title: 'Config', click: 'updateData();', url: '#/app/config' },
	    { icon:"ion-gear-a", title: 'MyData', url: '#/app/mydata' },
	  ];
  };
  */
  $scope.logout = function() {
  	ADNBP.signOut();
  	AuthService.logOut();
  	$scope.setMenuItems([]);
  	$state.go('home.login');
  	$scope.$apply;
  };
  
  // Form data for the login modal
  $scope.loginData = {};
  
  // Create the login modal that we will use later
  $ionicModal.fromTemplateUrl('templates/login.html', {
    scope: $scope
  }).then(function(modal) {
    $scope.modal = modal;
  });

  // Triggered in the login modal to close it
  $scope.closeLogin = function() {
    $scope.modal.hide();
  };

  // Open the login modal
  $scope.login = function() {
    $scope.modal.show();
  };

  // Perform the login action when the user submits the login form
  $scope.doLogin = function() {
    console.log('Doing login', $scope.loginData);

    // Simulate a login delay. Remove this and replace with your login
    // code if using a login system
    $timeout(function() {
      $scope.closeLogin();
    }, 1000);
  };

  $scope.setCurrentUsername = function(name) {
    $scope.username = name;
  };

});

// My OwnData
app.controller('MydataCtrl', function($scope, $state,$ionicModal, $timeout,ADNBP) {
	$scope.ADNBP = ADNBP;
	$scope.title = "Settings";
});

app.controller('PlaylistsCtrl', function($scope) {
  $scope.playlists = [
    { title: 'A1', id: 1 },
    { title: 'A2', id: 2 },
    { title: 'A3', id: 3 },
    { title: 'A4', id: 4 },
    { title: 'A5', id: 5 },
    { title: 'A6', id: 6 }
  ];
});

app.controller('PlaylistCtrl', function($scope, $stateParams) {
});

app.controller('Config', function($scope,$state,AuthService) {
	
  if(!$scope.userData.auth.isAuth) $state.go('home.login');


  $scope.settingsList = [
    { text: "Wireless", checked: true },
    { text: "GPS", checked: false },
    { text: "Bluetooth", checked: false }
  ];

  $scope.pushNotificationChange = function() {
    console.log('Push Notification Change', $scope.pushNotification.checked);
  };
  
  $scope.pushNotification = { checked: true };
  $scope.emailNotification = 'Subscribed';
  
});
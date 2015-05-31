// LOGIN
app.controller('LoginCtrl',function($scope,$rootScope,$state, $ionicPopup, $ionicLoading,AuthService,ADNBP) {
	
	
	$scope.title = 'Template login.html';
	$scope.user = {username:AuthService.lastUserName,password:""};
	$scope.signInSemaphore = false;
	$scope.userData = {public:ADNBP.userData,isAuth:ADNBP.isAuth,private:ADNBP.userAuthData};
	
	var counter = (typeof $scope.userData.public['counter']=='undefined')?0:$scope.userData.public['counter'];

	
	$scope.fbLogin = function () {
		$state.go('app.browse');
	};
	
	$scope.googleLogin = function () {
		$state.go('app.browse');
	};
	
	$scope.signIn = function (data) {
		counter++;
		ADNBP.setKey('counter',counter);
		
		
		if($scope.signInSemaphore) return false;
		if(data.username !='') {
			$scope.signInSemaphore = true;
			
			
			ADNBP.signOut();
			AuthService.login(data.username,data.password)
			// OK
			.then(function(authenticated) {
			  $scope.signInSemaphore = false;
			  
			  ADNBP.signIn(data.username,'peding to assign');
			  
		      $state.go('app.browse', {}, {reload: true});
		      $scope.setCurrentUsername(data.username);
		    }, 
		    // ERR
		    function(err) {
		      $scope.signInSemaphore = false;
		      var alertPopup = $ionicPopup.alert({
		        title: 'Login failed!',
		        template: 'Please check your credentials!'
		      });
		    });
		} 
		// if(data.username=="admin") $state.go('app.main');
		// else $scope.loginResult.msg = "wrong user";
		
	};
	
	if(AuthService.isAuthenticated()) $state.go('app.browse');

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

  if(!AuthService.isAuthenticated()) $state.go('home.login');
  
  $scope.title = 'Menu';
  $scope.logoutMenu = {icon: 'ion-log-out',title:'Logout'};
  $scope.username = AuthService.username();
  
  $scope.menuItems = [
    { icon:"ion-search", title: 'Search', url: '#/app/search' },
    { icon:"ion-ios-browsers", title: 'Browse', url: '#/app/browse' },
    { icon:"ion-play", title: 'Playlist', url: '#/app/playlists' },
    { icon:"ion-gear-a", title: 'Config', click: 'updateData();', url: '#/app/config' },
    { icon:"ion-gear-a", title: 'MyData', url: '#/app/mydata' },
  ];
  
  $scope.logout = function() {
  	$scope.username = "not assigned 2";
  	AuthService.logout();
  	ADNBP.signOut();
  	$state.go('home.login');
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
app.controller('MydataCtrl', function($scope, $state,$ionicModal, $timeout,AuthService,ADNBP) {
	console.log('MydataCtrl');
	$scope.title = "Settings";
	if(!ADNBP.isAuth) $state.go('home.login');
	$scope.userData = {public:ADNBP.userData,isAuth:ADNBP.isAuth,private:ADNBP.userAuthData};
	
	$scope.updateData = function() {
		console.log('reloading '+JSON.stringify(AuthService.userData));
		$scope.userData = AuthService.userData;
  	};
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
	
  if(!AuthService.isAuthenticated()) $state.go('home.login');


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
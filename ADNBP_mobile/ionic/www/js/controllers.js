// APP and Main menu
app.controller('AppCtrl', function($scope, $state,$http, $window,$ionicModal,$ionicPopup, $timeout,AuthService,ADNBP) {
  var semaphore = {reloadMenu:false};
  $scope.userData = ADNBP.userData;
  $scope.title = 'Menu';
  $scope.logoutMenu = {icon: 'ion-log-out',title:'Logout'};
  
  // MENU SCOPE
  $scope.menuItems = [];
  $scope.setMenuItems = function (data) { $scope.menuItems = data;};
  $scope.reloadMenu = function () {
		if(semaphore.reloadMenu) return false;
		if(!$scope.userData.auth.isAuth) return false;
		semaphore = true;
		AuthService.readMenu()
		// OK
		.then(function(data) {
		  semaphore.reloadMenu = false;
	      
	      // assign json menu
	      $scope.setMenuItems(data.menu); 
	      
	      // Assign States
	      angular.forEach(data.states, function(value, key) {
	      	  // avoid to reload too times the states
			  if($state.get(key) == null) {
		      	  console.log('loading '+key);
				  app.stateProvider.state(key, value);
			  }
		   });
	      
	      console.log('Menu reloaded');
		}, 
	    // ERR
	    function(err) {
	    	  console.log('Error reading menu');
		      semaphore.reloadMenu = false;
		      var alertPopup = $ionicPopup.alert({
		        title: 'Reading menu Failed!',
		        template: 'Please check your credentials!'
		      });
	    });
	};

  $scope.logout = function() {
  	ADNBP.signOut();
  	AuthService.logOut();
  	$scope.setMenuItems([]);
  	$scope.$apply;
  	$state.go('home.login');
  	$window.location.reload();
  };
  
  // If reload the page check if I am auth and keep the Auth Token
  if(!$scope.userData.auth.isAuth) {
		$state.go('home.login');
  } else {
		$http.defaults.headers.common['X-CloudFrameWork-AuthToken'] = $scope.userData.auth.data.user.token;
	    $scope.reloadMenu();
  }
  /*
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
  */

});

app.controller('listCtrl',function($scope,$state,$ionicPopup,ADNBP) {
	console.log($state.current);
	$scope.data = $state.current.data;
    $scope.loadItems = function() {
    	if(typeof $state.current.data.apiurl == "undefined") return false;
    	ADNBP.apiGet($state.current.data.apiurl)
		.then(function(data) {
			$scope.data.items = data;
	        console.log('Loaded: '+ $state.current.data.apiurl);
	        console.log(data);
		}, 
	    // ERR
	    function(err) {
	    	  console.log('Error reading '+ $state.current.data.apiurl);
		      var alertPopup = $ionicPopup.alert({
		        title: 'Reading api Failed!',
		        template: 'Please check '+ $state.current.data.apiurl
		      });
	    });
    };
    
    $scope.loadItems();
});

app.controller('listItemCtrl',function($scope,$state,$stateParams) {
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

app.controller('Config', function($scope,$state,ADNBP) {
  $scope.userData = ADNBP.userData;
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
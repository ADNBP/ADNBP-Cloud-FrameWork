var adnbp = angular.module('adnbp.controllers', []);

adnbp.controller('LoginCtrl',function($scope,$state) {
	
	
	$scope.title = 'Template login.html';
	$scope.user = {name:"",password:""};
	$scope.loginResult = { msg: "" };
	
	$scope.fbLogin = function () {
		$state.go('app.main');
	};
	
	$scope.googleLogin = function () {
		$state.go('app.main');
	};
	
	$scope.signIn = function (data) {
		$state.go('app.main');
		
	};

});

adnbp.controller('AppCtrl', function($scope, $state,$ionicModal, $timeout) {

  $scope.title = 'Menu';
  $scope.menuItems = [
    { title: 'Search', url: '#/app/search' },
    { title: 'Browse', url: '#/app/browse' },
    { title: 'Playlist', url: '#/app/playlists' },
    { title: 'Config', url: '#/app/main' },
    { title: 'Logout', click: 'logout();' }
  ];
  
  $scope.logout = function() {
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



});



adnbp.controller('PlaylistsCtrl', function($scope) {
  $scope.playlists = [
    { title: 'A1', id: 1 },
    { title: 'A2', id: 2 },
    { title: 'A3', id: 3 },
    { title: 'A4', id: 4 },
    { title: 'A5', id: 5 },
    { title: 'A6', id: 6 }
  ];
});

adnbp.controller('PlaylistCtrl', function($scope, $stateParams) {
});

adnbp.controller('Config', function($scope) {

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
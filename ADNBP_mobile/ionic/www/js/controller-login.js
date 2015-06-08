// LOGIN
app.controller('LoginCtrl',function($scope,$state,$http,$ionicPopup, $cordovaOauth,AuthService,ADNBP) {
	
	$scope.title = 'Template login.html';
	$scope.userData = ADNBP.userData;
	$scope.user = {user:ADNBP.getKey('lastUserName'),password:"",provider:"",token:""};
	var semaphore = false;
	
	$scope.googleLogin = function() {
		if(semaphore) return false;
		semaphore = true;
		$cordovaOauth.google("679953635351-fqa3ei4a09qc1qah3hkpj2f1v1hu6u8g.apps.googleusercontent.com", ["email","profile"]).then(function(result) {
		   $scope.user.provider = 'Google';
		   $scope.user.token = result.access_token;
		   $scope.user.user = ""; 
		   $scope.user.password = ""; 
		   semaphore = false;
		   $scope.signIn();
		}, function(error) {
		   semaphore = false;
		   var alertPopup = $ionicPopup.alert({
			        title: 'Google auth canceled!',
			        template: 'Try again'
			});
		    console.log("Error -> " + error);
		});
	};
	
	$scope.userPasswordLogin = function() {
		if(semaphore) return false;
		if($scope.user.user !='' && $scope.user.password != '') {
		   $scope.user.provider = '';
		   $scope.user.token = '';
		   $scope.signIn();
		} else {
			var alertPopup = $ionicPopup.alert({
			        title: 'Signin missing fields',
			        template: 'Please complet user and password.'
			     });
		}
	};
	
	$scope.signIn = function () {
		if(semaphore) return false;
		if($scope.user.user !='' || $scope.user.provider != '') {
			ADNBP.signOut();
			if($scope.user.user !='') {
				console.log('Trying to auth with '+$scope.user.user);
				ADNBP.setKey('lastUserName',$scope.user.user);
			} else
				console.log('Trying to auth with '+$scope.user.provider);
			semaphore = true;
			AuthService.authUser($scope.user)
			// OK
			.then(function(ret) {
			  ADNBP.signIn(ret.email,ret);
			  $http.defaults.headers.common['X-CloudFrameWork-AuthToken'] = ret.token;
			  semaphore = false;
			  $state.go('app.home');
	          $scope.reloadMenu();
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

	if($scope.userData.auth.isAuth) {
		$state.go('app.home');
		$http.defaults.headers.common['X-CloudFrameWork-AuthToken'] = $scope.userData.auth.data.user.token;
	    $scope.reloadMenu();
	}




});
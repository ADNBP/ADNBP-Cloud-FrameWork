// LOGIN
app.controller('LoginCtrl',function($scope,$state, $http,$ionicPopup, AuthService,ADNBP) {
	
	$scope.title = 'Template login.html';
	$scope.userData = ADNBP.userData;
	$scope.user = {username:ADNBP.getKey('lastUserName'),password:""};
	var semaphore = false;
	
	$scope.googleLogin = function () {
		$state.go('app.home');
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
	    $scope.reloadMenu();
	}

});
// LOGIN
app.controller('LoginCtrl',function($scope,$state, $http,$ionicPopup, AuthService,ADNBP) {
	
	$scope.title = 'Template login.html';
	$scope.userData = ADNBP.userData;
	$scope.user = {username:ADNBP.getKey('lastUserName'),password:""};
	var semaphore = false;
	
	$scope.googleLogin = function () {
		$state.go('app.home');
	};
	
	$scope.test = function() {
		window.open('http://apache.org', '_blank', 'location=yes');
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

	// FB Login
    $scope.fbLogin = function () {
        FB.login(function (response) {
            if (response.authResponse) {
                getUserInfo();
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, {scope: 'email,user_photos,user_videos'});
 
        function getUserInfo() {
            // get basic info
            FB.api('/me', function (response) {
                console.log('Facebook Login RESPONSE: ' + angular.toJson(response));
                // get profile picture
                FB.api('/me/picture?type=normal', function (picResponse) {
                    console.log('Facebook Login RESPONSE: ' + picResponse.data.url);
                    response.imageUrl = picResponse.data.url;
                    // store data to DB - Call to API
                    // Todo
                    // After posting user data to server successfully store user data locally
                    var user = {};
                    user.name = response.name;
                    user.email = response.email;
                    if(response.gender) {
                        response.gender.toString().toLowerCase() === 'male' ? user.gender = 'M' : user.gender = 'F';
                    } else {
                        user.gender = '';
                    }
                    user.profilePic = picResponse.data.url;
                    $cookieStore.put('userInfo', user);
                    $state.go('dashboard');
 
                });
            });
        }
    };
    // END FB Login
    
	// Google Plus Login
    $scope.gplusLogin = function () {
        var myParams = {
            // Replace client id with yours
            'clientid': '679953635351-fqa3ei4a09qc1qah3hkpj2f1v1hu6u8g.apps.googleusercontent.com',
            'cookiepolicy': 'single_host_origin',
            'callback': loginCallback,
            'approvalprompt': 'force',
            'scope': 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/plus.profile.emails.read'
        };
        gapi.auth.signIn(myParams);
 
        function loginCallback(result) {
            if (result['status']['signed_in']) {
                var request = gapi.client.plus.people.get({'userId': 'me'});
                request.execute(function (resp) {
                    console.log('Google+ Login RESPONSE: ' + angular.toJson(resp));
                    var userEmail;
                    if (resp['emails']) {
                        for (var i = 0; i < resp['emails'].length; i++) {
                            if (resp['emails'][i]['type'] == 'account') {
                                userEmail = resp['emails'][i]['value'];
                            }
                        }
                    }
                    // store data to DB
                    var user = {};
                    user.name = resp.displayName;
                    user.email = userEmail;
                    if(resp.gender) {
                        resp.gender.toString().toLowerCase() === 'male' ? user.gender = 'M' : user.gender = 'F';
                    } else {
                        user.gender = '';
                    }
                    //user.profilePic = resp.image.url;
                    ADNBP.signIn(resp.displayName,resp);
                    $state.go('app.home');
	         		$scope.reloadMenu();
                    //$cookieStore.put('userInfo', user);
                    //$state.go('dashboard');
                });
            }
        }
    };
    // END Google Plus Login


	if($scope.userData.auth.isAuth) {
		$state.go('app.home');
	    $scope.reloadMenu();
	}




});
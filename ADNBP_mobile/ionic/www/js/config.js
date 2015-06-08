// System constans
	app.constant('AUTH_EVENTS', {
	  notAuthenticated: 'auth-not-authenticated',
	  notAuthorized: 'auth-not-authorized'
	});
	 
	app.constant('USER_ROLES', {
	  admin: 'admin_role',
	  public: 'public_role'
	});
	
	// http://localhost:9080
	// https://cloud.adnbp.com
	app.constant('API_URLS', {
	  base: 'https://cloud.adnbp.com',
	  credentials: '/api/cf_credentials',
	  mobile: '/api/cf_mobile'
	});

// It's also possible to override the OPTIONS request (was only tested in Chrome):
// If not any post outside the domain is converted to OPTIONS call
app.config(['$httpProvider', function ($httpProvider) {
  //Reset headers to avoid OPTIONS request (aka preflight)
  $httpProvider.defaults.headers.common = {};
  $httpProvider.defaults.headers.post = {};
  $httpProvider.defaults.headers.put = {};
  $httpProvider.defaults.headers.patch = {};
  $httpProvider.defaults.useXDomain = true;

}]);


// Navigation Menu
app.config(function($stateProvider, $urlRouterProvider,API_URLS) {

  app.stateProvider = $stateProvider; // Delegate to dynamic reload
  // HOME INTRO
  $stateProvider 
  .state('home', {
    url: "/home",
    abstract: true,
    template: '<ui-view><ion-nav-view name="loginContent"></ion-nav-view></ui-view>'
  })
   .state('home.login', {
    url: "/login",
    views: {
      'loginContent': {
	    templateUrl: 'templates/login.html',
	    controller: 'LoginCtrl'
      }
    }
  }) 

  // APP STATES
  .state('app', {
    url: "/app",
    abstract: true,
    templateUrl: "templates/menu.html"
  })  
  .state('app.home', {
    url: "/home",
    views: {
      'menuContent': {
        template: "<ion-view view-title='Home'>Home</ion-view>"
      }
    }
  });
  /*

  .state('app.config', {
    url: "/config",
    views: {
      'menuContent': {
        templateUrl: "templates/config.html",
        controller: 'Config'
      },
      data: {
      	authorizedRoles: [USER_ROLES.admin]
    	}
    }
  })

  .state('app.mydata', {
    url: "/mydata",
    views: {
      'menuContent': {
        templateUrl: "templates/mydata.html",
        controller: 'MydataCtrl',
        resolve: { myData: function(AuthService) { return AuthService.userData;}}
      },
      data: {
      	authorizedRoles: [USER_ROLES.admin]
    	}
    }
  })
    
  .state('app.search', {
    url: "/search",
    views: {
      'menuContent': {
        template: "<ion-view view-title='Search'>Searching</ion-view>"
      }
    }
  })

  .state('app.browse', {
    url: "/browse",
    views: {
      'menuContent': {
        template: "<ion-view view-title='Browsing'>Browsing</ion-view>"
      }
    }
  })
    .state('app.playlists', {
      url: "/playlists",
      views: {
        'menuContent': {
          templateUrl: "templates/playlists.html",
          controller: 'PlaylistsCtrl'
        }
      }
    })

  .state('app.single', {
    url: "/playlists/:playlistId",
    views: {
      'menuContent': {
        templateUrl: "templates/playlists.html",
        controller: 'PlaylistCtrl'
      }
    }
  });
  */
  // if none of the above states are matched, use this as the fallback
  	$urlRouterProvider.otherwise('/home/login');
});
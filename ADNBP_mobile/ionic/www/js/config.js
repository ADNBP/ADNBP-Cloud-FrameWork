// System constans
	app.constant('AUTH_EVENTS', {
	  notAuthenticated: 'auth-not-authenticated',
	  notAuthorized: 'auth-not-authorized'
	});
	 
	app.constant('USER_ROLES', {
	  admin: 'admin_role',
	  public: 'public_role'
	});

// Navigation Menu
app.config(function($stateProvider, $urlRouterProvider,USER_ROLES) {
	
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
	    templateUrl: "templates/login.html",
	    controller: 'LoginCtrl'
      }
    }
  }) 

  // APP STATES
  .state('app', {
    url: "/app",
    abstract: true,
    templateUrl: "templates/menu.html",
    controller: 'AppCtrl'
  })

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
  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/home/login');
});
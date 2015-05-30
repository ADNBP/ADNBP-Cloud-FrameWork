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

app.config(function($stateProvider, $urlRouterProvider) {
	
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

  .state('app.search', {
    url: "/search",
    views: {
      'menuContent': {
        templateUrl: "templates/search.html"
      }
    }
  })

  .state('app.browse', {
    url: "/browse",
    views: {
      'menuContent': {
        templateUrl: "templates/browse.html"
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
        templateUrl: "templates/playlist.html",
        controller: 'PlaylistCtrl'
      }
    }
  })
  
  .state('app.main', {
    url: "/main",
    views: {
      'menuContent': {
        templateUrl: "templates/config.html",
        controller: 'Config'
      }
    }
  });
  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/home/login');
});
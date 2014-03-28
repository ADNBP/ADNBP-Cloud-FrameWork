<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <title>ADNBP Cloud FrameWork <?=date("Y")?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cloud Framwork to develop Cloud Solutions.">
    <meta name="author" content="ADNBP Business & IT Solutions.">

    <link href="/ADNBP/static/css/bootstrap.css" rel="stylesheet" media="screen">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>

    <link href="/ADNBP/static/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="/ADNBP/static/js/html5shiv.js"></script>
    <![endif]-->      
         
    <script src="/ADNBP/static/js/adnbpcloudframework.js"></script>

        <?php if(strlen($this->getConf("GooglePublicAPICredential")) && $this->getConf("GoogleMapsAPI")) {?>
            <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
            <style type="text/css">
              #map-canvas { height: 100% }
            </style>
            <script type="text/javascript"src="https://maps.googleapis.com/maps/api/js?key=<?=$this->getConf("GooglePublicAPICredential")?>&sensor=true"></script>
         <?php } ?>
         
    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/ADNBP/static/img/ico/apple-touch-icon-ipad3.png" />    
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/ADNBP/static/img/ico/apple-touch-icon-iphone4.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/ADNBP/static/img/ico/apple-touch-icon-ipad.png" /> 
    <link rel="apple-touch-icon-precomposed" href="/ADNBP/static/img/ico/apple-touch-icon-iphone.png" /> 
    <link rel="shortcut icon" href="/ADNBP/static/img/ico/favicon.ico">  
                                               
    </head>
    <body>
        
      <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#">Cloud Framework <?=date("Y")?></a>
          <div class="nav-collapse collapse">
           <?php if($this->isAuth()) { ?>
            <p class="navbar-text pull-right">
              Logged in as <?=$this->getAuthUserData("name")?> <a href="?logout=1" class="navbar-link">[log-out]</a>
            </p>
            <?php } ?>
              <ul class="nav">
              <li <?=($this->getConf("pageCode")=="home")?"class='active'":""?>><a href="/CloudFrameWork">Home</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Services <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li <?=($this->getConf("pageCode")=="GeoLocation")?"class='active'":""?>><a href="/CloudFrameWork/GeoLocation">GeoLocation</a></li>
                  <li <?=($this->getConf("pageCode")=="CloudSQL")?"class='active'":""?>><a href="/CloudFrameWork/CloudSQL">CloudSQL/Mysql Access</a></li>
                  <li <?=($this->getConf("pageCode")=="Email")?"class='active'":""?>><a href="/CloudFrameWork/Email">Email Function</a></li>
                  <li <?=($this->getConf("pageCode")=="SMS")?"class='active'":""?>><a href="/CloudFrameWork/SMS">SMS Function</a></li>
                  <li <?=($this->getConf("pageCode")=="File")?"class='active'":""?>><a href="/CloudFrameWork/File">I/O functions</a></li>
                  <li <?=($this->getConf("pageCode")=="oauth")?"class='active'":""?>><a href="/CloudFrameWorkOauth">Testing Oauth</a></li>
                  </ul>
            </ul>
            <?php if(!$this->isAuth()) { ?>
           <form class="navbar-form pull-right" method=post>
              <input class="span2" type="text" placeholder="Email" name='CloudUser'>
              <input class="span2" type="password" placeholder="Password" name='CloudPassword'>
              <button type="submit" class="btn">Sign in</button>
            </form>
            <?php } ?>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container">
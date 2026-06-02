<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['security_code'] = rand(10000, 99999);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Xisbiga Midnimada iyo Cadaaladda (Tiir) - xisbigatiir.com</title>
<meta name="description" content="" />
<meta name="keywords" content="" />
<link rel="shortcut icon" type="image/x-icon" href="https://xisbigatiir.so/theme/tiir/images/favicon.ico" />
<meta name="publisher" content="Xisbiga Midnimada iyo Cadaaladda" />
<meta name="copyright" content="Xisbiga Midnimada iyo Cadaaladda &copy; All Rights Reserved" />
<meta name="language" content="English" />
<meta name="robots" content="index" />
<meta name="robots" content="follow" />
<meta name="revisit-after" content="1 day" />
<meta name="generator" content="Powered by Ileys Web Control! v3.6" />

  <!--Load Stylesheets-->
  <link href='https://xisbigatiir.so/theme/tiir/css/font-awasome/css/font-awesome.min.css' type='text/css' rel='stylesheet' media='all' />
  <link type="text/css" rel="stylesheet" media="screen" href="https://xisbigatiir.so/assets/css/reset.css" />
  <link type="text/css" rel="stylesheet" media="screen" href="https://xisbigatiir.so/assets/css/style.css" />
  <link type="text/css" rel="stylesheet" media="screen" href="https://xisbigatiir.so/assets/colorbox/css/colorbox.css" />
  <link type="text/css" rel="stylesheet" media="screen" href="https://xisbigatiir.so/theme/tiir/css/style.css?v=1778761216">
  <link type="text/css" rel="stylesheet" media="screen" href="https://xisbigatiir.so/theme/tiir/css/menu.css?v=2">
  <link type="text/css" rel="stylesheet" media="screen" href="https://xisbigatiir.so/theme/tiir/slider/css/slider.css?v=1778761216">

  <!--Load Javascripts-->
  <script type="text/javascript" src="https://xisbigatiir.so/assets/js/jquery.js"></script>
  <script type="text/javascript" src="https://xisbigatiir.so/assets/js/swfobject.js"></script>
  <script type="text/javascript" src="https://xisbigatiir.so/assets/colorbox/js/jquery.colorbox.js"></script>
  <script type="text/javascript" src="https://xisbigatiir.so/assets/js/script.js"></script>
  <script type="text/javascript" src="https://xisbigatiir.so/assets/js/tooltip.js"></script>
  <script type="text/javascript" src="https://xisbigatiir.so/theme/tiir/js/menu.js"></script>
  <script type="text/javascript" src="https://xisbigatiir.so/theme/tiir/slider/js/slider.js"></script>
  <script src="https://xisbigatiir.so/assets/players/responsive/mediaelement-and-player.min.js"></script>
  <link rel="stylesheet" href="https://xisbigatiir.so/assets/players/responsive/mediaelementplayer.min.css" />
  <link rel="stylesheet" href="assets/styles-custom.css" />
  <script type="text/javascript"> 
  // <![CDATA[
      var THEMEURL = "https://xisbigatiir.so/theme/tiir";
      var SITEURL = "https://xisbigatiir.so";
  // ]]>
  </script>
	<script type="text/javascript"> 
		// <![CDATA[
		 $(document).ready(function(){
			$(".colorbox").colorbox({rel:'colorbox'});
			$('#menu ul').superfish({ 
				delay:500, 
				animation:{opacity: 'show', height: 'show'}, 
				speed:'fast', 
				autoArrows:false, 
				dropShadows:false 
			}); 	 
			$(function(){
				$("#menu ul li:has(ul)").find("span:first").addClass("down");
				$("#menu ul li ul li:has(ul)").find("span:first").removeClass("down");
				$("#menu ul li ul li:has(ul)").find("a:first").addClass("fly");
			});

			// Mobile menu toggle
			var menuToggle = document.getElementById('menuToggle');
			var menu = document.getElementById('menu');

			if (menuToggle && menu) {
				menuToggle.addEventListener('click', function(e) {
					e.preventDefault();
					menu.classList.toggle('active');
				});

				// Close menu when clicking on a link
				var menuLinks = menu.querySelectorAll('a');
				menuLinks.forEach(function(link) {
					link.addEventListener('click', function() {
						// Close on mobile after clicking a direct link (not submenu toggle)
						if (window.innerWidth <= 768) {
							menu.classList.remove('active');
						}
					});
				});

				// Close menu if clicking outside
				document.addEventListener('click', function(e) {
					if (!menu.contains(e.target) && !menuToggle.contains(e.target)) {
						menu.classList.remove('active');
					}
				});
			}
     });
		// ]]>
  </script>
</head>

<body>
  <div id="container">
  
    <div id="header">
      <a href="https://xisbigatiir.so" id="logo">
                  <img src="https://xisbigatiir.so/uploads//banner.jpg" alt="Xisbiga Midnimada iyo Cadaaladda" />
                </a>
      <a href="https://xisbigatiir.so/isdiiwaangeli" id="join_btn"> </a>      
    </div>
    
<style type="text/css">
#menu ul li:last-child a{
  background:#45a51d;
}
#menu ul li:last-child a:hover{
  background:#a51d1d;
}
</style>
</body>

</html>

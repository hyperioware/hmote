<?php
require_once "scripts/dbfunctions.php";
require_once "scripts/classes.php";
$loggedin = false;
$member = "";
$business = "";
$page = "home";
session_start();
$content = "";
$page_id = "";
$return = "";
/******* WHAT PAGE IS IT? **********/
if(isset($_GET['page'])){
	$page = sanitizeString($_GET['page']);
	if(isset($_GET['id'])){
		$page_id = sanitizeString($_GET['id']);
	}
	switch($page){
		case "home": $content = "scripts/home.php";break;
		case "org": $content = "scripts/org.php";break;
		case "settings": $content = "scripts/settings.php";break;
		case "shop": $content = "scripts/shop.php";break;
		case "bank": $content = "scripts/bank.php";break;
		case "search": $content = "scripts/search.php";break;
		case "app": $content = "scripts/app.php";break;
		case "login": $content = "scripts/login.php";break;
		case "logout": $content = "scripts/logout.php";break;
		case "notauth": $content = "scripts/notauth.php";break;
		case "reset": $content = "scripts/reset.php";break;
	}
}else{
	$content = "scripts/home.php";
}
/******* IS USER LOGGED ON? ************/
if(isset($_SESSION['email']) && isset($_SESSION['password'])){
	$email = sanitizeString($_SESSION['email']);
	$password = $_SESSION['password'];
	if(Member::user_valid($email,$password)){
		$loggedin = true;
	}else{
		$content = "scripts/notauth.php";
	}
}else if($page != 'reset'){
	$content = "scripts/login.php";
	if(isset($_GET['return'])){
		$return = sanitizeString($_GET['return']);
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<link rel='stylesheet' type='text/css' href='styles/web.css'>
<link rel='stylesheet' type='text/css' href='styles/icons.css'>
<link rel='stylesheet' type='text/css' href='styles/<?php echo $page;?>.css'>
<link rel="stylesheet" type="text/css" href="js/ui/css/smoothness/jquery-ui-1.10.3.custom.css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
<script src="js/ui/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="//connect.facebook.net/en_US/sdk.js"></script>
<script src="js/hmote.js"></script>
<script src="js/<?php echo $page;?>.js"></script>
<script>
$(document).ready(function(){
	FB.init({
	  appId: 770345716349771,
	  frictionlessRequests: true,
	  status: true,
	  version: 'v2.0'
	});
});
function login(callback) {
  FB.login(callback);
}
function loginCallback(response) {
  console.log('loginCallback',response);
  if(response.status != 'connected') {
    top.location.href = 'https://www.facebook.com/appcenter/hmoteapp';
  }else if(response.status == 'connected'){
  	processUser();
  }
}
function onStatusChange(response) {
  if( response.status != 'connected' ) {
    login(loginCallback);
  } else {
    processUser();
  }
}
function onAuthResponseChange(response) {
  console.log('onAuthResponseChange', response);
}

function processUser(){
	FB.api('/me',function(response){
		$.ajax({
			type: 'POST',
			url: 'scripts/fb_login.php',
			data: {fb_id:response.id,page:"<?php echo $page;?>",page_id:"<?php echo $page_id;?>",first_name:response.first_name,last_name:response.last_name,gender:response.gender},
			dataType: 'json',
			success: function(data){
				
			}
		});
	});
}
</script>
<style>
img{border:none;}
</style>
</head>
<body>

<div id='wrapper'>
    <div id='main-menu'>
    	<ul class='menu'>
        	<li><img src='img/hmote.png' height='35' style='padding-left:10px;'/></li>
            <li class='slide-btn'><a href='index.php?page=home'><img src='img/home.png' height='35' class='slide-btn-item'/></a></li>
            <li class='slide-btn'><a href='index.php?page=org'><img src='img/case.png' height='35' class='slide-btn-item'/></a></li>
            <li class='slide-btn'><a href='index.php?page=shop'><img src='img/tag.png' height='35' class='slide-btn-item'/></a></li>
            <li class='slide-btn'><a href='index.php?page=bank'><img src='img/money.png' height='35' class='slide-btn-item'/></a></li>
            <li class='slide-btn'><a href='index.php?page=settings'><img src='img/gear.png' height='35' class='slide-btn-item'/></a></li>
            <li style="float:right;"><img src='img/search.png' height='35'/></li>
        </ul>
    </div>
    <div id='content'>
    <?php require_once $content; ?>
    </div><!--end content-->
    <div id='submenu'>
    	<ul class='menu'>
        	<li><img src='img/mail.png' height='35'/></li>
            <li><img src='img/alert.png' height='35' /></li>
            <li><img src='img/star.png' height='35' /></li>
            <li><img src='img/cart.png' height='35' /></li>
        </ul>
    </div>
    <div id='footer'>
    	<ul class='menu'>
        	<li>&copy; 2014 Hyperioware, LLC
        	<li>Businesses</li>
            <li>FAQ's</li>
            <li>Privacy</li>
        </ul>
    </div>
</div>

</body>
</html>
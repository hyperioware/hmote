<?php
require_once "dbfunctions.php";
require_once "hmotefunctions.php";
require_once "classes.php";

/**************POSTS**********************/
if(isset($_POST['token']) && isset($_POST['email'])){
	$email = sanitizeString($_POST['email']);
	if(Member::email_exists($email)){
		$_SESSION['password_reset_token'] = $_POST['token'];
		$_SESSION['password_reset_email'] = $email;
		header("Location: emails.php?request=password_reset&email=$email");
	}else{
		header("Location: ./index.php?page=reset&error=error");
	}
}else if(isset($_POST['email']) && isset($_POST['password'])){
	$email = sanitizeString($_POST['email']);
	$password = $_POST['password'];
	Member::reset_password($email,$password);
	header("Location: ./index.php?page=login");
}
/*************GETS*********************/
if(isset($_GET['token']) && isset($_GET['email']) && isset($_SESSION['password_reset_token']) && isset($_SESSION['password_reset_email'])){
	$token = $_GET['token'];
	$email = $_GET['email'];
	if($token === $_SESSION['password_reset_token'] && $email === $_SESSION['password_reset_email']){
		unset($_SESSION['password_reset_token']);
		unset($_SESSION['password_reset_email']);
		echo "
		<span class='h1'>Password Reset</span>
		<span>Please enter a new password below.</span>
		<form action='reset.php' method='post'>
			<input type='hidden' name='email' value='$email'>
			<label for='password'>New password</label><input type='password' name='password' required='required' placeholder='Password'><input class='hmote-btn' type='submit' value='Reset'>
		</form>
		";
	}
}else if(isset($_GET['action'])){
	if($__GET['action'] === 'sent'){
		echo "<span class='h1'>Password Reset</span><span>An email has been sent to the provided email address. Please click the included link to finish the reset process.</span>";
	}
}else{
	$token = Member::generate_password_reset_token();
	if(isset($_GET['error'])){
		echo "<span class='h1'>Password Reset</span><span>The email address you entered is not in our records. Please try a different email address.</span>
		<form action='reset.php' method='post'>
			<input type='hidden' name='token' value='$token'>
			<label for='email'>Email</label><input type='email' name='email' required='required' placeholder='Email'><input class='hmote-btn' type='submit' value='Submit'>
		</form>
		";
	}else{
		echo "
		<span class='h1'>Password Reset</span>
		<span>Please enter your email address below and a message containing a reset link will be sent.</span>
		<form action='scripts/reset.php' method='post'>
			<input type='hidden' name='token' value='$token'>
			<label for='email'>Email</label><input type='email' name='email' required='required' placeholder='Email'><input class='hmote-btn' type='submit' value='Submit'>
		</form>
		";
	}
}
?>
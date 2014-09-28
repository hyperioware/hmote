<?php
require_once "dbfunctions.php";
require_once "hmotefunctions.php";
require_once "classes.php";
$error = "";
if(isset($_GET['error'])){
	$error = sanitizeString($_GET['error']);
	if($error == 'login'){
		$error = "The email or password you entered do not match our records. Please try again.";
	}else if($error == 'register'){
		$error = "The email you entered matches an account already in our system. Please log in or try a different email address.";
	}
}
if(isset($_POST['request'])){
	session_start();
	$request = sanitizeString($_POST['request']);
	if($request === 'login' && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['return'])){
		$email = sanitizeString($_POST['email']);
		$password = $_POST['password'];
		$return = sanitizeString($_POST['return']);
		if(Member::user_valid($email,$password)){
			$_SESSION['email'] = $email;
			$_SESSION['password'] = $password;
			header("Location: ../index.php");
		}else{
			header("Location: ../index.php?page=login&error=login&return=$return");
		}
	}else if($request === 'register' && isset($_POST['first-name']) && isset($_POST['last-name']) && isset($_POST['gender']) && isset($_POST['birthday']) && isset($_POST['city']) && isset($_POST['state']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['return'])){
		if(!Member::user_valid(sanitizeString($_POST['email']),$_POST['password'])){
			Member::register_new_user(sanitizeString($_POST['first-name']),sanitizeString($_POST['last-name']),sanitizeString($_POST['gender']),sanitizeString($_POST['birthday']),sanitizeString($_POST['city']),sanitizeString($_POST['state']),sanitizeString($_POST['email']),$_POST['password']);
			$_SESSION['email'] = sanitizeString($_POST['email']);
			$_SESSION['password'] = $_POST['password'];
			header("Location: ../index.php");
		}else{
			header("Location: ../index.php?page=login&error=register&return=$return");
		}
	}
}
?>
	<div id='login-container'>
	<span class='h1'>Login</span>
    <span class='p'>Please log in to continue.</span>
<form id='login-form' action='scripts/login.php' method='post'>
	<label for='email'>Email</label><input type='email' name='email' placeholder='Email' required='required' /><br />
    <label for='password'>Password</label><input type='password' name='password' placeholder='Password' required='required' /><br />
    <input type='hidden' name='return' value='<?php echo $page;?>' />
    <input type='hidden' name='request' value='login' />
    <input type='submit' value='Login' class='hmote-btn' />
    <span class='sub-note'>Can't remember your password? Click <a href='index.php?page=reset&return=<?php echo $page;?>'>here</a>.</span>
</form>
<span class='error-message'><?php echo $error;?></span>
</div>
<div id='register-container'>
<span class='h1'>Not yet a member?</span>
<span class='p'>Registration is free and easy!</span>
<form id='register-form' action='scripts/login.php' method='post'>
<ul>
<li><label for='first-name'>First Name</label><input type='text' name='first-name' placeholder='First Name' required='required'  /></li>
<li><label for='last-name'>Last Name</label><input type='text' name='last-name' placeholder='Last Name' require='required'  /></li>
<li><label for='gender'>Gender</label><select name='gender' required='required'><option value='male'>Male</option><option value='female'>Female</option></select></li>
<li><label for='birthday'>Birthday</label><input type='date' name='birthday' required='required' placeholder='mm/dd/yyyy'/></li>
<li><label for='city'>City</label><input type='text' name='city' placeholder='City' required='required' /></li>
<li><label for='state'>State</label><select name='state'><?php echo getStateHTML();?></select></li>
<li><label for='email'>Email</label><input type='email' name='email' placeholder='Email' required='required' /></li>
<li><label for='password'>Password</label><input type='password' name='password' required='required' placeholder='Password'  /></li>
<input type='hidden' name='return' value='<?php echo $page;?>' />
<input type='hidden' name='request' value='register' />
<li><input type='submit' value='Register' class='hmote-btn'/></li>
</ul>
</form>
<img src='img/hmote_banner.png' alt='Hmote the new remote for your business' width='500' >
</div>
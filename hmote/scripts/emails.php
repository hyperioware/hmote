<?php
require_once 'dbfunctions.php';
session_start();
if(isset($_GET['request'])){
	$request = sanitizeString($_GET['request']);
	if($request == 'password_reset'){
			$email = $_SESSION['email'];
			$token = $_SESSION['token'];
			$to = $email;
			$subject = "Hmote Password Reset";
			$message = "
			<html>
			<body>
			<a href='https://www.hyperioware.com' style='border:none;'><img src='http://www.hyperioware.com/img/logo.png' width='400'/></a>
			<div style='
			border-top:3px solid black;
			background:#F0F0F0;
			font-family:Verdana,Tahoma,sans-serif;
			width:100%;'>
			<h2 style='padding:10px;'>Password Reset Request</h2>
			<p style='padding:10px;'>A password reset was just requested for an account using this email address. Please click the link below or copy-and-paste it into the address bar of your web browser.</p>
			<a style='padding:10px;' href='https://apps.hyperioware.com/hmote/index.php?page=reset&email=$to&token=$token'>https://apps.hyperioware.com/hmote/index.php?page=reset&email=$to&token=$token</a>
			<br>
			<p style='padding:10px;'>If you feel you have received this email in error, please disregard.</p>
			<p style='padding:10px;'>Sincerely,</p>
			<p style='padding:10px;'>Mike</p>
			</div>
			</body>
			</html>
			";
			$from = "Hyperioware <mrudd@hyperioware.com>"."\r\n";
			$headers = "From:" . $from;
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			mail($to,$subject,$message,$headers);
			header("Location: ../index.php?page=reset&action=sent");
	}else if($request == 'register'){
		$email = $_SESSION['email'];
		$to = $email;
		$subject = "Welcome to Hmote!";
		$message = "
		<html>
		<body>
		<a href='https://www.hyperioware.com' style='border:none;'><img src='http://www.hyperioware.com/img/logo.png' width='400'/></a>
		<div style='
		border-top:3px solid black;
		background:#F0F0F0;
		font-family:Verdana,Tahoma,sans-serif;
		width:100%;'>
		<h2 style='padding:10px;'>Welcome!</h2>
		<p style='padding:10px;'>Congratulations on your registration! You have just taken a big (but important) step into a new community! Hmote pools daily tasks into one consistent app that gives you convenience you can enjoy, as well as flexibility of budget through our fully-customizable in-app pricing! Greater control through nearly any internet-connected device connects you to your local community like never before! Welcome again and feel free to click below to start clicking your brand-new remote!</p>
		<a style='padding:10px;' href='https://apps.hyperioware.com/hmote/index.php'>Go Home!</a>
		<br>
		<p style='padding:10px;'>If you feel you have received this email in error, please disregard.</p>
		<p style='padding:10px;'>Sincerely,</p>
		<p style='padding:10px;'>Mike</p>
		</div>
		</body>
		</html>
		";
		$from = "Hyperioware <mrudd@hyperioware.com>"."\r\n";
		$headers = "From:" . $from;
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		mail($to,$subject,$message,$headers);
		header("Location: ../index.php");
	}
	
}
?> 
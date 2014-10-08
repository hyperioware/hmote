<?php
require_once "dbfunctions.php";
require_once "classes.php";

session_start();
$member = "";
$business = "";
$page = sanitizeString($_POST['page']);
switch($page){
	case "home": require_once "home.php";
	case "org": require_once "org.php";
}
$fb_id = sanitizeString($_POST['fb_id']);
$first_name = sanitizeString($_POST['first_name']);
$last_name = sanitizeString($_POST['last_name']);
$gender = sanitizeString($_POST['gender']);
$page_id = "";
if(isset($_POST['page_id'])){
	$page_id = sanitizeString($_POST['page_id']);
}
if(isset($_SESSION['member'])){
	$session = $_SESSION['member'];
	if($session['fb_id'] == $fb_id){
		$_SESSION['member'] = Member::get_properties_by_fb_id($fb_id);
		echo json_encode(array("status" => "success", "reason" => "FB ID matches account", "page" => getPage($_SESSION['member']['properties'],$page_id)));
		
	}else{
		$_SESSION['member'] = Member::get_properties_by_fb_id($fb_id);
		//echo json_encode(array("status" => "test", "array" => $_SESSION['member']));
		echo json_encode(array("status" => "success", "reason" => "changed accounts", "page" => getPage($_SESSION['member']['properties'],$page_id)));
		
	}
	
}else{
	if(Member::fb_id_exists($fb_id)){
		$member = Member::get_properties_by_fb_id($fb_id);
		$_SESSION['member'] = $member['properties'];
		echo json_encode(array("status" => "success", "reason" => "FB ID Found", "page" => getPage($_SESSION['member']['properties'],$page_id)));
		
	}else{
		
		echo json_encode(array("status" => "success", "reason" => "Facebook ID now registered", "page" => getPage(array("request" => "new_user", "fb_id" => $fb_id,"first_name" => $first_name, "last_name" => $last_name,"gender" => $gender),$page_id)));
	}
}
?>
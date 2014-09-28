<?php
include_once("dbconnect.php");

function createTable($name, $query){
	if(tableExists($name)){
		echo "Table '$name' already exists<br/>";
	}else{
		queryMysql("CREATE TABLE $name($query)");
		echo "Table '$name' created<br/>";
	}
}

function tableExists($name){
	$result = queryMysql("SHOW TABLES LIKE '$name'");
	return mysql_num_rows($result);
}

function queryMysql($query){
	$result = mysql_query($query) or die(mysql_error());
	return $result;
}

function destroySession(){
	$_SESSION = array();
	
	if(session_id() != "" || isset($_COOKIE[session_name()]))
		setcookie(session_name(), '', time()-2592000, '/');
		session_destroy();
}

function sanitizeString($var){
	$var = strip_tags($var);
	$var = htmlentities($var);
	$var = stripslashes($var);
	return mysql_real_escape_string($var);
}
?>
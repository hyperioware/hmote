<?php
class HmoteItem{
	
}

class Member extends HmoteItem{
	
	public static function generate_password_reset_token(){
		$t = time();
		$token = md5($t);
		return $token;
	}
	
	public static function reset_password($email,$password){
		$passHash = Member::passHash($password);
		$query = "UPDATE members SET password='$passHash' WHERE email='$email'";
		$result = queryMysql($query);
	}
	public static function passHash($password){
		$salt = "!8oM";
		return md5($salt.$password);
	}
	
	public static function register_new_user($first_name,$last_name,$city,$state,$gender,$birthday,$email,$password){
		$passHash = Member::passHash($password);
		$query = "INSERT INTO members (email,password,first_name,last_name,city,state,gender,birthday) VALUES ('$email','$passHash','$first_name','$last_name','$city','$state','$gender','$birthday')";
		$result = queryMysql($query);
	}
	public static function id_exists($id){
		if(is_int($id)){
			$result = queryMysql("SELECT id FROM members WHERE id='$id'");
			if(mysql_num_rows($result)){
				return true;
			}else{
				return false;
			}
		}else{return false;}
	}
	
	public static function fb_id_exists($fb_id){
		$result = queryMysql("SELECT id FROM members WHERE fb_id='$fb_id'");
		if(mysql_num_rows($result)){
			return true;
		}else{
			return false;
		}
	}
	
	public static function email_exists($email){
		$result = queryMysql("SELECT email FROM members WHERE email='$email'");
		if(mysql_num_rows($result)){
			return true;
		}else{
			return false;
		}
	}
	
	public static function fb_is_match($fb_id,$id){
		$result = queryMysql("SELECT id FROM members WHERE id='$id' AND fb_id='$fb_id'");
		if(mysql_num_rows($result)){
			return true;
		}else{return false;}
	}
	
	public static function user_valid($email,$password){
		$passhash = Member::passHash($password);
		$result = queryMysql("SELECT id FROM members WHERE email='$email' AND password='$passhash'");
		if(mysql_num_rows($result)){
			return true;
		}else{return false;}
	}
	
	public static function getProperty($email,$prop){
		$result = queryMysql("SELECT $prop FROM members WHERE email='$email'");
		if(mysql_num_rows($result)){
			$row = mysql_fetch_row($result);
			return $row[0];
		}else{
			return "";
		}
	}
	
}

class Business extends HmoteItem{
	
	
	public static function id_exists($id){
		$result = queryMysql("SELECT id FROM businesses WHERE id='$id'");
		if(mysql_num_rows($result)){
			return true;
		}else{
			return false;
		}
	}
	
	public static function email_exists($email){
		$result = queryMysql("SELECT email FROM businesses WHERE email='$email'");
		if(mysql_num_rows($result)){
			return true;
		}else{
			return false;
		}
	}
	
}

class Order extends HmoteItem{
	
	
	public function checkout(){
		//Create order record
		if($this->properties['meta']['id'] != ""){
			if(Order::id_exists($this->properties['meta']['id'])){
			}else{
				$businessID = $this->properties['meta']['businessID'];
				$memberID = $this->properties['meta']['memberID'];
				$result = queryMysql("INSERT INTO orders (businessID,memberID) VALUES('$businessID','$memberID')");
				$this->properties['meta']['id'] = mysql_insert_id();
				for($i = 0; $i < sizeof($this->properties['items']); $i++){
					$orderID = $this->properties['meta']['id'];
					$productID = $this->properties['items'][$i]['productID'];
					$price_in_dollars = $this->properties['items'][$i]['price_in_dollars'];
					$price_in_credits = $this->properties['items'][$i]['price_in_credits'];
					$quantity = $this->properties['items'][$i]['quantity'];
					$result = queryMysql("INSERT INTO transactions (productID,orderID,price_in_dollars,price_in_credits,quantity) VALUES('$productID','$orderID','$price_in_dollars','$price_in_credits','$quantity')");
				}
			}
		}else{
			$businessID = $this->properties['meta']['businessID'];
			$memberID = $this->properties['meta']['memberID'];
			$result = queryMysql("INSERT INTO orders (businessID,memberID) VALUES('$businessID','$memberID')");
			$this->properties['meta']['id'] = mysql_insert_id();
			for($i = 0; $i < sizeof($this->properties['items']); $i++){
				$orderID = $this->properties['meta']['id'];
				$productID = $this->properties['items'][$i]['productID'];
				$price_in_dollars = $this->properties['items'][$i]['price_in_dollars'];
				$price_in_credits = $this->properties['items'][$i]['price_in_credits'];
				$quantity = $this->properties['items'][$i]['quantity'];
				$result = queryMysql("INSERT INTO transactions (productID,orderID,price_in_dollars,price_in_credits,quantity) VALUES('$productID','$orderID','$price_in_dollars','$price_in_credits','$quantity')");
			}
		}
	}
	
	public function addItemToOrder($productID,$price_in_dollars,$price_in_credits,$quantity){
		$this->properties['items'][] = array("id" => "", "productID" => $productID, "price_in_dollars" => $price_in_dollars, "price_in_credits" => $price_in_credits, "quantity" => $quantity);
		end($this->properties['items']);
		$key = key($this->properties['items']);
		$this->properties['items'][$key]['orderID'] = $this->properties['meta']['id'];
	}
	
	public function getTotalInDollars(){
		$total = 0;
		for($i = 0; $i < sizeof($this->properties['items']); $i++){
			$total += $this->properties['items'][$i]['quantity'] * $this->properties['items'][$i]['price_in_dollars'];
		}
		return $total;
	}
	
	public function getTotalInCredits(){
		$total = 0;
		for($i = 0; $i < sizeof($this->properties['items']); $i++){
			$total += $this->properties['items'][$i]['quantity'] * $this->properties['items'][$i]['price_in_credits'];
		}
		return $total;
	}
	
	public function getTotalQuantity(){
		$total = 0;
		for($i = 0; $i < sizeof($this->properties['items']); $i++){
			$total += $this->properties['items'][$i]['quantity'];
		}
		return $total;
	}
	
	public static function id_exists($id){
		$result = queryMysql("SELECT id FROM orders WHERE id='$id'");
		if(mysql_num_rows($result)){
			return true;
		}else{
			return false;
		}
	}
	
	public static function get_properties_by_id($id){
		if(Order::id_exists($id)){
			$result = queryMysql("SELECT * FROM orders WHERE id='$id'");
			$row = mysql_fetch_row($result);
			$meta = array("id" => $row[0],"businessID" => $row[1], "memberID" => $row[2], "date" => $row[3]);
			$response = array("status" => "found", "properties" => array("meta" => $meta));
			$result = queryMysql("SELECT * FROM transactions WHERE orderID='$id'");
			if(mysql_num_rows($result)){
				$items = "";
				for($i = 0; $i < mysql_num_rows($result); $i++){
					$row = mysql_fetch_row($result);
					$items[$i] = array("id" => $row[0],"productID" => $row[1], "price_in_dollars" => $row[3], "price_in_credits" => $row[4], "quantity" => $row[5]);
				}
				$response['properties']['items'] = $items;
			}
			return array("status" => "found", "properties" => $response);
			
		}else{return array("status" => "failed");}
	}
}
?>
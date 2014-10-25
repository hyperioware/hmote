<?php
require "dbfunctions.php";
require "classes.php";
require "hmotefunctions.php";
session_start();
$member = $_SESSION['member'];
if(isset($_POST['request'])){
	$request = sanitizeString($_POST['request']);
	if($request === "checkout"){
		$businessID = sanitizeString($_POST['businessID']);
		$items = $_POST['items'];
		$memberID = $member->getProperty("id");
		$order = new Order(array("meta" => array("id" => "","businessID" => $businessID, "memberID" => $memberID, "date" => ""),"items" => $items));
		$total_credit = 0;
		for($i = 0; $i < sizeof($items);$i++){
			if($items[$i]['productID'] == 1){
				$temp = $total_credit;
				$total_credit = $temp + $items[$i]['quantity'];
			}
		}
		$cost = 0;
		for($i = 0; $i < sizeof($items);$i++){
			$cost += ($items[$i]['price_in_credits'] * $items[$i]['quantity']);
		}
		if($total_credit){
			$temp = $total_credit;
			$total_credit = $temp + $member->getProperty("total_credits");
			$member->editProperty("total_credits",$total_credit);
			$member->updateRecord();
			$_SESSION['member'] = $member->getProperties();
		}else{
			$total_credit = $member->getProperty("total_credits");
		}
		
		if($cost){
			if($cost > $member->getProperty("total_credits")){
				echo json_encode(array("status" => "error","type" => "Insufficient credits for transaction","available_credits" => number_format($member->getProperty("total_credits"),0,".",","), "needed_credits" => number_format($cost - $member->getProperty("total_credits"),0,".",",")));
			}else{
				$order->checkout();
				$member->editProperty("total_credits",$total_credit - $cost);
				$member->updateRecord();
				$_SESSION['member'] = $member->getProperties();
				echo json_encode(array("status" => "success", "total_credits" => number_format($member->getProperty("total_credits"),0,".",",")));
			}
		}else{
			$order->checkout();
			$member->editProperty("total_credits",$total_credit - $cost);
			$member->updateRecord();
			$_SESSION['member'] = $member->getProperties();
			echo json_encode(array("status" => "success", "total_credits" => number_format($member->getProperty("total_credits"),0,".",",")));}
	}else if($request === "check_state"){
		$states = array('AZ','AR','AK','AL','CO','CA','CT','DE','DC','FL','GA','HI','ID','IL','IN',
		'IA','KS','KY','LA','ME','MD','MI','MT','MA','MN','MS','MO','NE','NV','NH','NC','ND','NJ','NM','NY',
		'OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WS','WY');
		$state = sanitizeString($_POST['state']);
		if(in_array($state,$states)){
			$response = array("status"=>"success");
			echo json_encode($response);
		}else{
			$response = array("status"=>"fail");
			echo json_encode($response);
		}
	}else if($request === "credit_summary"){
		$id = $member->getProperty("id");
		echo getCreditSummary($id);
	}else if($request === "update"){
		$type = sanitizeString($_POST['type']);
		$memberID = $member->getProperty("id");
		$response = Business::get_properties_by_id($member->getProperty("business_id"));
		$business = new Business($response['properties']);
		if($type === "member"){
			if(isset($_POST['first_name'])){
				$first_name = sanitizeString($_POST['first_name']);
				$last_name = sanitizeString($_POST['last_name']);
				$member->editProperty("first_name",$first_name);
				$member->editProperty("last_name",$last_name);
				$member->updateRecord();
				$_SESSION['member'] = $member->getProperties();
				header("Location: ../index.php?page=profile");
			}else if(isset($_POST['email'])){
				$email = sanitizeString($_POST['email']);
				$member->editProperty("email",$email);
				$member->updateRecord();
				$_SESSION['member'] = $member->getProperties();
				header("Location: ../index.php?page=profile");
			}else if(isset($_POST['street'])){
				$street = sanitizeString($_POST['street']);
				$city = sanitizeString($_POST['city']);
				$state = sanitizeString($_POST['state']);
				$zipcode = sanitizeString($_POST['zipcode']);
				$member->editProperty("street",$street);
				$member->editProperty("city",$city);
				$member->editProperty("state",$state);
				$member->editProperty("zipcode",$zipcode);
				$member->updateRecord();
				$_SESSION['member'] = $member->getProperties();
				header("Location: ../index.php?page=profile");
			}
		}else if($type === "business"){
			if(isset($_POST['name'])){
				$name = sanitizeString($_POST['name']);
				$business->editProperty("name",$name);
				$business->updateRecord();
				$_SESSION['business'] = $business->getProperties();
				header("Location: ../index.php?page=manage");
			}else if(isset($_POST['email'])){
				$email = sanitizeString($_POST['email']);
				$business->editProperty("email",$email);
				$business->updateRecord();
				$_SESSION['business'] = $business->getProperties();
				header("Location: ../index.php?page=manage");
			}else if(isset($_POST['street'])){
				$street = sanitizeString($_POST['street']);
				$city = sanitizeString($_POST['city']);
				$state = sanitizeString($_POST['state']);
				$zipcode = sanitizeString($_POST['zipcode']);
				$business->editProperty("street",$street);
				$business->editProperty("city",$city);
				$business->editProperty("state",$state);
				$business->editProperty("zipcode",$zipcode);
				$business->updateRecord();
				$_SESSION['business'] = $business->getProperties();
				header("Location: ../index.php?page=manage");
			}else if(isset($_POST['business_type'])){
				$businessType = sanitizeString($_POST['business_type']);
				$business->editProperty("type",$businessType);
				$business->updateRecord();
				$_SESSION['business'] = $business->getProperties();
				header("Location: ../index.php?page=manage");
			}
		}
	}else if($request === "add_product"){
		$businessID = $member->getProperty('business_id');
		$name = sanitizeString($_POST['name']);
		$description = sanitizeString($_POST['description']);
		$price_in_credits = sanitizeString($_POST['price_in_credits']);
		$result = queryMysql("INSERT INTO products (businessID,name,description,price_in_credits,price_in_dollars) VALUES('$businessID','$name','$description','$price_in_credits','')");
		header("Location: ../index.php?page=manage");
	}else if($request === "searchlist"){
		$string = sanitizeString($_POST['term']);
		if($string != ""){
			$query = "SELECT id,name,city,state,type FROM businesses WHERE name LIKE '%$string%' OR city LIKE '%$string%' OR state LIKE '%$string%' OR type LIKE '%$string%' LIMIT 5 ";
			$result = queryMysql($query);
			$html = "";
			if(mysql_num_rows($result)){
				$num = mysql_num_rows($result);
				for($i = 0; $i < $num; $i++){
					$row = mysql_fetch_row($result);
					$html .= "
					<li class='list-result'>
						<span class='search-name'><a href='index.php?view=profile&id=$row[0]&type=b'>$row[1]</a></span><br>
						<span class='search-type'><a href='index.php?view=search&type=$row[4]'>$row[4]</a></span><br>
						<span class='search-location'><a href='index.php?view=search&city=$row[2]'>$row[2]</a>, <a href='index.php?view=search&state=$row[3]'>$row[3]</a></span>
					</li>
					";
				}
			}else{
				$html .= "<li class='list-result'>There are no entries that match your search.</li>";
			}
			
			$html .= "
			<li class='list-result'><a href='index.php?view=search&term=$string'>See all results</a></li>
			";
			echo $html;
		}
	}else if($request === "get_org_content"){
		$type = sanitizeString($_POST['type']);
		$id = sanitizeString($_POST['id']);
		$status = sanitizeString($_POST['status']);
		if($status === 'member'){
			switch($type){
				case "summary": echo json_encode(array("status" => "success", "content" => getDash(getTopProduct($id),getRating($id),get30DaysSales($id),getTopCustomer($id))));break;
				case "settings": echo json_encode(array("status" => "success", "content" => getOrgSettings($id)));break;
				case "sales": echo json_encode(array("status" => "success", "content" => getSalesList($id)));break;
				case "customers": echo json_encode(array("status" => "success", "content" => getCustomerList($id)));break;
				case "storefront": echo json_encode(array("status" => "success", "content" => getStoreFront($id)));break;
			}
		}
	}else if($request === "verify_business_name"){
		$name = sanitizeString($_POST['name']);
		$result = queryMysql("SELECT id FROM businesses WHERE name='$name'");
		if(mysql_num_rows($result)){
			echo json_encode(array("status" => "error","message"=>"Name already exists."));
		}else{
			echo json_encode(array("status" => "success"));
		}
	}else if($request === "register_business"){
		$name = sanitizeString($_POST['register-business-name']);
		$street = sanitizeString($_POST['street']);
		$city = sanitizeString($_POST['city']);
		$state = sanitizeString($_POST['state']);
		$zipcode = sanitizeString($_POST['zipcode']);
		$mobilePhone = sanitizeString($_POST['mobile-phone']);
		$landPhone = sanitizeString($_POST['land-phone']);
		$fax = sanitizeString($_POST['fax']);
		$facebook = sanitizeString($_POST['facebook']);
		$twitter = sanitizeString($_POST['twitter']);
		$website = sanitizeString($_POST['website']);
		$email = sanitizeString($_POST['email']);
		$member_id = Member::getProperty($_SESSION['email'],'id');
		$id = Business::generate_id();
		$result = queryMysql("INSERT INTO businesses (id,memberID,name,email,street,city,state,zipcode,mobilePhone,landPhone,fax,facebook,twitter,website) VALUES('$id','$member_id','$name','$email','$street','$city','$state','$zipcode','$mobilePhone','$landPhone','$fax','$facebook','$twitter','$website')");
		$result = queryMysql("UPDATE members SET business_id='$id' WHERE id='$member_id'");
		header("Location: emails.php?request=new_biz");
	}else{echo json_encode(array("status" => "error", "type" => "Request '$request' not recognized"));}
}else{echo json_encode(array("status" => "error", "type" => "No request sent"));}
?>
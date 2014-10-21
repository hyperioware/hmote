<?php
require_once "dbfunctions.php";
include "hmotefunctions.php";
require_once "classes.php";
	$page_id = 0;
	if(isset($_GET['page_id'])){
		$page_id = sanitizeString($_GET['page_id']);
	}
	$id = 0;
	$title = "";
	$emp_title = "";
	$emp_id = 0;
	$mem_id = Member::getProperty($_SESSION['email'],'id');
	$result = queryMysql("SELECT org_id,title FROM org_members WHERE member_id='$mem_id'");
	if(mysql_num_rows($result)){
		$row = mysql_fetch_row($result);
		$emp_id = $row[0];
		$emp_title = "&#8226; ".$row[1];
	}
	$memberOf = false;
	if($page_id != 0){//business id is set -- trying to view a business's page
		$id = $page_id;
		if($member['business_id'] != 0){//Member has a business
			if($member['business_id'] == $page_id){//Member is viewing their own business page
				$memberOf = true;
				$title = "&#8226; Owner";
			}
		}else{//Member does not have a business
			if($emp_id != 0 && $emp_id == $id){//Member is an employee of the business that owns the page
				$title = $emp_title;
				$memberOf = true;
			}
		}
	}else if($member['business_id'] != 0){//business id not set in GET but is set in member details
		$id = $member['business_id'];
		$title = "&#8226; Owner";	
		$memberOf = true;
	}else if($emp_id != 0){//business id not set in GET and not set in member details. check to see if member is employee
		$id = $emp_id;
		$title = $emp_title;
		$memberOf = true;
	}
	if($id != 0 && $memberOf == true){
		$business = Business::get_properties_by_id($id);
		$name = $business['properties']['name'];
		$leftPane = "<input type='hidden' value='$id' id='biz_id'/><input type='hidden' value='member' id='status'/><ul id='business-menu'>
			<li class='active-menu-item'><a href='#' class='hmote-menu-btn four-box-icon' id='summary-btn' onclick='getOrgContent(this);'></a></li>
			<li><a href='#' class='hmote-menu-btn two-gears-icon' id='settings-btn' onclick='getOrgContent(this);'></a></li>
			<li><a href='#' class='hmote-menu-btn bar-graph-icon' id='sales-btn' onclick='getOrgContent(this);'></a></li>
			<li><a href='#' class='hmote-menu-btn man-outline-icon' id='customers-btn' onclick='getOrgContent(this);'></a></li>
			<li><a href='#' class='hmote-menu-btn sale-tag-icon' id='storefront-btn' onclick='getOrgContent(this);'></a></li>
		</ul>";
		$topProduct = getTopProduct($id);
		$topCustomer = getTopCustomer($id);
		$rating = getRating($id);
		$sales = get30DaysSales($id);
		$rightPane = getDash($topProduct,$rating,$sales,$topCustomer);
	}else if($id != 0 && $memberOf == false){
		$leftPane = "Profile Menu";
		$rightPane = "Profile Content";
	}else{
		$name = "Let's get you started!";
		$leftPane = "
		<form  action='scripts/post.php' method='post'>
		<ul id='business-menu'>
			<li class='active-menu-item'><a href='#' class='hmote-menu-btn biz-register house-icon' id='start-btn'></a></li>
			<li><a href='#' class='hmote-menu-btn  biz-register location-marker-icon' id='location-btn'></a></li>
			<li><a href='#' class='hmote-menu-btn  biz-register handset-icon' id='contact-btn'></a></li>
			<li><a href='#' class='hmote-menu-btn  biz-register money-icon' id='payment-btn' ></a></li>
			<li><a href='#' class='hmote-menu-btn  biz-register checkmark-icon' id='confirm-btn'></a></li>
		</ul>";
		$stateList = getStateHTML();
		$rightPane = "
		<div class='register-pane' id='start-pane'>
			<span style='font-size:24px;position:relative;float:left;display:block;width:860px;padding:10px 0 100px 0;'>Enter your business name below and we will let you know if it's already taken!</span>
			<input type='text' name='register-business-name' style='position:relative;float:left;display:block;font-size:28px;border-radius:10px;margin-left:250px;'/><span class='input-verify' id='verify-business'></span>
			<span class='pane-error error'></span>
			<span class='pane-description'>This is the first step in your journey to get started! Picking a name is one of the most important parts of starting a business. If you can't think of a good name for your vision, you might as well stop here! It is important to keep in mind that your business's name should be unique in your market; you want that thing to pop when your potential customers see it!</span>
		</div><!--end start-pane-->
		<div class='register-pane' id='location-pane'>	
			<span class='h1' style='color:#f7f7f7;font-size:32px;'>Location</span>
			<div id='address-entries'>
				<label for='street'>Street</label>
				<input class='address-input' name='street' type='text' required='required' />
				<label for='city'>City</label>
				<input class='address-input'  name='city' type='text' required='required' />
				<label for='state'>State</label>
				<select class='address-input'  name='state' id='state'>$stateList</select>
				<label for='zipcode'>Zipcode</label>
				<input class='address-input' name='zipcode' type='text' maxlength='5' required='required' />
				<a href='#' class='hmote-btn' id='confirm-address'>Confirm</a>
			</div><!--end address-entries-->
			<div id='preview-map' onload='initialize();'></div>
		</div><!--end location-pane-->
		<div class='register-pane' id='contact-pane'>
			<span class='h1' style='color:#f7f7f7;padding-bottom:50px;font-size:32px;'>Contact</span>
			<label for='mobile-phone'>Mobile Phone</label><input type='phone' name='mobile-phone' />
			<label for='land-phone'>Land Phone</label><input type='phone' name='land-phone' />
			<label for='fax'>Fax</label><input type='phone' name='fax' />
			<label for='facebook'>Facebook URL</label><input type='url' name='facebook' placeholder='Ex: https://www.facebook.com/hyperioware'/>
			<label for='twitter'>Twitter URL</label><input type='url' name='twitter'  placeholder='Ex: https://www.twitter.com/hyperioware'/>
			<label for='website'>Website</label><input type='url' name='website'  placeholder='Ex: http://www.yourbiz.com'/>
		</div><!--end contact-pane-->
		<div class='register-pane' id='payment-pane'>Payment</div><!--end payment-pane-->
		<div class='register-pane' id='confirm-pane'>Confirm</div><!--end confirm-pane-->
		</form>
		";
	}
echo <<<END
<div id='headline'><span id='biz-name'>$name</span>&nbsp;&nbsp;<span id='job-title'>$title</span></div>
<div id='left-pane'>$leftPane</div>
<div id='right-pane'>$rightPane</div>
END;
?>
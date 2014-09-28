<?php
require_once "dbfunctions.php";
include "hmotefunctions.php";
require_once "classes.php";
function getPage($member,$page_id){
	
	$id = 0;
	$title = "";
	$emp_title = "";
	$emp_id = 0;
	$mem_id = $member['id'];
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
		$leftPane = "Register new business";
		$rightPane = "Registration content";
	}
return <<<END
<div id='headline'><span id='biz-name'>$name</span>&nbsp;&nbsp;<span id='job-title'>$title</span></div>
<div id='left-pane'>$leftPane</div>
<div id='right-pane'>$rightPane</div>
END;
}


?>
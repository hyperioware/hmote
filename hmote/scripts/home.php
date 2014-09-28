<?php
include "hmotefunctions.php";
require_once "classes.php";
function getPage($member,$page_id){
	if(array_key_exists("new_user",$member)){
		
	}
	$fb_id = $member['fb_id'];
	$name = $member['first_name']." ".$member['last_name'];
	$location = "";
	$businessName = "";
	if($member['city'] != ""){
		$location .= $member['city'];
	}
	
	if($member['city'] != "" && $member['state'] != ""){
		$location .= ", ".$member['state'];
	}else if($member['city'] == "" && $member['state'] != ""){
		$location .= $member['state'];
	}
	$total_credits = number_format($member['total_credits'], 0, '.', ',');
	$businessInfo = "<table id='biz-list-table'>
	<thead><tr><th class='org-list-heading' colspan='3'><img src='img/case.png' height='25' style='position:relative;display:inline;float:left;padding-right:10px;'/><span style='position:relative;display:inline;float:left;height:25px;line-height:25px;'>My Organizations</span></th></tr></thead>
	<tfoot><tr><td colspan='3'><!--Need a job to do? <a href='index.php?page=org&id=apply' class='hmote-btn'>Apply now!</a>--></td></tr></tfoot>
	<tbody>
	";
	$result = queryMysql("SELECT id FROM members WHERE fb_id='$fb_id'");
	$row = mysql_fetch_row($result);
	$id = $row[0];
	$found = false;
	$result = queryMysql("SELECT id,name FROM businesses WHERE memberID='$id'");
	$biz_id = "";
	$businessSum = "";
	if(mysql_num_rows($result)){
		$found = true;
		$row = mysql_fetch_row($result);
		$biz_id = $row[0];
		$businessName = $row[1];
		$businessInfo .= "<tr class='org-row'><td class='business-name'>$row[1]</td><td class='job-title'>Owner</td><td><a href='index.php?page=org&id=$row[0]' class='hmote-btn'>Manage</a></td></tr>";
	}
	$result = queryMysql("SELECT org_id,title FROM org_members WHERE member_id='$id' AND status='1'");
	if(mysql_num_rows($result)){
		$found = true;
		for($i = 0; $i < mysql_num_rows($result); $i++){
			$row = mysql_fetch_row($result);
			$org_id = $row[0];
			$title = $row[1];
			$result1 = queryMysql("SELECT name FROM businesses WHERE id='$org_id'");
			$row = mysql_fetch_row($result1);
			$bizname = $row[0];
			$businessInfo .= "<tr class='org-row'><td class='business-name'>$bizname</td><td class='job-title'>$title</td><td><a href='index.php?page=org&id=$org_id' class='hmote-btn'>Go</a></td></tr>";
		}
	}
	if(!$found){
		$businessInfo = "<table id='biz-list-table'>
		<thead><tr><th class='org-list-heading' colspan='3'><img src='img/case.png' height='25' style='position:relative;display:inline;float:left;padding-right:10px;'/><span style='position:relative;display:inline;float:left;height:25px;line-height:25px;'>My Organizations</span></th></tr></thead>
		<tbody><tr><td class='business-name' style='text-align:center;'><a href='index.php?page=org&id=new' class='hmote-btn'>Start one now!</a></td><td class='job-title' style='text-align:center;'><a href='index.php?page=org&id=apply' class='hmote-btn'>Apply now!</a></td><td></td></tr></tbody></table>";
	}else{
		$businessInfo .= "
		</tbody>
		</table>";
		$topProduct = "";
		$topProduct = getTopProduct($biz_id);
		$rating = getRating($biz_id);
		$sales = get30DaysSales($biz_id);
		$businessSum = "
			<li class='org-sum-module'><div id='sales-total'><img src='img/chart_bar_up.png' width='50'/><span class='section-title'>Total Sales</span><span id='total-sales' class='section-content'>$sales</span></div></li>
			<li class='org-sum-module'><div id='top-product'><img src='img/star.png'  width='50'/><span class='section-title'>Top Product</span><span id='top-product' class='section-content'>$topProduct</span></div></li>
			<li class='org-sum-module'><div id='customer-rating'><img src='img/heart.png'  width='50'/><span class='section-title'>Customer Rating</span><span id='customer-rating' class='section-content'>$rating</span></div></li>
			";
	}
	$recentOrder = getOrders(1,$id,"member");
return <<<END
<div id='left-pane'>
<ul>
	<li><div id='profile-sum' class='left-pane-module'>
		<img src='https://graph.facebook.com/$fb_id/picture?type=normal&height=50' id='profile-pic'/>
		<span id='name'>$name</span><span id='location'>$location</span>
	</div></li>
	<li><div id='org-panel' class='left-pane-module'>
		<ul>
			<li><div id='org-sum' class='org-panel-module'>
				$businessInfo
			</div></li>
			$businessSum
		</ul>
	</div></li>
</ul>
</div>
<div id='right-pane'>
	<div id='money-sum'><div id='total-credits'><span class='right-pane-title'>Total Credits</span><div class='right-pane-content'><span id='total-credits-amount' style='font-size:28px;'>$total_credits</span><div id='add-credit-btn-wrapper'><a href='#' id='add-credits' class='hmote-btn'>Add Credits</a></div></div></div>
	<div id='recent-order'><span  class='right-pane-title'>Recent Order</span><ul>$recentOrder</ul></div></div><!--end money-sum-->
	<div id='dash-cart'></div>
</div>
END;
}
?>
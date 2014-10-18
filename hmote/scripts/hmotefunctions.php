<?php
function unauthorized_access($return_url){
	return "
	<div id='login'>
		<h3>Please login to continue.</h3>
		<form id='login-form' method='post' action='login.php'>
			<input type='email' name='email' placeholder='Email Address' required/>
			<input type='password' name='password' required />
			<input type='hidden' name='return_url' value='$return_url' />
			<input type='submit' value='Login' />
		</form>
	</div>
	<hr>
	<div id='signup'>
		<h3>Please signup to continue.</h3>
		<form id='signup-form' method='post' action='signup.php'>
			<input type='text' name='first_name' placeholder='First Name' required/>
			<input type='text' name='last_name' placeholder='Last Name' required/>
			<input type='email' name='email' placeholder='Email Address' required/>
			<input type='password' name='password' required />
			<input type='hidden' name='return_url' value='$return_url' />
			<input type='submit' value='Signup' />
		</form>
	</div>
	";
}

function getOrders($limit,$id,$type){
	$result;
	if($limit){
		if($type === "member"){
			$result = queryMysql("SELECT * FROM orders WHERE member_id='$id' ORDER BY date DESC LIMIT $limit");
		}else{
			$result = queryMysql("SELECT * FROM orders WHERE business_id='$id' ORDER BY date DESC LIMIT $limit");
		}
	}else{
		if($type === "member"){
			$result = queryMysql("SELECT * FROM orders WHERE member_id='$id' ORDER BY date DESC");
		}else{
			$result = queryMysql("SELECT * FROM orders WHERE business_id='$id' ORDER BY date DESC");
		}
	}
	if(mysql_num_rows($result)){
		$rows = mysql_num_rows($result);
		$html = "
		<table>
		<tr><th>Date</th><th>Business</th><th>Product</th><th>Price In Credits</th></tr>
		";
		for($i = 0; $i < $rows; $i++){
			$row = mysql_fetch_row($result);
			$result1 = queryMysql("SELECT DATE_FORMAT(date, '%d-%b-%Y'),productID,price_in_credits,quantity FROM transactions WHERE orderID='$row[0]'");
			$sum = 0;
			$temp = 0;
			$row1 = "";
			for($a = 0; $a < mysql_num_rows($result1);$a++){
				$row1 = mysql_fetch_row($result1);
				$temp = $sum;
				$sum = $temp + ($row1[2] * $row1[3]);
			}
			$result2 = queryMysql("SELECT name FROM businesses WHERE id='$row[1]'");
			$row2 = mysql_fetch_row($result2);
			$business_name = $row2[0];
			$result2 = queryMysql("SELECT name FROM products WHERE id='$row1[1]'");
			$row2 = mysql_fetch_row($result2);
			$product_name = $row2[0];
			if($sum){
				$sum = number_format($sum,0,".",",");
				$html .= "<tr><td>$row1[0]</td><td>$business_name</td><td>$product_name</td><td style='text-align:center'>$sum</td></tr>";
			}
		}
		return $html."</table>";
	}else{
		return "<li class='item'>No records are available</li>";
	}
}

function search($city,$state,$zipcode){
	$query = "";
	if($city != "" && $state != "" && $zipcode != ""){//City, State, Zip
		$query = "SELECT * FROM businesses WHERE city='$city' AND state='$state' AND zipcode='$zipcode' ORDER BY name ASC";
	}else if($city == "" && $state != "" && $zipcode != ""){//State, Zip
		$query = "SELECT * FROM businesses WHERE state='$state' AND zipcode='$zipcode' ORDER BY name ASC";
	}else if($city != "" && $state != "" && $zipcode == ""){//City, State
		$query = "SELECT * FROM businesses WHERE city='$city' AND state='$state' ORDER BY name ASC";
	}else if($city != "" && $state == "" && $zipcode != ""){//City, Zip
		$query = "SELECT * FROM businesses WHERE city='$city' AND zipcode='$zipcode' ORDER BY name ASC";
	}else if($city != "" && $state == "" && $zipcode == ""){//City
		$query = "SELECT * FROM businesses WHERE city='$city' ORDER BY name ASC";
	}else if($city == "" && $state != "" && $zipcode == ""){//State
		$query = "SELECT * FROM businesses WHERE state='$state' ORDER BY name ASC";
	}else if($city == "" && $state == "" && $zipcode != ""){//Zip
		$query = "SELECT * FROM businesses WHERE zipcode='$zipcode' ORDER BY name ASC";
	}else{
		$query = "SELECT * FROM businesses ORDER BY name ASC";
	}
	
	$result = queryMysql($query);
	if(mysql_num_rows($result)){
		$html = "";
		$rows = mysql_num_rows($result);
		for($i =0; $i < $rows; $i++){
			$row = mysql_fetch_row($result);
			$name = $row[1];
			$street = $row[4];
			$city = $row[5];
			$state = $row[6];
			$zipcode = $row[7];
			$type = $row[8];
			$html .= "<li class='search-item'><a href='index.php?page=profile&id=$row[0]'>$name</a><br>$street, $city, $state $zipcode<br>$type</li>";
		}
		return $html;
	}else{
		return "<li class='search-item'>There are no businesses that match your request</li>";
	}
}

function getProducts($id){
	$result = queryMysql("SELECT id,name,price_in_credits FROM products WHERE businessID='$id'");
	if(mysql_num_rows($result)){
		$rows = mysql_num_rows($result);
		$html = "";
		for($i = 0; $i < $rows; $i++){
			$row = mysql_fetch_row($result);
			$html .= "<li class='product-item'><span class='product-name'>$row[1]</span><span class='product-price'>$row[2]</span></li>";
		}
		return $html;
	}else{
		return "<li class='product-item'>No records are available</li>";
	}
}

function getTopProduct($id){
	$products = "";
	$result = queryMysql("SELECT id,name FROM products WHERE business_id='$id'");
	if(mysql_num_rows($result)){
		$rows = mysql_num_rows($result);
		for($i = 0; $i < $rows; $i++){
			$row = mysql_fetch_row($result);
			$products[$i] = array("id" =>$row[0],"name" => $row[1]);
			if(mysql_num_rows(queryMysql("SELECT id FROM transactions WHERE productID='$row[0]'"))){
				$result1 = queryMysql("SELECT SUM(price_in_credits * quantity) As Total FROM transactions WHERE productID='$row[0]' AND date > (CURDATE()-30)");
				$row1 = mysql_fetch_assoc($result1);
				$products[$i]["total"] = $row1["Total"];
			}else{
				$products[$i]["total"] = 0;
			}
		}
		$topTotal = 0;
		$name = "";
		$id = "";
		for($i = 0; $i < sizeof($products); $i++){
			if($products[$i]['total'] > $topTotal){
				$topTotal = $products[$i]['total'];
				$name = $products[$i]['name'];
				$id = $products[$i]['id'];
			}
		}
		if($topTotal){
			$topTotal = number_format($topTotal, 0, ".", ",");
			return "<span id='top-product'><span id='top-product-name'>$name</span><span id='top-product-total'>$topTotal</span></span>";
		}else{
			return "<span id='top-product'>No sales available</span>";
		}
		
	}else{return "<span id='top-product'>No products available</span>";}
}

function getPurchaseSummary($id){
	$html = "
	<table>
	<tr><th>Company</th><th>Amount</th></tr>
	";
	$result = queryMysql("SELECT DISTINCT business_id,id FROM orders WHERE memberID='$id' AND date > (CURRENT_TIMESTAMP()-30)");
	if(mysql_num_rows($result)){
		$rows = mysql_num_rows($result);
		$companies = array();
		for($i = 0; $i < $rows; $i++){
			$row = mysql_fetch_row($result);
			$result1 = queryMysql("SELECT SUM(price_in_credits * quantity) As Total FROM transactions WHERE orderID='$row[1]'");
			$result2 = queryMysql("SELECT name FROM businesses WHERE id='$row[0]'");
			$row1 = mysql_fetch_assoc($result1);
			$total = number_format($row1['Total'],0,".",",");
			$row1 = mysql_fetch_row($result2);
			$name = $row1[0];
			$html .= "<tr><td class='center'>$name</td><td class='center'>$total</td></tr>";
		}
		return $html;
	}else{
		return $html."<tr><td colspan='2'><i>None in the last 30 days</i></td></tr></table>";
	}
}

function getCreditSummary($id){
	$html = "
	<table>
	<tr><th>Date</th><th>Credits</th><th>Sale Total</th></tr>
	";
	$orders = queryMysql("SELECT id FROM orders WHERE memberID='$id' AND date > (CURDATE()-30)");
	if(mysql_num_rows($orders)){
		$rows = mysql_num_rows($orders);
		for($i = 0; $i < $rows; $i++){
			$row = mysql_fetch_row($orders);
			$result = queryMysql("SELECT DATE_FORMAT(date, '%d-%b-%Y'),quantity,(price_in_dollars * quantity) As Total FROM transactions WHERE orderID='$row[0]' AND price_in_dollars > 0 ORDER BY date");
			if(mysql_num_rows($result)){
				$row = mysql_fetch_row($result);
				
				$html .= "<tr><td class='center'>$row[0]</td><td class='center'>$row[1]</td><td class='right'>$$row[2]</td></tr>";
			}else{
				$html .= "<tr><td colspan='3'>fail</td></tr>";
			}
		}
		return $html."</table>";
	}else{
		return $html."<tr><td colspan='3' class='center'><i>None in the last 30 days</i></td></tr></table>";
	}
}
function getCustomerList($id){
	$result = queryMysql("SELECT DISTINCT member_id FROM orders WHERE business_id='$id'");
	$html = "
		<table class='report-table'>
		<thead><tr><th class='member-name-cell'>Name</th><th class='city-cell'>City</th><th class='state-cell'>State</th><th class='zipcode-cell'>Zipcode</th></tr></thead>
		<tfoot><tr><td colspan='6'>Total</td><td></td></tr></tfoot>
		<tbody>";
	if(mysql_num_rows($result)){
		for($i = 0; $i < mysql_num_rows($result); $i++){
			$row = mysql_fetch_row($result);
			$result1 = queryMysql("SELECT first_name,last_name,city,state,zipcode FROM members WHERE id='$row[0]'");
			$row1 = mysql_fetch_row($result1);
			$html .= "<tr><td class='member-name-cell'>$row1[1], $row1[0]</td><td class='city-cell'>$row1[2]</td><t class='state-cell'd>$row1[3]</td><td class='zipcode-cell'>$row1[4]</td></tr>";
		}
		$html .= "</tbody></table>";
		return $html;
	}else{
		$html .= "
		<tr class='error-row'><td colspan='4'><img src='img/error.png' alt='No customers available' class='report-error-img' draggable='false'/></td></tr>
		</tbody>
		</table>
		";
		return $html;
	}
}

function getRating($id){
	$result = queryMysql("SELECT rating FROM reviews WHERE business_id='$id'");
	if(mysql_num_rows($result)){
		$total = 0;
		$num = mysql_num_rows($result);
		for($i = 0; $i < $num; $i++){
			$row = mysql_fetch_row($result);
			$temp = $row[0];
			$total = $total + $temp;
		}
		$avg = number_format($total/$num,1,".",",");
		return $avg;
	}else{return "No ratings available";}
	
}

function get30DaysSales($id){
	$result = queryMysql("SELECT id FROM orders WHERE business_id='$id' AND date >= (CURDATE()-30)");
	if(mysql_num_rows($result)){
		$total = 0;
		for($i = 0; $i < mysql_num_rows($result); $i++){
			$row = mysql_fetch_row($result);
			$order_id = $row[0];
			$result1 = queryMysql("SELECT SUM(price_in_credits * quantity) As Total WHERE order_id='$order_id'");
			if(mysql_num_rows($result1)){
				$row1 = mysql_fetch_row($result1);
				$total = $total + $row1[0];
			}
		}
		return number_format($total,0,".",",");
	}else{return "No sales in last 30 days";}
}

function getDash($topProduct,$rating,$sales,$topCustomer){
	return "
	<div id='total-sales' class='dash-module'><span class='sum-title'><img src='img/chart_bar_up.png' width='50'/><span class='section-title'>Total Sales</span></span><span id='total-sales' class='section-content'>$sales</span></div>
	<div id='rating' class='dash-module'><span class='sum-title'><img src='img/heart.png'  width='50'/><span class='section-title'>Customer Rating</span></span><span id='customer-rating' class='section-content'>$rating</span></div>
	<div id='top-product' class='dash-module'><span class='sum-title'><img src='img/star.png'  width='50'/><span class='section-title'>Top Product</span></span><span id='top-product' class='section-content'>$topProduct</span></div>
	<div id='top-customer' class='dash-module'><span class='sum-title'><img src='img/smile.png' width='50'/><span class='section-title'>Top Customer</span><span id='top-customer' class='section-content'>$topCustomer</span></span></div>
	";
}

function getTopCustomer($id){
	$result = queryMysql("SELECT DISTINCT member_id FROM orders WHERE business_id='$id'"); //Get list of customers
	$customers = array();
	$totals = array();
	if(mysql_num_rows($result)){
		for($i = 0; $i < mysql_num_rows($result); $i++){//Cycle through each member found
			$row = mysql_fetch_row($result);
			$mem_id = $row[0];
			$customers[$i] = $mem_id;
			$mem_total = 0;
			$result1 = queryMysql("SELECT id FROM orders WHERE member_id='$mem_id'");
			for($a = 0; $a < mysql_num_rows($result1); $a++){//Cycle through each order
				$row1 = mysql_fetch_row($result1);
				$order_id = $row1[0];
				$result2 = queryMysql("SELECT SUM(price_in_credits * quantity) As Total FROM transactions WHERE orderID='$order_id'");
				$row2 = mysql_fetch_row($result2);
				$total = $row2[0];
				$mem_total = $mem_total + $total;
			}
			$totals[$i] = $mem_total;
		}
		$max = number_format(max($totals),0,'.',',');
		$key = array_search($max,$totals);
		$customer = $customers[$key];
		$result1 = queryMysql("SELECT first_name,last_name FROM members WHERE id='$customer'");
		$row = mysql_fetch_row($result1);
		return "
		<span id='top-customer-name'>$row[0] $row[1]</span>
		<span id='top-customer-total'>$max</span>
		";
	}else{
		return "No customers available";
	}
}
function getOrgSettings($id){
	$result = queryMysql("SELECT * FROM businesses WHERE id='$id'");
	$row = mysql_fetch_row($result);
	$email = $row[3];
	$street = $row[4];
	$city = $row[5];
	$state = $row[6];
	$zipcode = $row[7];
	$mobilePhone = $row[8];
	$landPhone = $row[9];
	$fax = $row[10];
	$type = $row[11];
	
	return "
	<h1>Information</h1>
	<ul id='profile-list'>
		<li class='profile-label'>Address</li><li class='profile-info'>$street, $city, $state, $zipcode</li><li class='profile-edit'><a href='#' class='hmote-btn'>Edit</a></li>
		<li class='profile-label'>Email</li><li class='profile-info'>$email</li><li class='profile-edit'><a href='#' class='hmote-btn'>Edit</a></li>
		<li class='profile-label'>Mobile</li><li class='profile-info'>$mobilePhone</li><li class='profile-edit'><a href='#' class='hmote-btn'>Edit</a></li>
		<li class='profile-label'>Home</li><li class='profile-info'>$landPhone</li><li class='profile-edit'><a href='#' class='hmote-btn'>Edit</a></li>
		<li class='profile-label'>Fax</li><li class='profile-info'>$fax</li><li class='profile-edit'><a href='#' class='hmote-btn'>Edit</a></li>
		<li class='profile-label'>Type</li><li class='profile-info'>$type</li><li class='profile-edit'><a href='#' class='hmote-btn'>Edit</a></li>
	</ul>
	";
}


function getSalesList($id){
	$result = queryMysql("SELECT id,date FROM orders WHERE business_id='$id' ORDER BY date ASC");
	$html = "
	<table class='report-table'>
	<thead><tr><th></th><th class='date-cell'>Date</th><th class='city-cell'>City</th><th class='state-cell'>State</th><th class='id-cell'>Order ID</th><th class='quantity-cell'>#</th><th class='total-cell'>Total</th><th style='width:20px;'></th></tr></thead>
	<tfoot><tr><td colspan='6'>Total</td><td></td></tr></tfoot>
	";
	if(mysql_num_rows($result)){
		$html .= "
		<tbody>";
		$html .= "
		</tbody>
		</table>
		";
	}else{
		$html .= "
		<tbody>
		<tr class='error-row'><td colspan='7'><img src='img/no_sales.png' alt='No sales reported' class='report-error-img' draggable='false'/></td></tr>
		</tbody>
		</table>
		";
		return $html;
	}
}

function getStoreFront($id){
	$result = queryMysql("SELECT name,facebook,twitter,linkedin,website,uploadedPhoto,description FROM businesses WHERE id='$id'");
	$row = mysql_fetch_row($result);
	$img = "";
	if($row[5]){
		$img = "users/businesses/$id/img/$id.png";
	}else{
		$img = "img/credit_card.png";
	}
	return "
	<div id='store-banner'><img src='$img' height='100'/><span id='business-name'>$row[0]</span></div>
	<div id='left-storefront'>
		<p id='business-description'>This is a description of the business.</p>
		<span id='storefront-rating'></span>
		<div id='control-panel'>
		<ul>
			<li class='storefront-btn storefront-btn-active'><img src='img/tag.png' height='25' /><span>Products</span><img src='img/control_panel_arrow.png' class='control-panel-arrow' id='cp-products-btn' height='25'/></li>
			<li class='storefront-btn'><img src='img/camera.png' height='25'/><span>Images</span><img src='img/control_panel_arrow.png' class='control-panel-arrow' id='cp-gallery-btn' height='25'/></li>
			<li class='storefront-btn'><img src='img/reviews.png' height='25' /><span>Reviews</span><img src='img/control_panel_arrow.png' class='control-panel-arrow' id='cp-reviews-btn' height='25'/></li>
			<li class='storefront-btn'><img src='img/facebook.png' height='25' /><span>Facebook</span><img src='img/control_panel_arrow.png' class='control-panel-arrow' id='cp-facebook-btn' height='25'/></li>
			<li class='storefront-btn'><img src='img/twitter.png' height='25' /><span>Twitter</span><img src='img/control_panel_arrow.png' class='control-panel-arrow' id='cp-twitter-btn' height='25'/></li>
			<li class='storefront-btn'><img src='img/linkedin.png' height='25' /><span>LinkedIn</span><img src='img/control_panel_arrow.png' class='control-panel-arrow' id='cp-linkedin-btn' height='25'/></li>
			<li class='storefront-btn'><img src='img/globe.png' height='25' /><span>Website</span><img src='img/control_panel_arrow' class='control-panel-arrow' id='cp-website-btn' height='25'/></li>
		</ul>
		</div>
	</div>
	<div class='gradient-border-blue'></div>
	<div id='right-storefront'>
			<div class='no-products-error'><span class='error-text'>No products are available. Click the button to start selling!</span><span class='hmote-menu-btn plus-tag-icon'></span></div>
	</div>
	";
}

function getStateHTML(){
	$html = "
		<option value='AZ' selected='selected'>AZ</option>
		<option value='AR'>AR</option>
		<option value='AK'>AK</option>
		<option value='AL'>AL</option>
		<option value='CO'>CO</option>
		<option value='CA'>CA</option>
		<option value='CT'>CT</option>
		<option value='DE'>DE</option>
		<option value='DC'>DC</option>
		<option value='FL'>FL</option>
		<option value='GA'>GA</option>
		<option value='HI'>HI</option>
		<option value='ID'>ID</option>
		<option value='IL'>IL</option>
		<option value='IN'>IN</option>
		<option value='IA'>IA</option>
		<option value='KS'>KS</option>
		<option value='KY'>KY</option>
		<option value='LA'>LA</option>
		<option value='ME'>ME</option>
		<option value='MD'>MD</option>
		<option value='MI'>MI</option>
		<option value='MD'>MD</option>
		<option value='MI'>MI</option>
		<option value='MT'>MT</option>
		<option value='MA'>MA</option>
		<option value='MN'>MN</option>
		<option value='MS'>MS</option>
		<option value='MO'>MO</option>
		<option value='NE'>NE</option>
		<option value='NV'>NV</option>
		<option value='NH'>NH</option>
		<option value='NC'>NC</option>
		<option value='ND'>ND</option>
		<option value='NJ'>NJ</option>
		<option value='NM'>NM</option>
		<option value='NY'>NY</option>
		<option value='OH'>OH</option>
		<option value='OK'>OK</option>
		<option value='OR'>OR</option>
		<option value='PA'>PA</option>
		<option value='RI'>RI</option>
		<option value='SC'>SC</option>
		<option value='SD'>SD</option>
		<option value='TN'>TN</option>
		<option value='TX'>TX</option>
		<option value='UT'>UT</option>
		<option value='VT'>VT</option>
		<option value='VA'>VA</option>
		<option value='WA'>WA</option>
		<option value='WV'>WV</option>
		<option value='WS'>WS</option>
		<option value='WY'>WY</option>
		";
		return $html;
	
}
?>
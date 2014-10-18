// JavaScript Document
$(document).ready(function(){
	$("#start-pane.register-pane").show();
	$(".biz-register").click(function(){
		$("#business-menu li").removeClass('active-menu-item');
		$(this).parent().addClass('active-menu-item');
		var id = $(this).attr('id');
		var temp = id.slice(0,-4);
		$(".register-pane").hide();
		$('#'+temp+"-pane").show();
	});
	
	$("input[name=register-business-name]").keyup(function(){
		if($(this).val().trim() != ""){
			$("#verify-business").html("<img src='img/pro-load.gif' height='50' alt='Loading...' />");
			var name = $(this).val();
			$.ajax({
				type: 'post',
				url: 'scripts/post.php',
				data: {request:'verify_business_name',name:name},
				dataType: 'json',
				success: function(response){
					if(response.status == 'success'){
						$("#verify-business").html("<img src='img/ok-green.png' height='50' alt='Loading...' />");
						$("#start-pane .error").text("");
					}else{
						$("#verify-business").html("<img src='img/close.png' height='50' alt='Loading...' />");
						$("#start-pane .error").text(response.message);
					}
				}
			});
		}else{
			$("#verify-business").html("");
			$("#start-pane .error").text("");
		}
	});
	
	$("#confirm-address").click(function(){
		var emptyInputs = $(this).parent().find('.address-input').filter(function() { return $(this).val() == ""; });
		if (emptyInputs.length) {
		}else{
			codeAddress();
		}
	});
});
/************* GOOGLE MAPS LOADER***************/
/***********************************************/
var geocoder;
var map;
function initialize() {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(-34.397, 150.644);
    var mapOptions = {
      zoom: 8,
      center: latlng
    }
    map = new google.maps.Map(document.getElementById('preview-map'), mapOptions);
  }
google.maps.event.addDomListener(window, 'load', initialize);
function codeAddress() {
 var street = $("input[name=street]").val().trim();
 street = street.replace(/ /g,"+");
 var city = $("input[name=city]").val();
 var state = $("#state").val();
var address = street+",+"+city+",+"+state;
geocoder.geocode( { 'address': address}, function(results, status) {
  if (status == google.maps.GeocoderStatus.OK) {
	map.setCenter(results[0].geometry.location);
	var marker = new google.maps.Marker({
		map: map,
		position: results[0].geometry.location
	});
  } else {
	alert("Geocode was not successful for the following reason: " + status);
  }
});
}
  /********************************************/
  /********************************************/
function getOrgContent(el){
	var id = $(el).attr("id");
	$("#business-menu li").removeClass('active-menu-item');
	$(el).parent().addClass('active-menu-item');
	var type = "";
	var status = $("#status").val();
	var biz_id = $("#biz_id").val();
	switch(id){
		case "summary-btn": type = "summary";break;
		case "settings-btn": type = "settings";break;
		case "sales-btn": type = "sales";break;
		case "customers-btn": type = "customers";break;
		case "storefront-btn": type = "storefront";break;
	}
	$.ajax({
		type: 'POST',
		url: 'scripts/post.php',
		data: {request:'get_org_content',type:type,status:status,id:biz_id},
		dataType: 'json',
		success: function(response){
			$("#right-pane").html(response.content);
			$(".storefront-btn").hover(function(){
				$(this).find(".control-panel-arrow").css("opacity","1.0");
			},function(){
				$(this).find(".control-panel-arrow").css("opacity","0.0");
			});
			$(".storefront-btn").click(function(){
				$(".storefront-btn").removeClass('storefront-btn-active');
				$(this).addClass('storefront-btn-active');
			});
		}
	});
}
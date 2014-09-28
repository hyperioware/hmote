// JavaScript Document
$(document).ready(function(){
		
});
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
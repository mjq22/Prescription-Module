jQuery(document).ready(function ($) {
   $('#LoadLogin').click(function(){
      $('#is_reserved_login').val('');
   }); 
});
function ajaxSubmit(){
	
	var user_name = jQuery("#login-form #username").val();
	var pass_word = jQuery("#login-form #password").val();
	var is_reserved = jQuery("#login-form #is_reserved_login").val();
	
	//jQuery("#login-form p.status").show().text(ajax_login_object.loadingmessage);
	
	jQuery(".sp-prescription-loader").fadeIn(200);
   
	return jQuery.ajax({
		type:"POST",
		dataType:"json",
		url:ajax_login_object.ajaxurl,
		data:{
			action:"ajaxlogin",
			username:user_name,
			password:pass_word,
			is_reservation_check:is_reserved,
			security:jQuery("#login-form #security").val()
		},
        success:function(e){
			if(jQuery("#login-form .status").length) {
				jQuery("#login-form .status").html(e.message);  
			}
			else {
				jQuery("#login-form").append('<p class="status" style="padding: 2px 10px;font-size: 12px;margin-top: 10px;border: 1px solid #777;">' + e.message + '</p>');             
            }
			if(e.loggedin) {
				jQuery(".component_data #inline-1").html('<a href="#" class="switch-presc">Change Prescription Method</a><div class="sp-prescription-loader"><img src="/wp-content/uploads/2020/05/loader.gif"></div><div class="saved-presc"><div class="wrapper-popup">' + e.prescription + '</div></div>');
			}
			jQuery(".sp-prescription-loader").fadeOut(500);
			jQuery(".switch-presc").on("click", function(e) {
				e.preventDefault();
				setTimeout(function() {
					jQuery("body #component_" + _step_id).find(".component_options").show();
				}, 200);
				jQuery("body #component_" + _step_id).find(".component_content").hide();
			});
		}
	}); 	
}	
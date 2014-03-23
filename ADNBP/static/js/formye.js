/*
* Formy JS library
* by Luiszuno.com
*/
 
 jQuery(document).ready(function($) {

	// Hide messages 
	$("#formy-success").hide();
	$("#formy-error").hide();
	
	// on submit...
	$("#formy #submit").click(function() {
		
		// Required fields:
		
		//name
		var name = $("#name").val();
		if(name == "" || name == "Name"){
			$("#name").focus();
			$("#formy-error").fadeIn().text("Name required");
			return false;
		}
		
		// email
		var email = $("#email").val();
		if(email == "" || email == "Email"){
			$("#email").focus();
			$("#error").fadeIn().text("Email required");
			return false;
		}
				
		// comments
		var comments = $("#comments").val();
		if(comments == "" || comments == "How can i help you?"){
			$("#comments").focus();
			return false;
		}
		
		// send mail php
		var sendMailUrl = $("#sendMailUrl").val();
		
		// Retrieve values for to, from & subject at the form
		var to = $("#to").val();
		var from = $("#from").val();
		var subject = $("#subject").val();
		
		// Create the data string
		var dataString = 'name='+ name
						+ '&email=' + email        
						+ '&comments=' + comments
						+ '&to=' + to
						+ '&from=' + from
						+ '&subject=' + subject;						         
		// ajax 
		$.ajax({
			type:"POST",
			url: sendMailUrl,
			data: dataString,
			success: success()
		});
	});  
		
		
	// On success...
	 function success(){
	 	$("#formy-success").fadeIn().text("Message send. Thank you!");
	 	$("#formy-error").hide();
	 	$("#formy").fadeOut();
	 	return false;
	 }
	
    return false;
});



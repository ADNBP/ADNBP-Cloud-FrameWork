/*
* ADNBP Cloud Services Auth JS 
* by hector lópez
*/
// The HTML requires 
// Input user with id='CloudServiceAutCloudServiceInputUserhButton'
// Input password with id='CloudServiceInputPassword'
// Input <button data-sending='{text when sending}' id='CloudServiceAuthButton'>{Text}</button>


$(document).ready(function() {
	
	// Action to send Auth info to Cloud Service FrameWork
	$('#CloudServiceAuthButton').on('click', function(){
		var textButton = $('#CloudServiceAuthButton').text();
		
		if(!formInputValidate('email',$('#CloudServiceInputUser').val())) {
			alert('User incorrect. '+$('#CloudServiceInputUser').val()+' has to be a valid email.');
		} else if($('#CloudServiceInputPassword').val().length < 2 ) {
			alert('Password has less than 2 chars.');
		} else {
			$('#CloudServiceAuthButton').text( $('#CloudServiceAuthButton').attr('data-sending'));
			$.post('/api/auth'
			,{  "user":$('#CloudServiceInputUser').val()
			    ,"password": $('#CloudServiceInputPassword').val()
			    ,"clientfingerprint": 'improving'
			 } 
			,function(ret) {
				if(ret.success) {
					document.location = document.location.href;
				} else {
					alert(ret.error.message);
				}
				$('#CloudServiceAuthButton').text( textButton);
		   }).fail(function(err) {
		   		alert(err.responseJSON.error.message);
		   		if(err.status==404) {
		   			$('#CloudServiceInputUser').focus();		   			
		   		}else if(err.status==401) {
		   			$('#CloudServiceInputPassword').focus();
		   			$('#CloudServiceInputPassword').val('');
		   		}
				$('#CloudServiceAuthButton').text(textButton);
        	});			
		}
	});
});

/*
function auth(user,password) {

}
*/


/*
* ADNBP Cloud Services Auth JS 
* by hector lópez
*/
// The HTML requires 
// Input user with id='CloudServiceAutCloudServiceInputUserhButton'
// Input password with id='CloudServiceInputPassword'
// Input <button data-sending='{text when sending}' id='CloudServiceAuthButton'>{Text}</button>


$(document).ready(function() {
	
	$('#CloudServiceInputUser').focus();
	// Action to send Auth info to Cloud Service FrameWork
	$('#CloudServiceInputPassword').on('keypress', function(e){
		if(e.which == 13) {
			$('#CloudServiceAuthButton').trigger('click');
    	}
	});
	
	$('#CloudServiceAuthButton').on('click', function(){
		var textButton = $('#CloudServiceAuthButton').text();
		if(!formInputValidate('email',$('#CloudServiceInputUser').val())) {
			$('#CloudServiceMsg').text('User incorrect. '+$('#CloudServiceInputUser').val()+' has to be a valid email.');
			$('#CloudServiceInputUser').focus();
		} else if($('#CloudServiceInputPassword').val().length < 2 ) {
			$('#CloudServiceMsg').text('Password has less than 2 chars.');
			$('#CloudServiceInputPassword').focus();
		} else {
			$('#CloudServiceAuthButton').text( $('#CloudServiceAuthButton').attr('data-sending'));
			$.post('/api/auth'
			,{  "user":$('#CloudServiceInputUser').val()
			    ,"password": $('#CloudServiceInputPassword').val()
			    ,"clientfingerprint": 'improving'
			 } 
			,function(ret) {
				if(ret.success) {
					$('#CloudServiceAuthButton').text( 'loading.. wait');
					document.location = '/en/portal';
				} else {
					$('#CloudServiceMsg').text(ret.error.message);
				}
				$('#CloudServiceAuthButton').text( textButton);
		   }).fail(function(err) {
		   		$('#CloudServiceMsg').text(err.responseJSON.error.message);
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


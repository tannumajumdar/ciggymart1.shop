// JavaScript Document
$(document).ready(function(){

	
	//////////////////////////////////
	//  form validation
	////////////////////////////////////
    $("#changePassword ").validate({
	  rules: 
		{   
		  old_pwd: 
			{ 
				required: true,
				equalTo: "#password"
			},
			new_pwd:
			{
				required: true,
				
			},
		   con_pwd:
			{
				required: true,
				equalTo: "#new_pwd"
			},
		   
		},
		messages:
		{
			old_pwd: 
			{ 				
				equalTo: "Incorrect Old Password"
			},
			con_pwd:
			{				
				equalTo: "Password does not Match"
			},
		}
	});// eof validation
	
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$('#submit').click(function(){
	  if ($("#changePassword").valid())
		{	
		
						
			var x;
			$.ajax({
			   type: "POST",
			   url: "pwd_curd.php",
			   data: {type: "changePassword", new_pwd:$('#new_pwd').val()},
			   async: false,
			   success: function(data){ //alert(data);
			   
			   
				   x=data;
			   },
			  
			});//eof ajax
		
			if(x==1)
			{
				location.reload();
			}
			
		}
	});	
	

		
	
});// JavaScript Document
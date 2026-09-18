// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add Hub form validation
	////////////////////////////////////
	$("#insertOperator").validate({
		rules: 
		{ 
			operator_name: 
			{ 
				required: true,
			},
			state_code: 
			{ 
				required: true,
			},
			state_name: 
			{ 
				required: true,
			},
			operator_contact:
			{
				required: true,
				minlength: 10,
				maxlength: 11
			},
			email:
			{
				required: true,
				emailExistValid:true,
			},
			password:
			{
				required: true,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Edit Hub form validation
	////////////////////////////////////
	$("#editOperator").validate({
		rules: 
		{ 
			operator_name: 
			{ 
				required: true,
			},
			state_code: 
			{ 
				required: true,
			},
			state_name: 
			{ 
				required: true,
			},
			operator_contact:
			{
				required: true,
			},
			email:
			{
				required: true,
				emailExistEditValid:true,
			},
			password:
			{
				required: true,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('emailExistValid', function(val, element)
	{
		$.ajax({
				 url:"operator_curd.php",
				 type: "POST",
				 data: {type:"emailExist",email:$("#email").val()},
				 async:false,
				 dataType : 'json',
				 error : function(jqXHR, textStatus, errorThrown)
				 {
					//alert(errorThrown);
					alert('some error occured while submitting the form');
				 },
				 success : function(response,  textStatus,  jqXHR )
				 {
					isSuccess = response ;
				 }
		});//eof ajax
		return isSuccess ;				
	}, 'Email Already in Use, Please Choose Another Email Id For This Operator.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('emailExistEditValid', function(val, element)
	{
		$.ajax({
				 url:"operator_curd.php",
				 type: "POST",
				 data: {type:"emailEditExist",operator_id:$("#operator_id").val(),email:$("#email").val()},
				 async:false,
				 dataType : 'json',
				 error : function(jqXHR, textStatus, errorThrown)
				 {
					//alert(errorThrown);
					alert('some error occured while submitting the form');
				 },
				 success : function(response,  textStatus,  jqXHR )
				 {
					isSuccess = response ;
				 }
		});//eof ajax
		return isSuccess ;				
	}, 'Email Already in Use, Please Choose Another Email Id For This Operator.');
	
	
	////////////////////////////////////////////////////
	// Update status on click of Block or Unblock button
	////////////////////////////////////////////////////
	$(".status").click(function(){
		status = $(this).attr('id');
		
		$.ajax({
			type:"POST",
			url: "operator_curd.php",
			data: {type:'updateStatus', status:status},
			success:function(data){
				x=data;
				if(x==1){
					window.location.replace("index.php");
				}
				else if(x==2){
					window.location.replace("index.php");
				}
			}
		})//eof ajax
	});
	
	
	////////////////////////////////////
	// on click of state code drop down
	////////////////////////////////////	
	$(document).on("click","#state_code, li", function(){
					
		var formdata = new FormData();
		formdata.append('type', "getStateName");
		formdata.append('state_code', $("#state_code").val());

		var x;
		$.ajax({
		   type: "POST",
		   url: "operator_curd.php",
		   data:formdata,
		   success: function(data){ //alert(data);
			   $('#statename').html(data);
		   },
		   cache: false,
		   contentType: false,
		   processData: false
		});//eof ajax
		
	});
	
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$('#submit').click(function(){
		flag=$("#insertOperator").valid();
		
		if (flag==true)
		{			
			var formdata = new FormData();
			formdata.append('type', "addOperator");
			formdata.append('operator_name', $("#operator_name").val());
			formdata.append('o_address', $("#o_address").val());
			formdata.append('state_id', $("#state_id").val());
			formdata.append('operator_contact', $("#operator_contact").val());
			formdata.append('email', $("#email").val());
			formdata.append('password', $("#password").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "operator_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x==1)
					{
						window.location.replace("index.php");
					}
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
			
		}// eof if condition
		
	});
	
	
	//////////////////////////////////
	// on click of update button
	//////////////////////////////////
	$("#edit").click(function(){
		
		flag=$("#editOperator").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "editoperator");
			formdata.append('operator_id', $("#operator_id").val());
			formdata.append('operator_name', $("#operator_name").val());
			formdata.append('o_address', $("#o_address").val());
			formdata.append('state_id', $("#state_id").val());
			formdata.append('operator_contact', $("#operator_contact").val());
			formdata.append('email', $("#email").val());
			formdata.append('password', $("#password").val());
	  
			 var x;
			 $.ajax({
			   type: "POST",
			   url: "operator_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x==0)
					{
						window.location.replace("index.php");				
					}
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});
			
			
		}//eof if condition
	});
	
	
	//////////////////////////////////
	// on click of delete button
	//////////////////////////////////
	$(".delete").click(function(){
		
		var didConfirm = confirm("Are you sure?");
	    if (didConfirm == true) {
			var Hub_Id=$(this).attr("id");
			
			$.ajax({
				url:"hub_curd.php",
				type: "POST",
				data: {type:"delete",Hub_Id:Hub_Id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});
	
});//eof ready function
// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add Bank form validation
	////////////////////////////////////
	$("#insertBank").validate({
		rules: 
		{ 
			bank_name: 
			{ 
				required: true,
			},
			account_name: 
			{ 
				required: true,
			},
			account_no: 
			{ 
				required: true,
				number:true,
				accountNoExis:true,
			},
			branch_address:
			{
				required: true,
			},
			IFSC_code:
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
	$("#editBank").validate({
		rules: 
		{ 
			bank_name: 
			{ 
				required: true,
			},
			account_name: 
			{ 
				required: true,
			},
			account_no: 
			{ 
				required: true,
				number:true,
				accountNoEditExist:true,
			},
			branch_address:
			{
				required: true,
			},
			IFSC_code:
			{
				required: true,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('accountNoExis', function(val, element)
	{		
		$.ajax({
			 url:"bank_curd.php",
			 type: "POST",
			 data: {type:"accountNoExis", account_no: $('#account_no').val()},
			 async:false,
			 success:function(data){ //alert(data);
				 isSuccess=(data==0)?true:false;
			 }
			 
		});//eof ajax
		return isSuccess ;				
	}, 'Account No Already Exist. Please Use Deifferent Account No.');
	
	
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('accountNoEditExist', function(val, element)
	{		
		$.ajax({
			 url:"bank_curd.php",
			 type: "POST",
			 data: {type:"accountNoEditExist", bank_id: $('#bank_id').val(), account_no: $('#account_no').val()},
			 async:false,
			 success:function(data){ //alert(data);
				 isSuccess=(data==0)?true:false;
			 }
			 
		});//eof ajax
		return isSuccess ;				
	}, 'Account No Already Exist. Please Use Deifferent Account No.');
	
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$('#submit').click(function(){
		flag=$("#insertBank").valid();
		
		if (flag==true)
		{			
			var formdata = new FormData();
			formdata.append('type', "addBank");
			formdata.append('bank_name', $("#bank_name").val());
			formdata.append('account_name', $("#account_name").val());
			formdata.append('account_no', $("#account_no").val());
			formdata.append('branch_address', $("#branch_address").val());
			formdata.append('IFSC_code', $("#IFSC_code").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "bank_curd.php",
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
		
		flag=$("#editBank").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "editbank");
			formdata.append('bank_id', $("#bank_id").val());
			formdata.append('bank_name', $("#bank_name").val());
			formdata.append('account_name', $("#account_name").val());
			formdata.append('account_no', $("#account_no").val());
			formdata.append('branch_address', $("#branch_address").val());
			formdata.append('IFSC_code', $("#IFSC_code").val());
	  
			 var x;
			 $.ajax({
			   type: "POST",
			   url: "bank_curd.php",
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
			var Bank_Id=$(this).attr("id");
			
			$.ajax({
				url:"bank_curd.php",
				type: "POST",
				data: {type:"delete",Bank_Id:Bank_Id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});
	
});//eof ready function
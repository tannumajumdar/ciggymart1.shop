// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add Destination form validation
	////////////////////////////////////
	$("#insertDest").validate({
		rules: 
		{ 
			dest_code: 
			{ 
				required: true,
				maxlength: 4,
				DestCodeExist:true
			},
			dest_name: 
			{ 
				required: true,
				DestNameExist:true
			},
			state_code: 
			{ 
				required: true,
			},
			state_name: 
			{ 
				required: true,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Edit Destination form validation
	////////////////////////////////////
	$("#editDest").validate({
		rules: 
		{ 
			dest_code: 
			{ 
				required: true,
				DestCodeEditExist:true,
				maxlength: 4
			},
			dest_name: 
			{ 
				required: true,
				DestNameEditExist:true,
			},
			state_code: 
			{ 
				required: true,
			},
			state_name: 
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
	$.validator.addMethod('DestCodeExist', function(val, element)
	{
		dest_code=$("#dest_code").val();

		$.ajax({
				 url:"destination_curd.php",
				 type: "POST",
				 data: {type:"validate1",dest_code:dest_code},
				 async:false,
				 dataType : 'json',
				 error : function(jqXHR, textStatus, errorThrown)
				 {
					alert('some error occured while submitting the form');
				 },
				 success : function(response,  textStatus,  jqXHR )
				 {
					isSuccess = response ;
				 }
		});//eof ajax
		return isSuccess ;				
	}, 'Destination Code Already exists.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('DestNameExist', function(val, element)
	{
		dest_name=$("#dest_name").val();

		$.ajax({
				 url:"destination_curd.php",
				 type: "POST",
				 data: {type:"validate2",dest_name:dest_name},
				 async:false,
				 dataType : 'json',
				 error : function(jqXHR, textStatus, errorThrown)
				 {
					alert('some error occured while submitting the form');
				 },
				 success : function(response,  textStatus,  jqXHR )
				 {
					isSuccess = response ;
				 }
		});//eof ajax
		return isSuccess ;				
	}, 'Destination Name Already exists.');
	
	
	/////////////////////////////////////////////////////////////////
	// Method to check is the data already exist or not for Edit Page
	/////////////////////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('DestCodeEditExist', function(val, element)
	{
		dest_code=$("#dest_code").val();
		dest_id=$("#id").val();

		$.ajax({
				 url:"destination_curd.php",
				 type: "POST",
				 data: {type:"destCodeEditValid",dest_code:dest_code, dest_id:dest_id},
				 async:false,
				 dataType : 'json',
				 error : function(jqXHR, textStatus, errorThrown)
				 {
					alert('some error occured while submitting the form');
				 },
				 success : function(response,  textStatus,  jqXHR )
				 {
					isSuccess = response ;
				 }
		});//eof ajax
		return isSuccess ;				
	}, 'Destination Code Already exists.');
	
	
	/////////////////////////////////////////////////////////////////
	// Method to check is the data already exist or not for EDIT page
	/////////////////////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('DestNameEditExist', function(val, element)
	{
		dest_name=$("#dest_name").val();
		dest_id=$("#id").val();

		$.ajax({
				 url:"destination_curd.php",
				 type: "POST",
				 data: {type:"destNameEditValid",dest_name:dest_name, dest_id:dest_id},
				 async:false,
				 dataType : 'json',
				 error : function(jqXHR, textStatus, errorThrown)
				 {
					alert('some error occured while submitting the form');
				 },
				 success : function(response,  textStatus,  jqXHR )
				 {
					isSuccess = response ;
				 }
		});//eof ajax
		return isSuccess ;				
	}, 'Destination Name Already exists.');
	
	
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
		   url: "destination_curd.php",
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
		flag=$("#insertDest").valid();
		
		if (flag==true)
		{	
			var formdata = new FormData();
			formdata.append('type', "addDestination");
			formdata.append('dest_code', $("#dest_code").val());
			formdata.append('dest_name', $("#dest_name").val());
			formdata.append('state_id', $("#state_id").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "destination_curd.php",
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
		
		flag=$("#editDest").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "editdestination");
			formdata.append('id', $("#id").val());
			formdata.append('dest_code', $("#dest_code").val());
			formdata.append('dest_name', $("#dest_name").val());
			formdata.append('state_id', $("#state_id").val());
	  
			 var x;
			 $.ajax({
			   type: "POST",
			   url: "destination_curd.php",
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
			});
			
			
		}//eof if condition
	});
	
	
	//////////////////////////////////
	// on click of delete button
	//////////////////////////////////
	$(".delete").click(function(){
		
		var didConfirm = confirm("Are you sure?");
	    if (didConfirm == true) {
			var Destination_Id=$(this).attr("id");
			
			$.ajax({
				url:"destination_curd.php",
				type: "POST",
				data: {type:"delete",Destination_Id:Destination_Id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});
	
});// eof ready function
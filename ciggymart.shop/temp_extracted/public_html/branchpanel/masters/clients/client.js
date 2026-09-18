// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add Customer form validation
	////////////////////////////////////
	$("#insertClient").validate({
		rules: 
		{ 
			client_code: 
			{ 
				required: true,
				ClientCodeExist:true,
			},
			client_name: 
			{ 
				required: true,
				ClientExist:true,
			},			
			address:
			{
				required: true,
			},			
			dest_code:
			{
				required: true,
			},
			Destination_Name:
			{
				required: true,
			},
			contact_no:
			{
				required: true,
				minlength: 10,
				maxlength: 11
			},
			fuelSurcharge:
			{
				required: true,
			},
			email:
			{
				required: true,
				email:true
			},			
			password: 
			{ 
				required: true,
				minlength: 6,
			}
			
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Add Customer form validation
	////////////////////////////////////
	$("#editClient").validate({
		rules: 
		{ 
			client_code: 
			{ 
				required: true,
				EditClientCodeExist:true,
			},
			client_name: 
			{ 
				required: true,
				ClientEditExist:true,
			},			
			address:
			{
				required: true,
			},			
			dest_code:
			{
				required: true,
			},
			Destination_Name:
			{
				required: true,
			},
			contact_no:
			{
				required: true,
				minlength: 10,
				maxlength: 11
			},
			fuelSurcharge:
			{
				required: true,
			},
			email:
			{
				required: true,
				email:true
			},			
			password: 
			{ 
				required: true,
				minlength: 6,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	///////////////////////////////////
	// Add Customer form validation
	////////////////////////////////////
	$("#searchClientWise").validate({
		rules: 
		{ 
			client_name: 
			{ 
				required: true,
				SearchclientNameExist: true,
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
	$.validator.addMethod('ClientExist', function(val, element)
	{
		client_name=$("#client_name").val();

		$.ajax({
				 url:"client_curd.php",
				 type: "POST",
				 data: {type:"clientNameExist",client_name:client_name},
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
	}, 'Client Name Already Exists.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('SearchclientNameExist', function(val, element)
	{
		client_name=$("#client_name").val();

		$.ajax({
				 url:"client_curd.php",
				 type: "POST",
				 data: {type:"SearchclientNameExist",client_name:client_name},
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
	}, 'Client Name Not Exists.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	$.validator.addMethod('ClientCodeExist', function(val, element)
	{
		client_code=$("#client_code").val();

		$.ajax({
				 url:"client_curd.php",
				 type: "POST",
				 data: {type:"clientCodeExist",client_code:client_code},
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
	}, 'Client Code Already Exists.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	$.validator.addMethod('ClientEditExist', function(val, element)
	{
		$.ajax({
				 url:"client_curd.php",
				 type: "POST",
				 data: {type:"clientNameEditExist",client_id:$("#client_id").val(),client_name:$("#client_name").val()},
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
	}, 'Client Name Already Exists.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	$.validator.addMethod('EditClientCodeExist', function(val, element)
	{
		$.ajax({
				 url:"client_curd.php",
				 type: "POST",
				 data: {type:"EditClientCodeExist",client_id:$("#client_id").val(),client_code:$("#client_code").val()},
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
	}, 'Client Code Already Exists.');
	
	
	//////////////////////////////////////////
	// on change of destination code drop down
	//////////////////////////////////////////	
	$(document).on("click","#dest_code, li", function(){
					
		var formdata = new FormData();
		formdata.append('type', "getDestinationName");
		formdata.append('dest_code', $("#dest_code").val());

		var x;
		$.ajax({
		   type: "POST",
		   url: "client_curd.php",
		   data:formdata,
		   success: function(data){ //alert(data);
			   $('#destinationName').html(data);
		   },
		   cache: false,
		   contentType: false,
		   processData: false
		});//eof ajax
		
	});
	
	
	//////////////////////////////////
	// on click of search button
	//////////////////////////////////
	$(document).on('click', '#search', function() {
		
		flag=$("#searchClientWise").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "search");
			formdata.append('client_name', $("#client_name").val());
			
			$.ajax({
			   type: "POST",
			   url: "client_curd.php",
			   data: formdata,
			   success: function(data){ //alert(data);
				   $("#detail").html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax			
		}
		
	});
	
	//////////////////////////////////////////
	// check if client is within state or not
	//////////////////////////////////////////
	$(document).on('click', '#gstwithinstate', function() {
		
		if($(this).is(":checked")) {							
			$(this).val("1");
		}
		else{
			$(this).val("0");
		}
	});
	
	//////////////////////////////////////////////////
	// check if billing address same as address or not
	//////////////////////////////////////////////////
	$(document).on('click', '#billingAddChckBx', function() {
		
		if($(this).is(":checked")) {							
			$("#billingAdd").val($("#address").val());
		}
		else{
			$("#billingAdd").val("");
		}
	});
	
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$(document).on('click', '#submit', function() {
	
		if($("#gstwithinstate").val()=='on'){
			$("#gstwithinstate").val('0');
		}
		
		flag=$("#insertClient").valid();
		
		if (flag==true)
		{			
			var formdata = new FormData();
			formdata.append('type', "addClient");
			formdata.append('client_code', $("#client_code").val());
			formdata.append('client_name', $("#client_name").val());
			formdata.append('dest_id', $("#dest_id").val());
			formdata.append('address', $("#address").val());
			formdata.append('billingAdd', $("#billingAdd").val());
			formdata.append('contact_no', $("#contact_no").val());
			formdata.append('withinState', $("#gstwithinstate").val());
			formdata.append('gstin', $("#gstin").val());
			formdata.append('panNo', $("#panNo").val());
			formdata.append('insurance', $("#insurance").val());
			formdata.append('fuelSurcharge', $("#fuelSurcharge").val());
			formdata.append('email', $("#email").val());
			formdata.append('password', $("#password").val());
				
			var x;
			$.ajax({
			   type: "POST",
			   url: "client_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x!=0)
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
	// on click of edit button
	//////////////////////////////////
	$(document).on('click', '#edit', function() {
		
		if($("#gstwithinstate").val()=='on'){
			$("#gstwithinstate").val('0');
		}
	
		flag=$("#editClient").valid();
		
		if (flag==true)
		{	
			var formdata = new FormData();
			formdata.append('type', "editClient");
			formdata.append('client_id', $("#client_id").val());
			formdata.append('client_code', $("#client_code").val());
			formdata.append('client_name', $("#client_name").val());
			formdata.append('dest_id', $("#dest_id").val());
			formdata.append('address', $("#address").val());
			formdata.append('billingAdd', $("#billingAdd").val());
			formdata.append('contact_no', $("#contact_no").val());
			formdata.append('withinState', $("#gstwithinstate").val());
			formdata.append('gstin', $("#gstin").val());
			formdata.append('panNo', $("#panNo").val());
			formdata.append('insurance', $("#insurance").val());
			formdata.append('fuelSurcharge', $("#fuelSurcharge").val());
			formdata.append('email', $("#email").val());
			formdata.append('password', $("#password").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "client_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x!=0)
					{
						window.location.href="index.php";
					}
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		}// eof if condition
	});//eof edit function
	
	
	//////////////////////////////////
	// on click of delete button
	//////////////////////////////////
	$(document).on('click', '.delete', function() {
		
		var didConfirm = confirm("Are you sure?");
	    if (didConfirm == true) {
			var id=$(this).attr("id");
			
			$.ajax({
				url:"client_curd.php",
				type: "POST",
				data: {type:"delete",id:id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});
	
	////////////////////////////////////////////////////
	// Update status on click of Block or Unblock button
	////////////////////////////////////////////////////
	$(document).on('click', '.status', function() {
		status = $(this).attr('id');
		
		$.ajax({
			type:"POST",
			url: "client_curd.php",
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

});//eof of ready function
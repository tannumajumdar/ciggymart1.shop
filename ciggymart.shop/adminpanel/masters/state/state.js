// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add State form validation
	////////////////////////////////////
	$("#insertState").validate({
		rules: 
		{ 
			state_code: 
			{ 
				required: true,
				StateCodeExist:true,
				maxlength: 4
			},
			state_name: 
			{ 
				required: true,
				StateNameExist:true
			},
			zone_code: 
			{ 
				required: true,
			},
			zone_name: 
			{ 
				required: true,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Edit State form validation
	////////////////////////////////////
	$("#editState").validate({
		rules: 
		{ 
			state_code: 
			{ 
				required: true,
				StateCodeExistEdit:true,
				maxlength: 4
			},
			state_name: 
			{ 
				required: true,
				StateNameExistEdit:true,
			},
			zone_code: 
			{ 
				required: true,
			},
			zone_name: 
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
	$.validator.addMethod('StateCodeExist', function(val, element)
	{
		state_code=$("#state_code").val();

		$.ajax({
				 url:"state_curd.php",
				 type: "POST",
				 data: {type:"validate1",state_code:state_code},
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
	}, 'State Code Already exists.');
	
		
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('StateNameExist', function(val, element)
	{
		state_name=$("#state_name").val();

		$.ajax({
				 url:"state_curd.php",
				 type: "POST",
				 data: {type:"validate2",state_name:state_name},
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
	}, 'State Name Already exists.');
	
	
	////////////////////////////////////////////////////////////
	// Method to check is the data already exist or not in EDIT
	////////////////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('StateCodeExistEdit', function(val, element)
	{
		state_code=$("#state_code").val();
		state_id=$("#state_id").val();

		$.ajax({
				 url:"state_curd.php",
				 type: "POST",
				 data: {type:"EditStateCodeValid", state_code:state_code, state_id:state_id},
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
	}, 'State Code Already exists.');
	
	////////////////////////////////////////////////////////////
	// Method to check is the data already exist or not in EDIT
	////////////////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('StateNameExistEdit', function(val, element)
	{
		state_name=$("#state_name").val();
		state_id=$("#state_id").val();

		$.ajax({
				 url:"state_curd.php",
				 type: "POST",
				 data: {type:"EditStateNameValid",state_name:state_name, state_id:state_id},
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
	}, 'State Name Already exists.');
	
	////////////////////////////////////
	// on change of zone code drop down
	////////////////////////////////////	
	$(document).on("click","#zone_code, li", function(){
					
			var formdata = new FormData();
			formdata.append('type', "getZone");
			formdata.append('zone_code', $("#zone_code").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "state_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $('#zonename').html(data);
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
		flag=$("#insertState").valid();
		
		if (flag==true)
		{			
			var formdata = new FormData();
			formdata.append('type', "addState");
			formdata.append('state_code', $("#state_code").val());
			formdata.append('state_name', $("#state_name").val());
			formdata.append('zone_id', $("#zone_id").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "state_curd.php",
			   data:formdata,
			   async: false,
			   success: function(data){ //alert(data);
				   x=data;
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
			if(x==1)
			{
				window.location.replace("index.php");
			}
		}// eof if condition
		
	});
	
	
	//////////////////////////////////
	// on click of update button
	//////////////////////////////////
	$(document).on("click","#edit", function(){
		
		flag=$("#editState").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "editstate");
			formdata.append('state_id', $("#state_id").val());
			formdata.append('state_code', $("#state_code").val());
			formdata.append('state_name', $("#state_name").val());
			formdata.append('zone_id', $("#zone_id").val());
			
	  
			 var x;
			 $.ajax({
			   type: "POST",
			   url: "state_curd.php",
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
			var State_Id=$(this).attr("id");
			
			$.ajax({
				url:"state_curd.php",
				type: "POST",
				data: {type:"delete",State_Id:State_Id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});

	
});
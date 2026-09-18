// JavaScript Document
$(document).ready(function(){
	
	
	///////////////////////////////////
	// Add Zone form validation
	////////////////////////////////////
	$("#insertZone").validate({
		rules: 
		{ 
			zone_code: 
			{ 
				required: true,
				ZoneCodeExist:true
			},
			zone_name: 
			{ 
				required: true,
				ZoneNameExist:true
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Edit Zone form validation
	////////////////////////////////////
	$("#editZone").validate({
		rules: 
		{ 
			zone_code: 
			{ 
				required: true,
				ZoneCodeExistEdit:true
			},
			zone_name: 
			{ 
				required: true,
				ZoneNameExistEdit:true
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
	$.validator.addMethod('ZoneCodeExist', function(val, element)
	{
		zone_code=$("#zone_code").val();

		$.ajax({
				 url:"zone_curd.php",
				 type: "POST",
				 data: {type:"validate1",zone_code:zone_code},
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
	}, 'Zone Code Already exists.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('ZoneNameExist', function(val, element)
	{
		zone_name=$("#zone_name").val();

		$.ajax({
				 url:"zone_curd.php",
				 type: "POST",
				 data: {type:"validate2",zone_name:zone_name},
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
	}, 'Zone Name Already exists.');
	
	
	///////////////////////////////////////////////////////////
	// Method to check is the data already exist or not in EDIT
	///////////////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('ZoneCodeExistEdit', function(val, element)
	{
		zone_code=$("#zone_code").val();
		zone_id=$("#zone_id").val();
		
		$.ajax({
				 url:"zone_curd.php",
				 type: "POST",
				 data: {type:"zoneCodeEditValid",zone_code:zone_code, zone_id:zone_id},
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
	}, 'Zone Code Already exists.');
	
	////////////////////////////////////////////////////////////
	// Method to check is the data already exist or not in EDIT
	////////////////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('ZoneNameExistEdit', function(val, element)
	{
		zone_name=$("#zone_name").val();
		zone_id=$("#zone_id").val();

		$.ajax({
				 url:"zone_curd.php",
				 type: "POST",
				 data: {type:"zoneNameEditValid",zone_name:zone_name, zone_id:zone_id},
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
	}, 'Zone Name Already exists.');
	
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$('#submit').click(function(){
		flag=$("#insertZone").valid();
		
		if (flag==true)
		{			
			var formdata = new FormData();
			formdata.append('type', "addZone");
			formdata.append('zone_code', $("#zone_code").val());
			formdata.append('zone_name', $("#zone_name").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "zone_curd.php",
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
				//$( "#reset" ).trigger( "click" );
				window.location.replace("index.php");
			}
		}// eof if condition
		
	});
	
	
	//////////////////////////////////
	// on click of update button
	//////////////////////////////////
	$("#edit").click(function(){
		
		flag=$("#editZone").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "edit");
			formdata.append('zone_id', $("#zone_id").val());
			formdata.append('zone_code', $("#zone_code").val());
			formdata.append('zone_name', $("#zone_name").val());
			
	  
			 var x;
			 $.ajax({
			   type: "POST",
			   url: "zone_curd.php",
			   data:formdata,
			   async: false,
			   success: function(data){ //alert(data);
				   x=data;
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});
			
			if(x==0)
			{
				window.location.replace("index.php");				
			}
		}//eof if condition
	});
	
	
	//////////////////////////////////
	// on click of delete button
	//////////////////////////////////
	$(".delete").click(function(){
		
		var didConfirm = confirm("Are you sure?");
	    if (didConfirm == true) {
			var zone_id=$(this).attr("id");
			
			$.ajax({
				url:"zone_curd.php",
				type: "POST",
				data: {type:"delete",zone_id:zone_id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});
	
});
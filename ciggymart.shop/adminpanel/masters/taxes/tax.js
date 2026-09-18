// JavaScript Document
$(document).ready(function(){
	
	
	///////////////////////////////////
	// Add Tax form validation
	////////////////////////////////////
	$("#insertTax").validate({
		rules: 
		{ 
			tax_type: 
			{ 
				required: true,
				taxTypeExist:true
			},
			tax_percent: 
			{ 
				required: true,
				number: true,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Edit Zone form validation
	////////////////////////////////////
	$("#editTax").validate({
		rules: 
		{ 
			tax_type: 
			{ 
				required: true,
				taxTypeExistEdit:true
			},
			tax_percent: 
			{ 
				required: true,
				number: true,
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
	$.validator.addMethod('taxTypeExist', function(val, element)
	{
		tax_type=$("#tax_type").val();

		$.ajax({
				 url:"tax_curd.php",
				 type: "POST",
				 data: {type:"validate1",tax_type:tax_type},
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
	}, 'Tax Type already exists.');
	
	
	///////////////////////////////////////////////////////////
	// Method to check is the data already exist or not in EDIT
	///////////////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('taxTypeExistEdit', function(val, element)
	{
		tax_type=$("#tax_type").val();
		tax_id=$("#tax_id").val();
		
		$.ajax({
				 url:"tax_curd.php",
				 type: "POST",
				 data: {type:"taxTypeEditValid",tax_type:tax_type, tax_id:tax_id},
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
	}, 'Tax Type Already exists.');
	
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$('#submit').click(function(){
		
		flag=$("#insertTax").valid();
		
		if (flag==true)
		{			
			var formdata = new FormData();
			formdata.append('type', "addTax");
			formdata.append('tax_type', $("#tax_type").val());
			formdata.append('tax_percent', $("#tax_percent").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "tax_curd.php",
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
		
		flag=$("#editTax").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "edit");
			formdata.append('tax_id', $("#tax_id").val());
			formdata.append('tax_type', $("#tax_type").val());
			formdata.append('tax_percent', $("#tax_percent").val());
			
	  
			 var x;
			 $.ajax({
			   type: "POST",
			   url: "tax_curd.php",
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
			var tax_id=$(this).attr("id");
			
			$.ajax({
				url:"tax_curd.php",
				type: "POST",
				data: {type:"delete",tax_id:tax_id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});
	
});
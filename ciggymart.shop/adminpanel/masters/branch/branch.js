// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add Branch form validation
	////////////////////////////////////
	$("#insertBranch").validate({
		rules: 
		{ 
			branch_code:
			{
				required:true,
				BcodeExistValid:true,
			},
			branch_name: 
			{ 
				required: true,
			},
			franchise_logo:
			{
				extension: "png|PNG|jpg|JPG|jpeg|JPEG|gif|GIF",
				uploadFile:true,
			},
			cont_person: 
			{ 
				required: true,
			},
			dest_code: 
			{ 
				required: true,
			},			
			address:
			{
				required: true,
			},
			phone_no:
			{
				required: true,
				minlength: 10,
				maxlength: 11
			},
			email:
			{
				required: true,
				emailExistValid:true,
				email:true
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
	// Edit Branch form validation
	////////////////////////////////////
	$("#editBranch").validate({
		rules: 
		{ 
			branch_code:
			{
				required:true,
				BcodeExistEditValid:true,
			},
			branch_name: 
			{ 
				required: true,
			},
			franchise_logo:
			{
				extension: "png|PNG|jpg|JPG|jpeg|JPEG|gif|GIF",
				uploadFile:true,
			},
			cont_person: 
			{ 
				required: true,
			},
			dest_code: 
			{ 
				required: true,
			},			
			address:
			{
				required: true,
			},
			phone_no:
			{
				required: true,
				minlength: 10,
				maxlength: 11
			},
			email:
			{
				required: true,
				emailEditExistValid:true,
				email:true
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
	//Image Size Validate Here
	///////////////////////////////////////////////////
	$.validator.addMethod("uploadFile", function (val, element) {
		 if($("#franchise_logo").val().length>0)
			{
			  var size = element.files[0].size;
			  //  console.log(size);
	
			   if (size > 300000)// checks the file more than 1048576 kb =1 MB
			   {
					return false;
			   } else {
				   return true;
			   }
			} else {
				   return true;
			   }

      }, "Error: file size must be less than 300kb ");
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('BcodeExistValid', function(val, element)
	{
		branchCode=$("#branch_code").val();
		$.ajax({
				 url:"branch_curd.php",
				 type: "POST",
				 data: {type:"BCExist",branchCode:$("#branch_code").val()},
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
	}, 'Branch Code Already Exists.');
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('BcodeExistEditValid', function(val, element)
	{
		
		$.ajax({
				 url:"branch_curd.php",
				 type: "POST",
				 data: {type:"BCEditExistValid",branch_id:$("#branch_id").val(),branchCode:$("#branch_code").val()},
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
	}, 'Branch Code Already Exists.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('emailExistValid', function(val, element)
	{
		$.ajax({
				 url:"branch_curd.php",
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
	}, 'Email Already in Use, Please Choose Another Email Id For This Branch.');
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	var isSuccess ;
	$.validator.addMethod('emailEditExistValid', function(val, element)
	{
		$.ajax({
				 url:"branch_curd.php",
				 type: "POST",
				 data: {type:"emailEditExist",branch_id:$("#branch_id").val(),email:$("#email").val()},
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
	}, 'Email Already in Use, Please Choose Another Email Id For This Branch.');
	
	
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
		   url: "branch_curd.php",
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
	// on click of submit button
	//////////////////////////////////
	$('#submit').click(function(){		
		
		flag=$("#insertBranch").valid();
		
		if (flag==true)
		{			
			var formdata = new FormData();
			formdata.append('type', "addBranch");
			formdata.append('branch_code', $("#branch_code").val());
			formdata.append('branch_name', $("#branch_name").val());
			formdata.append('franchise_name', $("#franchise_name").val());
			formdata.append('franchise_logo', $('#franchise_logo')[0].files[0]);
			
			formdata.append('cont_person', $("#cont_person").val());
			formdata.append('address', $("#address").val());			
			formdata.append('dest_id', $("#dest_id").val());
			formdata.append('gstNo', $("#gstNo").val());
			formdata.append('serv_tax_no', $("#serv_tax_no").val());			
			formdata.append('panNo', $("#panNo").val());
			formdata.append('phone_no', $("#phone_no").val());
			formdata.append('email', $("#email").val());
			formdata.append('password', $("#password").val());
				
			var x;
			$.ajax({
			   type: "POST",
			   url: "branch_curd.php",
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
	
	
	////////////////////////////////////////////////////
	// Update status on click of Block or Unblock button
	////////////////////////////////////////////////////
	$(".status").click(function(){
		status = $(this).attr('id');
		
		$.ajax({
			type:"POST",
			url: "branch_curd.php",
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
	
	
	//////////////////////////////////
	// on click of update button
	//////////////////////////////////
	$("#edit").click(function(){
		
		flag=$("#editBranch").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "editBranch");
			formdata.append('branch_id', $("#branch_id").val());
			formdata.append('branch_code', $("#branch_code").val());
			formdata.append('branch_name', $("#branch_name").val());
			formdata.append('franchise_name', $("#franchise_name").val());
			formdata.append('franchise_logo', $('#franchise_logo')[0].files[0]);
			formdata.append('frpic', $("#frpic").val());
			
			formdata.append('cont_person', $("#cont_person").val());
			formdata.append('address', $("#address").val());			
			formdata.append('dest_id', $("#dest_id").val());
			formdata.append('gstNo', $("#gstNo").val());
			formdata.append('serv_tax_no', $("#serv_tax_no").val());			
			formdata.append('panNo', $("#panNo").val());
			formdata.append('phone_no', $("#phone_no").val());
			formdata.append('email', $("#email").val());
			formdata.append('password', $("#password").val());
	  
			 var x;
			 $.ajax({
			   type: "POST",
			   url: "branch_curd.php",
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
			var Branch_Id=$(this).attr("id");
			
			$.ajax({
				url:"branch_curd.php",
				type: "POST",
				data: {type:"delete",Branch_Id:Branch_Id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});
	
});//eof of ready function
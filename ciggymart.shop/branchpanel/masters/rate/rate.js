// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add Rate form validation
	////////////////////////////////////
	$("#insertRate").validate({
		rules: 
		{ 
			/*client_code: 
			{ 
				required: true,
				ClientInvalid:true,
			},*/
			client_name: 
			{ 
				required: true,
				ClientInvalid:true
			},
			zone_code: 
			{ 
				required: true,
				zoneInvalid: true,
				zoneExists:true,
			},
			zone_name: 
			{ 
				required: true,
			},
			send_by:
			{
				required: true,
			},
			additional_weight:
			{
				required:true,	
			},
			additional_rate:
			{
				required:true,	
			}
			
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Edit Rate form validation
	////////////////////////////////////
	$("#editRate").validate({
		rules: 
		{ 
			additional_weight:
			{
				required:true,	
			},
			additional_rate:
			{
				required:true,	
			}
			
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	$("#searchRate").validate({
		rules: 
		{ 
			client_name: 
			{ 
				required: true,
				ClientInvalid:true,
				//custExists:true,
			},
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
	$.validator.addMethod('ClientInvalid', function(val, element)
	{		
		$.ajax({
			 url:"rate_curd.php",
			 type: "POST",
			 data: {type:"clientInvalid",client_name: $('#client_name').val()},
			 async:false,
			 success:function(data){//alert(data);
				 isSuccess=(data==1)?true:false;
			 }
		});//eof ajax
		return isSuccess ;				
	}, 'Invalid Client Name.');
	
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
   	$.validator.addMethod('zoneInvalid', function(val, element)
	{		
		$.ajax({
				 url:"rate_curd.php",
				 type: "POST",
				 data: {type:"zone_Invalid",zone_code: $('#zone_code').val()},
				 async:false,
				 success:function(data){// alert(data);
					 isSuccess=(data==1)?true:false;
					 //return isSuccess;
				}
		});//eof ajax
		return isSuccess ;	
	}, 'Invalid Zone Code.');
	
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
   	$.validator.addMethod('zoneExists', function(val, element)
	{ 
		if($('#client_name').valid() )
		{
		
			$.ajax({
				 url:"rate_curd.php",
				 type: "POST",
				 data: {type:"zoneExists",zone_code: $('#zone_code').val(),client_name:$('#client_name').val(), send_by:$('#send_by').val()},
				 async:false,
				 success:function(data){ //alert(data);
					 isSuccess=(data==0)?true:false;					 				 
				 }//eof success
			});//eof ajax	
			return isSuccess ;	
		}//eof if condition
	}, 'Rates Already Exists For This Zone.'); 
		
	///////////////////////////////////////////////////
	// Method to check is the data already exist or not
	///////////////////////////////////////////////////
    $.validator.addMethod('zoneExists2', function(val, element)
	{
		$.ajax({
			 url:"rate_curd.php",
			 type: "POST",
			 data: {type:"zoneExists2",zone_code: $('#zone_code').val(),id:$('#id').val(),cust_code: $('#client_code').val()},
			 async:false,
			 success:function(data){// alert(data);
				 isSuccess=(data==0)?true:false;				 
			}//eof success
		});//eof ajax
		return isSuccess ;				
	}, 'Rates Already Exists For This Zone.'); 
	
	
	////////////////////////////////////
	// on click of client name drop down
	////////////////////////////////////	
	$(document).on("click","#client_name, li", function(){
					
		if($("#client_name").val()!=""){
			var formdata = new FormData();
			formdata.append('type', "getClientName");
			formdata.append('client_name', $("#client_name").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "rate_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $('#clientname').html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax	
		}
		
	});
	
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
			   url: "rate_curd.php",
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
	// on click weightfrom class
	//////////////////////////////////
	$(document).on('change', '.weightfrom', function() {
		
		if ($(this).val()!="")
		{
			$(this).addClass('weight');
			$(this).addClass('ieng');
		}// eof if condition
		
	});
	
	//////////////////////////////////
	// on click weightto class
	//////////////////////////////////
	$(document).on('change', '.weightto', function() {	
			
		if ($(this).val() != "")
		{
			$(this).addClass('weight');
		}// eof if condition
		
	});
	
	//////////////////////////////////
	// on click rate class
	//////////////////////////////////	
	$(document).on('change', '.rate', function() {	
			
		if ($(this).val() != "")
		{
			$(this).addClass('weight');
		}// eof if condition
		
	});
	
	//////////////////////////////////
	// on click add more colomn
	//////////////////////////////////
	var numItems = $('.weightfrom').length	
	var i=numItems;
	
	$(document).on('click', '#add_more', function() {
		
		if(i<40)
		{
			i=i+1;
	  
			$("p").append('<div class="form-group" id="a'+i+'"><label class="control-label col-sm-2 mandatory" for="waight"></label><div class="col-sm-2"><input type="text" class="form-control input-sm weightfrom" id="w_from'+i+'" name="w_from'+i+'"/></div><div class="col-sm-1">kg </div><div class="col-sm-2"><input type="text" class="form-control input-sm weightto" id="w_to'+i+'" name="w_to'+i+'" /></div><div class="col-sm-1">kg </div><div class="col-sm-2"><input type="text" class="form-control input-sm rate "  id="rate'+i+'" name="rate'+i+'" /></div><div class="col-sm-1"><button type="button"class="add_cancel glyphicon glyphicon-remove btn-danger" id="'+i+'" name="add_cancel"></button></div></div>');
		}
		
	});//eof on click event

	//////////////////////////////////
	// on click cancle colomn
	//////////////////////////////////
	$(document).on('click', '.add_cancel', function() {
	   var j=$(this).attr('id');	
	   $('#a' + j).remove();
    });//eof on click event
	

	/////////////////////////////////////////////
	// we unchange the textbox value 
	// when checkbox clicked
	/////////////////////////////////////////////
	$(document).on('click', '#clientChkbx', function() {
		
		if($(this).is(":checked")) {							
			$("#client_name").attr('readonly', 'true');
		}
		else{
			$("#client_name").removeAttr('readonly');
		}
	});
	
	/////////////////////////////////////////////
	$(document).on('click', '#sendByChkbx', function() {
		
		if($(this).is(":checked")) {							
			$('#send_by').prop('disabled',true);
		}
		else{
			$('#send_by').prop('disabled',false);
		}
	});
	
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$(document).on('click', '#submit', function() {
		
		flag=$("#insertRate").valid();
		if (flag==true)
		{
			var weight=[];
			var str = $('.ieng').length;
			
			for(j=1; j<=str; j++)
			{
				 weight.push($('#w_from'+j).val()+'@'+$('#w_to'+j).val()+'@'+$('#rate'+j).val());			
			}
		
			var formdata = new FormData();
			formdata.append('type', "addRate");
			formdata.append('client_id', $("#client_id").val());
			formdata.append('zone_id', $("#zone_id").val());
			formdata.append('send_by', $("#send_by").val());
			formdata.append('weight', weight);
			formdata.append('additional_weight', $("#additional_weight").val());
			formdata.append('additional_rate', $("#additional_rate").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "rate_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x!=0)
					{
						if($("#clientChkbx").is(":checked")){
							var client_name = $("#client_name").val();
							$("input[type=text], textarea").val("");
							$("#client_name").val(client_name);
						}
						else{
							$("input[type=text], textarea").val("");	
						}
						
						if($("#sendByChkbx").is(":checked")){
							var sendBy = $('#send_by').val();
							$('#send_by').val(sendBy);
						}
						else{
							$('#send_by').prop('selectedIndex',0);	
						}
						
						
						$("#msg").show();
						
						//window.location.replace("index.php");
					}
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
			
		}// eof if condition
		
	});
	
	 
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$(document).on('click', '#search', function() {
		
		flag=$("#searchRate").valid();
		if (flag==true)
		{
		
			var formdata = new FormData();
			formdata.append('type', "searchRateList");
			formdata.append('client_id', $("#client_id").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "rate_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x!=0)
					{
						$("#rateListResult").html(data);
					}
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
			
		}// eof if condition
		
	});//eof search function
	 
	 
	 //////////////////////////////////
	// on click of update button
	//////////////////////////////////
	$(document).on('click', '#edit', function() {
		
		
		flag=$("#editRate").valid();
		if (flag==true)
		{
			var weight=[];
			var str1 = $('.ieng').length;
			var str = $('#count').val();
			var sum = parseInt(str) + parseInt(str1);
				
			for(j=1; j<=sum ; j++)
			{
				if($('#w_from'+j).val()!="" || $('#w_to'+j).val()!= "" || $('#rate'+j).val()!="")
				{
					weight.push($('#w_from'+j).val()+'@'+$('#w_to'+j).val()+'@'+$('#rate'+j).val());
				}
			}
	
			var formdata = new FormData();
			formdata.append('type', "editRate");
			formdata.append('id', $("#id").val());
			formdata.append('client_id', $("#client_id").val());
			formdata.append('zone_id', $("#zone_id").val());
			formdata.append('weight', weight);
			formdata.append('additional_weight', $("#additional_weight").val());
			formdata.append('additional_rate', $("#additional_rate").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "rate_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x==1)
					{
						window.location.href="ratelist.php";
					}
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		}// eof if condition
		
	});
	 
	//////////////////////////////////
	// on click of delete button
	//////////////////////////////////
	$(document).on('click', '.delete', function() {
		
		var didConfirm = confirm("Are you sure?");
	    if (didConfirm == true) {
			var id=$(this).attr("id");
			
			$.ajax({
				url:"rate_curd.php",
				type: "POST",
				data: {type:"delete",id:id},
				async:false,
				success: function(data){ alert(data);
				}
			});
			location.reload();
	    }
	});
	
});//eof of ready function
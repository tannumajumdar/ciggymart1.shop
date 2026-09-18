// JavaScript Document
$(document).ready(function(){
	
	///////////////////////////////////
	// Add Customer form validation
	////////////////////////////////////
	$("#insertConsignment").validate({
		rules: 
		{ 
			date: 
			{ 
				required: true,
			},			
			consignment_no:
			{
				required: true,
				ABWExist:true,
				number:true
			},
			client_name:
			{
				required: true,
				ClientInvalid:true,
			},
			Destination_Name:
			{
				required: true,
				DestinationInvalid:true,
				rateNotExist:true,
			},
			pieces:
			{
				required: true,
				number:true,
			},
			send_by:
			{
				required: true,
				SendByRateNotExist:true,
			},
			weight:
			{
				required: true,
				//clientDestEmpty:true,
				//weightNotFound:true,
				number:true,
			},
			subtotal:
			{
				required: true,
				number:true,
			},
			discount_percent:
			{
				number:true,
			},
			discount_rs:
			{
				number:true,
			},
			total_amt:
			{
				required: true,
			},
			insured_value:
			{
				//required: true,
			}
			
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	///////////////////////////////////
	// Edit Consignment form validation
	////////////////////////////////////
	$("#editConsignment").validate({
		rules: 
		{ 
			date: 
			{ 
				required: true,
			},			
			consignment_no:
			{
				required: true,
				number:true
			},
			/*client_code:
			{
				required: true,
				ClientInvalid:true,
				number:true
			},*/
			client_name:
			{
				required: true,
				ClientInvalid:true,
			},
			dest_code:
			{
				required: true,
				DestinationInvalid:true,
				rateNotExist:true,
			},
			Destination_Name:
			{
				required: true,
			},
			pieces:
			{
				required: true,
				number:true,
			},
			weight:
			{
				required: true,
				//weightNotFound:true,
				number:true,
			},
			subtotal:
			{
				required: true,
				number:true,
			},
			discount_percent:
			{
				number:true,
			},
			discount_rs:
			{
				number:true,
			},
			total_amt:
			{
				required: true,
			},
			insured_value:
			{
				//required: true,
			}
		},
		messages:
		{
			
		}
	});// eof validation
	
	
	var isSuccess ;
		
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('ClientInvalid', function(val, element)
	{		
		$.ajax({
			 url:"consignment_curd.php",
			 type: "POST",
			 data: {type:"clientInvalid", client_name: $('#client_name').val()},
			 async:false,
			 success:function(data){
				 isSuccess=(data==1)?true:false;
			 }
			 
		});//eof ajax
		return isSuccess ;				
	}, 'Invalid Client Code.');
	
	
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('DestinationInvalid', function(val, element)
	{		
		$.ajax({
			 url:"consignment_curd.php",
			 type: "POST",
			 data: {type:"destInvalid",Destination_Name: $('#Destination_Name').val()},
			 success:function(data){//alert(data);
				 isSuccess=(data==1)?true:false;
			 }
		});//eof ajax
		return isSuccess ;				
	}, 'Invalid Destination Name.');
	
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('rateNotExist', function(val, element)
	{		
		$.ajax({
			 url:"consignment_curd.php",
			 type: "POST",
			 data: {type:"rateNoExist",dest_id: $('#dest_id').val()},
			 async:false,
			 success:function(data){ //alert(data);
				 isSuccess=(data==1)?true:false;
			 }
		});//eof ajax
		return isSuccess ;				
	}, 'Rates are not defined in the rate master for this detination and related zone.');
	
	
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('SendByRateNotExist', function(val, element)
	{		
	
		//alert($('#send_by').val());
		
		$.ajax({
			 url:"consignment_curd.php",
			 type: "POST",
			 data: {type:"SendByRateNotExist",dest_id: $('#dest_id').val(), client_id:$("#client_id").val(), send_by: $('#send_by').val()},
			 async:false,
			 success:function(data){ 
			 	 //console.log(data);
				 isSuccess=(data==1)?true:false;
			 }
		});//eof ajax
		return isSuccess ;				
	}, 'Rates are not defined in the rate master for this Send By Mode, detination and related zone.');
	
	
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('ABWExist', function(val, element)
	{		
		$.ajax({
			 url:"consignment_curd.php",
			 type: "POST",
			 data: {type:"ABWExist",consignment_no: $('#consignment_no').val(), date: $("#date").val()},
			 async:false,
			 success:function(data){//alert(data);
				 isSuccess=(data!=1)?true:false;
			 }
		});//eof ajax
		return isSuccess ;				
	}, 'You can use this same AWB No. after 30 days.');
	
	
	////////////////////////////////////
	// on click of disPercentRadio
	////////////////////////////////////	
	$(document).on("click","#disPercentRadio", function(){

		$("#discount_percent").removeAttr('readonly');
		$("#discount_rs").attr('readonly', 'true');
		$("#discount_rs").val("");
		
		var subtotal= $("#subtotal").val();
		$("#total_amt").val(subtotal);
		
	});
	
	////////////////////////////////////
	// on click of disRsRadio
	////////////////////////////////////	
	$(document).on("click","#disRsRadio", function(){
		
		$("#discount_rs").removeAttr('readonly');
		$("#discount_percent").attr('readonly', 'true');
		$("#discount_percent").val("");
		
		var subtotal= $("#subtotal").val();
		$("#total_amt").val(subtotal);
		
	});
	
	///////////////////////////////////////////
	// on keyup function when change the weight
	///////////////////////////////////////////
	$(document).on("change","#send_by", function(){
		if($("#weight").val()!="")
		{
			
			$.ajax({
				 url:"consignment_curd.php",
				 type: "POST",
				 data: {type:"SendByRateNotExist",dest_id: $('#dest_id').val(), client_id:$("#client_id").val(), send_by: $('#send_by').val()},
				 success:function(data){ 
					 
					 if(data==1){
						
						var formdata = new FormData();
						formdata.append('type', "getSubtotal");
						formdata.append('client_id', $("#client_id").val());
						formdata.append('dest_id', $("#dest_id").val());
						formdata.append('send_by', $("#send_by").val());
						formdata.append('weight', $("#weight").val());
						
						var x;
						$.ajax({
							type:"POST",
							url:"consignment_curd.php",
							data:formdata,
							success: function(data){ //alert(data);
								
								if($("#discount_percent").val()!=0 && $("#discount_rs").val()==0){
									
									var discount = (data * $("#discount_percent").val())/100;
									var total_amt = data - discount;
									
									$("#subtotal").val(data);
									$("#total_amt").val(total_amt);
									
								}
								else if($("#discount_percent").val()==0 && $("#discount_rs").val()!=0){
																
									var total_amt = data - $("#discount_rs").val();
										
									$("#subtotal").val(data);
									$("#total_amt").val(total_amt);
										
								}
								else{
									$("#subtotal").val(data);
									$("#total_amt").val(data);
								}
							},			
							cache: false,
							contentType: false,
							processData: false
						});//eof ajax 
						
					 }//eof if condition
					 else{
						$("#subtotal").val("");
						$("#total_amt").val("");
					 }
					 
				 }
			});//eof ajax
		}
	});
	
	
	/////////////////////////////////////////////
	// on keyup function when change the subtotal
	/////////////////////////////////////////////
	$(document).on("keyup","#subtotal", function(){
		
		if($(this).val()=="")
		{
			$("#total_amt").val("");		
		}
		else{
			
			if($("#discount_percent").val()!=0 && $("#discount_rs").val()==0){
							
				var discount = ($(this).val() * $("#discount_percent").val())/100;
				var total_amt = $(this).val() - discount;
				
				$("#total_amt").val(total_amt);
				
			}
			else if($("#discount_percent").val()==0 && $("#discount_rs").val()!=0){
											
				var total_amt = $(this).val() - $("#discount_rs").val();
					
				$("#total_amt").val(total_amt);
					
			}
			else{
				$("#total_amt").val($(this).val());
			}
			
		}
	});
	
	
	///////////////////////////////////////////
	// on keyup function when change the weight
	///////////////////////////////////////////
	$(document).on("keyup","#weight", function(){
		
		if($("#weight").val()=="" || isNaN($(this).val()))
		{
			$("#total_amt").val("");
			$("#subtotal").val("");			
		}
		else{
			
			if($("#client_code").val()!="" && $("#Destination_Name").val()!="")
			{
				var formdata = new FormData();
				formdata.append('type', "getSubtotal");
				formdata.append('client_id', $("#client_id").val());
				formdata.append('dest_id', $("#dest_id").val());
				formdata.append('send_by', $("#send_by").val());
				formdata.append('weight', $("#weight").val());
				
				var x;
				$.ajax({
					type:"POST",
					url:"consignment_curd.php",
					data:formdata,
					success: function(data){ //alert(data);
						
						if($("#discount_percent").val()!=0 && $("#discount_rs").val()==0){
							
							var discount = (data * $("#discount_percent").val())/100;
							var total_amt = data - discount;
							
							$("#subtotal").val(data);
							$("#total_amt").val(total_amt);
							
						}
						else if($("#discount_percent").val()==0 && $("#discount_rs").val()!=0){
														
							var total_amt = data - $("#discount_rs").val();
								
							$("#subtotal").val(data);
							$("#total_amt").val(total_amt);
								
						}
						else{
							$("#subtotal").val(data);
							$("#total_amt").val(data);
						}
					},			
					cache: false,
					contentType: false,
					processData: false
				});//eof ajax	
			}//eof if condition
		}//eof else condition
		
	});//eof function
	
	/////////////////////////////////////////
	// on change of Discount Percent text box
	/////////////////////////////////////////	
	$(document).on("keyup","#discount_percent", function(){
		
		var discount = ($("#subtotal").val() * $(this).val())/100;
		var total_amt = $("#subtotal").val() - discount;
		
		if($("#subtotal").val()!=""){
		
			if($(this).val()!=""){
				
				if($("#total_amt").val()!=""){
					
					$("#total_amt").val("");
					$("#total_amt").val(total_amt);
				}
				else{
					$("#total_amt").val(total_amt);
				}			
			}
			else{
				var subtotal= $("#subtotal").val();
				$("#total_amt").val(subtotal);
			}
			
		}
	});
	
	
	/////////////////////////////////////////
	// on change of Discount Rupees text box
	/////////////////////////////////////////	
	$(document).on("keyup","#discount_rs", function(){
		
		var total_amt = $("#subtotal").val() - $(this).val();
		
		if($(this).val()!=""){
			
			if($("#total_amt").val()!=""){
				
				$("#total_amt").val("");
				$("#total_amt").val(total_amt);
			}
			else{
				$("#total_amt").val(total_amt);
			}			
		}
		else{
			var subtotal= $("#subtotal").val();
			$("#total_amt").val(subtotal);
		}
		
	});
	
	////////////////////////////////////
	// on click of client code drop down
	////////////////////////////////////	
	$(document).on("click","#client_name, ul, li.ui-menu-item, li.ui-menu-item a", function(){
					
		var formdata = new FormData();
			formdata.append('type', "getClientName");
			formdata.append('client_name', $("#client_name").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "consignment_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $('#clientname').html(data);
				   
				   if($('#dest_id').val()=="" || $("#send_by").val()=="" || $("#weight").val()==""){
					
						$("#subtotal").val("");
						$("#total_amt").val("");
					   
				   }//eof if condition
				   else{
						
						// this function is written in 
						// add_consignment and edit consignment
						getAmt();

					}//eof else
				   
			   },//eof success
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
	});
	
	
	//////////////////////////////////////////
	// on change of destination code drop down
	//////////////////////////////////////////	
	$(document).on("click","#Destination_Name, ul, li.ui-menu-item, li.ui-menu-item a", function(){
					
		var formdata = new FormData();
		formdata.append('type', "getDestinationName");
		formdata.append('Destination_Name', $("#Destination_Name").val());

		var x;
		$.ajax({
		   type: "POST",
		   url: "consignment_curd.php",
		   data:formdata,
		   success: function(data){ //alert(data);
			   $('#destinationName').html(data);
			   
			   	  if($('#client_id').val()=="" || $("#send_by").val()=="" || $("#weight").val()==""){
					
						$("#subtotal").val("");
						$("#total_amt").val("");
					   
				   }//eof if condition
				   else{
						
						// this function is written in 
						// add_consignment and edit consignment
						getAmt();
						 
					}//eof else
			   
		   },//eof success
		   cache: false,
		   contentType: false,
		   processData: false
		});//eof ajax
		
	});	
	
	
	/////////////////////////////////////////////
	// we unchange the textbox value 
	// when checkbox clicked
	/////////////////////////////////////////////
	$(document).on('click', '#dateChkbx', function() {
		
		if($(this).is(":checked")) {							
			$("#date").attr('readonly', 'true');
		}
		else{
			$("#date").removeAttr('readonly');
		}
	});
	
	$(document).on('click', '#cnoChkbx', function() {
		
		if($(this).is(":checked")) {							
			$("#consignment_no").attr('readonly', 'true');
		}
		else{
			$("#consignment_no").removeAttr('readonly');
		}
	});
	
	$(document).on('click', '#ccodeChkbx', function() {
		
		if($(this).is(":checked")) {							
			$("#client_name").attr('readonly', 'true');
		}
		else{
			$("#client_name").removeAttr('readonly');
		}
	});
	
	$(document).on('click', '#dcodeChkbx', function() {
		
		if($(this).is(":checked")) {							
			$("#Destination_Name").attr('readonly', 'true');
		}
		else{
			$("#Destination_Name").removeAttr('readonly');
		}
	});
	
	$(document).on('click', '#modeChkbx', function() {
		
		if($(this).is(":checked")) {							
			$('#mode').prop('disabled',true);
		}
		else{
			$('#mode').prop('disabled',false);
		}
	});
	
	$(document).on('click', '#piecesChkbx', function() {
		
		if($(this).is(":checked")) {							
			$("#pieces").attr('readonly', 'true');
		}
		else{
			$("#pieces").removeAttr('readonly');
		}
	});
	
	$(document).on('click', '#sendByChkbx', function() {
		
		if($(this).is(":checked")) {							
			$('#send_by').prop('disabled',true);
		}
		else{;
			$('#send_by').prop('disabled',false);
		}
	});
	
	$(document).on('click', '#weightChkbx', function() {
		
		if($(this).is(":checked")) {							
			$("#weight").attr('readonly', 'true');
		}
		else{
			$("#weight").removeAttr('readonly');
		}
	});
	
	//////////////////////////////////
	// on click of submit button
	//////////////////////////////////
	$(document).on('click', '#submit', function() {
		
		flag=$("#insertConsignment").valid();
		
		if (flag==true)
		{			
		
			var formdata = new FormData();
			formdata.append('type', "addConsignment");
			formdata.append('date', $("#date").val());
			formdata.append('consignment_no', $("#consignment_no").val());
			formdata.append('client_id', $("#client_id").val());
			formdata.append('dest_id', $("#dest_id").val());
			formdata.append('mode', $("#mode").val());
			formdata.append('pieces', $("#pieces").val());
			formdata.append('send_by', $("#send_by").val());
			formdata.append('weight', $("#weight").val());
			formdata.append('subtotal', $("#subtotal").val());			
			formdata.append('discount_percent', $("#discount_percent").val());
			formdata.append('discount_rs', $("#discount_rs").val());
			formdata.append('total_amt', $("#total_amt").val());
			formdata.append('insured_value', $("#insured_value").val());
			formdata.append('other_charges', $("#other_charges").val());
				
			var x;
			$.ajax({
			   type: "POST",
			   url: "consignment_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x==1)
					{
						$("#msg").show();
						/*$("input[type=text], textarea").val("");*/						
						
						var cuurentDate = $("#cuurentDate").val();						
						if($("#dateChkbx").is(":checked")) {
							$("#date").val();
						}
						else {
							
							$("#date").val(cuurentDate);	
						}
						
						if($("#cnoChkbx").is(":checked")) {
							var consNo = $("#consignment_no").val();
							newConsNo = parseInt(consNo) + 1;
							$("#consignment_no").val(newConsNo);
						}
						else{
							
							$("#consignment_no").val("");
						}
						
						if($("#ccodeChkbx").is(":checked")) {
							var clientName = $("#client_name").val();
							$("#client_name").val(clientName);
						}
						else{
							
							$("#client_name").val("");
						}
						
						if($("#dcodeChkbx").is(":checked")) { 
							//var destCode = $("#dest_code").val();
							var destName = $("#Destination_Name").val();
							
							//$("#dest_code").val(destCode);
							$("#Destination_Name").val(destName);
						}
						else{
							
							//$("#dest_code").val("");
							$("#Destination_Name").val("");
						}
						
						if($("#modeChkbx").is(":checked")) { 
							//alert($("#mode").val());
							var mode = $("#mode").val();
							if(mode == 1){
								$('#mode').prop('selectedIndex',0);
							}
							else{
								$('#mode').prop('selectedIndex',1);
							}
						}
						else{
							
							$('#mode').prop('selectedIndex',0);
						}
						
						if($("#piecesChkbx").is(":checked")) { 
							$("#pieces").val("1");
						}
						else{
							$("#pieces").val("1");	
						}
												
						if($("#sendByChkbx").is(":checked")) { 
							var send_by = $("#send_by").val();
							$('#send_by').prop('selectedIndex',send_by);
						}
						else{
							
							$('#send_by').prop('selectedIndex',0);
							
						}
						
						if($("#weightChkbx").is(":checked")) { 
							var weight = $("#weight").val();
							$("#weight").val(weight);
						}
						else{
							
							$("#weight").val("");
						}
						
						if(!$("#sendByChkbx").is(":checked") || !$("#dcodeChkbx").is(":checked") || !$("#ccodeChkbx").is(":checked") || !$("#weightChkbx").is(":checked")) { 
						
							
							$("#subtotal").val("");
							$("#discount_percent").val("");
							$("#discount_rs").val("");
							$("#total_amt").val("");
							$("#insured_value").val(0);
							$("#other_charges").val(0);
							
						
						}
						
						
						/*$("input[type=text], textarea").val("");
						$('#send_by').prop('selectedIndex',0);
						$('#mode').prop('selectedIndex',0);*/
						
						//setInterval( function() {window.location.href="add_consignment.php";}, 800)						
					}
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		}// eof if condition
		
	});// eof submit function
	
	
	//////////////////////////////////
	// on click of edit button
	//////////////////////////////////
	$(document).on('click', '#edit', function() {
	
		flag=$("#editConsignment").valid();
		if (flag==true)
		{	
		
			var formdata = new FormData();
			formdata.append('type', "editConsignment");
			formdata.append('consignment_id', $("#consignment_id").val());
			formdata.append('date', $("#date").val());
			formdata.append('consignment_no', $("#consignment_no").val());
			formdata.append('client_id', $("#client_id").val());
			formdata.append('dest_id', $("#dest_id").val());
			formdata.append('mode', $("#mode").val());
			formdata.append('pieces', $("#pieces").val());
			formdata.append('send_by', $("#send_by").val());
			formdata.append('weight', $("#weight").val());
			formdata.append('subtotal', $("#subtotal").val());			
			formdata.append('discount_percent', $("#discount_percent").val());
			formdata.append('discount_rs', $("#discount_rs").val());
			formdata.append('total_amt', $("#total_amt").val());
			formdata.append('insured_value', $("#insured_value").val());
			formdata.append('other_charges', $("#other_charges").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "consignment_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   x=data;
				   if(x==1)
					{
						$("#msg").show();
						setInterval( function() {window.location.href="index.php";}, 800)						
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
				url:"consignment_curd.php",
				type: "POST",
				data: {type:"delete",id:id},
				async:false,
				success: function(data){ //alert(data);
				}
			});
			location.reload();
	    }
	});//eof delete function
	
	
	///////////////////////////////////
	// Search form validation
	////////////////////////////////////
	$("#consignmentSrchFrm").validate({
		rules: 
		{
			consignment_no:
			{
				required: true,
				invalidCons: true
			}
		},
		messages:
		{
			
		}
	});//eof validation
	
	///////////////////////////////////////////////////
	// Method to check the data is valid or not
	///////////////////////////////////////////////////
	$.validator.addMethod('invalidCons', function(val, element)
	{		
		$.ajax({
			 url:"consignment_curd.php",
			 type: "POST",
			 data: {type:"invalidCons", consignment_no: $('#consignment_no').val()},
			 async:false,
			 success:function(data){
				 isSuccess=(data==1)?true:false;
			 }
			 
		});//eof ajax
		return isSuccess ;				
	}, 'Invalid Consignment No.');
	
	////////////////////////////////////////
	// on click of search_consignment button
	////////////////////////////////////////
	$(document).on('click', '#search_consignment', function() {			
		
		flag=$("#consignmentSrchFrm").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "search_consignment");
			formdata.append('consignment_no', $("#consignment_no").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "consignment_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $("#detail").html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		}//eof if condition
		
	});//eof edit function
	
	////////////////////////////////////
	// Search form validation
	////////////////////////////////////
	$("#clientSrchFrm").validate({
		rules: 
		{
			client_name:
			{
				required: true,
				//clientInvalid: true
			}
		},
		messages:
		{
			
		}
	});//eof validation	
	
	////////////////////////////////////////
	// on click of search button
	////////////////////////////////////////
	$(document).on('click', '#searchClientWise', function() {	
		
		flag=$("#clientSrchFrm").valid();
		
		if (flag==true)
		{
			var formdata = new FormData();
			formdata.append('type', "searchClientWise");
			formdata.append('client_name', $("#client_name").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "consignment_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $("#detail").html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax	
		}
	});//eof edit function
	
	
	////////////////////////////////////
	// Search form validation
	////////////////////////////////////
	$("#dateSrchFrm").validate({
		rules: 
		{
			to_date:
			{
				//required: true,
				greaterThenFrom: true,
				//fromDateEmpty:true
			}
		},
		messages:
		{
			
		}
	});//eof validation		
	
	
	////////////////////////////////////
	///// method for date validation////
	////////////////////////////////////
    $.validator.addMethod('greaterThenFrom', function(val, element)
	{
		$.ajax({
			 url:"consignment_curd.php",
			 type:"POST",
			 data:{type:"greaterThenFromDate",from_date: $('#from_date').val(), to_date: $("#to_date").val()},
			 async:false,
			 success:function(data){ //alert(data);
			 	
				isSuccess=(data==1)?true:false;
				
			}
		});//eof ajax		
		return isSuccess ;
				
	}, 'The date should not be greater than FROM Date.');
	
	////////////////////////////////////////
	// on click of search button
	////////////////////////////////////////
	$(document).on('click', '#searchByDateBtn', function() {	
		
		flag=$("#dateSrchFrm").valid();
		
		if (flag==true)
		{
			
			$("#loader").show();
			
			var formdata = new FormData();
			formdata.append('type', "searchDateWise");
			formdata.append('client_name', $("#client_name").val());
			formdata.append('from_date', $("#from_date").val());
			formdata.append('to_date', $("#to_date").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "consignment_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $("#detail").html(data);
				   $("#loader").hide('slow');
				   
				   $('input:text').val('');
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax	
		}
		
	});	
	
	
	/*#########################################*/
	// change the focus on keypress of TAB key
	/*#########################################*/
	$("#consignment_no").keydown(function (e) {    
	  if (e.which == 9) {
		      
		if($("#client_name").val()==""){
			$("#client_name").focus();	
		}
		else if($("#Destination_Name").val()==""){
			$("#Destination_Name").focus();	
		}
		else if($("#send_by").val()==""){
			$("#send_by").focus();	
		}
		else if($("#weight").val()==""){
			$("#weight").focus();
		}
		/*else if($("#insured_value").val()==""){
			$("#insured_value").focus();
		}*/
		else {
			$("#submit").focus();
		}
		
		e.preventDefault();
	  }
	});
	
	//#############################################
	
	$("#client_name").keydown(function (e) {    
	  if (e.which == 9) {
		      
		if($("#Destination_Name").val()==""){
			$("#Destination_Name").focus();	
		}
		else if($("#send_by").val()==""){
			$("#send_by").focus();	
		}
		else if($("#weight").val()==""){
			$("#weight").focus();
		}
		/*else if($("#insured_value").val()==""){
			$("#insured_value").focus();
		}*/
		else {
			$("#submit").focus();
		}
		
		e.preventDefault();
	  }
	});
	
	//#############################################
	
	$("#Destination_Name").keydown(function (e) {    
	  if (e.which == 9) {
		      
		if($("#send_by").val()==""){
			$("#send_by").focus();	
		}
		else if($("#weight").val()==""){
			$("#weight").focus();
		}
		/*else if($("#insured_value").val()==""){
			$("#insured_value").focus();
		}*/
		else {
			$("#submit").focus();
		}
		
		e.preventDefault();
	  }
	});
	
	//#############################################
	
	$("#send_by").keydown(function (e) {    
	  if (e.which == 9) {
		      
		if($("#weight").val()==""){
			$("#weight").focus();	
		}
		else {
			$("#submit").focus();
		}
		
		e.preventDefault();
	  }
	});
	
	//#############################################
	
	$("#weight").keydown(function (e) {    
	  if (e.which == 9) {
		      
		/*if($("#insured_value").val()==""){
			$("#insured_value").focus();	
		}
		else {*/
		$("#submit").focus();
		//}
		
		e.preventDefault();
	  }
	});
	

});//eof of ready function
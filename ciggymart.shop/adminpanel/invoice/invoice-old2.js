// JavaScript Document
$(document).ready(function(){
	
	$('#hideDiv').hide();
	
	///////////////////////////////////
	// validation for Search Client
	////////////////////////////////////
	$("#searchClient").validate({
		rules: 
		{ 		
			client_name: 
			{ 
				required: true,
				clinetExists:true,
			},
		},
		message:
		{
		}
		/*onkeyup:false,
		onfocusout:false,
		onclick:false*/
	});// eof validation
	
	///////////////////////////////////
	// validation for Date
	////////////////////////////////////
	$("#searchRate").validate({
		rules: 
		{ 
		
			from_date: 
			{ 
				required: true,
			    maxDate:true,
				// dateExists:true,
			},
			to_date:
			{
				required: true,
				greaterThenFrom: true,
			},
			
		},
		messages:
		{
			
		}
	});// eof validation


	/*$("#editInvoice").validate({
		rules: 
		{ 
		
			from_date: 
			{ 
				required: true,
			    maxDate:true,
				// dateExists:true,
			},
			to_date:
			{
				required: true,
				greaterThenFrom: true,
			},
			
		},
		messages:
		{
			
		}
	});// eof validation*/
	
	
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
			   url: "invoice_curd.php",
			   data:formdata,
			   success: function(data){ //alert(data);
				   $('#clientname').html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
	});
	
	////////////////////////////////////	
	//// method for client validation ////
	////////////////////////////////////
    $.validator.addMethod('clinetExists', function(val, element)
	{		
		$.ajax({
			 url:"invoice_curd.php",
			 type: "POST",
			 data: {type:"clinetExists",client_name:$('#client_name').val()},
			 async:false,
			 success:function(data){//alert(data);
				 isSuccess=(data==1)?true:false;
			 }
		});//eof ajax
		return isSuccess ;				
	}, 'Invalid Client Name.'); 
	
	////////////////////////////////////
	///// method for date validation////
	////////////////////////////////////
    $.validator.addMethod('greaterThenFrom', function(val, element)
	{
		$.ajax({
			 url:"invoice_curd.php",
			 type:"POST",
			 data:{type:"greaterThenFromDate",from_date: $('#from_date').val(), to_date: $("#to_date").val()},
			 async:false,
			 success:function(data){ //alert(data);
			 	
				isSuccess=(data==1)?true:false;
				
			}
		});//eof ajax		
		return isSuccess ;
				
	}, 'The date should not be greater than FROM Date.');
	
	
	////////////////////////////////////////////
	///// method for date validation ////
	////////////////////////////////////////////	
    $.validator.addMethod('maxDate', function(val, element)
	{				
		$.ajax({
			 url:"invoice_curd.php",
			 type:"POST",
			 data:{type:"maxDate",from_date: $('#from_date').val(),client_id:$('#client_id').val(),Last_Date_input:$('#Last_Date_input').val()},
			 async:false,
			 success:function(data){ //alert(data);
			 	if(data!=2){
					isSuccess=(data==1)?true:false;
				}
				else{
					isSuccess=true;
				}
			}
		});//eof ajax		
		return isSuccess ;				
	}, 'Date Should be greater than Last Invoice Date.');
	
	
	////////////////////////////////////////////
	// Method to check if the data exist or not
	////////////////////////////////////////////
    $.validator.addMethod('dateExists', function(val, element)
	{		
		$.ajax({
				 url:"invoice_curd.php",
				 type: "POST",
				 data: {type:"dateExists",from_date: $('#from_date').val(),cust_code:$('#cust_code').val()},
				 async:false,
				 success:function(data){// alert(data);
					 isSuccess=(data==0)?true:false;
				 }
		});//eof ajax
		return isSuccess ;				
	}, 'Already Generate Invoice.'); 
	
	
	//////////////////////////////////////////
	// on click of submit button /////////////
	//////////////////////////////////////////
	$(document).on("click","#submit", function(){
		
		flag=$("#searchClient").valid();
		
		if (flag==true)
		{		
			$("#loader").show();
			
			var x;
			$.ajax({
			   type: "POST",
			   url: "invoice_curd.php",
			   data:{type:"client",client_id:$('#client_id').val()},
			   async: false,
			   success: function(data){ //alert(data);
			   	  
				  $("#loader").show();
				  
				  x=data;
				  value=data.split("@"); 
				  $('#hideDiv').show();
				  
				  $('#C_Name').html(value[0]);
				  $('#Joining_Date').html(value[1]);
                  $('.Last_Date').html(value[2]);
				  
				  var lastdate = value[2]==''? value[1] : value[2];
				  
				  $('#Last_Date_input').val(lastdate);
				  $('#from_date').val('');
				  $('#to_date').val('');
				  
				  $("#loader").hide('slow');
			   },			 
			});//eof ajax			
		}
	});
	
	
	////////////////////////////////////////////
	// Click Function to get all consignments
	////////////////////////////////////////////		
	$(document).on('click', '#search', function() {
		
		
		flag=$("#searchRate").valid();
		
		if (flag==true)
		{		
			
			var x;
			$.ajax({
			   type: "POST",
			   url: "invoice_curd.php",
			   data:{type:"searchClient",from_date: $('#from_date').val(),to_date:$('#to_date').val(),client_id:$('#client_id').val()},
			   async: false,
			   success: function(data){ //alert(data);
				  //x=data;
				  //value=data.split("@"); 
				  /*$('#client_code').val(value[0]);*/
				  //$('#last_date').val(value[1]);
				  //$('#detail').html(value[2]);
				  $('#detail').html(data)
			   },
			 
			});//eof ajax
			
		}
	});
	
	////////////////////////////////////////////
	// Click Function to generate the invoice
	////////////////////////////////////////////
	$(document).on('click', '#generate', function() {	
		
		
		flag=$("#searchRate").valid();
		
		if (flag==true)
		{			
			$("#loader").show();
			
			var x;
			$.ajax({
			   type: "POST",
			   url: "invoice_curd.php",
			   data:{type:"generateInvoice",from_date: $('#from_date').val(),to_date:$('#to_date').val(),client_id:$('#client_id').val()},
			   success: function(data){ //alert(data);
				  x=data;
				  if(x!=0)
				  {
					$("#loader").hide('slow');
					location.reload();
				  }
			   },
			 
			});//eof ajax
			 
		}
	});
	
	/////////////////////////////////////////////////////
	// click function to select all records
	/////////////////////////////////////////////////////
	$(document).on('click', '#select_all', function() {	
		
		var x;
		$.ajax({
		   type: "POST",
		   url: "invoice_curd.php",
		   data:{type:"searchAll"},
		   async: false,
		   success: function(data){ //alert(data);
			   x=data;
			   $('#detail').html(data);
		   },
		});//eof ajax
	});
	
	
	/////////////////////////////////////////////////////
	// function for Invoice payment
	/////////////////////////////////////////////////////
	$(document).on('click', '.pay', function() {
		
			var x;
			$.ajax({
			   type: "POST",
			   url: "invoice_curd.php",
			   data:{type:"payment",id: $(this).attr('id')},
			   async: false,
			   success: function(data){//alert(data);
				   x=data;
			   },
			  
			});//eof ajax
		 if(x!=0)
			  {
				location.reload();  
				  
			  }
			  
	});
	
	
	/////////////////////////////////////////////////////
	//function for resend the invoice to client
	/////////////////////////////////////////////////////
	$(document).on('click', '.resend', function() {
		
			var x;
			$.ajax({
			   type: "POST",
			   url: "invoice_curd.php",
			   data:{type:"resend",id: $(this).attr('id')},
			   async: false,
			   success: function(data){// alert(data);
				   x=data;
			   },
			  
			});//eof ajax
		 if(x!=0)
			  {
				location.reload();  
				  
			  }
			  
			  
	});
	
	/////////////////////////////////////////////////////
	//function for delete the invoice record
	/////////////////////////////////////////////////////
	
	/*$(document).on('click', '.delete', function() {
		var didConfirm = confirm("Are you sure?");
	    if (didConfirm == true) {
			
			var x;
			$.ajax({
			   type: "POST",
			   url: "invoice_curd.php",
			   data:{type:"delete",id: $(this).attr('id')},
			   async: false,
			   success: function(data){ //alert(data);
				   x=data;
			   },
			  
			});//eof ajax
		 
				location.reload();  
			  }
			  
			  
	});*/
	
	/////////////////////////////////////////////////////
	// In edit invoice page radio buttons manupulation 
	/////////////////////////////////////////////////////	
	$(document).on('click', '.disPercentRadio', function() {
		var str = $(this).attr('id');
		var id = str.split('-');
		
		$("#discount_percent-"+id[1]).removeAttr('readonly');
		$("#discount_rs-"+id[1]).attr('readonly', 'true');
                
        /* ################################################# */
        var discount = ($("#subtotal"+id[1]).val() * $("#discount_percent-"+id[1]).val())/100;
		var total_amt = $("#subtotal"+id[1]).val() - discount;
		var Final_Amt = parseInt(total_amt) + parseInt($("#insrncChrgs"+id[1]).val());
		/* ################################################# */

		if($("#subtotal"+id[1]).val()!=""){
		
			if($("#discount_percent-"+id[1]).val()!=""){
				
				if($("#total_amt"+id[1]).val()!=""){
					
					$("#total_amt"+id[1]).val("");
					$("#total_amt"+id[1]).val(total_amt.toFixed(2));// toFixed(2) is used for display 2 decimal places
					$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2))
					reCalulate();
				}
				else{
					$("#total_amt").val(total_amt.toFixed(2)); // toFixed(2) is used for display 2 decimal places
					$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2))
					reCalulate();
				}			
			}
			else{
				var subtotal= $("#subtotal"+id[1]).val();
				$("#total_amt"+id[1]).val(subtotal.toFixed(2));// toFixed(2) is used for display 2 decimal places
				reCalulate();
			}
			
		}
	});
	
	///////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////
	
	$(document).on('click', '.disRsRadio', function() {
		var str = $(this).attr('id');
		var id = str.split('-');
		
		$("#discount_rs-"+id[1]).removeAttr('readonly');
		$("#discount_percent-"+id[1]).attr('readonly', 'true');
		
		/*##############################################*/
        var total_amt = $("#subtotal"+id[1]).val() - $("#discount_rs-"+id[1]).val();
        var Final_Amt = parseInt(total_amt) + parseInt($("#insrncChrgs"+id[1]).val());
        /* ################################################# */
        		
		if($("#discount_rs-"+id[1]).val()!=""){
			
			if($("#total_amt"+id[1]).val()!=""){
				
				$("#total_amt"+id[1]).val("");
				$("#total_amt"+id[1]).val(total_amt.toFixed(2));// toFixed(2) is used for display 2 decimal places
				$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2))
				reCalulate();
			}
			else{
				$("#total_amt"+id[1]).val(total_amt.toFixed(2));// toFixed(2) is used for display 2 decimal places
				reCalulate();
			}	
		}
		else{
			var subtotal= $("#subtotal"+id[1]).val();
			$("#total_amt"+id[1]).val(subtotal.toFixed(2));// toFixed(2) is used for display 2 decimal places
			$("#FinalAmt"+id[1]).val(total_amt + $("#insrncChrgs"+id[1]).val())
			reCalulate();
		}
		
	});
	

	//////////////////////////////////////////////////////////
	// In "edit invoice page" Discount text boxes manupulation 
	//////////////////////////////////////////////////////////
	
	$(document).on('keyup', '.discount_percent', function() {
		
		//////////////////////////////////////////////////
		//validation for discount in percent
		// input[name^=discount_percent] means the input box 
		// which name attribute value starts with "discount_percent"
		//////////////////////////////////////////////////
		$("input[name^=discount_percent]").each(function () {
			$(this).rules("add", {
				required: true,
				number: true
			});
		});
		////////////////////////////
		
		var str = $(this).attr('id');
		var id = str.split('-');

		var discount = ($("#subtotal"+id[1]).val() * $(this).val())/100;
		var total_amt = $("#subtotal"+id[1]).val() - discount;
		var Final_Amt = parseInt(total_amt) + parseInt($("#insrncChrgs"+id[1]).val());
		
		$("#total_amt"+id[1]).val(total_amt.toFixed(2));
		$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2));
		$("#discount_rs-"+id[1]).val(0);

		reCalulate();
	});

	//////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////
	$(document).on('keyup', '.discount_rs', function() {
		
		//////////////////////////////////////////////////
		//validation for discount in rupees
		// input[name^=discount_percent] means the input box 
		// which name attribute value starts with "discount_percent"
		//////////////////////////////////////////////////
		$("input[name^=discount_rs]").each(function () {
			$(this).rules("add", {
				required: true,
				number: true
			});
		});
		////////////////////////////
		
		var str = $(this).attr('id');
		var id = str.split('-');

		var total_amt = $("#subtotal"+id[1]).val() - $(this).val();
		var Final_Amt = parseInt(total_amt) + parseInt($("#insrncChrgs"+id[1]).val());
		
		$("#total_amt"+id[1]).val(total_amt.toFixed(2));
		$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2));
		$("#discount_percent-"+id[1]).val(0);

		reCalulate();
	});
	

	//////////////////////////////////////////////////////////
	// In "edit invoice page" this keyup function will execute 
	// when we change the weight 
	//////////////////////////////////////////////////////////
	
	$(document).on('keyup', '.weight', function() {
		
		////////////////////////////
		//validation for weight
		////////////////////////////
		$(this).rules("add", {
			required: true,
			number: true
		});
		////////////////////////////		
		
		var str = $(this).attr('id');
		var id = str.split('-');

		if($(this).val()=="" || $(this).val()==0 || isNaN($(this).val()))
		{
			$("#subtotal"+id[1]).val(0);			
			$("#total_amt"+id[1]).val(0);
			$("#FinalAmt"+id[1]).val(0);
		}
		else{

			var formdata = new FormData();
				formdata.append('type', "getSubtotal");
				formdata.append('client_id', $("#client_id").val());
				formdata.append('dest_id', $("#dest_id"+id[1]).val());
				formdata.append('send_by', $("#send_by"+id[1]).val());
				formdata.append('weight', $(this).val());
			
			var x;
				$.ajax({
					type:"POST",
					url:"invoice_curd.php",
					data:formdata,
					success: function(data){ 
						
						if($("#discount_percent-"+id[1]).val()!=0 && $("#discount_rs-"+id[1]).val()==0){
							
							var discount = (data * $("#discount_percent-"+id[1]).val())/100;
							var total_amt = data - discount;
							var Final_Amt = parseInt(total_amt) + parseInt($("#insrncChrgs"+id[1]).val());
							
							$("#subtotal"+id[1]).val(data);
							$("#total_amt"+id[1]).val(total_amt.toFixed(2));
							$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2));
							
							reCalulate();
							
						}//eof if condition
							else if($("#discount_percent-"+id[1]).val()==0 && $("#discount_rs-"+id[1]).val()!=0){
															
								var total_amt = data - $("#discount_rs-"+id[1]).val();
								var Final_Amt = parseInt(total_amt) + parseInt($("#insrncChrgs"+id[1]).val());
									
								$("#subtotal"+id[1]).val(data);
								$("#total_amt"+id[1]).val(total_amt.toFixed(2));
								$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2));
								
								reCalulate();
									
							}//eof else if condition

						else if($("#discount_percent-"+id[1]).val()==0 && $("#discount_rs-"+id[1]).val()==0){

							var total_amt = data;
							var Final_Amt = parseInt(total_amt) + parseInt($("#insrncChrgs"+id[1]).val());

							$("#subtotal"+id[1]).val(data);
							$("#total_amt"+id[1]).val(data);
							$("#FinalAmt"+id[1]).val(Final_Amt.toFixed(2));
							
							reCalulate();
						}//eof else
					},			
					cache: false,
					contentType: false,
					processData: false
				});//eof ajax
		}// eof else

	});// eof keyup function
	
	
	//////////////////////////////////////
	// call a function on page load
	//////////////////////////////////////
	reCalulate();
	/////////////////////////
	
	/////////////////////////////////////////
	// this function is used to re-calculate the
	// invoice amount
	function reCalulate(){
		var invoicetotal = 0;
		
		$(".finalAmount").each(function(){
			invoicetotal = invoicetotal + parseFloat($(this).val());
		});
		
		$("#invoicetotal").val(invoicetotal.toFixed(2));
		
		var fuelSurcharge = (invoicetotal.toFixed(2) * parseFloat($("#fuelsrchrgPercent").val()))/100;
		$("#fuelsurcharge").val(fuelSurcharge.toFixed(2));
		
		var invoicesubtotal = parseFloat(invoicetotal.toFixed(2)) + parseFloat(fuelSurcharge.toFixed(2));
		$("#invoiceSubtotal").val(invoicesubtotal.toFixed(2));
		
		var noGstDate = new Date("2017-06-30");
		var dateto = new Date($("#dateto").val());
		
		// if noGstDate is smaller than
		// dateto then calculate only GST
		if(noGstDate < dateto)
		{

			if($("#GST_Within_State").val()=='0'){
				var sgst = (invoicesubtotal.toFixed(2) * parseFloat($("#SGSTpercent").val()))/100;
				$("#sgst").val(sgst.toFixed(2));
				
				var cgst = (invoicesubtotal.toFixed(2) * parseFloat($("#CGSTpercent").val()))/100;
				$("#cgst").val(cgst.toFixed(2));
				
				var igst = parseFloat("0.00");
				$("#igst").val(igst.toFixed(2));
			}
			else{
				var sgst = parseFloat("0.00");
				$("#sgst").val(sgst.toFixed(2));
				
				var cgst = parseFloat("0.00");
				$("#cgst").val(cgst.toFixed(2));
				
				var igst = (invoicesubtotal.toFixed(2) * parseFloat($("#IGSTpercent").val()))/100;
				$("#igst").val(igst.toFixed(2));
			}
			
			var serviceTax = parseFloat("0.00");
			$("#serviceTax").val(serviceTax.toFixed(2));
			
			var sbTax = parseFloat("0.00");
			$("#sbTax").val(sbTax.toFixed(2));
			
			var kkTax = parseFloat("0.00");
			$("#kkTax").val(kkTax.toFixed(2));
			
			var invoiceFinalAmt = parseFloat(invoicesubtotal + igst + sgst + cgst + serviceTax + sbTax + kkTax);
			$("#invoiceFinalAmt").val(Math.round(parseFloat(invoiceFinalAmt)).toFixed(2));
		}
		else{

			var sgst = parseFloat("0.00");
			$("#sgst").val(sgst.toFixed(2));
			
			var cgst = parseFloat("0.00");
			$("#cgst").val(cgst.toFixed(2));
			
			var igst = parseFloat("0.00");
			$("#igst").val(igst.toFixed(2));			
			
			var serviceTax = (invoicesubtotal.toFixed(2) * parseFloat($("#servicetaxpercent").val()))/100;
			$("#serviceTax").val(serviceTax.toFixed(2));
			
			var sbTax = (invoicesubtotal.toFixed(2) * parseFloat($("#sbTaxPercent").val()))/100;
			$("#sbTax").val(sbTax.toFixed(2));
			
			var kkTax = (invoicesubtotal.toFixed(2) * parseFloat($("#kkTaxPercent").val()))/100;
			$("#kkTax").val(kkTax.toFixed(2));	
			
			var invoiceFinalAmt = parseFloat(invoicesubtotal + igst + sgst + cgst + serviceTax + sbTax + kkTax);
			$("#invoiceFinalAmt").val(Math.round(parseFloat(invoiceFinalAmt)).toFixed(2));
		}
		
		
	}
	
	
	/////////////////////////////////////////////
	// execute this function on click of 
	// "selecctall" class checkbox
	/////////////////////////////////////////////
	function selectallids(){
		$('.delete1').each(function() {			
			 this.checked = true;  //select all checkboxes with class "delete1"			
			// checked itms only pushed in array
			checkedCons.push($(this).attr("id"));//put the checked Consignment_Id's in the array
		});
		return checkedCons;
	}
	
	/////////////////////////////////////////////
	// check or uncheck each "delete1" class 
	// on click of "selecctall" class checkbox
	/////////////////////////////////////////////
	var checkedCons = [];
	
	$(document).on('click', '#selecctall', function(event) {//on click 
        if(this.checked) { // check select status
			selectallids();
        }
		else { // check select status
            $('.delete1').each(function() { //loop through each checkbox
                this.checked = false;  //select all checkboxes with class "delete1"
				// remove all values from array
				checkedCons = jQuery.grep(checkedCons, function( a ) {
				  return a == $(this).attr("id");
				});
				
            });
        }
		
    });
	
	
	//////////////////////////////////////////
	// Get the ID's of Deleted consignments //
	//////////////////////////////////////////
	$(document).on('click', '.delete1', function(){
		     
		if($(this).prop('checked') == true)
		{
			$(this).parents().children('td').css({'background-color':'#FA6C69', 'color':'#fff'})
			
			//$.inArray() method returns -1 when it doesn't find a match. 
			//If the first element within the array matches value, $.inArray() returns 0
			var trueFlaseVal = $.inArray($(this).attr("id"),checkedCons);
			
			if(trueFlaseVal == -1){
				
				checkedCons.push($(this).attr("id"));//put the checked Consignment_Id's in the array
				//alert(checkedCons);
			}
			else{				
				checkedCons;
				//alert(checkedCons);				
			}
			
		}//eof if condition
		
		else{
			
			$(this).parents().children('td').css({'background-color':'#fff', 'color':'#555'})
			
			// remove all values from array
			checkedCons = jQuery.grep(checkedCons, function( a ) {
			  return a == $(this).attr("id");
			});				
			
			// checked itms only pushed in array
			$(".delete1").each(function(){
				if($(this).prop('checked') == true)
				{
					checkedCons.push($(this).attr("id"));//put the checked Consignment_Id's in the array
				}
			});
			
		}
				
	});
	
	
	////////////////////////////////////////////////////////
	// if uncheck any "delete1" class of checkbox
	// then uncheck the "selecctall" class of checkbox also
	////////////////////////////////////////////////////////
	$(".delete1").change(function (event) { 
          if($('#selecctall').prop('checked') == true)	{	
	          $('#selecctall').attr('checked',false); 	
		  }
    });
	
	//////////////////////////////////////
	// it should be define for validation
	//////////////////////////////////////
	$("#editInvoice").validate({
	});
	
	////////////////////////////////////
	// click function for edit button
	////////////////////////////////////	
	$(document).on('click', '#edit', function() {
		
		//////////////////////////////////////////////////
		// validation for weight
		// input[name^=discount_percent] means the input box 
		// which name attribute value starts with "discount_percent"
		//////////////////////////////////////////////////
		$("#editInvoice").valid();
		
		$("#loader").show();
		
		$("input[name^=weight]").each(function () {
			$(this).rules("add", {
				required: true,
				number: true
			});
		});
		///////////////////////////////
		// getting the form values
		///////////////////////////////		
		var weight = [];
		var cid = [];
		$(".weight").each(function(){
			weight.push($(this).val());
			
			// this is consignment id
			cid.push($(this).attr('id'));			
		});
		
		var consignmentDate = [];
		$(".consignmentDate").each(function(){
			consignmentDate.push($(this).val());
		});
		
		var consignmentNo = [];
		$(".consignmentNo").each(function(){
			consignmentNo.push($(this).val());
		});
		
		var destName = [];
		$(".destName").each(function(){
			destName.push($(this).val());
		});
		
		var subtotal = [];
		$(".subtotal").each(function(){
			subtotal.push($(this).val());
			//alert(subtotal);
		});
		
		var discount_percent = [];
		$(".discount_percent").each(function(){
			discount_percent.push($(this).val());
		});
		
		var discount_rs = [];
		$(".discount_rs").each(function(){
			discount_rs.push($(this).val());
		});
		
		var total_amt = [];
		$(".total_amt").each(function(){
			total_amt.push($(this).val());
		});
		
		var insrncChrgs = [];
		$(".insrncChrgs").each(function(){
			insrncChrgs.push($(this).val());
		});
		
		var finalAmount = [];
		$(".finalAmount").each(function(){
			finalAmount.push($(this).val());
		});
		
		/*var checkedConsignments = [];			
		$('.delete1').each(function(){	       
			if($(this).prop('checked') == true)		 
			{
				checkedConsignments.push($(this).attr("id"));//put the checked Consignment_Id's in the array
			}//eof if condition
	    });//eof each function*/
		
		var invoiceAmt = $("#invoicetotal").val();
		var fuelsrchrgPercent = $("#fuelsrchrgPercent").val();
		var fuelsurcharge = $("#fuelsurcharge").val();
		var invoiceSubtotal = $("#invoiceSubtotal").val();
		var igst = $("#igst").val();
		var sgst = $("#sgst").val();
		var cgst = $("#cgst").val();
		var serviceTax = $("#serviceTax").val();
		var sbTax = $("#sbTax").val();
		var kkTax = $("#kkTax").val();
		var invoiceFinalAmt = $("#invoiceFinalAmt").val();
		var client_id = $("#client_id").val();
		var invoiceId = $("#invoiceId").val();
		
		///////////////////////////////////
		// append the values into formdata
		///////////////////////////////////
		var formdata = new FormData();
		formdata.append('type', "update");
		formdata.append('cid', cid);
		formdata.append('consignmentDate', consignmentDate);
		formdata.append('consignmentNo', consignmentNo);
		formdata.append('destName', destName);
		formdata.append('weight', weight);
		formdata.append('subtotal', subtotal);		
		formdata.append('discount_percent', discount_percent);
		formdata.append('discount_rs', discount_rs);
		formdata.append('total_amt', total_amt);
		formdata.append('insrncChrgs', insrncChrgs);
		formdata.append('finalAmount', finalAmount);// finalAmount = total_amt + insrncChrgs
		formdata.append('checkedCons', checkedCons)// checkedCons holds only those consignment Id's which will be delete permanantly from DB.
		//formdata.append('checkedConsignments', checkedConsignments)// delete_id holds only checked consignment Id's
		
		
		formdata.append('invoiceAmt', invoiceAmt);
		formdata.append('fuelsrchrgPercent', fuelsrchrgPercent);
		formdata.append('fuelsurcharge', fuelsurcharge);
		formdata.append('invoiceSubtotal', invoiceSubtotal);
		formdata.append('igst', igst);
		formdata.append('sgst', sgst);
		formdata.append('cgst', cgst);
		formdata.append('serviceTax', serviceTax);
		formdata.append('sbTax', sbTax);
		formdata.append('kkTax', kkTax);
		formdata.append('invoiceFinalAmt', invoiceFinalAmt);
		
		formdata.append('client_id', client_id);
		formdata.append('invoiceId', invoiceId);
	
		
		var x;
			$.ajax({
				type:"POST",
				url:"invoice_curd.php",
				data:formdata,
				success: function(data){ //alert(data);
					x = data;
					
					if(x!=0){
						$("#loader").hide('slow');
						//$("#invoice").html(data);
						window.location.href='report.php';
					}
				},			
				cache: false,
				contentType: false,
				processData: false
			});//eof ajax
		
		
		/*var didConfirm = confirm("Are you sure?");
	    if (didConfirm == true) {
				
		}*///eof if condition
		
	});
	
	
	/////////////////////////////////////////////
	// click function for invoice preview button
	/////////////////////////////////////////////	
	$(document).on('click', '#preview', function() {
		
		$("#loader").show();
		
		///////////////////////////////
		// getting the form values
		///////////////////////////////		
		var weight = [];
		var cid = [];
		$(".weight").each(function(){
			weight.push($(this).val());
			
			// this is consignment id
			cid.push($(this).attr('id'));			
		});
		
		var consignmentDate = [];
		$(".consignmentDate").each(function(){
			consignmentDate.push($(this).val());
		});
		
		var consignmentNo = [];
		$(".consignmentNo").each(function(){
			consignmentNo.push($(this).val());
		});
		
		var destName = [];
		$(".destName").each(function(){
			destName.push($(this).val());
		});
		
		var subtotal = [];
		$(".subtotal").each(function(){
			subtotal.push($(this).val());
			//alert(subtotal);
		});
		
		var discount_percent = [];
		$(".discount_percent").each(function(){
			discount_percent.push($(this).val());
		});
		
		var discount_rs = [];
		$(".discount_rs").each(function(){
			discount_rs.push($(this).val());
		});
		
		var total_amt = [];
		$(".total_amt").each(function(){
			total_amt.push($(this).val());
		});
		
		var insrncChrgs = [];
		$(".insrncChrgs").each(function(){
			insrncChrgs.push($(this).val());
		});
		
		var finalAmount = [];
		$(".finalAmount").each(function(){
			finalAmount.push($(this).val());
		});
		
		var invoiceAmt = $("#invoicetotal").val();
		var fuelsrchrgPercent = $("#fuelsrchrgPercent").val();
		var fuelsurcharge = $("#fuelsurcharge").val();
		var invoiceSubtotal = $("#invoiceSubtotal").val();
		var igst = $("#igst").val(); 
		var sgst = $("#sgst").val();
		var cgst = $("#cgst").val();
		var serviceTax = $("#serviceTax").val();
		var sbTax = $("#sbTax").val();
		var kkTax = $("#kkTax").val();
		var invoiceFinalAmt = $("#invoiceFinalAmt").val();
		var client_id = $("#client_id").val();
		var invoiceId = $("#invoiceId").val();
		
		///////////////////////////////////
		// append the values into formdata
		///////////////////////////////////
		var formdata = new FormData();
		formdata.append('type', "preview");
		formdata.append('cid', cid);
		formdata.append('consignmentDate', consignmentDate);
		formdata.append('consignmentNo', consignmentNo);
		formdata.append('destName', destName);
		formdata.append('weight', weight);
		formdata.append('subtotal', subtotal);		
		formdata.append('discount_percent', discount_percent);
		formdata.append('discount_rs', discount_rs);
		formdata.append('total_amt', total_amt);
		formdata.append('insrncChrgs', insrncChrgs);
		formdata.append('finalAmount', finalAmount);// finalAmount = total_amt + insrncChrgs
		formdata.append('checkedCons', checkedCons)// checkedCons holds only those consignment Id's which will be delete permanantly from DB.
		//formdata.append('checkedConsignments', checkedConsignments)// delete_id holds only checked consignment Id's
		
		
		formdata.append('invoiceAmt', invoiceAmt);
		formdata.append('fuelsrchrgPercent', fuelsrchrgPercent);
		formdata.append('fuelsurcharge', fuelsurcharge);
		formdata.append('invoiceSubtotal', invoiceSubtotal);
		formdata.append('igst', igst);
		formdata.append('sgst', sgst);
		formdata.append('cgst', cgst);
		formdata.append('serviceTax', serviceTax);
		formdata.append('sbTax', sbTax);
		formdata.append('kkTax', kkTax);
		formdata.append('invoiceFinalAmt', invoiceFinalAmt);
		
		formdata.append('client_id', client_id);
		formdata.append('invoiceId', invoiceId);
	
		
		var x;
			$.ajax({
				type:"POST",
				url:"invoice_curd.php",
				data:formdata,
				success: function(data){ //alert(data);
					x = data;
					
					if(x!=0){
						$("#loader").hide('slow');
						$(".main").html(data);
						$("#preview").hide();
						$("#delete").hide();
						$("#backBtn").show();
					}
				},			
				cache: false,
				contentType: false,
				processData: false
			});//eof ajax
		
		
		
	});
	$("#backBtn").click(function(){
		location.reload();
	});
	
	//////////////////////////////////
	// on click of delete button
	//////////////////////////////////
	$("#delete").click(function(){
		
		var i=0;
		var delete_id = [];	
		
		$('.delete1').each(function(){	       
			if($(this).prop('checked') == true)		 
			{
				delete_id.push($(this).attr("id"));//put the Consignment_Id's in the array
				i++; //to getting how many checkboxes are checked
			}//eof if condition
	    });//eof each function
		
		
		if(i==0){			
			alert("Please Select Any One Option"); 			 
		}//eof if condition
		
		if(i!=0){			 
			
			var additionAmt = 0;
			
			$('.delete1').each(function(){
				if($(this).prop('checked') == true)		 
				{					
					var rec = $(this).closest("tr").find(".finalAmount").val();
					
					additionAmt = parseFloat(additionAmt) + parseFloat(rec);
					
					$(this).parent().parent().remove();
					
				}//eof if condition
			});//eof each function	
			
			reCalulate();
			
		}//eof if condition
		
	});//eof of delete event
	

});//eof of ready function
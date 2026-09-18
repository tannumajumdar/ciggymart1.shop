<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$getConsignments = $db->ExecuteQuery("SELECT Consignment_Id, DATE_FORMAT(Date_Of_Submit, '%d-%m-%Y') AS Date, Consignment_No, Send_By, Total_Weight_In_KG, Subtotal, Discount_Rs, Discount_Percent, Total_Amount, Insurance_Other_Charges, (Total_Amount + Insurance_Other_Charges) AS FinalAmount, C.Client_Id, C.Destination_Id, Destination_Name, FORMAT(Fuel_Surcharge, 2) AS Fuel_Surcharge, CL.GST_Within_State

FROM tbl_consignments C

INNER JOIN tbl_destinations D ON C.Destination_Id = D.Destination_Id 
INNER JOIN tbl_clients CL ON CL.Client_Id = C.Client_Id 
WHERE Date_Of_Submit BETWEEN (SELECT Date_From FROM tbl_invoices WHERE Invoice_Id=".$_GET['id'].") AND (SELECT Date_To FROM tbl_invoices WHERE Invoice_Id=".$_GET['id'].") AND C.Client_id = (SELECT Client_id FROM tbl_invoices WHERE Invoice_Id=".$_GET['id']." AND Branch_Id=".$_SESSION['buser'].") ORDER BY Date_Of_Submit ASC, Consignment_No ASC");

$getTaxes = $db->ExecuteQuery("SELECT IGST, SGST, CGST, Service_Tax, SB_Tax, KKC_Tax FROM tbl_taxes");

$getInvoiceDate = $db->ExecuteQuery("SELECT Invoice_No, DATE_FORMAT(Date_From, '%d-%m-%Y') AS Date_From, DATE_FORMAT(Date_To, '%Y-%m-%d') AS Date_To, DATE_FORMAT(Date_To, '%d-%m-%Y') AS Invoice_Date_To FROM tbl_invoices WHERE Invoice_Id=".$_GET['id']);

?>

<script type="text/javascript" src="invoice.js" ></script>

<script>
	
$(document).ready(function(){ 
	
	/////////////////////////////////////////////////////
	// Get Client Code and Name list(autocomplete)///////
	///////////////////////////////////////////////////// 
	var clientcodelist = [
		<?php // PHP begins here
		  
			$menu=$db->ExecuteQuery("SELECT Client_Code FROM tbl_clients WHERE Client_Id IN (SELECT Client_Id FROM tbl_rates)");
			foreach($menu as $val) {
				echo '"'.$val['Client_Code']. '",';
			} // PHP ends here*/
		?>
	];// eof json format
	
	$("#client_code").autocomplete({
   
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( clientcodelist, function(item,index){
				return matcher.test(item);
			});
			responseFn( a );
		}
	})
	.on( 'autocompleteresponse autocompleteselect', function( e, ui ){
					
			var formdata = new FormData();
			formdata.append('type', "getClientName");
			formdata.append('client_code', $("#client_code").val());
	
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
	
	///////////////////////////////////////////
	// Set Date Picker Date Format ////////////
	///////////////////////////////////////////
	$(function() {
		$("#from_date").datepicker({
			dateFormat: "dd-mm-yy"
		});
	
		$("#to_date").datepicker({
			dateFormat: "dd-mm-yy"
		});
	});
});
</script>

<style type="text/css" media="print">
  @page { size: portrait; }
  @media print
   {
      #admglobal_container, nav, .pageTitle {display:none;}
   }
</style>

<div id="loader">
    <div class="loader-block"><i class="fa-li fa fa-spinner fa-spin spinloader"></i></div>
</div>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="report.php"><i class="glyphicon glyphicon-list"></i> <strong>Invoice Report</strong></a>
        <button type="button" class="btn btn-danger btn-sm delete" id="delete" name="delete"> <span class="glyphicon glyphicon-trash"></span> Delete </button>
        <button onclick="window.print()" type="button" class="btn btn-info btn-sm" id="print" name="print"> <span class="glyphicon glyphicon-print"></span> Print </button>
        
        <button type="button" class="btn btn-warning btn-sm preview" id="preview" name="preview"> <span class="glyphicon glyphicon-eye-open"></span> Invoice Preview </button>
        
        <button id="backBtn" class="btn btn-sm btn-success" style="display:none;">Back</button>
    </div>
	<h1 class="pull-left">Edit Invoice</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
	<div>
    	<div class="col-sm-2"><strong>Invoice No:</strong> <?php echo $getInvoiceDate[1]['Invoice_No']; ?></div>
        <div class="col-sm-4"><strong>From:</strong> <?php echo $getInvoiceDate[1]['Date_From']; ?> <strong>To:</strong> <?php echo $getInvoiceDate[1]['Invoice_Date_To']; ?></div>
        <div class="clearfix"></div>
    </div>
    
	<div class="clear formbgstyle">
		<form class="form-horizontal" role="form" id="editInvoice" method="post">
            <input type="hidden" name="dateto" id="dateto" value="<?php echo $getInvoiceDate[1]['Date_To']; ?>" />
			<div>
		    	<table class="table table-bordered table-hover table-condensed">
		        	<tr class="success">
                    	<th><input type="checkbox" id="selecctall" /></th>
		            	<th>Sno</th>
		                <th width='90'>Date</th>
		                <th width='80'>AWB No.</th>
		                <th width='110'>Destination</th>
                        <th width='100'>Sent By</th>
		                <th width='70'>Total Wgt. (in KG.)</th>
		                <th>Subtotal</th>
		                <th>Discount (%)</th>
		                <th>Discount (Rs.)</th>
		                <th>Amount</th>
		                <th>Insurance Charges</th>
		                <th>Total Amount</th>
		            </tr>
		  <?php 
		  $i=1;
		  foreach($getConsignments as $getConsignmentsVal){ ?>
		            <tr class="consignmentInfo">
		            	<td class="singleCheckbox"><input class="delete1" type="checkbox" id="<?php echo $getConsignmentsVal['Consignment_Id']; ?>"  /></td>
                        <td><?php echo $i; ?></td>
		            	<td>
							<input type="hidden" class="consignmentDate" value="<?php echo $getConsignmentsVal['Date']; ?>">
							<?php echo $getConsignmentsVal['Date']; ?>
                        </td>
		                <td>
							<input type="hidden" class="consignmentNo" value="<?php echo $getConsignmentsVal['Consignment_No']; ?>">
							<?php echo $getConsignmentsVal['Consignment_No']; ?>
                        </td>                
		                <td>
							<input type="hidden" class="destName" value="<?php echo $getConsignmentsVal['Destination_Name']; ?>">
							<?php echo $getConsignmentsVal['Destination_Name']; ?>
                        </td>
                        <td>
                        	<select id="send_by<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="send_by<?php echo $getConsignmentsVal['Consignment_Id']; ?>"  class="form-control input-sm sentById" readonly>
                                <option value="">Select Mode</option>
                                <option <?php echo $getConsignmentsVal['Send_By']==1?'selected':'' ?> value="1">Surface</option>
                                <option <?php echo $getConsignmentsVal['Send_By']==2?'selected':'' ?> value="2">Air</option>
                                <option <?php echo $getConsignmentsVal['Send_By']==3?'selected':'' ?> value="3">Urgent</option>
                            </select>
                        </td>
		                
		                <td><input class="form-control input-sm weight" id="weight-<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="weight[<?php echo $i ?>]" type="text" value="<?php echo $getConsignmentsVal['Total_Weight_In_KG']; ?>"></td>
		                
		                <td><input class="form-control input-sm subtotal" id="subtotal<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="subtotal<?php echo $getConsignmentsVal['Consignment_Id']; ?>" type="text" value="<?php echo sprintf('%0.2f',$getConsignmentsVal['Subtotal']); ?>" readonly></td>
		                
		                <td>
		                    <div class="input-group">
		                        <span class="input-group-addon">
		                        	<input type="radio" id="disPercentRadio-<?php echo $getConsignmentsVal['Consignment_Id']; ?>" class="disPercentRadio" name="disRadio<?php echo $i; ?>" <?php echo $getConsignmentsVal['Discount_Percent']!=0?'checked':''?> />
		                        </span>
		                        <input type="text" class="form-control input-sm discount_percent" id="discount_percent-<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="discount_percent[<?php echo $i ?>]" placeholder="In (%)" value="<?php echo $getConsignmentsVal['Discount_Percent']; ?>" <?php echo $getConsignmentsVal['Discount_Percent']==0?'readonly':''?> />
		                    </div>
		                </td>                
		                
		                <td>
		                    <div class="input-group">
		                        <span class="input-group-addon">
		                            <input type="radio" id="disRsRadio-<?php echo $getConsignmentsVal['Consignment_Id']; ?>" class="disRsRadio" name="disRadio<?php echo $i; ?>" <?php echo $getConsignmentsVal['Discount_Rs']!=0?'checked':'' ;?> />
		                        </span>
		                        <input type="text" class="form-control input-sm discount_rs" id="discount_rs-<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="discount_rs[<?php echo $i ?>]" placeholder="In (Rs.)" value="<?php echo $getConsignmentsVal['Discount_Rs']; ?>" <?php echo $getConsignmentsVal['Discount_Rs']==0?'readonly':'' ;?> />
		                    </div>
		                </td>                
		                
		                <td><input type="text" class="form-control input-sm total_amt" id="total_amt<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="total_amt<?php echo $getConsignmentsVal['Consignment_Id']; ?>" value="<?php echo sprintf('%0.2f',$getConsignmentsVal['Total_Amount']); ?>" readonly /></td>
		                
		                <td><input type="text" class="form-control input-sm insrncChrgs" id="insrncChrgs<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="insrncChrgs<?php echo $getConsignmentsVal['Consignment_Id']; ?>" value="<?php echo sprintf('%0.2f', $getConsignmentsVal['Insurance_Other_Charges']); ?>" readonly /></td>
		                
		                <td class="FAmt"><input type="text" class="form-control input-sm finalAmount" id="FinalAmt<?php echo $getConsignmentsVal['Consignment_Id']; ?>" name="FinalAmt<?php echo $getConsignmentsVal['Consignment_Id']; ?>" value="<?php echo sprintf('%0.2f',$getConsignmentsVal['FinalAmount']); ?>" readonly /></td>
		                
		                <input type="hidden" name="abwNo<?php echo $getConsignmentsVal['Consignment_Id']; ?>" id="abwNo<?php echo $getConsignmentsVal['Consignment_Id']; ?>" value="<?php echo $getConsignmentsVal['Consignment_No']; ?>">
		                <input type="hidden"  name="dest_id<?php echo $getConsignmentsVal['Consignment_Id']; ?>" id="dest_id<?php echo $getConsignmentsVal['Consignment_Id']; ?>" value="<?php echo $getConsignmentsVal['Destination_Id']; ?>">
		                
		                
		            </tr>
		    <?php $i++; } ?>
            
            		<tr class="warning">
                    	<td colspan="12" align="right"><strong>Total</strong></td>
                        <td><input type="text" class="form-control input-sm" id="invoicetotal" value="" readonly /></td>
                    </tr>
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>Fuel Charges (<?php echo $getConsignmentsVal['Fuel_Surcharge']; ?>%)</strong></td>
                        <td>
                        	<input type="hidden" id="fuelsrchrgPercent" value="<?php echo $getConsignmentsVal['Fuel_Surcharge']; ?>" />
                            <input type="text" class="form-control input-sm" id="fuelsurcharge" value="" readonly />
                        </td>
                    </tr>
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>Sub-Total</strong></td>
                        <td><input type="text" class="form-control input-sm" id="invoiceSubtotal" value="" readonly /></td>
                    </tr>
                    
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>IGST (<?php echo $getTaxes[1]['IGST']; ?>%)</strong></td>
                        <td>
                        	<input type="hidden" id="IGSTpercent" value="<?php echo $getTaxes[1]['IGST']; ?>" />
                            <input type="hidden" id="GST_Within_State" value="<?php echo $getConsignmentsVal['GST_Within_State']; ?>" />
                            <input type="text" class="form-control input-sm" id="igst" value="" readonly />
                        </td>
                    </tr>
                    
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>SGST (<?php echo $getTaxes[1]['SGST']; ?>%)</strong></td>
                        <td>
                        	<input type="hidden" id="SGSTpercent" value="<?php echo $getTaxes[1]['SGST']; ?>" />
                            <input type="text" class="form-control input-sm" id="sgst" value="" readonly />
                        </td>
                    </tr>
                    
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>CGST (<?php echo $getTaxes[1]['CGST']; ?>%)</strong></td>
                        <td>
                        	<input type="hidden" id="CGSTpercent" value="<?php echo $getTaxes[1]['CGST']; ?>" />
                            <input type="text" class="form-control input-sm" id="cgst" value="" readonly />
                        </td>
                    </tr>
                    
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>Service Tax (<?php echo $getTaxes[1]['Service_Tax']; ?>%)</strong></td>
                        <td>
                        	<input type="hidden" id="servicetaxpercent" value="<?php echo $getTaxes[1]['Service_Tax']; ?>" />
                            <input type="text" class="form-control input-sm" id="serviceTax" value="" readonly />
                        </td>
                    </tr>
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>SB Tax (<?php echo $getTaxes[1]['SB_Tax']; ?>%)</strong></td>
                        <td>
                        	<input type="hidden" id="sbTaxPercent" value="<?php echo $getTaxes[1]['SB_Tax']; ?>" />
                            <input type="text" class="form-control input-sm" id="sbTax" value="" readonly />
                        </td>
                    </tr>
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>KK Tax (<?php echo $getTaxes[1]['KKC_Tax']; ?>%)</strong></td>
                        <td>
                        	<input type="hidden" id="kkTaxPercent" value="<?php echo $getTaxes[1]['KKC_Tax']; ?>" />
                            <input type="text" class="form-control input-sm" id="kkTax" value="" readonly />
                        </td>
                    </tr>
                    <tr class="warning">
                    	<td colspan="12" align="right"><strong>Total Bill Amount</strong></td>
                        <td><input type="text" class="form-control input-sm" id="invoiceFinalAmt" value="" readonly /></td>
                    </tr>
                    
                    
		    		<input name="invoiceId" id="invoiceId" type="hidden" value="<?php echo $_GET['id'] ?>">
		    		<input name="clientId" id="client_id" type="hidden" value="<?php echo $getConsignments[1]['Client_Id']; ?>">
		        </table>
		    </div>
            
            <div class="text-right"><button type="button" id="edit" class="btn btn-lg btn-success">Update</button></div>
	    
	    </form>
	</div>
</div>

<?php 
include('../../config.php'); 

require_once(PATH_LIBRARIES.'/classes/rate.php');
$rateClass = new rate();

require_once (ROOT."/dompdf/dompdf_config.inc.php");
$dompdf = new DOMPDF();

require (ROOT."/PHPMailer-master/class.phpmailer.php");
 
//set_include_path(get_include_path() . PATH_SEPARATOR ."dompdf");

///*******************************************************
/// Get Client Name
///*******************************************************
if($_POST['type']=="getClientName")
{
	$isarr = substr_count($_POST['client_name'], '-');		
	if($isarr!=0){
		$clientName = explode('-',$_POST['client_name']);
	
		$sql="SELECT Client_Id FROM tbl_clients WHERE Client_Name='".$clientName[0]."' AND Client_Code=".$clientName[1];
		$res=$rateClass->ExecuteQuery($sql);	
	}
		
	if(!empty($res))
    {
 		echo "<input type='hidden' id='client_id' name='client_id' value='".$res[1]['Client_Id']."' />";
    }
	
}

///*******************************************************
/// for invalid client validation
///*******************************************************
if($_POST['type']=="clinetExists")
{	
	$isarr = substr_count($_POST['client_name'], '-');		
	if($isarr!=0){
		$clientName = explode('-',$_POST['client_name']);
	
		$client=$rateClass->ExecuteQuery("SELECT EXISTS(SELECT Client_Id FROM tbl_clients WHERE Client_Name='".$clientName[0]."' AND Client_Code=".$clientName[1]." AND 1=1) AS 'Find'");
		echo $client[1]['Find'];	
	}
	  
}

///*******************************************************
/// for customer Name
///*******************************************************
if($_POST['type']=="client")
{	
	$client=$rateClass->ExecuteQuery("SELECT Client_Name, DATE_FORMAT(Joining_Date,'%d-%m-%Y') AS Joining_Date,(SELECT DATE_FORMAT(MAX(Date_To),'%d-%m-%Y') FROM tbl_invoices I WHERE I.Client_Id=C.Client_Id) AS 'Last_Date' FROM tbl_clients C WHERE Client_Id='".$_POST['client_id']."' AND 1=1");
	
	echo $client[1]['Client_Name'].'@'.$client[1]['Joining_Date'].'@'.$client[1]['Last_Date'];
	
 }


///*******************************************************
/// for date exists
///*******************************************************
if($_POST['type']=="dateExists")
{
	
	$fromDate=date('Y-m-d',strtotime($_POST['from_date']));
$sql=$rateClass->ExecuteQuery("SELECT exists( select 1 FROM invoice where Customer_Id =(select Customer_Id from customer_master where Customer_Code='".$_POST['client_code']."') and '".$fromDate."' between Date_From and Date_To ) as 'Find'");


echo $sql[1]['Find'] ;

	  
}

///*******************************************************
/// for Date Should be greater than Last Invice Date
///*******************************************************
if($_POST['type']=="maxDate")
{
	
	$fromDate=date('Y-m-d',strtotime($_POST['from_date']));
	$lastDate=date('Y-m-d',strtotime($_POST['Last_Date_input']));
	
	$res=$rateClass->ExecuteQuery("SELECT EXISTS (SELECT 1 FROM tbl_invoices WHERE Client_Id=".$_POST['client_id'].") AS Find");
	
	if($res[1]['Find']>0){
		$sql=$rateClass->ExecuteQuery("SELECT EXISTS (SELECT 1 FROM tbl_invoices WHERE Client_Id=".$_POST['client_id']." AND '".$lastDate."' < '".$fromDate."') AS 'Find2'");
		
		echo $sql[1]['Find2'] ;
	}
	else{
		echo 2;
	}
	
}

///*******************************************************
/// To Date Should be greater than From Date
///*******************************************************
if($_POST['type']=="greaterThenFromDate")
{
	
	$fromDate=date('Y-m-d',strtotime($_POST['from_date']));
	$toDate=date('Y-m-d',strtotime($_POST['to_date']));
	
	if($fromDate > $toDate){
		echo 0;
	}
	else{
		echo 1;
	}
	
}


///*******************************************************
/// for search consignment detail ////////////////////////
///*******************************************************
if($_POST['type']=="searchClient")
{
	//echo "Branch id is". 0;
	$fromDate=date('Y-m-d',strtotime($_POST['from_date']));
	$toDate=date('Y-m-d',strtotime($_POST['to_date']));
 	$sql="SELECT DATE_FORMAT(Date_Of_Submit, '%d-%m-%Y') AS Date, Consignment_No, Total_Weight_In_KG, Total_Amount, Insurance_Other_Charges, (Total_Amount + Insurance_Other_Charges) AS FinalAmount , D.Destination_Name FROM tbl_consignments CO
INNER JOIN tbl_destinations D ON D.Destination_Id = CO.Destination_Id
WHERE Client_Id=".$_POST['client_id']." AND Date_Of_Submit BETWEEN '".$fromDate."' AND '".$toDate."' ORDER BY Date_Of_Submit ASC, Consignment_No ASC";
	
	$consignment=$rateClass->ExecuteQuery($sql);

	//$invoice=$rateClass->ExecuteQuery("select DATE_FORMAT(MAX(Date_To),'%d-%m-%Y') as 'Last_Date' from invoice where Customer_Id=(select customer_Id from customer_master where Customer_Code='".$_REQUEST['cust_code']."')");

	//echo $_REQUEST['cust_code']."@".$invoice[1]['Last_Date']."@";
	?>
<div class="clear formbgstyle" style="padding-top:5px;">
<?php if(count($consignment) > 0){?>
        <table class="table table-hover table-bordered" id="addedProducts">
          <thead>
            <tr class="success">
              <th>Sno.</th>              
              <th>Con. Booking Date</th>
              <th>Consignment No</th>
              <th>Destination</th>
              <th>Weight</th>
              <th>Amount</th>
              <th>Insurance + Other Charges</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php 
		
			 $i=1;
			 $amount_here=0;
			 foreach($consignment as $val)
 		{  $amount_here=$amount_here+$val['Total_Amount'];      
            ?>
            <tr>
              <td ><?php echo $i;?></td>
              <td><?php echo $val['Date'];?></td>
              <td><?php echo $val['Consignment_No'];?></td>
              <td><?php echo $val['Destination_Name'];?></td>
              <td><?php echo $val['Total_Weight_In_KG'];?></td>
              <td><?php echo sprintf('%0.2f',$val['Total_Amount']);?></td>
              <td><?php echo sprintf('%0.2f',$val['Insurance_Other_Charges']);?></td>
              <td><?php echo sprintf('%0.2f',$val['FinalAmount']);?></td>
              
            </tr>
            
            <?php $i++;} ?>
			
          </tbody>
        </table>
        </div>
        <div class="form-group" id="hidediv">
        	
              <div class="col-sm-4  col-sm-offset-5" align="center">
             	<strong>Total:</strong> <input type="text" value="<?php echo $amount_here; ?>" readonly ><br><br>
             	<input type="button" class="btn btn-primary btn-lg" id="generate" value="Generate Invoice">

              </div>
           
         </div>
        <?php }
		
	else{
			
			?>
	<div class="clear formbgstyle" style="padding-top:5px;">
        <table class="table table-hover table-bordered" id="addedProducts">
          <thead>
            <tr class="success">
              <th>Sno.</th>              
              <th>Booking Date</th>
              <th>Consignment No</th>
              <th>Destination</th>
              <th>Weight</th>
              <th>Amount</th>
              <th>Insurance Charges</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
			<tr>
			  <td colspan="8" class="text-center">No Detail Found</td>    
			</tr>
          </tbody>
        </table>	
	<?php }?>
        
        <div>
            
	<?php
}


///*******************************************************
/// for generate Invoice no //////////////////////////////
///*******************************************************
if($_POST['type']=="generateInvoice")
{
	
	$fromDate=date('Y-m-d',strtotime($_POST['from_date']));
	$toDate=date('Y-m-d',strtotime($_POST['to_date']));	
	$billDate=date('Y-m-d',strtotime($_POST['bill_date']));	
	
	//On this date or smaller than this date
	//GST will not applied
	$noGstDate = '2017-06-30';
	
	//if "to_date" is smaller than "30th june 2017"
	//then GST will not apply
	if($noGstDate >= $toDate){
		
		
		$cust=$rateClass->ExecuteQuery("SELECT DATE_FORMAT(Date_Of_Submit, '%d-%m-%Y') AS Date, CO.Consignment_No, CO.Total_Weight_In_KG, Total_Amount, Insurance_Other_Charges, (Total_Amount + Insurance_Other_Charges) AS FinalAmount , D.Destination_Name, B.Branch_Code, B.Branch_Name, B.Franchise_Name, B.Franchise_Logo, B.Address, DN.Destination_Name AS City, B.PAN_No, B.Service_Tax_No, C.Client_Name, C.Address AS Client_Address, C.Fuel_Surcharge, C.Email 
	
		FROM tbl_consignments CO 
		
		INNER JOIN tbl_destinations D ON D.Destination_Id = CO.Destination_Id
		INNER JOIN tbl_branchs B ON B.Branch_Id = CO.Branch_Id
		INNER JOIN tbl_destinations DN ON DN.Destination_Id = B.Destination_Id
		INNER JOIN tbl_clients C ON C.Client_Id = CO.Client_Id
		WHERE CO.Client_Id=".$_POST['client_id']." AND CO.Date_Of_Submit BETWEEN '".$fromDate."' AND '".$toDate."' ORDER BY CO.Date_Of_Submit ASC, Consignment_No ASC");
	
		
		$tax=$rateClass->ExecuteQuery("SELECT Service_Tax, SB_Tax, KKC_Tax FROM tbl_taxes");
		
		$sum = 0;
		foreach ($cust as $val)
		{
		  $sum+=sprintf('%0.2f',$val['FinalAmount']);	 
		}
		
		$fuelsurcharge = sprintf('%0.2f',($sum*$cust[1]['Fuel_Surcharge'])/100);
		$subtotal = sprintf('%0.2f', ($sum + $fuelsurcharge));
		$serviceTax = sprintf('%0.2f',($subtotal*$tax[1]['Service_Tax'])/100);
		$SB_Tax = sprintf('%0.2f',($subtotal*$tax[1]['SB_Tax'])/100);
		$KKC_Tax = sprintf('%0.2f',($subtotal*$tax[1]['KKC_Tax'])/100);
		$Final_Total_Amt = sprintf('%0.2f',($subtotal + $serviceTax + $SB_Tax + $KKC_Tax));
		
		// Query to get the Max Invoice No
		//$invno=$rateClass->ExecuteQuery("SELECT Invoice_No FROM tbl_invoices WHERE Invoice_Id= (SELECT MAX(Invoice_Id) FROM tbl_invoices) AND 1=1");
		
		$invno=$rateClass->ExecuteQuery("SELECT Invoice_No FROM tbl_invoices WHERE Invoice_Id= (SELECT MAX(Invoice_Id) FROM tbl_invoices WHERE 1=1)");
		
		// Generate Invoice No serial wise
		if(!empty($invno[1]['Invoice_No'])){
			$maxInvoiceNo = explode('-', $invno[1]['Invoice_No']);
			//$maxInvoiceNo = $invno[1]['Invoice_No'];
			$invoiceNo = sprintf( '%08d', ($maxInvoiceNo[1] + 1) );
		}
		else{
			$invoiceNo = sprintf( '%08d', 1);	
		}
		
		// The admin panel serves every branch, so bill the invoice to the client's own branch.
		$bRow = $rateClass->ExecuteQuery("SELECT Branch_Id FROM tbl_clients WHERE Client_Id=".intval($_POST['client_id']));
		$invoiceBranchId = !empty($bRow) ? intval($bRow[1]['Branch_Id']) : 0;
		$insertInvoice=mysql_query("INSERT INTO tbl_invoices (Invoice_Date, Invoice_No, Date_From, Date_To, Bill_Date, Invoice_Amount, Fuel_Surcharge, Subtotal, Service_Tax, SB_Tax, KK_Tax, Final_Total_Amt, Client_Id, Branch_Id)  
	VALUES(NOW(), '".$cust[1]['Branch_Code']."-".$invoiceNo."', '".$fromDate."', '".$toDate."', '".$billDate."', ".$sum.", ".$fuelsurcharge.",".$subtotal.",".$serviceTax.",".$SB_Tax.",".$KKC_Tax.",".$Final_Total_Amt.",".$_POST['client_id'].",".$invoiceBranchId.")");
	
		
	
	
		if(!$insertInvoice)
		{
			echo 0;
		}
		else
		{
			echo 1;
		}
		
		
	}//eof if condition
	//if "to_date" is greater than "30th june 2017"
	//then GST will Apply
	//In the else portion GST applied and 
	//Invoice format changed.
	else{
		
		
		$cust=$rateClass->ExecuteQuery("SELECT DATE_FORMAT(Date_Of_Submit, '%d-%m-%Y') AS Date, CO.Consignment_No, CO.Total_Weight_In_KG, Total_Amount, Insurance_Other_Charges, (Total_Amount + Insurance_Other_Charges) AS FinalAmount , D.Destination_Name, B.Branch_Code, B.Branch_Name, B.Franchise_Name, B.Franchise_Logo, B.Address, DN.Destination_Name AS City, B.PAN_No, B.GSTIN, B.Service_Tax_No, C.Client_Name, C.Address AS Client_Address, C.Billing_Address AS Billing_Address, C.GST_Within_State, C.GSTIN_No, C.PAN_No AS Client_PAN_No, C.Fuel_Surcharge, C.Email 
	
		FROM tbl_consignments CO 
		
	INNER JOIN tbl_destinations D ON D.Destination_Id = CO.Destination_Id
	INNER JOIN tbl_branchs B ON B.Branch_Id = CO.Branch_Id
	INNER JOIN tbl_destinations DN ON DN.Destination_Id = B.Destination_Id
	INNER JOIN tbl_clients C ON C.Client_Id = CO.Client_Id
	WHERE CO.Client_Id=".$_POST['client_id']." AND CO.Date_Of_Submit BETWEEN '".$fromDate."' AND '".$toDate."' ORDER BY CO.Date_Of_Submit ASC, Consignment_No ASC");
	
		
		$tax=$rateClass->ExecuteQuery("SELECT IGST, SGST, CGST, Service_Tax, SB_Tax, KKC_Tax FROM tbl_taxes");
		
		$sum = 0;
		foreach ($cust as $val)
		{
		  $sum+=sprintf('%0.2f',$val['FinalAmount']);	 
		}
		
		$fuelsurcharge = sprintf('%0.2f',($sum*$cust[1]['Fuel_Surcharge'])/100);
		$subtotal = sprintf('%0.2f', ($sum + $fuelsurcharge));
		
		if($cust[1]['GST_Within_State']=="0"){
			$igst = sprintf('%0.2f',0);
			$sgst = sprintf('%0.2f',($subtotal*$tax[1]['SGST'])/100);
			$cgst = sprintf('%0.2f',($subtotal*$tax[1]['CGST'])/100);
		}
		else{
			$igst = sprintf('%0.2f',($subtotal*$tax[1]['IGST'])/100);
			$sgst = sprintf('%0.2f',0);
			$cgst = sprintf('%0.2f',0);
		}
				
		$Final_Total_Amt = sprintf('%0.2f',($subtotal + $igst + $sgst + $cgst));
		
		// Query to get the Max Invoice No
		$invno=$rateClass->ExecuteQuery("SELECT Invoice_No FROM tbl_invoices WHERE Invoice_Id= (SELECT MAX(Invoice_Id) FROM tbl_invoices WHERE 1=1)");
		
		// Generate Invoice No serial wise
		if(!empty($invno[1]['Invoice_No'])){
			$maxInvoiceNo = explode('-', $invno[1]['Invoice_No']);
			//$maxInvoiceNo = $invno[1]['Invoice_No'];
			$invoiceNo = sprintf( '%08d', ($maxInvoiceNo[1] + 1) );
		}
		else{
			$invoiceNo = sprintf( '%08d', 1);	
		}
		
		
		//Insert Invoice Details
		// The admin panel serves every branch, so bill the invoice to the client's own branch.
		$bRow = $rateClass->ExecuteQuery("SELECT Branch_Id FROM tbl_clients WHERE Client_Id=".intval($_POST['client_id']));
		$invoiceBranchId = !empty($bRow) ? intval($bRow[1]['Branch_Id']) : 0;
		$insertInvoice=mysql_query("INSERT INTO tbl_invoices (Invoice_Date, Invoice_No, Date_From, Date_To, Bill_Date,  Invoice_Amount, Fuel_Surcharge, Subtotal, IGST_Tax, SGST_Tax, CGST_Tax, Service_Tax, SB_Tax, KK_Tax, Final_Total_Amt, Client_Id, Branch_Id)  
		VALUES(NOW(), '".$cust[1]['Branch_Code']."-".$invoiceNo."', '".$fromDate."', '".$toDate."', '".$billDate."', ".$sum.", ".$fuelsurcharge.",".$subtotal.",".$igst.",".$sgst.",".$cgst.",0,0,0,".$Final_Total_Amt.",".$_POST['client_id'].",".$invoiceBranchId.")");
		
			 
		
		
		if(!$insertInvoice)
		{
			echo 0;
		}
		else
		{
			echo 1;	 
		}//eof else
		
		
	}//eof else
	
	
}// eof if condition for generate invoice


///*******************************************************
/// for resend Invoice no /////////////////////////////////
///*******************************************************
if($_POST['type']=="resend")
{
	require_once(PATH_LIBRARIES.'/classes/CourierEmailService.php');
	$dbConn = new DBConn();
	$emailService = new CourierEmailService($dbConn);

	$cust = $rateClass->ExecuteQuery("SELECT I.Invoice_No, I.Final_Total_Amt, I.Invoice_Date, C.Client_Name, C.Email 
	FROM tbl_invoices I 
	INNER JOIN tbl_clients C ON C.Client_Id = I.Client_Id 
	WHERE Invoice_Id=".intval($_POST['id']));
	
	if (!empty($cust)) {
		$pdfPath = ROOT . "/pdfmail/invoice/" . $cust[1]['Invoice_No'] . ".pdf";
		$sent = $emailService->sendInvoiceEmail(
			intval($_POST['id']),
			$cust[1]['Email'],
			$pdfPath,
			$cust[1]['Invoice_No'],
			$cust[1]['Final_Total_Amt'],
			$cust[1]['Client_Name'],
			date('d-m-Y', strtotime($cust[1]['Invoice_Date']))
		);
		echo $sent ? "Mail sent successfully" : "Error sending email";
	} else {
		echo "Invoice not found";
	}
}
///*******************************************************
/// for generate Invoice no /////////////////////////////////
///*******************************************************
if($_POST['type']=="searchAll")
{
	
$invoice=$rateClass->ExecuteQuery("SELECT Invoice_Id, Invoice_No, DATE_FORMAT(Date_From,'%d-%m-%Y') AS 'Date_From',DATE_FORMAT(Date_To,'%d-%m-%Y') AS 'Date_To', Final_Total_Amt, Client_Name 

FROM tbl_invoices I 

INNER JOIN tbl_clients C ON I.Client_Id = C.Client_Id 
WHERE 1=1 ORDER BY Invoice_Id DESC");
	?>
<table class="table table-hover table-bordered" id="addedProducts">
          <thead>
            <tr class="success">
              <th>Sno.</th>
              <th>Client Name</th>
              <th>Invoice No</th>
              <th>Date From</th>             
              <th>Date To</th>
              <th>Amount</th>
              <th>Invoice</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
			if(count($invoice) > 0)
			{
                $i=1;
                foreach($invoice as $val){ ?>
            <tr>
              <td ><?php echo $i;?></td>
              <td><?php echo $val['Client_Name'];?></td>
              <td><a href="<?php echo PATH_PDF."/".$val['Invoice_No'].".pdf"?>" target="_blank"><?php echo $val['Invoice_No'];?></a></td>
              <td><?php echo $val['Date_From'];?></td>              
              <td><?php echo $val['Date_To'];?></td>
              <td><?php echo round($val['Final_Total_Amt']);?></td>
              <td><a href="<?php echo PATH_PDF."/".$val['Invoice_No'].".pdf"?>" target="_blank"><i class="fa fa-file-pdf-o danger"></i></a></td>
              <td>
              <!--<?php if($val['Paid_Date']== ""){?>
              <button type="button" class="btn btn-success btn-sm pay" id="<?php echo $val['Invoice_Id']; ?>" name="delete"> Pay </button>
              <?php }
			  else{
                echo "<span class='text-success'><strong>Paid</strong></span>";				  
				  }?>-->
                <button type="button" class="btn btn-info btn-sm resend" id="<?php echo $val['Invoice_Id']; ?>" name="resend">Resend Invoice</button></td>
            </tr>
            <?php $i++;} 
			}
			else
	         { ?>
            <tr>
              <td colspan="8" align="center">No Record Found</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
	<?php
}


///*******************************************************
/// Delete row from invoice table
///*******************************************************
if($_POST['type']=="delete")
{
	$res=$rateClass->ExecuteQuery("select Invoice_No from invoice where Invoice_Id=".$_POST['id']."");
	$invoice=$res[1]['Invoice_No'];
	$sql=mysql_query("delete from invoice where Invoice_Id=".$_POST['id']."");
	
	unlink(PATH_PDF."/invoice/invoice-".$invoice.".pdf");
}

///*******************************************************
/// Get Subtotal Amount, onchange of weight text box
///*******************************************************

if($_POST['type']=="getSubtotal")
{
	$rateClass->getSubtotal($_POST['branchId'], $_POST['client_id'], $_POST['dest_id'], $_POST['send_by'], $_POST['weight']);
}

///*******************************************************
/// Update the Invoice ///////////////////////////////////
///*******************************************************
if($_POST['type']=="update")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{
		///////////////////////////////////////////
		// Set the Dom element values in a variable
		///////////////////////////////////////////
		$cid=explode(',',$_POST['cid']);
		$weight = explode(',',$_POST['weight']);
		$subtotal = explode(',',$_POST['subtotal']);
		$discount_rs = explode(',',$_POST['discount_rs']);
		$discount_percent = explode(',',$_POST['discount_percent']);
		$total_amt = explode(',',$_POST['total_amt']);
		$insrncChrgs = explode(',',$_POST['insrncChrgs']);

		$consignmentDate=explode(',',$_POST['consignmentDate']);
		$conCount = count($consignmentDate);

		
		foreach($consignmentDate as $consignmentDateVal){
			$consDate[]=date('Y-m-d',strtotime($consignmentDateVal));
		}

		$billDate=date('Y-m-d',strtotime($_POST['bill_date']));
		
		if(!empty($_POST['checkedCons'])){ 
			$checkedCons = explode(',',$_POST['checkedCons']);
		}
		else{ 
			$checkedCons = 0;
			}
		
		///*******************************************************
		/// Delete all checked consignment ///////////////////////
		///*******************************************************
		
		if($checkedCons!=0){ 
			foreach($checkedCons as $checkedItems)
			{	
				$res=mysql_query("DELETE FROM tbl_consignments WHERE Consignment_Id=".$checkedItems);
			}//eof foreach loop
			
			if(!$res)
			{
				throw new Exception('#1');
			}
		}		
		
		///*******************************************************
		/// Update the consignment table /////////////////////////
		///*******************************************************
		$i=0;
		
		foreach($cid as $cidval)
		{
			$consignmentId = explode('-',$cidval);

			
			
			$tableField=array('Total_Weight_In_KG','Subtotal','Discount_Rs','Discount_Percent','Total_Amount','Date_Of_Submit');
			$tableValue=array($weight[$i],$subtotal[$i],$discount_rs[$i],$discount_percent[$i],$total_amt[$i],$consDate[$i]);
			$condition=" Consignment_Id='".$consignmentId[1]."'";			
			$res=$rateClass->updateValue("tbl_consignments",$tableField,$tableValue,$condition);
			$i++;
		}//eof foreach loop
		
		

		if(!$res)
		{
			throw new Exception('#2');
		}
		
		///*******************************************************
		/// Update the invoice table /////////////////////////////
		///*******************************************************
		$tableField=array('Bill_Date','Invoice_Amount','Fuel_Surcharge','Subtotal','IGST_Tax','SGST_Tax','CGST_Tax','Service_Tax','SB_Tax','KK_Tax', 'Final_Total_Amt');
		$tableValue=array($billDate,$_POST['invoiceAmt'],$_POST['fuelsurcharge'],$_POST['invoiceSubtotal'],$_POST['igst'],$_POST['sgst'],$_POST['cgst'],$_POST['serviceTax'],$_POST['sbTax'],$_POST['kkTax'],$_POST['invoiceFinalAmt']);
		$condition=" Invoice_Id='".$_POST['invoiceId']."'";			
		$res=$rateClass->updateValue("tbl_invoices",$tableField,$tableValue,$condition);
		
		if(!$res)
		{
			throw new Exception('#3');
		}
		
		mysql_query("COMMIT",$con);
		echo 1;
		
		
	}//eof try
	catch(Exception $e)
	{
		echo  $e->getMessage();
		mysql_query('ROLLBACK',$con);
		mysql_query('SET AUTOCOMMIT=1',$con);
	}//eof catch
}// eof if condition for update type



///*******************************************************
/// preview of the Invoice ///////////////////////////////
///*******************************************************
if($_POST['type']=="preview")
{
	///////////////////////////////////////////
		// Set the Dom element values in a variable
		///////////////////////////////////////////
		$cid=explode(',',$_POST['cid']);
		$weight = explode(',',$_POST['weight']);
		$subtotal = explode(',',$_POST['subtotal']);
		$discount_rs = explode(',',$_POST['discount_rs']);
		$discount_percent = explode(',',$_POST['discount_percent']);
		$total_amt = explode(',',$_POST['total_amt']);
		$insrncChrgs = explode(',',$_POST['insrncChrgs']);
		
		if(!empty($_POST['checkedCons'])){ 
			$checkedCons = explode(',',$_POST['checkedCons']);
		}
		else{ 
			$checkedCons = 0;
			}
		
//////////////////////////////
// Convert Amount in Words
//////////////////////////////		
//$num = round($_POST['invoiceFinalAmt']);
$num = $_POST['invoiceFinalAmt'];

  $number = number_format($num, 2, '.', '');
 $number=explode('.',$number);
 $no = $number[0];
$point=$number[1];
//$point = round($point - $no, 2) * 100;


$hundred = null;
$digits_1 = strlen($no);
$i = 0;
$str = array();
$words = array('0' => 'zero', '1' => 'one', '2' => 'two',
'3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
'7' => 'seven', '8' => 'eight', '9' => 'nine',
'10' => 'ten', '11' => 'eleven', '12' => 'twelve',
'13' => 'thirteen', '14' => 'fourteen',
'15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
'18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
'30' => 'thirty', '40' => 'forty', '50' => 'fifty',
'60' => 'sixty', '70' => 'seventy',
'80' => 'eighty', '90' => 'ninety');
$digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
while ($i < $digits_1) {
$divider = ($i == 2) ? 10 : 100;
$number = floor($no % $divider);
$no = floor($no / $divider);
$i += ($divider == 10) ? 1 : 2;
if ($number) {
$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
$str [] = ($number < 21) ? $words[$number] .
" " . $digits[$counter] . $plural . " " . $hundred
:
$words[floor($number / 10) * 10]
. " " . $words[$number % 10] . " "
. $digits[$counter] . $plural . " " . $hundred;
} else $str[] = null;
}
$str = array_reverse($str);
$result = implode('', $str);
    $str_n="";
    $str_n.=$result . "Rupees  " ;
if($point>0)
 {
/*$points = ($point) ?
"." . $words[$point / 10] . " " . 
$words[$point = $point % 10] : '';
 $str_n.=$points . " Paise";
 */
 $points = ($point) ?
 $words[$point / 10] . " " . 
$words[$point = $point % 10] : '';
 $str_n.=$points . " Paise";
}
///////////////////////////////////////////////////////////////////******************//////////////////




		
		// Get the data from invoice table
		$invoiceInfo=$rateClass->ExecuteQuery("SELECT Invoice_No, DATE_FORMAT(Invoice_Date,'%d-%m-%Y') AS Invoice_Date, DATE_FORMAT(Date_From,'%d-%m-%Y') AS Date_From, DATE_FORMAT(Date_To,'%d-%m-%Y') AS Date_To, DATE_FORMAT(Bill_Date,'%d-%m-%Y') AS Bill_Date, Branch_Name, Franchise_Name, Franchise_Logo, B.Address AS Branch_Address, D.Destination_Name AS City, GSTIN, Service_Tax_No, PAN_No, (SELECT Client_Name FROM tbl_clients WHERE Client_Id=".$_POST['client_id'].") AS Client_Name, (SELECT GSTIN_No FROM tbl_clients WHERE Client_Id=".$_POST['client_id'].") AS GSTIN_No, (SELECT Address FROM tbl_clients WHERE Client_Id=".$_POST['client_id'].") AS Client_Address, (SELECT Billing_Address FROM tbl_clients WHERE Client_Id=".$_POST['client_id'].") AS Billing_Address, (SELECT PAN_No FROM tbl_clients WHERE Client_Id=".$_POST['client_id'].") AS Client_PAN_No  
	
		FROM tbl_invoices I
		
		INNER JOIN tbl_branchs B ON B.Branch_Id = I.Branch_Id
		INNER JOIN tbl_destinations D ON B.Destination_Id = D.Destination_Id
		
		WHERE Invoice_Id = ".$_POST['invoiceId']);
		
		
		$toDate=date('Y-m-d',strtotime($invoiceInfo[1]['Date_To']));	
		
		//On this date or smaller than this date
		//GST will not applied
		$noGstDate = '2017-06-30';
		
		//if "to_date" is smaller than "30th june 2017"
		//then GST will not apply
		if($noGstDate >= $toDate){
		
		//////////////////////////////////////////////
		// Prepare Bill format, to convert it into PDF
		//////////////////////////////////////////////
		$html='
		<html>
			<head>
				<style>
					table td{margin:0;}
				</style>
			</head>
			<body>
			<div style="width:100% margin:0 auto;">
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="80"><img width="100%" src="../../image_upload/franchise/thumb/'.$invoiceInfo[1]['Franchise_Logo'].'" alt=""></td>
					<td width="80%" align="center"><h4 style="border-bottom: none;" align="center">INVOICE</h4></td>
				</tr>
			</table>
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			 <tr>
				<td colspan="2" valign="top">
					<h1 style="text-transform: uppercase; margin-bottom:0; font-size:14pt; font-weight:bold;">'.$invoiceInfo[1]['Branch_Name'].'</h1>
					(Franchise of - '.$invoiceInfo[1]['Franchise_Name'].')<br>'.$invoiceInfo[1]['Branch_Address'].' '.$invoiceInfo[1]['City'].', India.
				</td>
			 </tr>
			 
			 <tr><td colspan="2"><hr></td></tr>			 
			 		  
			 <tr>
			 	<td colspan="2"><strong>PAN No.</strong>:- '.$invoiceInfo[1]['PAN_No'].', <strong>Service Tax No</strong>:- '.$invoiceInfo[1]['Service_Tax_No'].', <strong>Category</strong>:- Courier Service </td>				
			 </tr>
			 
			 <tr><td colspan="2"><hr></td></tr>	
			 
			 <tr>			
				<td>
					<strong>To,</strong>
					<br/>
					'.$invoiceInfo[1]['Client_Name'].'
					<br/>'.$invoiceInfo[1]['Client_Address'].', India 
				</td>
                <td>
					<strong>BILL NO. '.$invoiceInfo[1]['Invoice_No'].'</strong><br>
				 	<strong>BILL DATE:</strong> '.$invoiceInfo[1]['Bill_Date'].'<br>
					<strong>BILL PERIOD:</strong> '.$invoiceInfo[1]['Date_From'].' To '.$invoiceInfo[1]['Date_To'].'
				</td>
			  </tr>
			 			  
			  <tr><td colspan="2"><hr></td></tr>			
			</table>
			
			
			<table style="font-size:10pt; border-spacing: 0 0;" width="100%" border="1">
					
						<tr bgcolor="#EBEBEB">
							<td width="30">SNO</td>
							<td width="50">DATE</td>
							<td width="75" align="center">AWB NO</td>
							<td width="100" align="center">DESTINATION</td>
							<td width="30" align="center">WT(Kg)</td>
							<td width="40" align="center">AMT</td>							
							<td width="75" align="center">OTHER CHGS. / INS. CHGS.</td>
							<td width="65" align="center">TOTAL</td>
						</tr>
					
					';
					
					///////////////////////////////////////////
					// Set the Dom element values in a variable
					///////////////////////////////////////////
					$consignmentDate = explode(',',$_POST['consignmentDate']);
					$consignmentNo = explode(',',$_POST['consignmentNo']);
					$destName = explode(',',$_POST['destName']);
					$finalAmount = explode(',',$_POST['finalAmount']);
					
					$invoiceAmt = $_POST['invoiceAmt'];
					$fuelsurcharge = $_POST['fuelsurcharge'];
					$invoiceSubtotal = $_POST['invoiceSubtotal'];
					$serviceTax = $_POST['serviceTax'];
					$sbTax = $_POST['sbTax'];
					$kkTax = $_POST['kkTax'];
					$invoiceFinalAmt = $_POST['invoiceFinalAmt'];
					
					
					$i=0; $j=1; $row='';
					foreach($cid as $cidval)
					{
						$row.='
		                  <tr>
						  	<td width="30">'.$j.'</td>							
							<td width="50">'.$consignmentDate[$i].'</td>
							<td width="75" align="center">'.$consignmentNo[$i].'</td>
							<td width="100" align="center">'.$destName[$i].'</td>
							<td width="30" align="center">'.$weight[$i].'</td>
							<td width="40" align="center">'.$total_amt[$i].'</td>
							<td width="75" align="center">'.$insrncChrgs[$i].'</td>
							<td width="65" align="center">'.$finalAmount[$i].'</td>						
						 </tr>';
					
						$i++; $j++;
					}//eof foreach loop
					
					$html2=$html.$row.'	<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Total</td>
								<td width="65" align="center">'.$invoiceAmt.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Fuel Charges ('.$_POST['fuelsrchrgPercent'].'%)</strong></td>
								<td width="65" align="center">'.$fuelsurcharge.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Sub-Total</td>
								<td width="65" align="center">'.$invoiceSubtotal.'/-</td>	
							</tr>
							
							<tr >
								<td width="400" colspan="7" align="right" style="padding-right:10px">Service Tax (14.00%)</td>
								<td width="65" align="center">'.$serviceTax.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">SB Tax (0.5%)</td>
								<td width="65" align="center">'.$sbTax.'/-</td>	
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">KK Tax (0.5%)</td>
								<td width="65" align="center">'.$kkTax.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Total Bill Amount</td>
								<td width="65" align="center">'.$invoiceFinalAmt.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px"><strong>Say</strong></td>
								<td width="65" align="center">'.sprintf('%0.2f',($invoiceFinalAmt)).'/-</td>
							</tr>
							
				   
			  </table>
			  <div><strong>In Words :</strong> '.strtoupper($str_n).'</div>
              <div style="padding-top:50px;">Authorized Signatory<br><br>'.$invoiceInfo[1]['Branch_Name'].'</div>			  
			  
			</div>  
			</body>
			</html>
		';
 		  // convert the above bill format into PDF
		  
		}//eof if condition
		
		else{
			
			//////////////////////////////////////////////
		// Prepare Bill format, to convert it into PDF
		//////////////////////////////////////////////
		$html='
		<html>
			<head>
				<style>
					table td{margin:0;}
				</style>
			</head>
			<body>
			<div style="width:100%; margin:0 auto;">
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="80"><img width="100%" src="../../image_upload/franchise/thumb/'.$invoiceInfo[1]['Franchise_Logo'].'" alt=""></td>
					<td width="80%" align="center"><h4 style="border-bottom: none;" align="center">INVOICE</h4></td>
				</tr>
			</table>
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			 <tr>
				<td colspan="2" valign="top">
					<h1 style="text-transform: uppercase; margin-bottom:0; font-size:14pt; font-weight:bold;">'.$invoiceInfo[1]['Branch_Name'].'</h1>
					(Franchise of - '.$invoiceInfo[1]['Franchise_Name'].')<br>'.$invoiceInfo[1]['Branch_Address'].' '.$invoiceInfo[1]['City'].', India.
				</td>
			 </tr>
			 
			 <tr><td colspan="2"><hr></td></tr>			 
			 		  
			 <tr>
			 	<td colspan="2"><strong>PAN No.</strong>:- '.$invoiceInfo[1]['PAN_No'].', <strong>GSTIN</strong>:- '.$invoiceInfo[1]['GSTIN'].', <strong>HSN/SAC CODE</strong>: 996812</td>				
			 </tr>
			 
			 <tr><td colspan="2"><hr></td></tr>	
			 
			 <tr>			
				<td>
					<strong>To,</strong>
					<br/>
					'.$invoiceInfo[1]['Client_Name'].'
					<br/>'.$invoiceInfo[1]['Client_Address'].', India <br><br>
					
					<strong>Billing Address:</strong><br>
					'.$invoiceInfo[1]['Client_Name'].'
					<br/>'.$invoiceInfo[1]['Billing_Address'].', India<br>
					GSTIN-'.$invoiceInfo[1]['GSTIN_No'].'<br>
					PAN NO-'.$invoiceInfo[1]['Client_PAN_No'].'
				</td>
                <td>
					<strong>BILL NO. '.$invoiceInfo[1]['Invoice_No'].'</strong><br>
				 	<strong>BILL DATE:</strong> '.$invoiceInfo[1]['Bill_Date'].'<br>
					<strong>BILL PERIOD:</strong> '.$invoiceInfo[1]['Date_From'].' To '.$invoiceInfo[1]['Date_To'].'<br>
					<strong>Description of Service:</strong> Courier Service
				</td>
			  </tr>
			 			  
			  <tr><td colspan="2"><hr></td></tr>			
			</table>
			
			
			<table style="font-size:10pt; border-spacing: 0 0;" width="100%" border="1">
					
						<tr bgcolor="#EBEBEB">
							<td width="30">SNO</td>
							<td width="50">DATE</td>
							<td width="75" align="center">AWB NO</td>
							<td width="100" align="center">DESTINATION</td>
							<td width="30" align="center">WT(Kg)</td>
							<td width="40" align="center">AMT</td>							
							<td width="75" align="center">OTHER CHGS. / INS. CHGS.</td>
							<td width="65" align="center">TOTAL</td>
						</tr>
					
					';
					
					///////////////////////////////////////////
					// Set the Dom element values in a variable
					///////////////////////////////////////////
					$consignmentDate = explode(',',$_POST['consignmentDate']);
					$consignmentNo = explode(',',$_POST['consignmentNo']);
					$destName = explode(',',$_POST['destName']);
					$finalAmount = explode(',',$_POST['finalAmount']);
					
					$invoiceAmt = $_POST['invoiceAmt'];
					$fuelsurcharge = $_POST['fuelsurcharge'];
					$invoiceSubtotal = $_POST['invoiceSubtotal'];
					$igst = $_POST['igst'];
					$sgst = $_POST['sgst'];
					$cgst = $_POST['cgst'];
					$serviceTax = $_POST['serviceTax'];
					$sbTax = $_POST['sbTax'];
					$kkTax = $_POST['kkTax'];
					$invoiceFinalAmt = $_POST['invoiceFinalAmt'];
					
					
					$i=0; $j=1; $row='';
					foreach($cid as $cidval)
					{
						$row.='
		                  <tr>
						  	<td width="30">'.$j.'</td>							
							<td width="50">'.$consignmentDate[$i].'</td>
							<td width="75" align="center">'.$consignmentNo[$i].'</td>
							<td width="100" align="center">'.$destName[$i].'</td>
							<td width="30" align="center">'.$weight[$i].'</td>
							<td width="40" align="center">'.$total_amt[$i].'</td>
							<td width="75" align="center">'.$insrncChrgs[$i].'</td>
							<td width="65" align="center">'.$finalAmount[$i].'</td>						
						 </tr>';

					
						$i++; $j++;
					}//eof foreach loop
					
					$html2=$html.$row.'	<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Total</td>
								<td width="65" align="center">'.$invoiceAmt.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Fuel Charges ('.$_POST['fuelsrchrgPercent'].'%)</strong></td>
								<td width="65" align="center">'.$fuelsurcharge.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Sub-Total</td>
								<td width="65" align="center">'.$invoiceSubtotal.'/-</td>	
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">IGST (18%)</td>
								<td width="65" align="center">'.$igst.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">SGST (9%)</td>
								<td width="65" align="center">'.$sgst.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">CGST (9%)</td>
								<td width="65" align="center">'.$cgst.'/-</td>	
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px">Total Bill Amount</td>
								<td width="65" align="center">'.$invoiceFinalAmt.'/-</td>
							</tr>
							
							<tr>
								<td width="400" colspan="7" align="right" style="padding-right:10px"><strong>Say</strong></td>
								<td width="65" align="center">'.sprintf('%0.2f',($invoiceFinalAmt)).'/-</td>
							</tr>
							
				   
			  </table>
			  <div><strong>In Words :</strong> '.strtoupper($str_n). '</div>
			  <div style="padding-top:50px; text-decoration:underline;">TERMS OF PAYMENT</div>
				  <ul>
				  	<li>Payment should be made to Authorised Officer Only.</li>
					<li>Please do make Cash/Cheque/DD/NEFT Payment only</li>
					<li>By A/C payee Cheque/Draft favouring '.$invoiceInfo[1]['Branch_Name'].'</li>
					<li>Payment should be made within 7 days from bill date.</li>
					<li>Late payments are subject to an interest charges @30% per annum.</li>
				  </ul>
              <div style="padding-top:50px;">Authorized Signatory<br><br>'.$invoiceInfo[1]['Branch_Name'].'</div>			  
			  
			</div>  
			</body>
			</html>
		';
 		  // convert the above bill format into PDF
			
		}//eof else
	
	
	echo $html2;
	
}// eof if condition for update type

?>



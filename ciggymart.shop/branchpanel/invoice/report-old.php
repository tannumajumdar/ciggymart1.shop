<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

$str = "";

	if($_SERVER['REQUEST_METHOD']=='POST'){
	  		
		if($_POST['client_name']!=""){
			
			$client_name = explode("-",$_POST['client_name']);
			
			$cust=$db->ExecuteQuery("SELECT Client_Id, Client_Name, DATE_FORMAT(Joining_Date,'%d-%m-%Y') AS Contract_Date FROM tbl_clients 
			WHERE Client_Name='".$client_name[0]."' AND Branch_Id=".$_SESSION['buser']."");
			
			if(isset($cust[1]['Client_Id']))
			{
				$str.=" AND C.Client_Id=".$cust[1]['Client_Id']."";
			}				
		}			
		
		if($_POST['from_date'] and $_POST['to_date']){
			  $fromdate = $_POST['from_date'];
			  $fromdate = date("Y-m-d", strtotime($fromdate));
			  $todate = $_POST['to_date'];
			  $todate = date("Y-m-d", strtotime($todate));
			  $str.=" AND Date_From  BETWEEN '$fromdate' AND '$todate' ";
		}				  
	   
		/*if($_POST['status']!=""){
			if($_POST['status']==1)
			{
			   $str.=" AND Paid_Date IS NOT NULL";
			}
			else
			 {
				$str.=" AND Paid_Date IS NULL"; 
			 }
		}*/
   }//eof of main if condition

	$invoice=$db->ExecuteQuery("SELECT Invoice_Id, Invoice_No, DATE_FORMAT(Date_From,'%d-%m-%Y') AS 'Date_From', DATE_FORMAT(Date_To,'%d-%m-%Y') AS 'Date_To', Invoice_Amount, I.Fuel_Surcharge, Subtotal, IGST_Tax, SGST_Tax, CGST_Tax, Service_Tax, SB_Tax, KK_Tax, Final_Total_Amt, Client_Name, Client_Code 
	
	FROM tbl_invoices I 
	
	INNER JOIN tbl_clients C ON I.Client_Id = C.Client_Id 
	WHERE I.Branch_Id=".$_SESSION['buser'].$str." ORDER BY Invoice_Id DESC");
	
?>

<script type="text/javascript" src="invoice.js" ></script>

<script>

	$(document).ready(function(){ 
		
		/////////////////////////////////////////////////////
		// Get Client Code and Name list(autocomplete)///////
		///////////////////////////////////////////////////// 
		var clientcodelist = [
			<?php // PHP begins here
			  
				$menu=$db->ExecuteQuery("SELECT Client_Code, Client_Name, Address FROM tbl_clients WHERE Branch_Id=".$_SESSION['buser']." AND Client_Id IN (SELECT Client_Id FROM tbl_rates)");
				foreach($menu as $val) {
					echo '"'.$val['Client_Name'].'-'.$val['Client_Code'].'-'.$val['Address'].'", ';
				} // PHP ends here*/
			?>
		];// eof json format
		
		$("#client_name").autocomplete({
	   
			source: function(req, responseFn) {
				var re = $.ui.autocomplete.escapeRegex(req.term);
				var matcher = new RegExp( "^" + re, "i" );
				var a = $.grep( clientcodelist, function(item,index){
					return matcher.test(item);
				});
				responseFn( a );
			}
		});//eof autocomplete
	});//eof ready function
	
	
	$(function() {
		$("#from_date").datepicker({
			dateFormat: "dd-mm-yy"	
		});
	
		$("#to_date").datepicker({
			dateFormat: "dd-mm-yy"	
		});
	});
</script>
<style type="text/css" media="print">
  @page { size: portrait; }
  @media print
   {
      #admglobal_container, nav, .pageTitle, .srchBox {display:none;}
   }
</style>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-plus"></i> <strong>Generate Invoice</strong></a>
        <button onclick="window.print()" type="button" class="btn btn-info btn-sm" id="print" name="print"> <span class="glyphicon glyphicon-print"></span> Print </button>
        <!--<a class="btn btn-success btn-sm" href="<?php echo BRANCH_MASTERS_LINK_CONTROL ?>/clients/report.php"><i class="glyphicon glyphicon-list"></i> <strong>Clients List</strong></a>-->
    </div>
	<h1 class="pull-left">Invoice Report</h1>
    <div class="clearfix"></div>
</div>

<div class="main">

	<div class="clear formbgstyle">
         <div class="col-sm-12 srchBox">
          <div class="col-sm-7">
          <form id="" class="form-inline" method="post">
            
              <div class="form-group">
                <input type="text" placeholder="Client Name" id="client_name" name="client_name" class="form-control input-sm"  value=""/>
              </div>
             
              <div class="form-group">
                <input type="text" placeholder="From-Date" id="from_date" name="from_date" class="form-control input-sm"  value=""/>
              </div>
              <div class="form-group">
                <input type="text" placeholder="To-Date" id="to_date" name="to_date" class="form-control input-sm" value="" />
              </div>
             
              <div class="form-group">
                <input type="submit" name="submit" Value="Search" class="btn btn-primary btn-sm">
              </div>
           
          </form>
         </div>
         <div class="col-sm-5 text-right">
             <button class="btn btn-sm btn-success" id="select_all">Search All</button>
         </div>
      </div>
  	  <div class="col-sm-6 col-sm-offset-5" style="padding-top:5px;"></div>

	<div class="clear formbgstyle" style="padding-top:5px;" id="detail">
        <table class="table table-hover table-bordered" id="addedProducts">
          <thead>
            <tr class="success">
              <th>Sno.</th>
              <th width="250">Client Name &amp; Code</th>
              <th>Invoice No</th>
              <th>Amount</th>
              <th>FUEL</th>
              <th>Subtotal</th>
              <th>SGST</th>
              <th>CGST</th>
              <th>IGST</th>
              <th>Amount</th>
              <th>Date From</th>             
              <th>Date To</th>              
              <!--<th>Invoice</th>-->
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
              <td><?php echo $val['Client_Name'].'-'.$val['Client_Code'];?></td>
              <td><!--<a href="<?php echo PATH_PDF_LINK."/invoice/".$val['Invoice_No'].".pdf"?>" target="_blank">--><?php echo $val['Invoice_No'];?><!--</a>--></td>
              <td><?php echo $val['Invoice_Amount'];?></td>
              <td><?php echo $val['Fuel_Surcharge'];?></td>
              <td><?php echo $val['Subtotal'];?></td>
              <td><?php echo $val['SGST_Tax'];?></td>
              <td><?php echo $val['CGST_Tax'];?></td>
              <td><?php echo $val['IGST_Tax'];?></td>
              <td><?php echo round($val['Final_Total_Amt']);?></td>
              <td><?php echo $val['Date_From'];?></td>              
              <td><?php echo $val['Date_To'];?></td>
              
              <!--<td><a href="<?php echo PATH_PDF_LINK."/invoice/".$val['Invoice_No'].".pdf"?>" target="_blank"><i class="fa fa-file-pdf-o danger"></i></a></td>-->
              <td>
              
                <a href="edit-invoice.php?id=<?php echo $val['Invoice_Id']; ?>" class="btn btn-sm btn-success">Edit</a>
                <!--<button type="button" class="btn btn-info btn-sm resend" id="<?php echo $val['Invoice_Id']; ?>" name="resend">Resend Invoice</button>--></td>
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
    </div>
    <?php //}
	?>
	
	
</div>
</div>
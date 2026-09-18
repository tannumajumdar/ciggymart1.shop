<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();
?>

<script type="text/javascript" src="invoice.js" ></script>

<script>
	
$(document).ready(function(){ 
	
	/////////////////////////////////////////////////////
	// Get Client Code and Name list(autocomplete)///////
	///////////////////////////////////////////////////// 
	var clientcodelist = [
		<?php // PHP begins here
		  
			$menu=$db->ExecuteQuery("SELECT Client_Code, Client_Name, Address FROM tbl_clients WHERE 1=1 AND Client_Id IN (SELECT Client_Id FROM tbl_rates)");
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
	})
	.on( 'autocompleteresponse autocompleteselect', function( e, ui ){
					
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

    $("#bill_date").datepicker({
      dateFormat: "dd-mm-yy"
    });
	});
});
</script>

<div id="loader">
    <div class="loader-block"><i class="fa-li fa fa-spinner fa-spin spinloader"></i></div>
</div>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="report.php"><i class="glyphicon glyphicon-list"></i> <strong>Invoice Report</strong></a>
    </div>
	<h1 class="pull-left">Generate Invoice</h1>
    <div class="clearfix"></div>
</div>

<div class="main" style="padding: 24px;">
  <div class="erp-card" style="background: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <form id="searchClient" method="post">
        <div class="row" style="display: flex; align-items: flex-end; gap: 16px;">
            <div class="col-sm-5 custom-fg" style="margin-bottom: 0;">
                <label>Client Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Search Client Name..." value="" />
            </div>
            <div class="col-sm-2" style="margin-bottom: 0;">
                <button type="button" class="btn btn-primary" id="submit" style="border-radius: 6px; padding: 8px 24px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.3); width: 100%;"><i class="fa fa-search"></i> Search</button>
            </div>
            <div class="col-sm-4" id="clientname" style="display:flex; align-items:center;">
                 <!-- ajax target -->
            </div>
        </div>
    </form>
    <br />
    
  <div id="hideDiv" style="display:none">
 	<div class="col-sm-5 col-sm-offset-3">
      <table class="table table-hover table-bordered" id="addedProducts">
          <thead>
            <tr class="success">
              <th>Customer Name</th>
              <th>Joining Date</th>
              <th>Last Invoice Date</th>
              
            </tr>
          </thead>
          <tbody>
          <tr>
             <td id="C_Name"></td>
             <td id="Joining_Date"></td> 
             <td class="Last_Date"></td>
          </tr>
          </tbody>
          </table>
      
      </div>
      
           <form id="searchRate" method="post">
        <input type="hidden" id="Last_Date_input" name="Last_Date_input" value=""/>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 24px;">
            <div class="row">
                <div class="col-sm-3 custom-fg">
                    <label>From Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="from_date" name="from_date" placeholder="DD-MM-YYYY" />
                </div>
                <div class="col-sm-3 custom-fg">
                    <label>To Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="to_date" name="to_date" placeholder="DD-MM-YYYY" />
                </div>
                <div class="col-sm-3 custom-fg">
                    <label>Bill Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="bill_date" name="bill_date" placeholder="DD-MM-YYYY" />
                </div>
                <div class="col-sm-3" style="display: flex; align-items: flex-end; margin-bottom: 16px;">
                    <button type="button" class="btn btn-success" id="search" style="border-radius: 6px; padding: 8px 24px; font-weight: 600; width: 100%; box-shadow: 0 4px 6px -1px rgba(34,197,94,0.3);"><i class="fa fa-file-text-o"></i> Generate Data</button>
                </div>
            </div>
            <!-- display all consignment details dynamically -->
            <div id="detail"></div>
        </div>
    </form>
    <br />
        
    </div> 
    
    </div>
 </div>
</div>
</div>




<?php
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
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

<div class="main">
  <div class="clear formbgstyle">
    <form class="form-horizontal" role="form" id="searchClient" method="post"  >

        <div class="center-block">
            <div class="form-group">
              <label class="control-label col-sm-4 mandatory" for="client_code">Client Name <span>*</span>:</label>
              <!--<div class="col-sm-2 padding-left-right-zero">
                <input type="text" class="form-control input-sm" id="client_code" name="client_code" placeholder="Ex: 101, 102..." />
              </div>-->
              <div class="col-sm-3 padding-left-right-zero">
                <input type="text" class="form-control input-sm" id="client_name" name="client_name" placeholder="Client Name" value="" />
              </div> 
              <div class="col-sm-1">
                <input type="button" class="btn btn-primary btn-sm" id="submit" value="Search">
              </div>
              <div class="col-sm-1" id="clientname">
                 
              </div>
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
      
       <form class="form-horizontal" role="form" id="searchRate" method="post" >
       
        <input type="hidden" id="Last_Date_input" name="Last_Date_input" value=""/>
       
        <div class="col-sm-10 col-sm-offset-1">
          <div style="padding:10px;">
            <div class="form-group">
              <label class="control-label col-sm-2 mandatory" for="from_date">From<span>*</span>:</label>
              <div class="col-sm-3">
                <input type="text" class="form-control input-sm" id="from_date" name="from_date" />
              </div>
              <label class="control-label col-sm-1 mandatory" for="to_date">To <span>*</span>:</label>
              <div class="col-sm-3">
                <input type="text" class="form-control input-sm" id="to_date" name="to_date" />
              </div>
              <div class="col-sm-2">
                <input type="button" class="btn btn-primary btn-sm" id="search" value="Search">
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-sm-2 mandatory" for="bill_date">Bill Date<span>*</span>:</label>
              <div class="col-sm-3">
                <input type="text" class="form-control input-sm" id="bill_date" name="bill_date" />
              </div>
            </div>
            
            
            <!-- display all consignment details dynamically -->
            <div id="detail"></div>
            
          </div>
     </div>
    </form>
    <br />
        
    </div> 
    
    </div>
 </div>
</div>
</div>
<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

?>
<script type="text/javascript" src="rate.js" ></script>

<script>
$(document).ready(function(){ 
    
	///////////// Get Client Name and Code list(autocomplete)///////////////////////  
	var clientNamelist = [
		<?php // PHP begins here
		  
			$menu=$db->ExecuteQuery("SELECT Client_Code, Client_Name, Address FROM tbl_clients WHERE 1=1");
			foreach($menu as $val) {
				echo '"'.$val['Client_Name'].'-'.$val['Client_Code'].'-'.$val['Address'].'",';
			} // PHP ends here*/
		?>
	];// eof json format
	
	$("#client_name").autocomplete({
   
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( clientNamelist, function(item,index){
				return matcher.test(item);
			});
			responseFn( a );
		}
	}).on( 'autocompleteresponse autocompleteselect', function( e, ui ){
					
			var formdata = new FormData();
			formdata.append('type', "getClientName");
			formdata.append('client_name', $("#client_name").val());
	
			var x;
			$.ajax({
			   type: "POST",
			   url: "rate_curd.php",
			   data:formdata,
			   success: function(data){ console.log(data);
				   $('#clientname').html(data);
			   },
			   cache: false,
			   contentType: false,
			   processData: false
			});//eof ajax
		
	});
});//eof ready function
</script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add Rate</strong></a>
        <!--<a class="btn btn-success btn-sm" href="defaultratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Default Rate List</strong></a>-->
    </div>
	<h1 class="pull-left">Rate List</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
	<div class="clear formbgstyle">
        <form class="form-horizontal" role="form" id="searchRate" method="post">
          <div class="col-sm-8 col-sm-offset-2">
            <div class="grayFrmArea">
                        	
                <div class="form-group custom-fg">
                  <label class="control-label col-sm-3 mandatory" for="client_name">Client Name <span>*</span>:</label>
                  <div class="col-sm-6">
                  	<input type="text" class="form-control input-sm" id="client_name" name="client_name" placeholder="Client Name" value="" />
                  </div>
                  <div class="col-sm-2" id="clientname">
                  	 
                  </div>
                </div>
            	
                <div class="form-group custom-fg">
                  <div class="col-sm-5"></div>
                  <div class="col-sm-5">
                    <input type="button" class="btn btn-primary btn-sm" id="search" value="Search">
                  </div>
                </div>
                <div class="clearfix"></div>
        	</div>
          </div>
        </form>
        
        <div id="rateListResult"></div>
        
    </div>
</div>




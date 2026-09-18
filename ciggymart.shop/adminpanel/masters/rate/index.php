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
		  
			$menu=$db->ExecuteQuery("SELECT Client_Code, Client_Name FROM tbl_clients WHERE 1=1");
			foreach($menu as $val) {
				echo '"'.$val['Client_Name'].'-'.$val['Client_Code'].'",';
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
	}).on( 'autocompleteselect', function( e, ui ){
					
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
		
	});
	
	
	///////////// Get Zone List(autocomplete)///////////////////////  
	var zonelist  = [
		<?php // PHP begins here
		  
			$menu=$db->ExecuteQuery("SELECT Zone_Code FROM tbl_zones ");
			foreach($menu as $val) {
				echo '"'.$val['Zone_Code']. '",';
			} // PHP ends here*/
		?>
	];// eof json format
	
	$("#zone_code").autocomplete({
   
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( zonelist, function(item,index){
				return matcher.test(item);
			});
			responseFn( a );
		}
	}).on( 'autocompleteresponse autocompleteselect', function( e, ui ){
					
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
	
});// eof ready function
</script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
       <!-- <a class="btn btn-success btn-sm" href="<?php echo MASTERS_LINK_CONTROL ?>/clients/index.php"><i class="glyphicon glyphicon-list"></i> <strong>Client List</strong></a>
        <a class="btn btn-success btn-sm" href="defaultratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Default Rate List</strong></a>-->
        <a class="btn btn-success btn-sm" href="ratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Rate List</strong></a>
    </div>
	<h1 class="pull-left">Add New Rate</h1>
    <div class="clearfix"></div>
</div>

<div class="main" style="padding: 24px;">
    <div class="erp-card" style="background: #ffffff; padding: 32px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
        
        <div style="margin-bottom: 32px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px;">
            <h4 style="color: #0f172a; font-weight: 700; margin: 0; font-size: 18px;"><i class="fa fa-info-circle text-primary"></i> Add / Edit Rate Master</h4>
        </div>

        <form role="form" id="searchClient" method="post">
            <div class="row">
                <div class="col-md-6 custom-fg">
                    <label>Client Name <span class="text-danger">*</span></label>
                    <div class="input-group" style="width: 100%;">
                        <span class="input-group-addon" style="background: #f8fafc;"><input type="checkbox" id="c_name" name="c_name" value="1"></span>
                        <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Type to search client..." />
                    </div>
                </div>
                
                <div class="col-md-6 custom-fg">
                    <label>Send By <span class="text-danger">*</span></label>
                    <div class="input-group" style="width: 100%;">
                        <span class="input-group-addon" style="background: #f8fafc;"><input type="checkbox" id="c_mode" name="c_mode" value="1"></span>
                        <select class="form-control" id="send_mode" name="send_mode">
                            <option value="0">Select Mode</option>
                            <option value="1">By Air</option>
                            <option value="2">By Train</option>
                            <option value="3">By Surface</option>
                            <option value="4">By Hand</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 16px;">
                <div class="col-md-6 custom-fg">
                    <label>Zone / Destination <span class="text-danger">*</span></label>
                    <div class="input-group" style="width: 100%;">
                        <span class="input-group-addon" style="background: #f8fafc;"><input type="checkbox" id="c_zone" name="c_zone" value="1"></span>
                        <div style="display: flex; gap: 8px; width: 100%;">
                            <input type="text" class="form-control" id="zone_code" name="zone_code" placeholder="Ex: Zone1, Zone2..." style="flex: 1;" />
                            <input type="text" class="form-control" id="zone_name" name="zone_name" placeholder="Zone Name" style="flex: 2; background-color: #f1f5f9;" readonly />
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 40px; margin-bottom: 24px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px;">
                <h4 style="color: #0f172a; font-weight: 700; margin: 0; font-size: 18px;"><i class="fa fa-list-alt text-primary"></i> Rate Details Structure</h4>
                <p style="color: #64748b; font-size: 13px; margin-top: 8px;">Enter the weight brackets and corresponding rates.</p>
            </div>

            <div style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; padding: 24px;">
                <div class="row" style="margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #cbd5e1;">
                    <div class="col-sm-4"><strong style="color: #334155;">Weight From (kg)</strong></div>
                    <div class="col-sm-4"><strong style="color: #334155;">Weight To (kg)</strong></div>
                    <div class="col-sm-4"><strong style="color: #334155;">Rate (₹)</strong></div>
                </div>                <div class="row" style="margin-bottom: 12px; display: flex; align-items: center;">
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightfrom" id="w_from1" name="w_from1" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightto" id="w_to1" name="w_to1" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control rate" id="rate1" name="rate1" placeholder="0.00" />
                    </div>
                </div>                <div class="row" style="margin-bottom: 12px; display: flex; align-items: center;">
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightfrom" id="w_from2" name="w_from2" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightto" id="w_to2" name="w_to2" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control rate" id="rate2" name="rate2" placeholder="0.00" />
                    </div>
                </div>                <div class="row" style="margin-bottom: 12px; display: flex; align-items: center;">
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightfrom" id="w_from3" name="w_from3" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightto" id="w_to3" name="w_to3" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control rate" id="rate3" name="rate3" placeholder="0.00" />
                    </div>
                </div>                <div class="row" style="margin-bottom: 12px; display: flex; align-items: center;">
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightfrom" id="w_from4" name="w_from4" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightto" id="w_to4" name="w_to4" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control rate" id="rate4" name="rate4" placeholder="0.00" />
                    </div>
                </div>                <div class="row" style="margin-bottom: 12px; display: flex; align-items: center;">
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightfrom" id="w_from5" name="w_from5" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightto" id="w_to5" name="w_to5" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control rate" id="rate5" name="rate5" placeholder="0.00" />
                    </div>
                </div>                <div class="row" style="margin-bottom: 12px; display: flex; align-items: center;">
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightfrom" id="w_from6" name="w_from6" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control weightto" id="w_to6" name="w_to6" placeholder="0.00" />
                    </div>
                    <div class="col-sm-4 custom-fg" style="margin-bottom: 0;">
                        <input type="text" class="form-control rate" id="rate6" name="rate6" placeholder="0.00" />
                    </div>
                </div>            </div>

            <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-default" style="background: #f1f5f9; border-color: #cbd5e1; color: #475569;">Cancel</button>
                <input type="button" class="btn btn-primary" id="submit" value="Save Rates" style="padding: 8px 32px; font-size: 15px;" />
            </div>
            
            <div id="addmsg" style="margin-top: 16px; text-align: right;"></div>
        </form>
    </div>
</div>
</div>
</div>
</body>
</html>

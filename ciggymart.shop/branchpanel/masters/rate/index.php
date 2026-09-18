<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

?>
<script type="text/javascript" src="rate.js" ></script>

<script>
$(document).ready(function(){ 
	///////////// Get Client Name and Code list(autocomplete)///////////////////////  
	var clientNamelist = [
		<?php // PHP begins here
		  
			$menu=$db->ExecuteQuery("SELECT Client_Code, Client_Name FROM tbl_clients WHERE Branch_Id=".$_SESSION['buser']);
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
       <!-- <a class="btn btn-success btn-sm" href="<?php echo BRANCH_MASTERS_LINK_CONTROL ?>/clients/index.php"><i class="glyphicon glyphicon-list"></i> <strong>Client List</strong></a>
        <a class="btn btn-success btn-sm" href="defaultratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Default Rate List</strong></a>-->
        <a class="btn btn-success btn-sm" href="ratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Rate List</strong></a>
    </div>
	<h1 class="pull-left">Add New Rate</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
    <div class="clear formbgstyle">
      
      <div id="msg" class="alert alert-success" style="display:none;"><strong>Rates Added successfully!</strong></div>
      <form class="form-horizontal" role="form" id="insertRate" method="post" >
        <div class="col-sm-8 col-sm-offset-2">
            <div class="grayFrmArea">
               <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="client_name">Client Name <span>*</span>:</label>
                  <input class="pull-left checkboxVerAlign" id="clientChkbx" type="checkbox" />
                  <div class="col-sm-6 input-group">
                  	<input type="text" class="form-control input-sm" id="client_name" name="client_name" placeholder="Client Name" value="" />
                  </div>
                  <div class="col-sm-2" id="clientname">
                  	 
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="send_by">Send By<span>*</span>:</label>
                  <input class="pull-left checkboxVerAlign" id="sendByChkbx" type="checkbox" />
                  <div class="col-sm-6 input-group">
                  	 <select id="send_by" name="send_by"  class="form-control input-sm">
                        <option value="">Select Mode</option>
                        <option value="1">Surface</option>
                        <option value="2">Air</option>
                        <option value="3">Urgent</option>
                      </select>
                  </div>
                </div>
                <div class="clearfix"></div>
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="zone_code">Zone Code <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="zone_code" name="zone_code" placeholder="Ex: Zone1, Zone2..." />
                  </div>
                  
                  <div class="col-sm-6" id="zonename">
                  	 <input type="text" class="form-control input-sm" id="zone_name" name="zone_name" placeholder="Zone Name" readonly="readonly" value="" />
                  </div>
                </div>                
            	<div class="clearfix"></div>
          </div>
          
          
          <div class="form-group">
            <div class="col-sm-3 col-sm-offset-5 frmHead2"><h2>Rate Detail</h2></div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2 mandatory" for="dest_to"></label>
            <div class="col-sm-3"><strong>Weight From</strong></div>
            <div class="col-sm-3"><strong>Weight To</strong></div>
            <div class="col-sm-3"><strong>Rate</strong></div>
          </div>
          <div class="form-group len" >
            <label class="control-label col-sm-2 mandatory" for="weight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom " id="w_from1" name="w_from1"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightto "  id="w_to1" name="w_to1" />
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm rate "  id="rate1" name="rate1" />
            </div>
          </div>
          <div class="form-group len">
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom" id="w_from2" name="w_from2"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightto"  id="w_to2" name="w_to2" />
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm rate"  id="rate2" name="rate2" />
            </div>
          </div>
          <div class="form-group len">
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom" id="w_from3" name="w_from3"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div id="dest_name2" class="col-sm-2">
              <input type="text" class="form-control input-sm weightto"  id="w_to3" name="w_to3" />
            </div>
            <div class="col-sm-1">kg </div>
            <div id="dest_name2" class="col-sm-2">
              <input type="text" class="form-control input-sm rate"  id="rate3" name="rate3" />
            </div>
          </div>
          <div class="form-group len">
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom" id="w_from4" name="w_from4"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightto"  id="w_to4" name="w_to4" />
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm rate "  id="rate4" name="rate4" />
            </div>
          </div>
          <div class="form-group len">
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom " id="w_from5" name="w_from5"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightto "  id="w_to5" name="w_to5" />
            </div>
            <div class="col-sm-1">kg </div>
            <div  class="col-sm-2">
              <input type="text" class="form-control input-sm rate "  id="rate5" name="rate5" />
            </div>
          </div>
          <div class="form-group len">
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom " id="w_from6" name="w_from6"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightto "  id="w_to6" name="w_to6" />
            </div>
            <div class="col-sm-1">kg </div>
            <div  class="col-sm-2">
              <input type="text" class="form-control input-sm rate "  id="rate6" name="rate6" />
            </div>
          </div>
          <div class="form-group len">
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom " id="w_from7" name="w_from7"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightto "  id="w_to7" name="w_to7" />
            </div>
            <div class="col-sm-1">kg </div>
            <div  class="col-sm-2">
              <input type="text" class="form-control input-sm rate "  id="rate7" name="rate7" />
            </div>
          </div>
          <div class="form-group len" >
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightfrom " id="w_from8" name="w_from8"/>
            </div>
            <div class="col-sm-1">kg </div>
            <div class="col-sm-2">
              <input type="text" class="form-control input-sm weightto "  id="w_to8" name="w_to8" />
            </div>
            <div class="col-sm-1">kg </div>
            <div  class="col-sm-2">
              <input type="text" class="form-control input-sm rate "  id="rate8" name="rate8" />
            </div>
          </div>
          <p></p>
          <div class="form-group">
            <label class="control-label col-sm-2 mandatory" for="waight"></label>
            <div class="col-sm-3 col-sm-offset-3">
              <button type="button" class="btn btn-success btn-sm" id="add_more" name="add_more">Add Row</button>
            </div>
          </div>
          
          <div class="form-group">
            <label class="control-label col-sm-4 mandatory" for="additional_weight">Additional Weight<span>*</span></label>
            <div class="col-sm-3">
             	<input type="text" class="form-control input-sm rate "  id="additional_weight" name="additional_weight" placeholder="Weight (In K.G.)" />
            </div>
            <label class="control-label col-sm-1 mandatory" for="additional_rate">Rs.<span>*</span></label>
            <div class="col-sm-3">
             	<input type="text" class="form-control input-sm rate "  id="additional_rate" name="additional_rate" placeholder="Rs." />
            </div>
          </div>
          
          <div class="form-group">
            <div class="col-sm-5"></div>
            <div class="col-sm-5">
              <input type="button" class="btn btn-primary btn-sm" id="submit" value="Submit">
              <input type="reset" class="btn btn-default btn-sm" value="Reset" id="reset">
            </div>
          </div>
        </div>
      </form>
    </div>
</div>
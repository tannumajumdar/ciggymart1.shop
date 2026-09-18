<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
require_once(PATH_LIBRARIES.'/functions/fun1.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get list of rate
$rate=$db->ExecuteQuery("SELECT R.Rate_Id, R.Additional_Weight, R.Additional_Rate, CASE WHEN R.Send_By=1 THEN 'Surface' WHEN R.Send_By=2 THEN 'Air' ELSE 'Urgent' END Send_By, R.Zone_Id, R.Client_Id, Weight_From, Weight_To, Amount, Zone_Code, Zone_Name, Client_Code, Client_Name 
FROM tbl_rates R 
INNER JOIN tbl_zones Z ON R.Zone_Id=Z.Zone_Id 
INNER JOIN tbl_weight_rate_relation WR on WR.Rate_Id=R.Rate_Id 
INNER JOIN tbl_clients C ON C.Client_Id = R.Client_Id 
WHERE R.Rate_Id=".$_REQUEST['id']);

?>
<script type="text/javascript" src="rate.js" ></script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
    	<a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add Rate</strong></a>
        <a class="btn btn-success btn-sm" href="ratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Rate List</strong></a>
    </div>
	<h1 class="pull-left">Edit Rate</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
    
   	<div class="clear formbgstyle">
    	<form class="form-horizontal" role="form" id="editRate" method="post" >
    	  
          <input type="hidden" id="id" value="<?php  echo $_REQUEST['id']?>" />
     	  <input type="hidden" id="count" value="<?php  echo count($rate);?>" />
          
          <div class="col-sm-8 col-sm-offset-2">
              <div class="grayFrmArea">
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="client_code">Client Code <span>*</span>:</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" id="client_code" name="client_code" placeholder="Ex: 101, 102..." value="<?php echo $rate[1]['Client_Code']?>" readonly="readonly" />
                  </div>
                  
                  <div class="col-sm-6" id="clientname">
                  	 <input type="hidden" id="client_id" name="client_id" value="<?php echo $rate[1]['Client_Id']?>" />
                  	 <input type="text" class="form-control input-sm" id="client_name" name="client_name" placeholder="Client Name" readonly="readonly" value="<?php echo $rate[1]['Client_Name']?>" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="send_by">Send By<span>*</span>:</label>
                  <div class="col-sm-6">
                  	 <select id="send_by" name="send_by"  class="form-control input-sm" readonly="readonly">
                        <option value=""><?php echo $rate[1]['Send_By']?></option>
                      </select>
                  </div>
                </div>
                
                <div class="clearfix"></div>
                
                <div class="form-group">
                  <label class="control-label col-sm-3 mandatory" for="zone_code">Zone Code <span>*</span>:</label>
                  <div class="col-sm-2">
                  	<input type="hidden" id="zone_id" name="zone_id" value="<?php echo $rate[1]['Zone_Id']?>" />
                    <input type="text" class="form-control input-sm" id="zone_code" name="zone_code" placeholder="Ex: Zone1, Zone2..." value="<?php echo $rate[1]['Zone_Code']?>" readonly="readonly" />
                  </div>
                  
                  <div class="col-sm-6" id="zonename">
                  	 <input type="text" class="form-control input-sm" id="zone_name" name="zone_name" placeholder="Zone Name" readonly="readonly" value="<?php echo $rate[1]['Zone_Name']?>" />
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
            
            <?php 
			$i=1;
			foreach($rate as $rateVal){ ?>
           
           	<div class="form-group len" >
              <label class="control-label col-sm-2 mandatory" for="weight"></label>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm weightfrom " id="w_from<?php echo $i ?>" name="w_from<?php echo $i ?>"  value="<?php if(isset($rateVal['Weight_From'])){echo $rateVal['Weight_From'];}?>" />
               </div>
               <div class="col-sm-1">kg </div>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm weightto"  id="w_to<?php echo $i ?>" name="w_to<?php echo $i ?>"  value="<?php if(isset($rateVal['Weight_To'])){echo $rateVal['Weight_To'];}?>"  />
               </div>
               <div class="col-sm-1">kg </div>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm rate "  id="rate<?php echo $i ?>" name="rate<?php echo $i ?>"  value="<?php if(isset($rateVal['Amount'])){ echo $rateVal['Amount'];}?>" />
               </div>
          </div>
          <?php
		  $i++;}
          ?>
          
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
             	<input type="text" class="form-control input-sm rate "  id="additional_weight" name="additional_weight" placeholder="Weight (In K.G.)" value="<?php echo $rate[1]['Additional_Weight']?>" />
            </div>
            <label class="control-label col-sm-1 mandatory" for="additional_rate">Rs.<span>*</span></label>
            <div class="col-sm-3">
             	<input type="text" class="form-control input-sm rate "  id="additional_rate" name="additional_rate" placeholder="Rs." value="<?php echo $rate[1]['Additional_Rate']?>" />
            </div>
          </div>    
      
                    
               <div class="form-group">
                 <div class="col-sm-5"></div>
                   <div class="col-sm-5">
                    <input type="button" class="btn btn-primary btn-sm" id="edit" value="Update">
                    <input type="reset" class="btn btn-default btn-sm" value="Reset" id="reset">
                  </div>
                </div>
               
            </div>
           
        </form>
    </div>
</div>
<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get list of rate
$rate=$db->ExecuteQuery("SELECT R.Rate_Id, R.Additional_Weight, R.Additional_Rate, CASE WHEN R.Send_By=1 THEN 'Surface' WHEN R.Send_By=2 THEN 'Air' ELSE 'Urgent' END Send_By, R.Zone_Id, Zone_Code, Zone_Name, Weight_From, Weight_To, Amount, R.Client_Id, Client_Code, Client_Name 
FROM tbl_rates R 
INNER JOIN tbl_zones Z ON R.Zone_Id = Z.Zone_Id 
INNER JOIN tbl_weight_rate_relation WR ON WR.Rate_Id = R.Rate_Id 
INNER JOIN tbl_clients C ON C.Client_Id = R.Client_Id 
WHERE WR.Rate_Id=".$_REQUEST['id']);

?>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add Rate</strong></a>
        <a class="btn btn-success btn-sm" href="ratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Rate List</strong></a>
    </div>
	<h1 class="pull-left">View Rate</h1>
    <div class="clearfix"></div>
</div>

<div class="main">
   <div class="clear formbgstyle">
    <form class="form-horizontal" role="form" id="insertRate" method="post" >
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
          <?php foreach($rate as $val){?>
          <div class="form-group " >
              <label class="control-label col-sm-2 mandatory" for="weight"></label>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm weightfrom " id="w_from1" name="w_from1" value="<?php echo $val['Weight_From']; ?>" readonly="readonly" />
               </div>
               <div class="col-sm-1">kg </div>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm weightto "  id="w_to1" name="w_to1" value="<?php echo $val['Weight_To']; ?>" readonly="readonly" />
               </div>
               <div class="col-sm-1">kg </div>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm rate "  id="rate1" name="rate1" value="<?php echo $val['Amount']; ?>" readonly="readonly" />
               </div>
               
          </div>
          <?php } ?>
          
          <hr />
          
          <div class="form-group">
            <label class="control-label col-sm-4 mandatory" for="additional_weight">Additional Weight</label>
            <div class="col-sm-3">
             	<input type="text" class="form-control input-sm rate "  id="additional_weight" name="additional_weight" value="<?php echo $rate[1]['Additional_Weight']?>" readonly="readonly" />
            </div>
            <label class="control-label col-sm-1 mandatory" for="additional_rate">Rs.</label>
            <div class="col-sm-3">
             	<input type="text" class="form-control input-sm rate "  id="additional_rate" name="additional_rate" value="<?php echo $rate[1]['Additional_Rate']?>" readonly="readonly" />
            </div>
          </div>
          
          <hr />
          
          <div class="text-center"><a id="editbtn" class="btn btn-success btn-sm" href="edit_rate.php?id=<?php echo $val['Rate_Id']?>"><span class="glyphicon glyphicon-edit"></span> Edit</a></div>
          
          <hr />
      </div>
    </form>
   </div>
</div>
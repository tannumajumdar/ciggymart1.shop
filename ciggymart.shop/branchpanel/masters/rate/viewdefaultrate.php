<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get list of rate
$rate=$db->ExecuteQuery("SELECT r.Rate_Id, Zone_Code, Zone_Name,Weight_From, Weight_To,Rate FROM rate_master r INNER JOIN zone_master z
on r.Zone=z.Zone_Id inner join rate_weight rw on rw.Rate_Id=r.Rate_Id  where r.Rate_Id=".$_REQUEST['id']."");
?>

<div class="main">
<div class="ef_header_tools pull-right">
  	<a class="btn btn-success btn-sm" href="defaultratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Back</strong></a>
  </div>
 <h4>View Default Rate</h4>
   <div class="clear formbgstyle">
    <form class="form-horizontal" role="form" id="insertRate" method="post" >
      <div class="col-sm-8 col-sm-offset-2">
        <div style="background: #E4E4E4; padding:10px;">
             
            <div class="form-group">
              <label class="control-label col-sm-3 mandatory" for="zone_code">Zone<span>*</span>:</label>
              <div class="col-sm-3">
                <input type="text" class="form-control input-sm dest" id="zone_code" name="zone_code" value="<?php echo $rate[1]['Zone_Code']; ?>"  readonly="readonly" />
              </div>
             <div id="zone_name" class="col-sm-5">
                <input type="text" class="form-control input-sm"  value="<?php echo $rate[1]['Zone_Name']?>"  readonly="readonly"/>
            </div>
           </div>
         </div>  
        
          <br/> 
          <div class="form-group">      
             <div class="col-sm-3 col-sm-offset-5"><strong>Rate Detail</strong></div> 
           </div>
          <?php foreach($rate as $val){?>
          <div class="form-group " >
              <label class="control-label col-sm-2 mandatory" for="weight"></label>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm weightfrom " id="w_from1" name="w_from1" value="<?php echo $val['Weight_From']; ?>"/>
               </div>
               <div class="col-sm-1">kg </div>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm weightto "  id="w_to1" name="w_to1" value="<?php echo $val['Weight_To']; ?>"/>
               </div>
               <div class="col-sm-1">kg </div>
               <div class="col-sm-2">
                 <input type="text" class="form-control input-sm rate "  id="rate1" name="rate1" value="<?php echo $val['Rate']; ?>" />
               </div>
               
          </div>
          <?php } ?>
        </form>
    </div>
</div>
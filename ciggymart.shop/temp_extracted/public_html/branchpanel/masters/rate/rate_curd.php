<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
require_once(PATH_LIBRARIES.'/classes/resize.php');

$db = new DBConn();

///*******************************************************
/// for Client invalid
///*******************************************************
if($_POST['type']=="clientInvalid")
{
	$clientName = explode("-",$_POST['client_name']);
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_clients WHERE Client_Name ='".$clientName[0]."' AND Branch_Id=".$_SESSION['buser']." ) AS 'Find'");
	
	echo $sql[1]['Find'] ;	  
}

///*******************************************************
/// for zone invalid
///*******************************************************
if($_POST['type']=="zone_Invalid")
{
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_zones WHERE Zone_Code='".$_POST['zone_code']."') AS 'Find'");
	echo $sql[1]['Find'] ;
}

///*******************************************************
/// for zone exists add rate
///*******************************************************
if($_POST['type']=="zoneExists")
{
	
	$isarr = substr_count($_POST['client_name'], '-');		
	if($isarr!=0){
		$cName = explode('-',$_POST['client_name']);
	
		$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_rates WHERE Client_Id =(SELECT Client_Id FROM tbl_clients WHERE Client_Name='".$cName[0]."' AND Client_Code=".$cName[1].") AND Zone_Id=(SELECT Zone_Id FROM tbl_zones WHERE Zone_Code ='".$_POST['zone_code']."') AND Send_By=".$_POST['send_by']." ) AS 'Find'");
		
		echo $sql[1]['Find'] ;
	}
	
	
}

///*******************************************************
/// for zone exists for edit rate
///*******************************************************
if($_POST['type']=="zoneExists2")
{
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_rates WHERE Client_Id =(SELECT Client_Id FROM tbl_clients WHERE Client_Code='".$_POST['client_code']."') AND Zone_Id=(SELECT Zone_Id FROM tbl_zones WHERE Zone_Code ='".$_POST['zone_code']."') AND Rate_Id<>".$_POST['id']." ) AS 'Find'");
	
	echo $sql[1]['Find'] ;
}

///*******************************************************
/// Get Client Name
///*******************************************************
if($_POST['type']=="getClientName")
{
	if(!empty($_POST['client_name'])){
		
		$isarr = substr_count($_POST['client_name'], '-');
		
		if($isarr!=0){
			
			$clientName = explode('-',$_POST['client_name']);
			
			$sql="SELECT Client_Id, Client_Name FROM tbl_clients WHERE Client_Name='".$clientName[0]."' AND Client_Code=".$clientName[1];
			$res=$db->ExecuteQuery($sql);	
		}
	}
		
	if(!empty($res))
    {
 		echo "<input type='hidden' id='client_id' name='client_id' value='".$res[1]['Client_Id']."' />";
    }		
}

///*******************************************************
/// Get Zone Name
///*******************************************************
if($_POST['type']=="getZone")
{
	$sql="SELECT Zone_Name, Zone_Id FROM tbl_zones WHERE Zone_Code='".$_POST['zone_code']."'";
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo "<input type='text' class='form-control input-sm' id='zone_name' name='zone_name' placeholder='Zone Name' readonly='readonly' value='' />";
    }
	else{
		
		echo "<input type='text' class='form-control input-sm' id='zone_name' name='zone_name' placeholder='Zone Name' readonly='readonly' value='".$res[1]['Zone_Name']."' /> <input type='hidden' class='form-control input-sm' id='zone_id' name='zone_id' value='".$res[1]['Zone_Id']."' />";
		
	}	

}

///*******************************************************
/// To Insert New Client /////////////////////////////////
///*******************************************************
if($_POST['type']=="addRate")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	
		$res=mysql_query("INSERT INTO tbl_rates (Additional_Weight, Additional_Rate, Send_By, Client_Id, Zone_Id) VALUES(".$_POST['additional_weight'].", ".$_POST['additional_rate'].", ".$_POST['send_by'].", ".$_POST['client_id'].", ".$_POST['zone_id'].")");

		if(!$res)
		{
			throw new Exception('error1');
		}
		
		$last_Id=mysql_insert_id();
		$weight=explode(',',$_POST['weight']);
		
		foreach($weight as  $val)
		{			
			$arr= split('[@]', $val);
			$res=mysql_query("INSERT INTO tbl_weight_rate_relation (Weight_From, Weight_To, Amount, Rate_Id) VALUES(".$arr[0].", ".$arr[1].", ".$arr[2].", ".$last_Id.")");
		}
		
		if(!$res)
		{
			throw new Exception('error2');
		}

		mysql_query("COMMIT",$con);
		echo 1;
	}
	catch(Exception $e)
	{
		echo  $e->getMessage();
		mysql_query('ROLLBACK',$con);
		mysql_query('SET AUTOCOMMIT=1',$con);
	}
}

///*******************************************************
/// To Edit Client /////////////////////////////////
///*******************************************************
if($_POST['type']=="editRate")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	
		$res=mysql_query("UPDATE tbl_rates SET Additional_Weight=".$_POST['additional_weight'].", Additional_Rate=".$_POST['additional_rate'].", Client_Id=".$_POST['client_id'].", Zone_Id=".$_POST['zone_id']." WHERE Rate_Id=".$_POST['id']);
		
		if(!$res)
		{
			throw new Exception('a');
		}
		$res=mysql_query("DELETE FROM tbl_weight_rate_relation WHERE Rate_Id=".$_POST['id']."");

		if(!$res)
		{
			throw new Exception('b');
		}
		
		$weight=explode(',',$_POST['weight']);
		
		foreach($weight as $val)
		{
			$arr= split('[@]', $val);
			$res=mysql_query("INSERT INTO tbl_weight_rate_relation (Rate_Id, Weight_From, Weight_To, Amount)  
VALUES(".$_POST['id'].",".$arr[0].",".$arr[1].",".$arr[2].")");
		}
		
		if(!$res)
		{
			throw new Exception('c');
		}

		mysql_query("COMMIT",$con);
		echo 1;
	}
	catch(Exception $e)
	{
		echo  $e->getMessage();
		mysql_query('ROLLBACK',$con);
		mysql_query('SET AUTOCOMMIT=1',$con);
	}
}

///*******************************************************
/// To Search Rates of client ////////////////////////////
///*******************************************************
if($_POST['type']=="searchRateList"){
	
	//Get list of rate
	$rateList=$db->ExecuteQuery("SELECT R.Rate_Id, R.Additional_Weight, R.Additional_Rate, CASE WHEN R.Send_By=1 THEN 'Surface' WHEN R.Send_By=2 THEN 'Air' ELSE 'Urgent' END Send_By, C.Client_Code, C.Client_Name, C.Address, Z.Zone_Code, Z.Zone_Name 
	FROM tbl_rates R 
	INNER JOIN tbl_zones Z ON R.Zone_Id=Z.Zone_Id 
	INNER JOIN tbl_clients C ON C.Client_Id = R.Client_Id 
	WHERE R.Client_Id = ".$_POST['client_id']);
	
	
	if(count($rateList) > 0)
	{		
		echo '	
		<div class="clear formbgstyle" style="padding-top:20px;">
		<div><strong>Client Code: </strong>'.$rateList[1]['Client_Name'].'-'.$rateList[1]['Client_Code'].'-'.$rateList[1]['Address'].'</div>
		<table class="table table-hover table-bordered" id="addedProducts">
		  <thead>
			<tr class="success">
			  <th>Sno.</th>
			  <th>Zone Code</th>
			  <th>Zone Name</th>
			  <th>Additional Weight (In K.G.)</th>
			  <th>Additional Rate</th>
			  <th>Sent By</th>
			  <th>Action</th>
			</tr>
		  </thead>
		  <tbody>';
	
		$i=1;
		foreach($rateList as $val){
		
			echo '<tr>
			  <td>'.$i.'</td>
			  <td>'.$val['Zone_Code'].'</td>
			  <td>'.$val['Zone_Name'].'</td>
			  <td>'.$val['Additional_Weight'].'</td>
			  <td>'.$val['Additional_Rate'].'</td>
			  <td>'.$val['Send_By'].'</td>
			  <td>
			  		<a id="viewbtn" class="btn btn-info btn-sm" href="viewrate.php?id='.$val['Rate_Id'].'">View Rate</a>
					<a id="editbtn" class="btn btn-success btn-sm" href="edit_rate.php?id='.$val['Rate_Id'].'"><span class="glyphicon glyphicon-edit"></span> Edit</a>
					
					<button type="button" class="btn btn-danger btn-sm delete" id="'.$val['Rate_Id'].'" name="delete"> <span class="glyphicon glyphicon-trash"></span> Delete </button></td>
			</tr>';
			$i++;
		}//eof foreach loop
				
		'</tbody>
		</table></div>';
	}//eof if condition
	else
	{
		echo'
			
		<div class="clear formbgstyle" style="padding-top:20px;">
		<table class="table table-hover table-bordered" id="addedProducts">
		  <thead>
			<tr class="success">
			  <th>Sno.</th>
			  <th>Zone Code</th>
			  <th>Zone Name</th>
			  <th>Additional Weight (In K.G.)</th>
			  <th>Additional Rate</th>
			  <th>Action</th>
			</tr>
		 
			<tr>
			  <td colspan="6" align="center">No Record Found</td>
			</tr></tbody>
			</table></div>';
	}//eof else
	
}// eof if condition

///*******************************************************
/// Delete row from rate_master and rate_weight table
///*******************************************************
if($_POST['type']=="delete")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	
		 $tblname="tbl_weight_rate_relation";
		 $condition="Rate_Id=".$_POST['id'];
		 $res=$db->deleteRecords($tblname,$condition);

      	if(!$res)
		{
			throw new Exception('a');
		}
		 
		 $tblname="tbl_rates";
		 $condition="Rate_Id=".$_POST['id'];
		 $res=$db->deleteRecords($tblname,$condition);

     	if(!$res)
		{
			throw new Exception('b');
		}

		mysql_query("COMMIT",$con);
		echo 1;
	}
	catch(Exception $e)
	{
		echo  $e->getMessage();
		mysql_query('ROLLBACK',$con);
		mysql_query('SET AUTOCOMMIT=1',$con);
	}
}
?>
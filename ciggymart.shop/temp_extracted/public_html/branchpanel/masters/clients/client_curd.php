<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
require_once(PATH_LIBRARIES.'/classes/resize.php');

$db = new DBConn();

///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="clientNameExist")
{
	$sql="SELECT Client_Name FROM tbl_clients WHERE Client_Name='".$_POST['client_name']."' AND Branch_Id=".$_SESSION['buser'];
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo 1;
    }
	else
	{
		echo 0;
	}

}

///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="SearchclientNameExist")
{
	$sql="SELECT Client_Name FROM tbl_clients WHERE Client_Name='".$_POST['client_name']."' AND Branch_Id=".$_SESSION['buser'];
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo 0;
    }
	else
	{
		echo 1;
	}

}

///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="clientCodeExist")
{
	$sql="SELECT Client_Code FROM tbl_clients WHERE Client_Code='".$_POST['client_code']."' AND Branch_Id=".$_SESSION['buser'];
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo 1;
    }
	else
	{
		echo 0;
	}

}

///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="EditClientCodeExist")
{
	$sql="SELECT Client_Code FROM tbl_clients WHERE Client_Id!=".$_POST['client_id']." AND Client_Code='".$_POST['client_code']."' AND Branch_Id=".$_SESSION['buser'];
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo 1;
    }
	else
	{
		echo 0;
	}

}

///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="clientNameEditExist")
{
	$sql="SELECT Client_Name FROM tbl_clients WHERE Client_Id!=".$_POST['client_id']." AND Client_Name='".$_POST['client_name']."'";
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo 1;
    }
	else
	{
		echo 0;
	}

}

///*******************************************************
/// Get Destination Id and Name
///*******************************************************
if($_POST['type']=="getDestinationName")
{

	$destCode = explode('-',$_POST['dest_code']);
	
	$sql="SELECT Destination_Id, Destination_Name FROM tbl_destinations WHERE Destination_Code='".$destCode[0]."'";
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo "<input type='text' class='form-control input-sm' id='Destination_Name' name='Destination_Name' placeholder='Name' readonly='readonly' value='' />";
    }
	else{
		
		echo "<input type='text' class='form-control input-sm' id='Destination_Name' name='Destination_Name' placeholder='Name' readonly='readonly' value='".$res[1]['Destination_Name']."' /> <input type='hidden' id='dest_id' name='dest_id' value='".$res[1]['Destination_Id']."' />";
		
	}
}

///*******************************************************
/// Update Status Block or Unblock ///////////////////////
///*******************************************************
if($_POST['type']=="updateStatus")
{
	$status=explode('-',$_POST['status']);
	if($status[0]=='Block')
	{
		$sql= mysql_query("UPDATE tbl_clients SET Is_Active=0 WHERE Client_Id=".$status[1]);
		if (!empty($sql))
		{
			echo 1;
		}
	}
	else{
		$sql= mysql_query("UPDATE tbl_clients SET Is_Active=1 WHERE Client_Id=".$status[1]);
		if (!empty($sql))
		{
			echo 2;
		}
	}
}

///*******************************************************
/// To Insert New Client /////////////////////////////////
///*******************************************************
if($_POST['type']=="addClient")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	
		$res=mysql_query("INSERT INTO tbl_clients (Joining_Date, Client_Code, Client_Name, Destination_Id, Address, Billing_Address, Contact_No, GST_Within_State, GSTIN_No, PAN_No, Insurance_Percent, Fuel_Surcharge, Email, Password, Branch_Id, Is_Active)
VALUES(CURDATE(), ".$_POST['client_code'].", '".$_POST['client_name']."', ".$_POST['dest_id'].", '".$_POST['address']."', '".$_POST['billingAdd']."', ".$_POST['contact_no'].", ".$_POST['withinState'].", '".$_POST['gstin']."', '".$_POST{'panNo'}."', '".$_POST['insurance']."', '".$_POST['fuelSurcharge']."', '".$_POST['email']."', '".$_POST['password']."', ".$_SESSION['buser'].",1)");

		if(!$res)
		{
			throw new Exception('0');
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
/// To edit Customer /////////////////////////////////
///*******************************************************
if($_POST['type']=="editClient")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	
		$res=mysql_query("UPDATE tbl_clients SET Client_Code= '".$_POST['client_code']."', Client_Name= '".$_POST['client_name']."', Destination_Id=".$_POST['dest_id'].", Address='".$_POST['address']."', Billing_Address='".$_POST['billingAdd']."', Contact_No='".$_POST['contact_no']."', GST_Within_State=".$_POST['withinState'].", GSTIN_No='".$_POST['gstin']."', PAN_No='".$_POST['panNo']."', Insurance_Percent=".$_POST['insurance'].", Fuel_Surcharge=".$_POST['fuelSurcharge'].", Email='".$_POST['email']."', Password= '".$_POST['password']."' WHERE Client_Id=".$_POST['client_id']);
		
		if(!$res)
		{
			throw new Exception('0');
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
/// Delete row from customer_master table
///*******************************************************

if($_POST['type']=="delete")
{
	 $tblname="tbl_clients";
	 $condition="Client_Id=".$_POST['id'];
	 $res=$db->deleteRecords($tblname,$condition);
	
}

///*******************************************************
/// Search row from tbl_clients table
///*******************************************************
if($_POST['type']=="search")
{ 
	$client=$db->ExecuteQuery("SELECT Client_Id, Client_Code, Client_Name, Destination_Name, Address, Contact_No, Email, Password, CASE WHEN Is_Active=1 THEN 'Block' ELSE 'Unblock' END AS Status FROM tbl_clients C

RIGHT JOIN tbl_destinations D ON D.Destination_Id = C.Destination_Id
WHERE Branch_Id=".$_SESSION['buser']." AND Client_Name='".$_POST['client_name']."'");
?>
<table class="table table-hover table-bordered" id="addedProducts">
     <thead>
      <tr class="success">
             <th>Sno.</th>
             <th>Client Code</th>
             <th>Client/Company Name</th>
             <th>Address</th>
             <th>City</th>
             <th>Contact No</th>
             <th>Email</th>
             <th>Password</th>
             <th>Status</th>
             <th>Action</th>
           </tr>
    </thead>
    <tbody>
	<?php 
    if(count($client) > 0)
    {
        $i=1;
        foreach($client as $val){ ?>
            <tr>
            	 <td ><?php echo $i;?></td>
                 <td><?php echo $val['Client_Code'];?></td>
                 <td><?php echo $val['Client_Name'];?></td>
                 <td><?php echo $val['Address'];?></td>
                 <td><?php echo $val['Destination_Name'];?></td>
                 <td><?php echo $val['Contact_No'];?></td>                     
                 <td><?php echo $val['Email'];?></td>
                 <td><?php echo $val['Password'];?></td>
                 
                 <td><button type="button" id="<?php echo $val['Status']."-".$val['Client_Id']; ?>" class="status btn btn-sm <?php echo (($val['Status']=="Block") ? 'btn-danger':'btn-info')?>"><i class="fa <?php echo (($val['Status']=="Block") ? 'fa-lock':'fa-unlock')?>" aria-hidden="true"></i> <?php echo $val['Status']; ?></button></td>
              
                 <td>
                      <a href="edit_client.php?id=<?php echo $val['Client_Id'];?>" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-edit"></span> Edit</a>
                      <button type="button" class="btn btn-danger btn-sm delete" id="<?php echo $val['Client_Id']; ?>" name="delete"> <span class="glyphicon glyphicon-trash"></span> Delete </button>
                 </td>
             
            </tr>
            <?php $i++;} 
			}
			else
	         { ?>
            <tr>
              <td colspan="10" align="center">No Record Found</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>	
<?php
}
?>
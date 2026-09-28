<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
require_once(PATH_LIBRARIES.'/functions/fun1.php');
$db = new DBConn();


///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="BCExist")
{

	$sql="SELECT Branch_Id FROM tbl_branchs WHERE  Branch_Code='".$_POST['branchCode']."'";
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
if($_POST['type']=="validate2")
{

	$sql="SELECT Branch_Name FROM tbl_branchs WHERE Branch_Name='".$_POST['branch_name']."'";
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
if($_POST['type']=="BCEditExistValid")
{

	$sql="SELECT Branch_Id FROM tbl_branchs WHERE Branch_Id!=".$_POST['branch_id']." AND Branch_Code='".$_POST['branchCode']."'";
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
if($_POST['type']=="emailExist")
{

	$sql="SELECT Email FROM tbl_branchs WHERE Email='".$_POST['email']."'";
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
/// Validate that the data already exist or not for EDIT
///*******************************************************
if($_POST['type']=="emailEditExist")
{

	$sql="SELECT Email FROM tbl_branchs WHERE Branch_Id!=".$_POST['branch_id']." AND Email='".$_POST['email']."'";	
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
	$sql="SELECT Destination_Id, Destination_Name FROM tbl_destinations WHERE Destination_Code='".$_POST['dest_code']."'";
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
		$sql= mysql_query("UPDATE tbl_branchs SET Is_Active=0 WHERE Branch_Id=".$status[1]);
		if (!empty($sql))
		{
			echo 1;
		}
	}
	else{
		$sql= mysql_query("UPDATE tbl_branchs SET Is_Active=1 WHERE Branch_Id=".$status[1]);
		if (!empty($sql))
		{
			echo 2;
		}
	}
}

///*******************************************************
/// To Insert New Branch /////////////////////////////////
///*******************************************************
$path = ROOT."/image_upload/franchise/";
$paththumbimg = ROOT."/image_upload/franchise/thumb/";

if($_POST['type']=="addBranch")
{
	
	// The logo is optional; without this guard an upload-less submit warned on
	// $_FILES and then built a filename from a missing extension.
	$imgname = isset($_FILES['franchise_logo']['name']) ? $_FILES['franchise_logo']['name'] : '';
	$imgextension = explode('.', $imgname);
	$newimgname = (count($imgextension) > 1 && $imgname !== '')
		? time() . '.' . end($imgextension)
		: '';

	$tmp = isset($_FILES['franchise_logo']['tmp_name']) ? $_FILES['franchise_logo']['tmp_name'] : '';
	//move_uploaded_file($tmp, $path.$newimgname);
	
	if($newimgname !== '' && is_uploaded_file($tmp) && move_uploaded_file($tmp, $path.$newimgname))
    {
	  // move the image in the thumb folder
	  $resizeObj1 = new resize($path.$newimgname);
	  $resizeObj1 ->resizeImage(200,200,'auto');
	  $resizeObj1 -> saveImage($paththumbimg.$newimgname, 100);	
    }
	
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	//Insert New Branch
		$pincode   = isset($_POST['pincode']) ? $_POST['pincode'] : '';
		$invStart  = isset($_POST['invoice_start_no']) && $_POST['invoice_start_no'] !== '' ? intval($_POST['invoice_start_no']) : 1;
		// Empty "valid till" means no expiry, stored as NULL.
		$validTill = (!empty($_POST['valid_till']))
			? "'" . date('Y-m-d', strtotime($_POST['valid_till'])) . "'"
			: "NULL";

		$res=mysql_query("INSERT INTO tbl_branchs (Branch_Code, Branch_Name, Franchise_Name, Franchise_Logo, Contact_Person, Address, Destination_Id, GSTIN, Service_Tax_No, PAN_No, Contact_No, Email, Password, Pincode, Invoice_Start_No, Valid_Till, Is_Active)
	values('".$_POST['branch_code']."','".$_POST['branch_name']."','".$_POST['franchise_name']."','".$newimgname."','".$_POST['cont_person']."','".$_POST['address']."',".$_POST['dest_id'].",'".$_POST['gstNo']."','".$_POST['serv_tax_no']."','".$_POST['panNo']."','".$_POST['phone_no']."','".$_POST['email']."','".$_POST['password']."','".$pincode."',".$invStart.",".$validTill.", 1)");
	
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
/// Edit Branch
///*******************************************************
if($_POST['type']=="editBranch")
{
	
	if(isset($_FILES['franchise_logo']['name'])){
		$imgname = $_FILES['franchise_logo']['name'];
	}
	else
	{
		$imgname=NULL;
	}
	
	if(strlen($imgname)>0) 
	{
		$imgextension = explode('.',$imgname);
		$newimgname = time().'.'.$imgextension[1];		
		$tmp = $_FILES['franchise_logo']['tmp_name'];
		
		// move the image in the thumb folder
		move_uploaded_file($tmp, $path.$newimgname);
		$resizeObj1 = new resize($path.$newimgname);
		$resizeObj1 ->resizeImage(200,200,'auto');
		$resizeObj1 -> saveImage($paththumbimg.$newimgname, 200);
		
		unlink($path.$_POST['frpic']);
		unlink($paththumbimg.$_POST['frpic']);
	}
	else{
		$newimgname=$_POST['frpic'];
	}
	
	//Update Branch Details
	$res=mysql_query("UPDATE tbl_branchs SET Branch_Code='".$_POST['branch_code']."', Branch_Name='".$_POST['branch_name']."', Franchise_Name='".$_POST['franchise_name']."', Franchise_Logo='".$newimgname."',Contact_Person='". $_POST['cont_person']."', Address='".$_POST['address']."', Destination_Id=".$_POST['dest_id'].", GSTIN='".$_POST['gstNo']."', Service_Tax_No='".$_POST['serv_tax_no']."', PAN_No='".$_POST['panNo']."', Contact_No='".$_POST['phone_no']."', Email='".$_POST['email']."', Password='".$_POST['password']."' WHERE Branch_Id=".$_POST['branch_id']);
	
	
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
/// Delete row from branch_master table
///*******************************************************
if($_POST['type']=="delete")
{
	 $getLogo=$db->ExecuteQuery("SELECT Franchise_Logo FROM tbl_branchs WHERE Branch_Id=".$_POST['Branch_Id']);
	
	 unlink($path.$getLogo[1]['Franchise_Logo']);
	 unlink($paththumbimg.$getLogo[1]['Franchise_Logo']);
	 
	 $tblname="tbl_branchs";
	 $condition="Branch_Id=".$_POST['Branch_Id'];
	 $res=$db->deleteRecords($tblname,$condition);
}


?>
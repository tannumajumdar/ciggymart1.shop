<?php 
set_time_limit(0);
require_once "DBConn.php";
require_once "queue.php";
class Calculate extends DBConn
{
	
	function total_points($tempid) 
	{
		$userlists = $this->all_left_right($tempid);
		//print_r($userlists);
		$Lmems = $userlists[1];
		$Rmems = $userlists[2];
		//print_r($Rmems);
		if(count($Lmems)>0)
		{
			$part = "";
			foreach($Lmems as $val)
			{
				$part = $part."*".$val."*".",";
				
			}
			//$part=$part."*".$tempid."*,";
			$part=substr($part,0,-1);
					$part=str_replace("*","'",$part);
					$part=substr($part,0,-1);
					$part=substr($part,1,strlen($part));
			
			  //$sql = "SELECT count(userid) as totunit FROM member WHERE referenceid IN('".$part."')";
			//$left = $this->ExecuteQuery($sql) ;
				
			$LUnits = $left[1]['totunit']+1; //Total Units Left
			 $s="select sum(points) as tot from registration_pin left join pin_values on pin_values.id=registration_pin.pinvalueid where consumerId  IN('".$part."')";
			$ob=$this->ExecuteQuery($s);
			$totbuisnessL=$ob[1]['tot'];
			
		}
		else
		{
			$LUnits = 0;
			$totbuisnessL=0;
		}
		
		if(count($Rmems)>0)
		{
			$part = "";
			foreach($Rmems as $val)
			{$part = $part."*".$val."*".",";
				
			}
			
			$part=substr($part,0,-1);
					$part=str_replace("*","'",$part);
					$part=substr($part,0,-1);
					$part=substr($part,1,strlen($part));
			// $sql = "SELECT count(userid) as totunit FROM userlist WHERE referenceid IN('".$part."')";
			//$right = $this->ExecuteQuery($sql);
			
			$RUnits = $right[1]['totunit']+1; //Total Units Left
			
			$s="select sum(points) as tot from registration_pin left join pin_values on pin_values.id=registration_pin.pinvalueid where consumerId  IN('".$part."')";
			$ob=$this->ExecuteQuery($s);
			$totbuisnessR=$ob[1]['tot'];
		}
		else
		{
			$RUnits = 0;
			$totbuisnessR=0;
		}
		$Units[1] = $totbuisnessL;
		$Units[2] = $totbuisnessR;
		return $Units;
		
	}
	
	function units_difference($tempid)
	{
		$totunits = $this->total_units($tempid);
		$ltotunits = $totunits[1];
		$rtotunits = $totunits[2];
		
		$left = $this->ExecuteQuery("SELECT SUM(LUnits) AS totunit FROM binary_income WHERE 
												userid='".$tempid."'");
		if(is_null($left[1]['totunit']))
		{
			$LUsedUnits = 0; //Total Units used in Left
		}
		else
		{
			$LUsedUnits = $left[1]['totunit']; //Total Units Left
		}
		$right = $this->ExecuteQuery("SELECT SUM(RUnits) AS totunit FROM binary_income WHERE 
												userid='".$tempid."'");
		if(is_null($right[1]['totunit']))
		{
			$RUsedUnits = 0; //Total Units Right
		}
		else
		{
			$RUsedUnits = $right[1]['totunit']; //Total Units Right
		}
		$Units[1] = $ltotunits - $LUsedUnits;
		$Units[2] = $rtotunits - $RUsedUnits;
		return $Units;
		
		
	}
	//This fucntion will return the left & right no. of sponsered member
	function count_sponsered($tempid)
	{
		$left = $this->ExecuteQuery("SELECT MAX(levvar) AS maxlev 
										FROM member WHERE sponserid='".$tempid."' AND place='L'");
		if(is_null($left[1]['maxlev']))
		{
			$SLeft = 0;
		}
		else
		{
			$SLeft = $left[1]['maxlev'];
		}
		
		$right = $this->ExecuteQuery("SELECT MAX(levvar) AS maxlev 
										FROM member WHERE sponserid='".$tempid."' AND place='R'");
		if(is_null($right[1]['maxlev']))
		{
			$SRight = 0;
		}
		else
		{
			$SRight = $right[1]['maxlev'];
		}
		
		$Sponsered[1] = $SLeft;
		$Sponsered[2] = $SRight;
		return $Sponsered;
		
	}
	//This function will return two array left & right info about all sponsered members
	function all_sponsered($tempid)
	{
		$left = array();
		$right = array();
		$left = $this->ExecuteQuery("SELECT * 
										FROM member WHERE sponserid='".$tempid."' AND place='L'");
		$right = $this->ExecuteQuery("SELECT * 
										FROM member WHERE sponserid='".$tempid."' AND place='R'");
		$obj[1] = $left;
		$obj[2] = $right;
		return $obj;
			
	}
	function binaryComm($tempid)
	{
		$Units = $this->units_difference($tempid);
		$LUnits = $Units[1];
		$RUnits = $Units[2];
		
		if($LUnits == 0 || $RUnits == 0)
		{
			$IncomeVar = 0;
		}
		else
		{
			$diff = $LUnits - $RUnits;
			if($diff == 0)
			{
				$IncomeVar = ($LUnits - 1) * 1000;
			}
			elseif($diff > 0)
			{
				$IncomeVar = $RUnits * 1000;
			}
			else
			{
				$IncomeVar = $LUnits * 1000;
			}
		}
		return $IncomeVar;
	}
	function deduction($tempid,$bcom)
	{
		$IncomeVar = $bcom;
		$TDS = floor(($IncomeVar/100)*10);
		$ST = floor(($IncomeVar/100)*7);
		
		$obj = $this->ExecuteQuery("SELECT PanNo FROM member WHERE userid='".$tempid."'");
		if($obj[1]['PanNo'] == "")
		{
			$PanDue = floor((($IncomeVar-($TDS + $ST))/100)*10);
		}
		else
		{
			$PanDue = 0;
		}
				
		$ded[1]['TDS'] = $TDS;
		$ded[1]['ST'] = $ST;
		$ded[1]['PanDue'] = $PanDue;
		return $ded;
		
	}
	
	function countleftrightmembers($tempid)
	{
		$members = $this->all_left_right($tempid);
		$Lmems = $members[1];
		$Rmems = $members[2];
		
		$totarr=array("Left=>",0,"Right=>",0);
		$totarr['Left']=count($Lmems);
		$totarr['Right']=count($Rmems);
		
		return $totarr;
			
	}
	function countACTIVELeftRightUser($tempid)
	{
		$members = $this->all_ACTIVE_left_right($tempid);
		$Lmems = $members[1];
		$Rmems = $members[2];
		
		$totarr=array("Left=>",0,"Right=>",0);
		$totarr['Left']=count($Lmems);
		$totarr['Right']=count($Rmems);
		
		return $totarr;
	}
	function all_ACTIVE_left_right($tempid)
	{
		$qu = new Queue();
		$left = array();
		$right = array();
		$i=0;
		$sql = "SELECT * FROM member WHERE refid='".$tempid."' AND place='L' and active=1";
		$rs = $this->ExecuteQuery($sql);
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$qu->insert($usrid);
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L' and active=1";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R' and active=1";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$left[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		$sql = "SELECT * FROM member WHERE refid='".$tempid."' AND place='R' and active=1";
		$rs = $this->ExecuteQuery($sql);
		$i = 0;
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$qu->insert($usrid);
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L' and active=1";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R' and active=1";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$right[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		
		$arr[1] = $left;
		$arr[2] = $right;
		
		return $arr;
	}
	
	function all_left_rightBINRY($tempid,$startdate,$endate)
	{
		$qu = new Queue();
		$left = array();
		$right = array();
		$i=0;
		 $sql = "SELECT * FROM member WHERE refid='".$tempid."' and place='L'";// and activationdate>='".$startdate."' and activationdate<='".$endate."' ";
		
		$rs = $this->ExecuteQuery($sql);
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$qu->insert($usrid);
			$part="'".$usrid."',";
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L'";// and activationdate>='".$startdate."' and activationdate<='".$endate."'";
					//echo $sql."<br/>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
						$part.="'".$rs[1]['userid']."',";
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R'";// and activationdate>='".$startdate."' and activationdate<='".$endate."'";
				//	echo $sql."<br/>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
						$part.="'".$rs[1]['userid']."',";
					}
					$left[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		$sql = "SELECT * FROM member WHERE refid='".$tempid."' AND place='R'";// and activationdate>='".$startdate."' and activationdate<='".$endate."'";
		$rs = $this->ExecuteQuery($sql);
		$i = 0;
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$qu->insert($usrid);
			$part2="'".$usrid."',";
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L'";// and activationdate>='".$startdate."' and activationdate<='".$endate."'";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
						$part2.="'".$rs[1]['userid']."',";
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R'";// and activationdate>='".$startdate."' and activationdate<='".$endate."'";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
						$part2.="'".$rs[1]['userid']."',";
					}
					$right[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		
		$arr[1] = $part;
		$arr[2] = $part2;
		
		return $arr;
	}
	
	function all_left_right($tempid)
	{
		$qu = new Queue();
		$left = array();
		$right = array();
		$i=0;
		$sql = "SELECT * FROM member WHERE refid='".$tempid."' AND place='L'";
		$rs = $this->ExecuteQuery($sql);
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$qu->insert($usrid);
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L'";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R'";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$left[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		$sql = "SELECT * FROM member WHERE refid='".$tempid."' AND place='R'";
		$rs = $this->ExecuteQuery($sql);
		$i = 0;
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$qu->insert($usrid);
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L'";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R'";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
					}
					$right[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		
		$arr[1] = $left;
		$arr[2] = $right;
		
		return $arr;
	}
	
	function allACTIVE_left_right($tempid)
	{
		$qu = new Queue();
		$left = array();
		$right = array();
		$i=0;
		$sql = "SELECT * FROM member WHERE refid='".$tempid."' AND place='L'";
		$rs = $this->ExecuteQuery($sql);
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$part="'".$usrid."',";
			$qu->insert($usrid);
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L' ";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
						$part.="'".$rs[1]['userid']."',";
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R' ";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
						$part.="'".$rs[1]['userid']."',";
					}
					$left[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		$sql = "SELECT * FROM member WHERE refid='".$tempid."' AND place='R' ";
		$rs = $this->ExecuteQuery($sql);
		$i = 0;
		if(count($rs)>0)
		{
			$usrid = $rs[1]['userid'];
			$part2="'".$rs[1]['userid']."',";
			$qu->insert($usrid);
			while(true)
			{
				if(!$qu->isempty())
				{
					$node = $qu->a[$qu->front];
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='L' ";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						$qu->insert($rs[1]['userid']);
						$part2.="'".$rs[1]['userid']."',";
					}
					$sql = "SELECT * FROM member WHERE refid='".$node."' AND place='R' ";
					//echo $sql."<br>";
					$rs = $this->ExecuteQuery($sql);
					if(count($rs)>0)
					{
						
							$qu->insert($rs[1]['userid']);
							$part2.="'".$rs[1]['userid']."',";
					}
					$right[++$i] = $qu->remove();
				}
				else
				{
					break;
				}
			}
		}
		
		$arr[1] = $part;
		$arr[2] = $part2;
		
		return $arr;
	}
	
	//Function for counting the no of payout to specified user id
	
	function count_payout($tempid)
	{
		$obj = $this->ExecuteQuery("SELECT COUNT(*) AS tot_no_payout 
								 	FROM binary_income WHERE userid='".$tempid."'");
		$tot_no_payout = $obj[1]['tot_no_payout'] + 1;
		return $tot_no_payout;
	}
	function tot_old_units($tempid,$cid)
	{
		$left = $this->ExecuteQuery("SELECT SUM(LUnits) AS totunit FROM binary_income WHERE 
												userid='".$tempid."' AND cid<>$cid");
		if(is_null($left[1]['totunit']))
		{
			$LUsedUnits = 0; //Total Units used in Left
		}
		else
		{
			$LUsedUnits = $left[1]['totunit']; //Total Units Left
		}
		$right = $this->ExecuteQuery("SELECT SUM(RUnits) AS totunit FROM binary_income WHERE 
												userid='".$tempid."'  AND cid<>$cid");
		if(is_null($right[1]['totunit']))
		{
			$RUsedUnits = 0; //Total Units Right
		}
		else
		{
			$RUsedUnits = $right[1]['totunit']; //Total Units Right
		}
		$Units[1] = $LUsedUnits;
		$Units[2] = $RUsedUnits;
		return $Units;
		
	}
	function tot_new_units($tempid,$cid)
	{
		
		$totunits = $this->tot_old_units($tempid,$cid);
		$loldunits = $totunits[1];
		$roldunits = $totunits[2];
		
		$totunits = $this->total_units($tempid);
		$ltotunits = $totunits[1];
		$rtotunits = $totunits[2];
				
		$Units[1] = $ltotunits - $loldunits;
		$Units[2] = $rtotunits - $roldunits;
		return $Units;
		
		
	}
	function member_details()
	{
		$regmem_1 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_regular 
										FROM member WHERE PinType=1");
		$regmem_2 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_classic 
										FROM member WHERE PinType=2");
		$regmem_3 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_premium 
										FROM member WHERE PinType=3");
		
		$regmem[1]['REGULAR'] = $regmem_1[1]['tot_regular'];
		$regmem[1]['CLASSIC'] = $regmem_2[1]['tot_classic'];
		$regmem[1]['PREMIUM'] = $regmem_3[1]['tot_premium'];
		
		$joimem_1 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_regular 
										FROM member WHERE PinType=1 AND ValidStatus=3 ");
		$joimem_2 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_classic 
										FROM member WHERE PinType=2 AND ValidStatus=3 ");
		$joimem_3 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_premium 
										FROM member WHERE PinType=3 AND ValidStatus=3 ");
		
		$joimem[1]['REGULAR'] = $joimem_1[1]['tot_regular'];
		$joimem[1]['CLASSIC'] = $joimem_2[1]['tot_classic'];
		$joimem[1]['PREMIUM'] = $joimem_3[1]['tot_premium'];
		
		$mem[1]['regmem'] = $regmem;
		$mem[1]['joimem'] = $joimem;
		
		return $mem;
		
	}
	function pin_requests_details()
	{
		$issued_1 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_regular FROM pin_requests  
				WHERE ApprStatus=2 AND PinType=1"); 
		
		$issued_2 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_classic FROM pin_requests 
				WHERE ApprStatus=2 AND PinType=2"); 
		
		$issued_3 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_premium FROM pin_requests  
				WHERE ApprStatus=2 AND PinType=3"); 
		$issued[1]['REGULAR'] = $issued_1[1]['tot_regular'];
		$issued[1]['CLASSIC'] = $issued_2[1]['tot_classic'];
		$issued[1]['PREMIUM'] = $issued_3[1]['tot_premium'];
		
		$new_1 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_new_regular FROM pin_requests 
									WHERE ApprStatus=1 AND PinType=1");
		$new_2 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_new_classic FROM pin_requests 
									WHERE ApprStatus=1 AND PinType=2");
		$new_3 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_new_premium FROM pin_requests 
									WHERE ApprStatus=1 AND PinType=3");
		$new[1]['REGULAR'] = $new_1[1]['tot_new_regular'];
		$new[1]['CLASSIC'] = $new_2[1]['tot_new_classic'];
		$new[1]['PREMIUM'] = $new_3[1]['tot_new_premium'];
		
		$used_1 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_used_regular FROM pin_requests  
				WHERE ApprStatus=2 AND PinType=1 AND UsedStatus=2"); 
		
		$used_2 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_used_classic FROM pin_requests 
				WHERE ApprStatus=2 AND PinType=2 AND UsedStatus=2"); 
		
		$used_3 = $this->ExecuteQuery("SELECT COUNT(*) AS tot_used_premium FROM pin_requests  
				WHERE ApprStatus=2 AND PinType=3 AND UsedStatus=2"); 
		
		$used[1]['REGULAR'] = $used_1[1]['tot_used_regular'];
		$used[1]['CLASSIC'] = $used_2[1]['tot_used_classic'];
		$used[1]['PREMIUM'] = $used_3[1]['tot_used_premium'];
		
		$notused[1]['REGULAR'] = $issued[1]['REGULAR'] - $used_1[1]['tot_used_regular'];
		$notused[1]['CLASSIC'] = $issued[1]['CLASSIC'] - $used_2[1]['tot_used_classic'];
		$notused[1]['PREMIUM'] = $issued[1]['PREMIUM'] - $used_3[1]['tot_used_premium'];
		
		$details[1]['issued'] = $issued;
		$details[1]['used'] = $used;
		$details[1]['notused'] = $notused;
		$details[1]['new'] = $new;
		
		
		
		return $details;
		
	}
	function tot_debit($tempid)
	{
		$rs1 = $this->ExecuteQuery("SELECT SUM(Amt) AS tot_credit FROM credit WHERE userid='".$tempid."'");
		$tot_credit = 0;
		if(is_null($rs1[1]['tot_credit']))
		{
			$tot_credit = 0;
		}
		else
		{
			$tot_credit = $rs1[1]['tot_credit'];
		}
		
		$rs2 = $this->ExecuteQuery("SELECT SUM(Per10Thousand) AS tot_debit FROM binary_income WHERE userid='".$tempid."'");
		$tot_debit1 = 0;
		if(is_null($rs2[1]['tot_debit']))
		{
			$tot_debit1 = 0;
		}
		else
		{
			$tot_debit1 = $rs2[1]['tot_debit'];
		}
		$rs3 = $this->ExecuteQuery("SELECT SUM(Per10Thousand) AS tot_debit FROM monthly_income WHERE userid='".$tempid."'");
		$tot_debit2 = 0;
		if(is_null($rs3[1]['tot_debit']))
		{
			$tot_debit2 = 0;
		}
		else
		{
			$tot_debit2 = $rs3[1]['tot_debit'];
		}
		$tot_debit = ($tot_debit1 + $tot_debit2) - $tot_credit;
		
		return $tot_debit;
		
	}
	//Count no of payout for monthly growth income
	function count_payout_monthly($tempid)
	{
		$obj = $this->ExecuteQuery("SELECT COUNT(*) AS tot_no_payout 
								 	FROM monthly_income WHERE userid='".$tempid."'");
		$tot_no_payout = $obj[1]['tot_no_payout'] + 1;
		return $tot_no_payout;
	}
	
	function tot_fund_debited($tempid)
	{
		$mon_deb=0; //Total Debit Amount from Monthly payout
		$bin_deb=0; //Total Debit Amount from Binary payout
		$received_fund = 0; //Total Debit Amount from fund received
		$obj1 = $this->ExecuteQuery("SELECT SUM(`AmtPaid`) AS tot_deb 
											   FROM monthly_income 
											   WHERE userid='".$tempid."' AND PayoutType=2");
		if(!is_null($obj1[1]['tot_deb']))
		{
			$mon_deb = $obj1[1]['tot_deb'];
		}
		$obj2 = $this->ExecuteQuery("SELECT SUM(`AmtPaid`) AS tot_deb 
											   FROM binary_income 
											   WHERE userid='".$tempid."' AND PayoutType=2");
		if(!is_null($obj2[1]['tot_deb']))
		{
			$bin_deb = $obj2[1]['tot_deb'];
		}
		
		$obj3 = $this->ExecuteQuery("SELECT SUM(`Amt`) AS tot_deb 
											   FROM fund_transfer 
											   WHERE ToUserId='".$tempid."'");
		if(!is_null($obj3[1]['tot_deb']))
		{
			$received_fund = $obj3[1]['tot_deb'];
		}
		return $mon_deb + $bin_deb + $received_fund;
	}
	function tot_fund_credited($tempid)
	{
		$fund_cre=0; //Total credit Amount from fund transfer
		$pin_cre=0; //Total credit Amount from pin purchasing
		$obj1 = $this->ExecuteQuery("SELECT SUM(`Amt`) AS tot_cre 
											   FROM fund_transfer 
											   WHERE FromUserId='".$tempid."'");
		if(!is_null($obj1[1]['tot_cre']))
		{
			$fund_cre = $obj1[1]['tot_cre'];
		}
		$obj2 = $this->ExecuteQuery("SELECT PinNo 
									FROM pin_using_fund 
									WHERE userid='".$tempid."'");
		if(count($obj2>0))
		{
			foreach($obj2 as $val)
			{
				$rs = $this->ExecuteQuery("SELECT PinType 
										  FROM pin_requests 
										  WHERE PinNo='".$val['PinNo']."'");
				$PinType = $rs[1]['PinType'];
				if($PinType == 1)
				{
					$pin_cre += 3000;
				}
				else if($PinType == 2)
				{
					$pin_cre += 5000;
				}
				else
				{
					$pin_cre += 10000;
				}
			}
		}
		
		return $fund_cre + $pin_cre;
		
	}
	function net_fund_balance($tempid)
	{
		$tot_deb = $this->tot_fund_debited($tempid);
		$tot_cre = $this->tot_fund_credited($tempid);
		//echo $tot_deb."=====";
		//echo $tot_cre;
		return $tot_deb-$tot_cre;
	}
	
	function total_Left_right_ampunt($spid)
	{
		$obj=$this->all_sponsered($spid);
		
		foreach($obj[1] as $valleft)
						  {
							 
							  
							  $s="select planPrice from member_plans where memberId='". $valleft['userid']."'"	;
							  $obj2 = $this->ExecuteQuery($s);
							  if(count($obj2)!=0)
							  { 
								  $totleft=$totleft+$obj2[1]['planPrice'];
								 // echo "<br/>";
								  //echo $valleft['userid']."-".$totleft;
							  }	
						  }
						  foreach($obj[2] as $valright)
						  {
							 
							  
							  $s="select planPrice from member_plans where memberId='". $valright['userid']."'"	;
							  $obj3 = $this->ExecuteQuery($s);
							  if(count($obj3)!=0)
							  {
								   
								  $totright=$totright+	$obj3[1]['planPrice'];
								   // echo "<br/>";
								  //echo $valright['userid']."-".$totright;
							  }	
						  }
						  
		$objLR[1]=$totleft;
		$objLR[2]=$totright;
		return 	$objLR;	
						  
	}
	
	function temptableinsertion($spid,$todate,$fromdate)
	{
			$l=1;
			$levequery="SELECT `Percent`,`Source` FROM `commision_percentage` WHERE `Source`!='MIS' order by `Source` asc";
			$obj_level = $this->ExecuteQuery($levequery);
			$per_array=array();
			$p=1;
			foreach ($obj_level as $percent)
			{
				$per_array[$p]=	 $percent['Percent'];
				$p++;
			}
			//print_r($per_array);	
		$sql="select count(uid) as tot from member";
		$sqtot = $this->ExecuteQuery($sql);	
		if(count($sqtot)!=0)
		{
			$totlrow=$sqtot[1]['tot'];
		  while($totlrow>0)
		  {
				$query="select sponserid  from member where userid='".$spid."'" ;
				$objspn = $this->ExecuteQuery($query);	
				if(count($objspn)!=0)
				{
					$spid=$objspn[1]['sponserid'];
					if($spid!=$_SESSION['memberid'])
					{
						$obj=$this->total_Left_right_ampunt($spid);
						 if( $obj[1]>=50000 &&  $obj[1]<=5000000 && $obj[2]>=50000 && $obj[2]<=5000000)
						 {
								if($l>9)
								{
									break;	
								}
								else
								{
									
										
											$amt=(5000*($per_array[$l]))/100;
										
										
											
									
							    $tblfield1=array('id','Sponser_id','Level','target_amount','To_date','From_date','Paid','Amount','	Percentage');
										$tblvalues1=array('',$spid,$l,5000,$todate,$fromdate,0,$amt,$per_array[$l]);
							    $this->valInsert('level_commission_detail',$tblfield1,$tblvalues1);
								}
							  $l++;
						 }
						 
						
					}
					else
						 {
							//echo "No body  purchased the plan"; 
							break;	 
						}
				}
				 $totlrow--;
			}
		}
	}
}

?>
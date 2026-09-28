<?php 
require_once("DBConn.php");

class rate extends DBConn{
	
	///*******************************************************
	/// Get Maximum Weight //////////////////////////////////
	///*******************************************************
	function getMaxWeight($clientId, $destId, $sendby, $branchId){
	
		// Get Branch Destination
		$getBranchDest=$this->ExecuteQuery("SELECT Destination_Id FROM tbl_branchs WHERE Branch_Id=".$branchId);
		// Get Branch State
		$getBranchState = $this->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$getBranchDest[1]['Destination_Id']);		
		// Get User Input Destination State 
		// To Compare with the Branch State
		$getInputState=$this->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$destId);
		
		// check if the input destination id is 
		// coming under the "within state
		if($getBranchState[1]['State_Id'] == $getInputState[1]['State_Id']){
			
			// check if the input destination id is 
			// coming under the "within city
			if($getBranchDest[1]['Destination_Id'] == $destId){
				
				$maxweight=$this->ExecuteQuery("SELECT MAX(Weight_To) AS MaxWeight, MAX(Amount) AS Amount FROM tbl_weight_rate_relation WHERE Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=1 AND Send_By=".$sendby.")");//Zone_Id=34 is for within city
				
			}
			else{
				$maxweight=$this->ExecuteQuery("SELECT MAX(Weight_To) AS MaxWeight, MAX(Amount) AS Amount FROM tbl_weight_rate_relation WHERE Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=2 AND Send_By=".$sendby.")");//Zone_Id=34 is for within state
			}
			
		}
		else{
			$maxweight=$this->ExecuteQuery("SELECT MAX(Weight_To) AS MaxWeight, MAX(Amount) AS Amount FROM tbl_weight_rate_relation WHERE Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=(SELECT Zone_Id FROM tbl_states WHERE State_Id=(SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$destId.")) AND Send_By=".$sendby.")");
		}
		
		//echo $maxweight[1]['MaxWeight']."-".$maxweight[1]['Amount'];
		return $maxweight[1]['MaxWeight']."-".$maxweight[1]['Amount'];
		
	}
	
	///*******************************************************
	/// Get Subtotal //////////////////////////////////
	///*******************************************************	
	function getSubtotal($branchId, $clientId, $destId, $sendby, $weight){

		$maxWeight = $this->getMaxWeight($clientId, $destId, $sendby, $branchId);
		
		$maxWeightRate = explode('-', $maxWeight);
		// getMaxWeight() returns "" when no slab is configured for this client /
		// service combination; PHP 8 then throws on the arithmetic below.
		$maxSlabWeight = isset($maxWeightRate[0]) ? floatval($maxWeightRate[0]) : 0.0;
		$maxSlabRate   = isset($maxWeightRate[1]) ? floatval($maxWeightRate[1]) : 0.0;
		
		// Check if the input weight is greater than 
		// weight from DB
		if($maxSlabWeight < $weight){
						
			$remainingWeight = $weight - $maxSlabWeight;
			
			// Get Branch Destination
			//$getBranchDest=$this->ExecuteQuery("SELECT Destination_Id FROM tbl_branchs WHERE Branch_Id=".$_SESSION['buser']);
			$getBranchDest=$this->ExecuteQuery("SELECT Destination_Id FROM tbl_branchs WHERE Branch_Id=".$branchId);
			// Get Branch State
			$getBranchState = $this->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$getBranchDest[1]['Destination_Id']);		
			// Get User Input Destination State 
			// To Compare with the Branch State
			$getInputState=$this->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$destId);
			
			// check if the input destination id is 
			// coming under the "within state
			if($getBranchState[1]['State_Id'] == $getInputState[1]['State_Id']){
				
				// check if the input destination id is 
				// coming under the "within city
				if($getBranchDest[1]['Destination_Id'] == $destId){
					$res=$this->ExecuteQuery("SELECT Additional_Weight, Additional_Rate FROM tbl_rates WHERE Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=1 AND Send_By=".$sendby.")");
				}
				else{
					$res=$this->ExecuteQuery("SELECT Additional_Weight, Additional_Rate FROM tbl_rates WHERE Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=2 AND Send_By=".$sendby.")");
				}
				
			}
			else{
				$res=$this->ExecuteQuery("SELECT Additional_Weight, Additional_Rate FROM tbl_rates WHERE Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=(SELECT Zone_Id FROM tbl_states WHERE State_Id=(SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$destId.")) AND Send_By=".$sendby.")");	
			}
						
			
			// No rate row for this client / zone / service yet: fall back to the
			// slab rate instead of dividing by a missing Additional_Weight.
			$addWeight = (!empty($res) && isset($res[1]['Additional_Weight'])) ? floatval($res[1]['Additional_Weight']) : 0.0;
			$addRate   = (!empty($res) && isset($res[1]['Additional_Rate']))   ? floatval($res[1]['Additional_Rate'])   : 0.0;

			if($addWeight <= 0){
				$totalAmt = $maxSlabRate;
			}
			else if($remainingWeight < $addWeight){
				$totalAmt = $maxSlabRate + $addRate;
			}
			else{
				$totalWeight = ceil($remainingWeight / $addWeight);
				$totalAmt = $maxSlabRate + ($addRate * $totalWeight);
			}
			
			
			
			echo sprintf("%0.2f",$totalAmt);
			
		}
		else{
			
			// Get Branch Destination
			// Use the $branchId argument, not $_SESSION['buser']: the admin panel
			// has no 'buser' key, which made this query fail there.
			$getBranchDest=$this->ExecuteQuery("SELECT Destination_Id FROM tbl_branchs WHERE Branch_Id=".intval($branchId));
			// Get Branch State
			$getBranchState = $this->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$getBranchDest[1]['Destination_Id']);		
			// Get User Input Destination State 
			// To Compare with the Branch State
			$getInputState=$this->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$destId);
			
			// check if the input destination id is 
			// coming under the "within state
			if($getBranchState[1]['State_Id'] == $getInputState[1]['State_Id']){
				
				// check if the input destination id is 
				// coming under the "within city
				if($getBranchDest[1]['Destination_Id'] == $destId){
					$res=$this->ExecuteQuery("SELECT Amount FROM tbl_weight_rate_relation WHERE Weight_From <= ".$weight." AND Weight_To >= ".$weight." AND Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=1 AND Send_By=".$sendby.")");
				}
				else{
					$res=$this->ExecuteQuery("SELECT Amount FROM tbl_weight_rate_relation WHERE Weight_From <= ".$weight." AND Weight_To >= ".$weight." AND Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=2 AND Send_By=".$sendby.")");
				}
			}//eof if condition
			else{
				$res=$this->ExecuteQuery("SELECT Amount FROM tbl_weight_rate_relation WHERE Weight_From <= ".$weight." AND Weight_To >= ".$weight." AND Rate_Id=(SELECT Rate_Id FROM tbl_rates WHERE Client_Id=".$clientId." AND Zone_Id=(SELECT Zone_Id FROM tbl_states WHERE State_Id=(SELECT State_Id FROM tbl_destinations WHERE Destination_Id=".$destId.")) AND Send_By=".$sendby.")");
			}
			
			if(count($res)!=0){
				echo $res[1]['Amount'];
			}
			else{
				echo 0;
			}
			
		}
		
	}
	
}
?>
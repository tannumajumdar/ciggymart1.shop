<?php
/**
 * Rate Calculator Logic
 * Handles automatic freight calculation based on Rate Master rules.
 */

class RateCalculator {
    private $db;

    public function __construct($dbConn) {
        $this->db = $dbConn;
    }

    /**
     * Calculates the freight amount for a given consignment.
     * 
     * @param int $customerId 
     * @param int $zoneId Destination Zone ID
     * @param float $actualWeight Actual dead weight in KG
     * @param float $length cm
     * @param float $width cm
     * @param float $height cm
     * @return array Calculated charges
     */
    public function calculateFreight($customerId, $zoneId, $actualWeight, $length = 0, $width = 0, $height = 0) {
        // 1. Fetch Rate Master Rules for this Customer and Zone
        $sql = "SELECT * FROM tbl_rate_master 
                WHERE Customer_Id = " . intval($customerId) . " 
                AND Zone_Id = " . intval($zoneId) . " 
                AND (Effective_To IS NULL OR Effective_To >= CURDATE())
                ORDER BY Effective_From DESC LIMIT 1";
        
        $rateRule = $this->db->ExecuteQuery($sql);
        
        if(empty($rateRule)) {
            return array('error' => 'No rate defined for this Zone and Customer.');
        }
        
        $rule = $rateRule[1];
        
        // 2. Calculate Volumetric Weight
        $volumetricDivisor = !empty($rule['Volumetric_Divisor']) ? $rule['Volumetric_Divisor'] : 5000;
        $volumetricWeight = 0;
        
        if ($length > 0 && $width > 0 && $height > 0) {
            $volumetricWeight = ($length * $width * $height) / $volumetricDivisor;
        }
        
        // 3. Chargeable Weight (Max of Actual vs Volumetric)
        $chargeableWeight = max($actualWeight, $volumetricWeight);
        
        // 4. Calculate Base and Additional Freight
        $freight = 0;
        $baseWeight = floatval($rule['Base_Weight']);
        $baseRate = floatval($rule['Base_Rate']);
        $additionalWeight = floatval($rule['Additional_Weight']);
        $additionalRate = floatval($rule['Additional_Rate']);
        
        if ($chargeableWeight <= $baseWeight) {
            $freight = $baseRate;
        } else {
            $freight = $baseRate;
            $extraWeight = $chargeableWeight - $baseWeight;
            
            // Calculate how many additional slabs apply (rounded up)
            $slabs = ceil($extraWeight / $additionalWeight);
            $freight += ($slabs * $additionalRate);
        }
        
        // 5. Apply Surcharges
        $fuelSurchargeAmt = ($freight * floatval($rule['Fuel_Surcharge_Percent'])) / 100;
        $odaChargeAmt = floatval($rule['ODA_Charge']);
        $fovAmt = ($freight * floatval($rule['FOV_Percent'])) / 100;
        
        $subtotal = $freight + $fuelSurchargeAmt + $odaChargeAmt + $fovAmt;
        
        return array(
            'chargeable_weight' => $chargeableWeight,
            'freight' => round($freight, 2),
            'fuel_surcharge' => round($fuelSurchargeAmt, 2),
            'oda_charge' => round($odaChargeAmt, 2),
            'fov_charge' => round($fovAmt, 2),
            'subtotal' => round($subtotal, 2)
        );
    }
}
?>

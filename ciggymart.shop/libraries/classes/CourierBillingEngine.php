<?php
require_once(dirname(__FILE__) . '/DBConn.php');

class CourierBillingEngine {

    /** Per-branch charge setup rows, looked up once each. */
    private $chargeSetupCache;
    private $db;
    private $settingsCache = null;

    public function __construct($db = null) {
        $this->db = $db ? $db : new DBConn();
    }

    /**
     * Get system setting value by key
     */
    public function getSetting($key, $default = '') {
        if ($this->settingsCache === null) {
            $this->settingsCache = [];
            $res = $this->db->ExecuteQuery("SELECT Setting_Key, Setting_Value FROM tbl_settings");
            if (!empty($res)) {
                foreach ($res as $row) {
                    $this->settingsCache[$row['Setting_Key']] = $row['Setting_Value'];
                }
            }
        }
        return isset($this->settingsCache[$key]) ? $this->settingsCache[$key] : $default;
    }

    /**
     * Calculate Volumetric Weight and Chargeable Weight
     */
    public function calculateWeight($actualWeight, $length = 0, $width = 0, $height = 0, $divisor = null) {
        $actualWeight = floatval($actualWeight);
        $length = floatval($length);
        $width = floatval($width);
        $height = floatval($height);

        if ($divisor === null || floatval($divisor) <= 0) {
            $divisor = floatval($this->getSetting('volumetric_divisor', 5000));
            if ($divisor <= 0) $divisor = 5000;
        }

        $volumetricWeight = 0;
        if ($length > 0 && $width > 0 && $height > 0) {
            $volumetricWeight = round(($length * $width * $height) / $divisor, 3);
        }

        $chargeableWeight = max($actualWeight, $volumetricWeight);
        if ($chargeableWeight <= 0) $chargeableWeight = $actualWeight;

        return [
            'actual_weight' => $actualWeight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'divisor' => $divisor,
            'volumetric_weight' => $volumetricWeight,
            'chargeable_weight' => $chargeableWeight
        ];
    }

    /**
     * Check if destination or pincode is ODA and get ODA charge
     */
    public function checkODA($destinationId = 0, $destinationName = '', $pincode = '') {
        $isODA = false;
        $odaCharge = 0.0;
        $defaultODA = floatval($this->getSetting('default_oda_charge', 150.00));

        // 1. Check by Pincode in ODA Master
        if (!empty($pincode)) {
            $safePin = $this->db->escape(trim($pincode));
            $res = $this->db->ExecuteQuery("SELECT ODA_Charge, Is_ODA FROM tbl_oda_master WHERE Pincode='$safePin' AND Is_Active=1");
            if (!empty($res) && isset($res[1])) {
                $isODA = ($res[1]['Is_ODA'] == 1);
                $odaCharge = floatval($res[1]['ODA_Charge'] > 0 ? $res[1]['ODA_Charge'] : $defaultODA);
                return ['is_oda' => $isODA, 'oda_charge' => ($isODA ? $odaCharge : 0.0)];
            }
        }

        // 2. Check by Destination Name in ODA Master
        if (!empty($destinationName)) {
            $safeName = $this->db->escape(trim($destinationName));
            $res = $this->db->ExecuteQuery("SELECT ODA_Charge, Is_ODA FROM tbl_oda_master WHERE City LIKE '%$safeName%' AND Is_Active=1");
            if (!empty($res) && isset($res[1])) {
                $isODA = ($res[1]['Is_ODA'] == 1);
                $odaCharge = floatval($res[1]['ODA_Charge'] > 0 ? $res[1]['ODA_Charge'] : $defaultODA);
                return ['is_oda' => $isODA, 'oda_charge' => ($isODA ? $odaCharge : 0.0)];
            }
        }

        // 3. Check Destination table
        if (!empty($destinationId)) {
            $res = $this->db->ExecuteQuery("SELECT Is_ODA, ODA_Charge FROM tbl_destinations WHERE Destination_Id=".intval($destinationId));
            if (!empty($res) && isset($res[1]) && $res[1]['Is_ODA'] == 1) {
                $isODA = true;
                $odaCharge = floatval($res[1]['ODA_Charge'] > 0 ? $res[1]['ODA_Charge'] : $defaultODA);
                return ['is_oda' => $isODA, 'oda_charge' => $odaCharge];
            }
        }

        return ['is_oda' => false, 'oda_charge' => 0.0];
    }

    /**
     * Get applicable Pickup charge
     */
    public function getPickupCharge($clientId = 0, $zoneId = 0) {
        $defaultPickup = floatval($this->getSetting('default_pickup_charge', 0.00));
        if ($clientId > 0) {
            // Zone-wise rate wins when one is configured for this client+zone.
            if ($zoneId > 0) {
                $zoneRow = $this->db->ExecuteQuery("SELECT Default_Charge FROM tbl_pickup_charges
                    WHERE Client_Id=".intval($clientId)." AND Zone_Id=".intval($zoneId)." AND Is_Active=1");
                if (!empty($zoneRow) && isset($zoneRow[1]['Default_Charge'])) {
                    return floatval($zoneRow[1]['Default_Charge']);
                }
            }
            $res = $this->db->ExecuteQuery("SELECT Pickup_Charge FROM tbl_clients WHERE Client_Id=".intval($clientId));
            if (!empty($res) && isset($res[1]['Pickup_Charge']) && floatval($res[1]['Pickup_Charge']) > 0) {
                return floatval($res[1]['Pickup_Charge']);
            }
            // Client-wide fallback row (Zone_Id NULL / 0).
            $res2 = $this->db->ExecuteQuery("SELECT Default_Charge FROM tbl_pickup_charges
                WHERE Client_Id=".intval($clientId)." AND (Zone_Id IS NULL OR Zone_Id=0) AND Is_Active=1");
            if (!empty($res2) && isset($res2[1]['Default_Charge'])) {
                return floatval($res2[1]['Default_Charge']);
            }
        }
        return $defaultPickup;
    }

    /**
     * Zone of a destination city, used for zone-wise pickup rates.
     */
    public function getDestinationZone($destId = 0) {
        if ($destId <= 0) {
            return 0;
        }
        $res = $this->db->ExecuteQuery("SELECT S.Zone_Id FROM tbl_destinations D
            INNER JOIN tbl_states S ON S.State_Id = D.State_Id
            WHERE D.Destination_Id=".intval($destId));
        return (!empty($res) && isset($res[1]['Zone_Id'])) ? intval($res[1]['Zone_Id']) : 0;
    }

    /**
     * Rate-master charge setup for a company (fuel / insurance / FOV / urgent).
     * Returns an empty array when nothing is configured, so callers fall back
     * to the global settings.
     */
    public function getChargeSetup($branchId = 0) {
        if ($branchId <= 0) {
            return array();
        }
        if (!isset($this->chargeSetupCache)) {
            $this->chargeSetupCache = array();
        }
        if (isset($this->chargeSetupCache[$branchId])) {
            return $this->chargeSetupCache[$branchId];
        }
        $res = $this->db->ExecuteQuery("SELECT * FROM tbl_charge_master WHERE Branch_Id=".intval($branchId)." AND Is_Active=1");
        $row = (!empty($res) && isset($res[1])) ? $res[1] : array();
        $this->chargeSetupCache[$branchId] = $row;
        return $row;
    }

    /**
     * Urgent / express surcharge, applied when Send_By is 3 (Urgent).
     */
    public function calculateUrgent($freight, $branchId = 0) {
        $setup = $this->getChargeSetup($branchId);
        $pct = isset($setup['Urgent_Percent']) ? floatval($setup['Urgent_Percent']) : 0.0;
        $min = isset($setup['Urgent_Minimum']) ? floatval($setup['Urgent_Minimum']) : 0.0;
        if ($pct <= 0 && $min <= 0) {
            return 0.0;
        }
        return round(max((floatval($freight) * $pct) / 100, $min), 2);
    }

    /**
     * FOV (carrier risk / freight-on-value): a percentage of the declared
     * value, subject to a minimum. Zero when nothing is declared.
     */
    public function calculateFOV($declaredValue = 0, $percent = null, $minimum = null, $branchId = 0) {
        $declaredValue = floatval($declaredValue);
        if ($declaredValue <= 0) {
            return ['fov_percent' => 0.0, 'fov_minimum' => 0.0, 'fov_charge' => 0.0];
        }
        $setup = $this->getChargeSetup($branchId);
        if ($percent === null && isset($setup['FOV_Percent']) && floatval($setup['FOV_Percent']) > 0) {
            $percent = $setup['FOV_Percent'];
        }
        if ($minimum === null && isset($setup['FOV_Minimum'])) {
            $minimum = $setup['FOV_Minimum'];
        }
        $pct = ($percent === null) ? floatval($this->getSetting('default_fov_percent', 0.20)) : floatval($percent);
        $min = ($minimum === null) ? floatval($this->getSetting('default_fov_minimum', 100.00)) : floatval($minimum);
        $calc = ($declaredValue * $pct) / 100;
        return [
            'fov_percent' => $pct,
            'fov_minimum' => $min,
            'fov_charge'  => round(max($calc, $min), 2)
        ];
    }

    /**
     * Fuel surcharge percentage: the client's own rate, else the global default.
     */
    public function getFuelPercent($clientId = 0, $branchId = 0) {
        $setup = $this->getChargeSetup($branchId);
        if (isset($setup['Fuel_Percent']) && floatval($setup['Fuel_Percent']) > 0) {
            return floatval($setup['Fuel_Percent']);
        }
        if ($clientId > 0) {
            $res = $this->db->ExecuteQuery("SELECT Fuel_Surcharge FROM tbl_clients WHERE Client_Id=".intval($clientId));
            if (!empty($res) && isset($res[1]['Fuel_Surcharge']) && floatval($res[1]['Fuel_Surcharge']) > 0) {
                return floatval($res[1]['Fuel_Surcharge']);
            }
        }
        return floatval($this->getSetting('default_fuel_percent', 0.00));
    }

    /**
     * Whether the client is billed within the branch's state (CGST+SGST) or
     * across states (IGST).
     */
    public function isWithinState($clientId = 0) {
        if ($clientId > 0) {
            $res = $this->db->ExecuteQuery("SELECT GST_Within_State FROM tbl_clients WHERE Client_Id=".intval($clientId));
            if (!empty($res) && isset($res[1]['GST_Within_State'])) {
                return intval($res[1]['GST_Within_State']) == 1;
            }
        }
        return true;
    }

    /**
     * Get applicable Door Delivery charge
     */
    public function getDoorDeliveryCharge($destId = 0, $clientId = 0) {
        $defaultDoor = floatval($this->getSetting('default_door_delivery_charge', 0.00));
        if ($destId > 0) {
            $res = $this->db->ExecuteQuery("SELECT Door_Delivery_Charge FROM tbl_destinations WHERE Destination_Id=".intval($destId));
            if (!empty($res) && isset($res[1]['Door_Delivery_Charge']) && floatval($res[1]['Door_Delivery_Charge']) > 0) {
                return floatval($res[1]['Door_Delivery_Charge']);
            }
            $res2 = $this->db->ExecuteQuery("SELECT Door_Charge FROM tbl_door_delivery_charges WHERE Destination_Id=".intval($destId)." AND Is_Active=1");
            if (!empty($res2) && isset($res2[1]['Door_Charge'])) {
                return floatval($res2[1]['Door_Charge']);
            }
        }
        if ($clientId > 0) {
            $res3 = $this->db->ExecuteQuery("SELECT Door_Delivery_Charge FROM tbl_clients WHERE Client_Id=".intval($clientId));
            if (!empty($res3) && isset($res3[1]['Door_Delivery_Charge']) && floatval($res3[1]['Door_Delivery_Charge']) > 0) {
                return floatval($res3[1]['Door_Delivery_Charge']);
            }
        }
        return $defaultDoor;
    }

    /**
     * Get applicable Docket charge
     */
    public function getDocketCharge($clientId = 0) {
        $defaultDocket = floatval($this->getSetting('default_docket_charge', 50.00));
        if ($clientId > 0) {
            $res = $this->db->ExecuteQuery("SELECT Docket_Charge FROM tbl_clients WHERE Client_Id=".intval($clientId));
            if (!empty($res) && isset($res[1]['Docket_Charge']) && floatval($res[1]['Docket_Charge']) > 0) {
                return floatval($res[1]['Docket_Charge']);
            }
            $res2 = $this->db->ExecuteQuery("SELECT Default_Charge FROM tbl_docket_charges WHERE Client_Id=".intval($clientId)." AND Is_Active=1");
            if (!empty($res2) && isset($res2[1]['Default_Charge'])) {
                return floatval($res2[1]['Default_Charge']);
            }
        }
        return $defaultDocket;
    }

    /**
     * Calculate Insurance premium
     */
    public function calculateInsurance($insuredValue, $clientId = 0, $customRate = null) {
        $insuredValue = floatval($insuredValue);
        if ($insuredValue <= 0) {
            return ['insured_value' => 0.0, 'insurance_rate' => 0.0, 'insurance_premium' => 0.0];
        }

        $rate = 0.0;
        if ($customRate !== null && floatval($customRate) > 0) {
            $rate = floatval($customRate);
        } else if ($clientId > 0) {
            $res = $this->db->ExecuteQuery("SELECT Insurance_Percent FROM tbl_clients WHERE Client_Id=".intval($clientId));
            if (!empty($res) && isset($res[1]['Insurance_Percent']) && floatval($res[1]['Insurance_Percent']) > 0) {
                $rate = floatval($res[1]['Insurance_Percent']);
            }
        }

        if ($rate <= 0) {
            $rate = floatval($this->getSetting('default_insurance_percent', 2.00));
        }

        $premium = round(($insuredValue * $rate) / 100, 2);
        return [
            'insured_value' => $insuredValue,
            'insurance_rate' => $rate,
            'insurance_premium' => $premium
        ];
    }

    /**
     * Calculate Base Freight using Rate Slabs
     */
    public function calculateBaseFreight($branchId, $clientId, $destId, $sendBy, $weight) {
        require_once(dirname(__FILE__) . '/rate.php');
        $rateClass = new rate();
        
        ob_start();
        $rateClass->getSubtotal($branchId, $clientId, $destId, $sendBy, $weight);
        $rawOutput = trim(ob_get_clean());
        
        $freight = floatval($rawOutput);
        return $freight;
    }

    /**
     * Complete Booking Calculation
     */
    public function computeBookingCharges($data) {
        $branchId = isset($data['branch_id']) ? intval($data['branch_id']) : 0;
        $clientId = isset($data['client_id']) ? intval($data['client_id']) : 0;
        $destId = isset($data['dest_id']) ? intval($data['dest_id']) : 0;
        $sendBy = isset($data['send_by']) ? intval($data['send_by']) : 1;
        $actualWeight = isset($data['actual_weight']) ? floatval($data['actual_weight']) : (isset($data['weight']) ? floatval($data['weight']) : 0.0);
        $length = isset($data['length']) ? floatval($data['length']) : 0.0;
        $width = isset($data['width']) ? floatval($data['width']) : 0.0;
        $height = isset($data['height']) ? floatval($data['height']) : 0.0;

        // Weight
        $weightCalc = $this->calculateWeight($actualWeight, $length, $width, $height);
        $chargeableWeight = $weightCalc['chargeable_weight'];

        // Base Freight
        $baseFreight = 0.0;
        if ($branchId > 0 && $clientId > 0 && $destId > 0 && $chargeableWeight > 0) {
            $baseFreight = $this->calculateBaseFreight($branchId, $clientId, $destId, $sendBy, $chargeableWeight);
        } else if (isset($data['subtotal']) && floatval($data['subtotal']) > 0) {
            $baseFreight = floatval($data['subtotal']);
        }

        // Discounts
        $discountPercent = isset($data['discount_percent']) ? floatval($data['discount_percent']) : 0.0;
        $discountRs = isset($data['discount_rs']) ? floatval($data['discount_rs']) : 0.0;
        $discountAmt = 0.0;

        if ($discountPercent > 0) {
            $discountAmt = round(($baseFreight * $discountPercent) / 100, 2);
        } else if ($discountRs > 0) {
            $discountAmt = $discountRs;
        }
        $freightAfterDiscount = max(0, $baseFreight - $discountAmt);

        // Additional Charges
        $zoneId = isset($data['zone_id']) ? intval($data['zone_id']) : $this->getDestinationZone($destId);
        $pickupCharge = isset($data['pickup_charge']) ? floatval($data['pickup_charge']) : $this->getPickupCharge($clientId, $zoneId);
        $doorCharge = isset($data['door_charge']) ? floatval($data['door_charge']) : $this->getDoorDeliveryCharge($destId, $clientId);
        $docketCharge = isset($data['docket_charge']) ? floatval($data['docket_charge']) : $this->getDocketCharge($clientId);
        
        // The booking form sends the delivery pincode as consignee_pincode.
        $odaPincode = isset($data['pincode']) ? $data['pincode'] : (isset($data['consignee_pincode']) ? $data['consignee_pincode'] : '');
        $oda = $this->checkODA($destId, isset($data['dest_name']) ? $data['dest_name'] : '', $odaPincode);
        $odaCharge = isset($data['oda_charge']) ? floatval($data['oda_charge']) : ($oda['is_oda'] ? $oda['oda_charge'] : 0.0);

        // Insurance
        $isInsured = !empty($data['is_insured']) && $data['is_insured'] == 1;
        $insuredValue = $isInsured && isset($data['insured_value']) ? floatval($data['insured_value']) : 0.0;
        $insCalc = $this->calculateInsurance($insuredValue, $clientId, isset($data['insurance_rate']) ? $data['insurance_rate'] : null);
        $insuranceCharge = $isInsured ? $insCalc['insurance_premium'] : 0.0;

        $otherCharges = isset($data['other_charges']) ? floatval($data['other_charges']) : 0.0;

        // FOV (carrier risk on declared value)
        $declaredValue = isset($data['insured_value']) ? floatval($data['insured_value']) : 0.0;
        $fovCalc = $this->calculateFOV($declaredValue, null, null, $branchId);
        $fovCharge = isset($data['fov_charge']) ? floatval($data['fov_charge']) : $fovCalc['fov_charge'];

        // Urgent / express surcharge (Send_By 3), from the company's rate master
        $urgentCharge = isset($data['urgent_charge'])
            ? floatval($data['urgent_charge'])
            : (($sendBy == 3) ? $this->calculateUrgent($freightAfterDiscount, $branchId) : 0.0);

        // Fuel surcharge, charged on the freight only
        $fuelPercent = isset($data['fuel_percent']) ? floatval($data['fuel_percent']) : $this->getFuelPercent($clientId, $branchId);
        $fuelCharge = isset($data['fuel_charge'])
            ? floatval($data['fuel_charge'])
            : round(($freightAfterDiscount * $fuelPercent) / 100, 2);

        /*
         * Total_Amount stays pre-tax and excludes fuel, because invoice
         * generation sums Total_Amount and then applies fuel + GST itself.
         * Changing it here would double-charge on every invoice.
         */
        $totalAmount = $freightAfterDiscount + $pickupCharge + $doorCharge + $docketCharge + $odaCharge + $insuranceCharge + $otherCharges;

        // Consignment-level taxable value and GST, stored for the AWB itself.
        $taxableAmount = round($totalAmount + $fovCharge + $fuelCharge + $urgentCharge, 2);
        $withinState = isset($data['within_state'])
            ? (intval($data['within_state']) == 1)
            : $this->isWithinState($clientId);
        $hsnCode = isset($data['hsn_code']) && $data['hsn_code'] !== '' ? $data['hsn_code'] : '996812';
        $gst = $this->computeGST($taxableAmount, $withinState, $hsnCode);

        return [
            'zone_id' => $zoneId,
            'fov_details' => $fovCalc,
            'fov_charge' => $fovCharge,
            'urgent_charge' => $urgentCharge,
            'fuel_percent' => $fuelPercent,
            'fuel_charge' => $fuelCharge,
            'taxable_amount' => $taxableAmount,
            'gst_details' => $gst,
            'cgst_amount' => $gst['cgst_amount'],
            'sgst_amount' => $gst['sgst_amount'],
            'igst_amount' => $gst['igst_amount'],
            'grand_total' => $gst['grand_total'],
            'weight_details' => $weightCalc,
            'base_freight' => $baseFreight,
            'discount_percent' => $discountPercent,
            'discount_rs' => $discountRs,
            'discount_amount' => $discountAmt,
            'freight_after_discount' => $freightAfterDiscount,
            'pickup_charge' => $pickupCharge,
            'door_delivery_charge' => $doorCharge,
            'docket_charge' => $docketCharge,
            'oda_details' => $oda,
            'oda_charge' => $odaCharge,
            'insurance_details' => $insCalc,
            'insurance_charge' => $insuranceCharge,
            'other_charges' => $otherCharges,
            'total_amount' => round($totalAmount, 2)
        ];
    }

    /**
     * Compute GST tax breakdown
     */
    public function computeGST($subtotal, $isWithinState = true, $hsnCode = '996812') {
        $subtotal = floatval($subtotal);
        $hsn = $this->db->ExecuteQuery("SELECT * FROM tbl_hsn_master WHERE HSN_Code='".$this->db->escape($hsnCode)."' AND Is_Active=1");
        
        $gstRate = 18.00;
        $cgstRate = 9.00;
        $sgstRate = 9.00;
        $igstRate = 18.00;

        if (!empty($hsn) && isset($hsn[1])) {
            $gstRate = floatval($hsn[1]['GST_Percent']);
            $cgstRate = floatval($hsn[1]['CGST_Percent']);
            $sgstRate = floatval($hsn[1]['SGST_Percent']);
            $igstRate = floatval($hsn[1]['IGST_Percent']);
        } else {
            $tax = $this->db->ExecuteQuery("SELECT * FROM tbl_taxes");
            if (!empty($tax) && isset($tax[1])) {
                $igstRate = isset($tax[1]['IGST']) ? floatval($tax[1]['IGST']) : 18.00;
                $cgstRate = isset($tax[1]['CGST']) ? floatval($tax[1]['CGST']) : 9.00;
                $sgstRate = isset($tax[1]['SGST']) ? floatval($tax[1]['SGST']) : 9.00;
                $gstRate = $igstRate;
            }
        }

        if ($isWithinState) {
            $cgst = round(($subtotal * $cgstRate) / 100, 2);
            $sgst = round(($subtotal * $sgstRate) / 100, 2);
            $igst = 0.00;
            $totalTax = $cgst + $sgst;
        } else {
            $cgst = 0.00;
            $sgst = 0.00;
            $igst = round(($subtotal * $igstRate) / 100, 2);
            $totalTax = $igst;
        }

        $grandTotal = round($subtotal + $totalTax, 2);

        return [
            'subtotal' => $subtotal,
            'is_within_state' => $isWithinState,
            'hsn_code' => $hsnCode,
            'gst_rate' => $gstRate,
            'cgst_rate' => $cgstRate,
            'cgst_amount' => $cgst,
            'sgst_rate' => $sgstRate,
            'sgst_amount' => $sgst,
            'igst_rate' => $igstRate,
            'igst_amount' => $igst,
            'total_tax' => $totalTax,
            'grand_total' => $grandTotal
        ];
    }

    /**
     * Get Client Balance and Outstanding
     */
    public function getClientOutstanding($clientId) {
        $clientId = intval($clientId);
        $invRes = $this->db->ExecuteQuery("SELECT SUM(Final_Total_Amt) AS Total_Billed FROM tbl_invoices WHERE Client_Id=$clientId");
        $totalBilled = (!empty($invRes) && isset($invRes[1]['Total_Billed'])) ? floatval($invRes[1]['Total_Billed']) : 0.0;

        $payRes = $this->db->ExecuteQuery("SELECT SUM(Payment_Amount) AS Total_Paid FROM tbl_payment_receipts WHERE Client_Id=$clientId");
        $totalPaid = (!empty($payRes) && isset($payRes[1]['Total_Paid'])) ? floatval($payRes[1]['Total_Paid']) : 0.0;

        $outstanding = max(0, $totalBilled - $totalPaid);

        return [
            'client_id' => $clientId,
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding
        ];
    }

    /**
     * Number to Indian Rupees Words converter
     */
    public static function numberToWords($number) {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'one', 2 => 'two',
            3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
            7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve',
            13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
            19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
            40 => 'forty', 50 => 'fifty', 60 => 'sixty',
            70 => 'seventy', 80 => 'eighty', 90 => 'ninety'
        );
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? 'Rupees ' . trim($Rupees) : '') . ($paise ? ' and ' . trim($paise) : '') . ' Only';
    }
}
?>

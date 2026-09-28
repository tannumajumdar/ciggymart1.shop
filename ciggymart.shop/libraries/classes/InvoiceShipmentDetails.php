<?php
/**
 * Full per-AWB booking data (route, consignee, weights, every charge and
 * GST) for the invoice screen and the printed invoice.
 *
 * Amounts are the ones stored when each consignment was booked. The
 * invoice summary above it still does its own fuel + GST calculation.
 */
class InvoiceShipmentDetails {

    /**
     * @param DBConn $db
     * @param array  $consignmentIds
     * @param int    $branchId  limit to one branch (branch panel); 0 = any
     * @return string HTML table, or '' when there is nothing to show
     */
    public static function render($db, $consignmentIds, $branchId = 0) {
        $ids = array();
        foreach ((array)$consignmentIds as $id) {
            $id = intval($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        if (empty($ids)) {
            return '';
        }

        $rows = $db->ExecuteQuery("SELECT CO.*, DATE_FORMAT(CO.Date_Of_Submit,'%d-%m-%Y') AS Booking_Date,
            D.Destination_Name, P.Partner_Name
            FROM tbl_consignments CO
            LEFT JOIN tbl_destinations D ON D.Destination_Id = CO.Destination_Id
            LEFT JOIN tbl_courier_partners P ON P.Partner_Id = CO.Courier_Partner_Id
            WHERE CO.Consignment_Id IN (" . implode(',', $ids) . ")" . ($branchId > 0 ? " AND CO.Branch_Id=" . intval($branchId) : "") . "
            ORDER BY CO.Date_Of_Submit ASC, CO.Consignment_No ASC");
        if (empty($rows)) {
            return '';
        }

        $services = array(1 => 'Surface', 2 => 'Air', 3 => 'Urgent');
        $modes = array(1 => 'Document', 2 => 'Parcel');
        $sumKeys = array('Insured_Value', 'Freight', 'FOV_Charge', 'ODA_Charge', 'Insurance_Charge', 'Fuel_Charge',
            'Other', 'CGST_Amount', 'SGST_Amount', 'IGST_Amount', 'Grand_Total');
        $sum = array_fill_keys($sumKeys, 0.0);
        $sumPieces = 0;
        $sumWeight = 0.0;

        $th = 'style="border:1px solid #999; padding:3px; background:#EBEBEB; font-weight:bold;"';
        $td = 'style="border:1px solid #999; padding:3px; vertical-align:top;"';
        $num = 'style="border:1px solid #999; padding:3px; vertical-align:top; text-align:right; white-space:nowrap;"';

        $html = '<div class="invoice-shipment-details" style="margin-top:16px;">'
            . '<div style="font-weight:bold; font-size:10pt; margin-bottom:4px;">SHIPMENT DETAILS</div>'
            . '<table width="100%" cellspacing="0" style="border-collapse:collapse; font-size:7.5pt;">'
            . '<tr>'
            . "<td $th>S.No</td>"
            . "<td $th>Date / AWB No.</td>"
            . "<td $th>Consignee / Mobile / Delivery Address</td>"
            . "<td $th>Origin &rarr; Destination (Pincode)</td>"
            . "<td $th>Courier Partner / Service / Type</td>"
            . "<td $th>Products / HSN</td>"
            . "<td $th>Pcs</td>"
            . "<td $th>Actual / Vol. / Chg. Wt (kg)<br>L &times; W &times; H (cm)</td>"
            . "<td $th>Value</td>"
            . "<td $th>Freight</td>"
            . "<td $th>FOV</td>"
            . "<td $th>ODA</td>"
            . "<td $th>Insurance</td>"
            . "<td $th>Fuel</td>"
            . "<td $th>Pickup / Door / Docket / Other</td>"
            . "<td $th>CGST</td>"
            . "<td $th>SGST</td>"
            . "<td $th>IGST</td>"
            . "<td $th>Total</td>"
            . '</tr>';

        $i = 1;
        foreach ($rows as $r) {
            $freight = floatval($r['Subtotal']);
            if (floatval($r['Discount_Percent']) > 0) {
                $freight -= round($freight * floatval($r['Discount_Percent']) / 100, 2);
            } elseif (floatval($r['Discount_Rs']) > 0) {
                $freight -= floatval($r['Discount_Rs']);
            }
            $freight = max(0, $freight);
            $other = floatval($r['Pickup_Charge']) + floatval($r['Door_Delivery_Charge'])
                + floatval($r['Docket_Charge']) + floatval($r['Other_Charges']);

            $line = array(
                'Insured_Value'    => floatval($r['Insured_Value']),
                'Freight'          => $freight,
                'FOV_Charge'       => floatval($r['FOV_Charge']),
                'ODA_Charge'       => floatval($r['ODA_Charge']),
                'Insurance_Charge' => floatval($r['Insurance_Charge']),
                'Fuel_Charge'      => floatval($r['Fuel_Charge']),
                'Other'            => $other,
                'CGST_Amount'      => floatval($r['CGST_Amount']),
                'SGST_Amount'      => floatval($r['SGST_Amount']),
                'IGST_Amount'      => floatval($r['IGST_Amount']),
                // Older bookings have no stored grand total; fall back to their booked amount.
                'Grand_Total'      => floatval($r['Grand_Total']) > 0 ? floatval($r['Grand_Total']) : floatval($r['Total_Amount']),
            );
            foreach ($line as $k => $v) {
                $sum[$k] += $v;
            }
            $sumPieces += intval($r['No_Of_Pieces']);
            $sumWeight += floatval($r['Chargeable_Weight']) > 0 ? floatval($r['Chargeable_Weight']) : floatval($r['Total_Weight_In_KG']);

            $dims = (floatval($r['Length_CM']) > 0)
                ? self::n($r['Length_CM'], 0) . ' &times; ' . self::n($r['Width_CM'], 0) . ' &times; ' . self::n($r['Height_CM'], 0)
                : '-';
            $service = isset($services[intval($r['Send_By'])]) ? $services[intval($r['Send_By'])] : '-';
            $mode = isset($modes[intval($r['Mode'])]) ? $modes[intval($r['Mode'])] : '-';

            $html .= '<tr>'
                . "<td $td>" . $i++ . '</td>'
                . "<td $td>" . self::e($r['Booking_Date']) . '<br><strong>' . self::e($r['Consignment_No']) . '</strong></td>'
                . "<td $td>" . self::e($r['Consignee_Name']) . self::br($r['Consignee_Mobile']) . self::br($r['Consignee_Address']) . '</td>'
                . "<td $td>" . self::place($r['Origin_City'], $r['Origin_Pincode']) . ' &rarr;<br>'
                    . self::place($r['Destination_Name'], $r['Consignee_Pincode']) . '</td>'
                . "<td $td>" . (empty($r['Partner_Name']) ? 'Own Network' : self::e($r['Partner_Name']))
                    . '<br>' . $service . ' / ' . $mode . '</td>'
                . "<td $td>" . self::e($r['Commodity_Type']) . self::br($r['HSN_Code']) . '</td>'
                . "<td $num>" . intval($r['No_Of_Pieces']) . '</td>'
                . "<td $num>" . self::n($r['Total_Weight_In_KG'], 3) . ' / ' . self::n($r['Volumetric_Weight'], 3)
                    . ' / <strong>' . self::n($r['Chargeable_Weight'], 3) . '</strong><br>' . $dims . '</td>';
            foreach ($sumKeys as $k) {
                $html .= "<td $num>" . self::n($line[$k]) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '<tr style="font-weight:bold;">'
            . "<td $td colspan=\"6\" align=\"right\">Total</td>"
            . "<td $num>" . $sumPieces . '</td>'
            . "<td $num>" . self::n($sumWeight, 3) . '</td>';
        foreach ($sumKeys as $k) {
            $html .= "<td $num>" . self::n($sum[$k]) . '</td>';
        }
        $html .= '</tr></table></div>';

        return $html;
    }

    private static function e($v) {
        return htmlspecialchars((string)$v);
    }

    private static function br($v) {
        return trim((string)$v) === '' ? '' : '<br>' . self::e($v);
    }

    private static function n($v, $decimals = 2) {
        return number_format(floatval($v), $decimals, '.', '');
    }

    private static function place($city, $pin) {
        $out = trim((string)$city) === '' ? '-' : self::e($city);
        return trim((string)$pin) === '' ? $out : $out . ' (' . self::e($pin) . ')';
    }
}
?>

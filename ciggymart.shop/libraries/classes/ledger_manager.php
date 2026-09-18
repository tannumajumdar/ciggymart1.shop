<?php
/**
 * Customer Ledger & Payment Logic
 * Handles automatic creation of ledger entries for invoices and payments.
 */

class LedgerManager {
    private $db;

    public function __construct($dbConn) {
        $this->db = $dbConn;
    }

    /**
     * Records a debit when a new Invoice is Generated.
     */
    public function recordInvoiceDebit($customerId, $invoiceId, $amount, $date) {
        $runningBalance = $this->getRunningBalance($customerId) + $amount;
        
        $sql = "INSERT INTO tbl_customer_ledger 
                (Customer_Id, Transaction_Date, Transaction_Type, Reference_Id, Debit, Running_Balance, Notes)
                VALUES (
                    ".intval($customerId).", 
                    '".$date."', 
                    'Invoice', 
                    ".intval($invoiceId).", 
                    ".floatval($amount).", 
                    ".$runningBalance.", 
                    'Auto-generated debit for Invoice #".$invoiceId."'
                )";
        return $this->db->valInsert('tbl_customer_ledger', array('Customer_Id', 'Transaction_Date', 'Transaction_Type', 'Reference_Id', 'Debit', 'Running_Balance', 'Notes'), 
                array($customerId, $date, 'Invoice', $invoiceId, $amount, $runningBalance, 'Auto-generated debit for Invoice #'.$invoiceId));
    }

    /**
     * Records a payment receipt and allocates it to an invoice if needed.
     */
    public function recordPaymentReceipt($customerId, $amount, $paymentMode, $referenceNo, $date, $allocatedInvoiceIds = array()) {
        // 1. Create Receipt Entry
        $receiptId = $this->db->valInsert('tbl_payment_receipts', 
            array('Customer_Id', 'Amount_Received', 'Payment_Mode', 'Reference_No', 'Receipt_Date'),
            array($customerId, $amount, $paymentMode, $referenceNo, $date)
        );

        // 2. Allocate across invoices (1-to-Many)
        // Basic implementation of FIFO or manual allocation based on input array
        if(!empty($allocatedInvoiceIds)) {
            foreach($allocatedInvoiceIds as $invoiceId => $allocAmt) {
                $this->db->valInsert('tbl_payment_allocations', 
                    array('Receipt_Id', 'Invoice_Id', 'Allocated_Amount'),
                    array($receiptId, $invoiceId, $allocAmt)
                );
                
                // Update Invoice status to Paid or Partially Paid
                // $this->updateInvoiceStatus($invoiceId);
            }
        }

        // 3. Create Ledger Entry (Credit)
        $runningBalance = $this->getRunningBalance($customerId) - $amount;
        $this->db->valInsert('tbl_customer_ledger', 
            array('Customer_Id', 'Transaction_Date', 'Transaction_Type', 'Reference_Id', 'Credit', 'Running_Balance', 'Notes'), 
            array($customerId, $date, 'Payment', $receiptId, $amount, $runningBalance, 'Payment Received via '.$paymentMode.' Ref: '.$referenceNo)
        );
        
        return $receiptId;
    }

    private function getRunningBalance($customerId) {
        $sql = "SELECT Running_Balance FROM tbl_customer_ledger WHERE Customer_Id = ".intval($customerId)." ORDER BY Ledger_Id DESC LIMIT 1";
        $res = $this->db->ExecuteQuery($sql);
        if(!empty($res)) {
            return floatval($res[1]['Running_Balance']);
        }
        return 0.00;
    }
}
?>

<?php
require_once(dirname(__FILE__) . '/DBConn.php');
if (file_exists(ROOT . '/PHPMailer-master/PHPMailerAutoload.php')) {
    require_once(ROOT . '/PHPMailer-master/PHPMailerAutoload.php');
}

class CourierEmailService {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ? $db : new DBConn();
    }

    private function getSetting($key, $default = '') {
        $res = $this->db->ExecuteQuery("SELECT Setting_Value FROM tbl_settings WHERE Setting_Key='".$this->db->escape($key)."'");
        return (!empty($res) && isset($res[1]['Setting_Value'])) ? $res[1]['Setting_Value'] : $default;
    }

    private function initMailer() {
        if (!class_exists('PHPMailer')) {
            return null;
        }

        $mail = new PHPMailer();
        $smtpHost = $this->getSetting('smtp_host', '');
        $smtpUser = $this->getSetting('smtp_user', '');
        $smtpPass = $this->getSetting('smtp_pass', '');
        $smtpPort = intval($this->getSetting('smtp_port', 587));
        $smtpSecure = $this->getSetting('smtp_secure', 'tls');
        $fromEmail = $this->getSetting('smtp_from_email', 'billing@ciggymart.shop');
        $fromName = $this->getSetting('smtp_from_name', 'Keshri Express Logistics');

        if (!empty($smtpHost) && !empty($smtpUser) && !empty($smtpPass)) {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            if (!empty($smtpSecure) && $smtpSecure !== 'none') {
                $mail->SMTPSecure = $smtpSecure;
            }
            $mail->Port = $smtpPort;
        }

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->From = $fromEmail;
        $mail->FromName = $fromName;
        $mail->Sender = $fromEmail;
        $mail->addReplyTo($fromEmail, $fromName);

        return $mail;
    }

    public function logEmail($entityType, $entityId, $recipientEmail, $subject, $status, $errorMsg = '') {
        $this->db->valInsert(
            'tbl_email_logs',
            ['Entity_Type', 'Entity_Id', 'Recipient_Email', 'Subject', 'Status', 'Error_Msg', 'Sent_At'],
            [$entityType, intval($entityId), $recipientEmail, $subject, $status, $errorMsg, date('Y-m-d H:i:s')]
        );
    }

    public function sendInvoiceEmail($invoiceId, $clientEmail, $pdfPath, $invoiceNo, $amount, $clientName = 'Valued Customer') {
        if (empty($clientEmail) || !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->logEmail('Invoice', $invoiceId, $clientEmail, "Invoice #$invoiceNo", 'Failed', 'Invalid recipient email address');
            return false;
        }

        $mail = $this->initMailer();
        if (!$mail) {
            $this->logEmail('Invoice', $invoiceId, $clientEmail, "Invoice #$invoiceNo", 'Failed', 'PHPMailer class not initialized');
            return false;
        }

        $companyName = $this->getSetting('company_name', 'Keshri Express Logistics');
        $companyPhone = $this->getSetting('company_phone', '+91 9876543210');
        $companyEmail = $this->getSetting('company_email', 'support@ciggymart.shop');

        $subject = "Invoice #$invoiceNo from $companyName";
        $mail->addAddress($clientEmail, $clientName);
        $mail->Subject = $subject;

        if (!empty($pdfPath) && file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath, "Invoice-$invoiceNo.pdf");
        }

        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
                .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                .email-header { background: #0f172a; color: #ffffff; padding: 24px 30px; }
                .email-header h2 { margin: 0 0 6px 0; font-size: 20px; font-weight: 600; }
                .email-header p { margin: 0; opacity: 0.8; font-size: 13px; }
                .email-body { padding: 30px; }
                .invoice-badge { display: inline-block; background: #eff6ff; color: #2563eb; font-weight: 600; padding: 6px 14px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #f8fafc; border-radius: 8px; overflow: hidden; }
                .info-table td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
                .info-table tr:last-child td { border-bottom: none; font-weight: bold; font-size: 16px; color: #0f172a; }
                .email-footer { background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class='email-card'>
                <div class='email-header'>
                    <h2>$companyName</h2>
                    <p>Express Courier & Cargo Management System</p>
                </div>
                <div class='email-body'>
                    <div class='invoice-badge'>INVOICE ATTACHED</div>
                    <p>Dear <strong>$clientName</strong>,</p>
                    <p>Thank you for choosing $companyName. Please find attached your official tax invoice for recent courier services.</p>
                    
                    <table class='info-table'>
                        <tr>
                            <td>Invoice Number:</td>
                            <td align='right'><strong>$invoiceNo</strong></td>
                        </tr>
                        <tr>
                            <td>Invoice Date:</td>
                            <td align='right'>".date('d M Y')."</td>
                        </tr>
                        <tr>
                            <td>Total Amount Due:</td>
                            <td align='right'><strong style='color:#2563eb;'>₹ ".number_format($amount, 2)."</strong></td>
                        </tr>
                    </table>

                    <p>The complete itemized invoice PDF is attached to this email for your accounting records.</p>
                    <p>If you have any questions or require assistance, please feel free to reach out to us at $companyEmail or call $companyPhone.</p>
                </div>
                <div class='email-footer'>
                    <p>&copy; ".date('Y')." $companyName. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $body;

        try {
            $sent = $mail->send();
            if ($sent) {
                $this->logEmail('Invoice', $invoiceId, $clientEmail, $subject, 'Sent');
                $this->db->query("UPDATE tbl_invoices SET Email_Status='Sent' WHERE Invoice_Id=".intval($invoiceId));
                return true;
            } else {
                $this->logEmail('Invoice', $invoiceId, $clientEmail, $subject, 'Failed', $mail->ErrorInfo);
                $this->db->query("UPDATE tbl_invoices SET Email_Status='Failed' WHERE Invoice_Id=".intval($invoiceId));
                return false;
            }
        } catch (Exception $e) {
            $this->logEmail('Invoice', $invoiceId, $clientEmail, $subject, 'Failed', $e->getMessage());
            $this->db->query("UPDATE tbl_invoices SET Email_Status='Failed' WHERE Invoice_Id=".intval($invoiceId));
            return false;
        }
    }

    public function sendPaymentReceiptEmail($receiptId, $clientEmail, $pdfPath, $receiptNo, $amount, $clientName = 'Valued Customer', $balance = 0.0) {
        if (empty($clientEmail) || !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->logEmail('PaymentReceipt', $receiptId, $clientEmail, "Payment Receipt #$receiptNo", 'Failed', 'Invalid recipient email address');
            return false;
        }

        $mail = $this->initMailer();
        if (!$mail) {
            $this->logEmail('PaymentReceipt', $receiptId, $clientEmail, "Payment Receipt #$receiptNo", 'Failed', 'PHPMailer class not initialized');
            return false;
        }

        $companyName = $this->getSetting('company_name', 'Keshri Express Logistics');
        $companyPhone = $this->getSetting('company_phone', '+91 9876543210');
        $companyEmail = $this->getSetting('company_email', 'support@ciggymart.shop');

        $subject = "Payment Receipt #$receiptNo - $companyName";
        $mail->addAddress($clientEmail, $clientName);
        $mail->Subject = $subject;

        if (!empty($pdfPath) && file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath, "Receipt-$receiptNo.pdf");
        }

        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
                .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                .email-header { background: #065f46; color: #ffffff; padding: 24px 30px; }
                .email-header h2 { margin: 0 0 6px 0; font-size: 20px; font-weight: 600; }
                .email-header p { margin: 0; opacity: 0.8; font-size: 13px; }
                .email-body { padding: 30px; }
                .receipt-badge { display: inline-block; background: #ecfdf5; color: #059669; font-weight: 600; padding: 6px 14px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #f8fafc; border-radius: 8px; overflow: hidden; }
                .info-table td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
                .info-table tr:last-child td { border-bottom: none; font-weight: bold; font-size: 16px; color: #065f46; }
                .email-footer { background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class='email-card'>
                <div class='email-header'>
                    <h2>$companyName</h2>
                    <p>Official Payment Acknowledgment</p>
                </div>
                <div class='email-body'>
                    <div class='receipt-badge'>PAYMENT RECEIVED & ACKNOWLEDGED</div>
                    <p>Dear <strong>$clientName</strong>,</p>
                    <p>We gratefully acknowledge receipt of your payment. The transaction details are outlined below:</p>
                    
                    <table class='info-table'>
                        <tr>
                            <td>Receipt Number:</td>
                            <td align='right'><strong>$receiptNo</strong></td>
                        </tr>
                        <tr>
                            <td>Payment Date:</td>
                            <td align='right'>".date('d M Y')."</td>
                        </tr>
                        <tr>
                            <td>Amount Paid:</td>
                            <td align='right'><strong style='color:#059669;'>₹ ".number_format($amount, 2)."</strong></td>
                        </tr>
                        <tr>
                            <td>Remaining Balance:</td>
                            <td align='right'>₹ ".number_format($balance, 2)."</td>
                        </tr>
                    </table>

                    <p>An official PDF payment receipt is attached with this email for your financial records.</p>
                    <p>For any billing inquiries, please reach out to us at $companyEmail or $companyPhone.</p>
                </div>
                <div class='email-footer'>
                    <p>&copy; ".date('Y')." $companyName. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $body;

        try {
            $sent = $mail->send();
            if ($sent) {
                $this->logEmail('PaymentReceipt', $receiptId, $clientEmail, $subject, 'Sent');
                $this->db->query("UPDATE tbl_payment_receipts SET Email_Status='Sent' WHERE Receipt_Id=".intval($receiptId));
                return true;
            } else {
                $this->logEmail('PaymentReceipt', $receiptId, $clientEmail, $subject, 'Failed', $mail->ErrorInfo);
                $this->db->query("UPDATE tbl_payment_receipts SET Email_Status='Failed' WHERE Receipt_Id=".intval($receiptId));
                return false;
            }
        } catch (Exception $e) {
            $this->logEmail('PaymentReceipt', $receiptId, $clientEmail, $subject, 'Failed', $e->getMessage());
            $this->db->query("UPDATE tbl_payment_receipts SET Email_Status='Failed' WHERE Receipt_Id=".intval($receiptId));
            return false;
        }
    }
}
?>

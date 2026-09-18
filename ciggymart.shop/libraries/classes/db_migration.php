<?php
/**
 * Keshri Express Courier ERP - Safe Database Migration & Schema Upgrades
 * Preserves all existing tables, rows, and relationships.
 */
class DBMigration {
    
    public static function runMigration($db) {
        // 1. Settings Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_settings` (
            `Setting_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Setting_Key` varchar(100) NOT NULL UNIQUE,
            `Setting_Value` text,
            `Description` varchar(255) DEFAULT NULL,
            `Updated_At` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`Setting_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Seed default settings if empty
        $defaultSettings = [
            'volumetric_divisor' => ['5000', 'Volumetric weight divisor (e.g. 5000 for cm)'],
            'company_name' => ['Keshri Express Logistics', 'Company Name displayed on invoices and receipts'],
            'company_tagline' => ['Express Courier & Cargo Services', 'Company Tagline'],
            'company_email' => ['support@keshriexpress.com', 'Company Support Email'],
            'company_phone' => ['+91 9876543210', 'Company Contact Number'],
            'company_address' => ['Corporate Office, Logistics Hub, India', 'Company Address'],
            'company_gstin' => ['07AAAAA0000A1Z5', 'Company Head GSTIN Number'],
            'company_pan' => ['AAAAA0000A', 'Company PAN Number'],
            'default_docket_charge' => ['50.00', 'Default Docket Charge in INR'],
            'default_pickup_charge' => ['0.00', 'Default Pickup Charge in INR'],
            'default_door_delivery_charge' => ['0.00', 'Default Door Delivery Charge in INR'],
            'default_oda_charge' => ['150.00', 'Default ODA Charge in INR'],
            'default_insurance_percent' => ['2.00', 'Default Insurance Premium Rate %'],
            'smtp_host' => ['smtp.gmail.com', 'SMTP Server Hostname'],
            'smtp_port' => ['587', 'SMTP Server Port'],
            'smtp_user' => ['', 'SMTP Username / Email'],
            'smtp_pass' => ['', 'SMTP Password / App Password'],
            'smtp_secure' => ['tls', 'SMTP Encryption (tls or ssl)'],
            'smtp_from_email' => ['billing@keshriexpress.com', 'System Sender Email'],
            'smtp_from_name' => ['Keshri Express Billing', 'System Sender Name'],
            'email_auto_invoice' => ['1', 'Auto Email Invoice on Generation (1=Yes, 0=No)'],
            'email_auto_payment' => ['1', 'Auto Email Payment Receipt (1=Yes, 0=No)']
        ];

        foreach ($defaultSettings as $key => $vals) {
            $check = $db->ExecuteQuery("SELECT Setting_Id FROM tbl_settings WHERE Setting_Key='$key'");
            if (empty($check) || count($check) == 0) {
                $db->query("INSERT INTO `tbl_settings` (`Setting_Key`, `Setting_Value`, `Description`) VALUES ('$key', '".addslashes($vals[0])."', '".addslashes($vals[1])."')");
            }
        }

        // 2. ODA Master Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_oda_master` (
            `ODA_Id` int(11) NOT NULL AUTO_INCREMENT,
            `City` varchar(150) NOT NULL,
            `State` varchar(150) DEFAULT NULL,
            `Pincode` varchar(20) DEFAULT NULL,
            `Zone_Id` int(11) DEFAULT '0',
            `Is_ODA` tinyint(1) NOT NULL DEFAULT '1',
            `ODA_Charge` decimal(10,2) NOT NULL DEFAULT '150.00',
            `Is_Active` tinyint(1) NOT NULL DEFAULT '1',
            `Created_At` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ODA_Id`),
            KEY `idx_pincode` (`Pincode`),
            KEY `idx_city` (`City`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 3. HSN Master Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_hsn_master` (
            `HSN_Id` int(11) NOT NULL AUTO_INCREMENT,
            `HSN_Code` varchar(50) NOT NULL UNIQUE,
            `Description` varchar(255) NOT NULL,
            `GST_Percent` decimal(5,2) NOT NULL DEFAULT '18.00',
            `CGST_Percent` decimal(5,2) NOT NULL DEFAULT '9.00',
            `SGST_Percent` decimal(5,2) NOT NULL DEFAULT '9.00',
            `IGST_Percent` decimal(5,2) NOT NULL DEFAULT '18.00',
            `Is_Active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`HSN_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Seed HSN default if empty
        $checkHsn = $db->ExecuteQuery("SELECT HSN_Id FROM tbl_hsn_master WHERE HSN_Code='996812'");
        if (empty($checkHsn) || count($checkHsn) == 0) {
            $db->query("INSERT INTO `tbl_hsn_master` (`HSN_Code`, `Description`, `GST_Percent`, `CGST_Percent`, `SGST_Percent`, `IGST_Percent`, `Is_Active`) VALUES
            ('996812', 'Courier & Express Cargo Services', 18.00, 9.00, 9.00, 18.00, 1),
            ('996813', 'Local Delivery & Transport Services', 18.00, 9.00, 9.00, 18.00, 1),
            ('996511', 'Road Transport Services of Goods', 12.00, 6.00, 6.00, 12.00, 1);");
        }

        // 4. Pickup Charges Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_pickup_charges` (
            `Pickup_Charge_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Client_Id` int(11) DEFAULT NULL,
            `Default_Charge` decimal(10,2) NOT NULL DEFAULT '0.00',
            `Is_Active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`Pickup_Charge_Id`),
            KEY `idx_client` (`Client_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 5. Door Delivery Charges Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_door_delivery_charges` (
            `Door_Charge_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Destination_Id` int(11) DEFAULT NULL,
            `State_Id` int(11) DEFAULT NULL,
            `Zone_Id` int(11) DEFAULT NULL,
            `Pincode` varchar(20) DEFAULT NULL,
            `Door_Charge` decimal(10,2) NOT NULL DEFAULT '0.00',
            `Is_Active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`Door_Charge_Id`),
            KEY `idx_dest` (`Destination_Id`),
            KEY `idx_pincode` (`Pincode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 6. Docket Charges Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_docket_charges` (
            `Docket_Charge_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Client_Id` int(11) DEFAULT NULL,
            `Default_Charge` decimal(10,2) NOT NULL DEFAULT '50.00',
            `Is_Active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`Docket_Charge_Id`),
            KEY `idx_client` (`Client_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 7. Payment Receipts Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_payment_receipts` (
            `Receipt_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Receipt_No` varchar(100) NOT NULL UNIQUE,
            `Payment_Date` date NOT NULL,
            `Client_Id` int(11) NOT NULL,
            `Branch_Id` int(11) NOT NULL,
            `Payment_Amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `Payment_Mode` varchar(50) NOT NULL DEFAULT 'Cash',
            `Reference_No` varchar(100) DEFAULT NULL,
            `Invoice_Id` int(11) DEFAULT NULL,
            `Previous_Balance` decimal(12,2) NOT NULL DEFAULT '0.00',
            `Paid_Amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `Remaining_Balance` decimal(12,2) NOT NULL DEFAULT '0.00',
            `Notes` text DEFAULT NULL,
            `Email_Status` varchar(50) DEFAULT 'Pending',
            `Created_At` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`Receipt_Id`),
            KEY `idx_client` (`Client_Id`),
            KEY `idx_branch` (`Branch_Id`),
            KEY `idx_invoice` (`Invoice_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 8. Email Activity Logs Table
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_email_logs` (
            `Log_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Entity_Type` varchar(50) NOT NULL,
            `Entity_Id` int(11) NOT NULL,
            `Recipient_Email` varchar(150) NOT NULL,
            `Subject` varchar(255) NOT NULL,
            `Status` varchar(50) NOT NULL DEFAULT 'Sent',
            `Error_Msg` text DEFAULT NULL,
            `Sent_At` timestamp DEFAULT CURRENT_TIMESTAMP,
            `Retry_Count` int(11) DEFAULT '0',
            PRIMARY KEY (`Log_Id`),
            KEY `idx_entity` (`Entity_Type`, `Entity_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 9. Safely Add Missing Columns to Existing Tables
        self::safeAddColumn($db, 'tbl_destinations', 'Pincode', "varchar(20) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_destinations', 'Is_ODA', "tinyint(1) DEFAULT '0'");
        self::safeAddColumn($db, 'tbl_destinations', 'ODA_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_destinations', 'Door_Delivery_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_destinations', 'Is_Active', "tinyint(1) DEFAULT '1'");

        self::safeAddColumn($db, 'tbl_clients', 'Company_Name', "varchar(255) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_clients', 'Contact_Person', "varchar(150) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_clients', 'Pickup_Address', "text DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_clients', 'Pincode', "varchar(20) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_clients', 'Door_Delivery_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_clients', 'Pickup_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_clients', 'Docket_Charge', "decimal(10,2) DEFAULT '50.00'");

        self::safeAddColumn($db, 'tbl_consignments', 'Volumetric_Weight', "decimal(10,3) DEFAULT '0.000'");
        self::safeAddColumn($db, 'tbl_consignments', 'Chargeable_Weight', "decimal(10,3) DEFAULT '0.000'");
        self::safeAddColumn($db, 'tbl_consignments', 'Length_CM', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Width_CM', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Height_CM', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'HSN_Code', "varchar(50) DEFAULT '996812'");
        self::safeAddColumn($db, 'tbl_consignments', 'Commodity_Type', "varchar(100) DEFAULT 'General Goods'");
        self::safeAddColumn($db, 'tbl_consignments', 'Package_Type', "varchar(50) DEFAULT 'Box'");
        self::safeAddColumn($db, 'tbl_consignments', 'Pickup_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Door_Delivery_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Docket_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'ODA_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Insurance_Charge', "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Consignee_Name', "varchar(200) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Consignee_Address', "text DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Consignee_City', "varchar(100) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Consignee_Mobile', "varchar(50) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Consignee_Pincode', "varchar(20) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Is_Insured', "tinyint(1) DEFAULT '0'");
        self::safeAddColumn($db, 'tbl_consignments', 'Insurance_Provider', "varchar(150) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Insurance_Policy_No', "varchar(100) DEFAULT NULL");

        self::safeAddColumn($db, 'tbl_invoices', 'Pickup_Charges_Total', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'Door_Delivery_Total', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'Docket_Charges_Total', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'ODA_Charges_Total', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'Insurance_Charges_Total', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'Freight_Total', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'Paid_Amount', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'Balance_Due', "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_invoices', 'Payment_Status', "varchar(50) DEFAULT 'Unpaid'");
        self::safeAddColumn($db, 'tbl_invoices', 'Email_Status', "varchar(50) DEFAULT 'Pending'");
    }

    private static function safeAddColumn($db, $tableName, $columnName, $definition) {
        $check = $db->ExecuteQuery("SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
        if (empty($check) || count($check) == 0) {
            $db->query("ALTER TABLE `$tableName` ADD `$columnName` $definition");
        }
    }
}
?>

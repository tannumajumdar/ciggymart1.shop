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

        /* -----------------------------------------------------------------
         * Courier partner master.
         * adminpanel/masters/courier/ already queries this table but it was
         * never created, so that page was broken.
         * ----------------------------------------------------------------- */
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_courier_partners` (
            `Partner_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Partner_Name` varchar(150) NOT NULL,
            `Partner_Code` varchar(50) DEFAULT NULL,
            `Contact_Person` varchar(150) DEFAULT NULL,
            `Contact_No` varchar(20) DEFAULT NULL,
            `Email` varchar(150) DEFAULT NULL,
            `Address` varchar(255) DEFAULT NULL,
            `Tracking_URL` varchar(255) DEFAULT NULL,
            `Is_Active` tinyint(1) DEFAULT '1',
            PRIMARY KEY (`Partner_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        /* -----------------------------------------------------------------
         * Consignment entry: origin, courier partner and the charge/tax
         * columns that were only being calculated at invoice time.
         * ----------------------------------------------------------------- */
        self::safeAddColumn($db, 'tbl_consignments', 'Origin_City',        "varchar(150) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Origin_Pincode',     "varchar(10) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'Courier_Partner_Id', "int(11) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_consignments', 'FOV_Charge',         "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Fuel_Charge',        "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Fuel_Percent',       "decimal(5,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'CGST_Amount',        "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'SGST_Amount',        "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'IGST_Amount',        "decimal(10,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Taxable_Amount',     "decimal(12,2) DEFAULT '0.00'");
        self::safeAddColumn($db, 'tbl_consignments', 'Grand_Total',        "decimal(12,2) DEFAULT '0.00'");

        /* -----------------------------------------------------------------
         * Zone-wise pickup rates. The table only had one flat charge per
         * client; Zone_Id makes it "zone wise, automatic". Rows with
         * Zone_Id NULL stay valid and act as the client-wide fallback.
         * ----------------------------------------------------------------- */
        self::safeAddColumn($db, 'tbl_pickup_charges', 'Zone_Id', "smallint(6) DEFAULT NULL");

        /* The settings table on older installs lacks these two columns, so the
         * seeding block at the top of this method fataled on any new key. */
        self::safeAddColumn($db, 'tbl_settings', 'Description', "varchar(255) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_settings', 'Updated_At',  "timestamp NULL DEFAULT CURRENT_TIMESTAMP");

        /* Default FOV / fuel settings used by the consignment form. */
        $fovDefaults = array(
            'default_fov_percent'  => array('0.20', 'FOV (carrier risk) % of declared value'),
            'default_fov_minimum'  => array('100.00', 'Minimum FOV charge in INR'),
            'default_fuel_percent' => array('0.00', 'Default fuel surcharge % when client has none set'),
        );
        foreach ($fovDefaults as $key => $vals) {
            $exists = $db->ExecuteQuery("SELECT Setting_Id FROM tbl_settings WHERE Setting_Key='" . $db->escape($key) . "'");
            if (empty($exists)) {
                $db->query("INSERT INTO tbl_settings (Setting_Key, Setting_Value, Description) VALUES ('"
                    . $db->escape($key) . "', '" . $db->escape($vals[0]) . "', '" . $db->escape($vals[1]) . "')");
            }
        }

        /* -----------------------------------------------------------------
         * Company / courier franchise master.
         * Name, logo, address, GSTIN, PAN and the on-off flag already exist on
         * tbl_branchs; these are the remaining fields from the spec.
         * ----------------------------------------------------------------- */
        self::safeAddColumn($db, 'tbl_branchs', 'Pincode',          "varchar(10) DEFAULT NULL");
        self::safeAddColumn($db, 'tbl_branchs', 'Invoice_Start_No', "int(11) DEFAULT '1'");
        self::safeAddColumn($db, 'tbl_branchs', 'Valid_Till',       "date DEFAULT NULL");

        /* -----------------------------------------------------------------
         * Rate master charge setup - one row per company, configured once.
         * Pickup stays zone-wise in tbl_pickup_charges and ODA in
         * tbl_oda_master; this covers fuel, insurance, FOV and urgent.
         * ----------------------------------------------------------------- */
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_charge_master` (
            `Charge_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Branch_Id` smallint(6) NOT NULL,
            `Fuel_Percent` decimal(5,2) DEFAULT '0.00',
            `Insurance_Percent` decimal(5,2) DEFAULT '0.00',
            `Insurance_Minimum` decimal(10,2) DEFAULT '0.00',
            `FOV_Percent` decimal(5,2) DEFAULT '0.00',
            `FOV_Minimum` decimal(10,2) DEFAULT '0.00',
            `Urgent_Percent` decimal(5,2) DEFAULT '0.00',
            `Urgent_Minimum` decimal(10,2) DEFAULT '0.00',
            `Is_Active` tinyint(1) DEFAULT '1',
            PRIMARY KEY (`Charge_Id`),
            UNIQUE KEY `uniq_branch` (`Branch_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Seed a row per company so the setup screen always has something to edit.
        $branchRows = $db->ExecuteQuery("SELECT Branch_Id FROM tbl_branchs");
        if (!empty($branchRows)) {
            foreach ($branchRows as $br) {
                $bid = intval($br['Branch_Id']);
                $has = $db->ExecuteQuery("SELECT Charge_Id FROM tbl_charge_master WHERE Branch_Id=$bid");
                if (empty($has)) {
                    $db->query("INSERT INTO tbl_charge_master
                        (Branch_Id, Fuel_Percent, Insurance_Percent, Insurance_Minimum, FOV_Percent, FOV_Minimum, Urgent_Percent, Urgent_Minimum, Is_Active)
                        VALUES ($bid, 0.00, 2.00, 0.00, 0.20, 100.00, 0.00, 0.00, 1)");
                }
            }
        }

        /* -----------------------------------------------------------------
         * Tables the application already queries but that were never created.
         * Each one made its page fatal on open (expenses, payments made,
         * ledger, tax master, rate calculator).
         * ----------------------------------------------------------------- */

        // adminpanel/accounting/expenses.php
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_expenses` (
            `Expense_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Expense_Date` date DEFAULT NULL,
            `Category` varchar(100) DEFAULT NULL,
            `Amount` decimal(12,2) DEFAULT '0.00',
            `Payment_Mode` varchar(50) DEFAULT NULL,
            `Reference_No` varchar(100) DEFAULT NULL,
            `Description` varchar(255) DEFAULT NULL,
            `Branch_Id` smallint(6) DEFAULT NULL,
            PRIMARY KEY (`Expense_Id`),
            KEY `idx_date` (`Expense_Date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // adminpanel/accounting/payments_made.php
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_payments_made` (
            `Payment_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Payment_Date` date DEFAULT NULL,
            `Payee_Name` varchar(150) DEFAULT NULL,
            `Category` varchar(100) DEFAULT NULL,
            `Amount` decimal(12,2) DEFAULT '0.00',
            `Payment_Mode` varchar(50) DEFAULT NULL,
            `Reference_No` varchar(100) DEFAULT NULL,
            `Notes` varchar(255) DEFAULT NULL,
            `Branch_Id` smallint(6) DEFAULT NULL,
            PRIMARY KEY (`Payment_Id`),
            KEY `idx_date` (`Payment_Date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // libraries/classes/ledger_manager.php
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_customer_ledger` (
            `Ledger_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Customer_Id` int(11) NOT NULL,
            `Transaction_Date` date DEFAULT NULL,
            `Transaction_Type` varchar(50) DEFAULT NULL,
            `Reference_Id` int(11) DEFAULT '0',
            `Debit` decimal(12,2) DEFAULT '0.00',
            `Credit` decimal(12,2) DEFAULT '0.00',
            `Running_Balance` decimal(12,2) DEFAULT '0.00',
            `Notes` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`Ledger_Id`),
            KEY `idx_customer` (`Customer_Id`),
            KEY `idx_date` (`Transaction_Date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // adminpanel/masters/taxes/
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_applicable_taxes` (
            `Tax_id` int(11) NOT NULL AUTO_INCREMENT,
            `tax_type` varchar(100) NOT NULL,
            `tax_percent` decimal(5,2) DEFAULT '0.00',
            `taxpercent_ondate` date DEFAULT NULL,
            PRIMARY KEY (`Tax_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // libraries/classes/rate_calculator.php
        $db->query("CREATE TABLE IF NOT EXISTS `tbl_rate_master` (
            `Rate_Master_Id` int(11) NOT NULL AUTO_INCREMENT,
            `Customer_Id` int(11) NOT NULL,
            `Zone_Id` int(11) NOT NULL,
            `Base_Weight` decimal(10,3) DEFAULT '0.000',
            `Base_Rate` decimal(10,2) DEFAULT '0.00',
            `Additional_Weight` decimal(10,3) DEFAULT '0.000',
            `Additional_Rate` decimal(10,2) DEFAULT '0.00',
            `Volumetric_Divisor` int(11) DEFAULT '5000',
            `Fuel_Surcharge_Percent` decimal(5,2) DEFAULT '0.00',
            `FOV_Percent` decimal(5,2) DEFAULT '0.00',
            `ODA_Charge` decimal(10,2) DEFAULT '0.00',
            `Effective_From` date DEFAULT NULL,
            `Effective_To` date DEFAULT NULL,
            PRIMARY KEY (`Rate_Master_Id`),
            KEY `idx_customer_zone` (`Customer_Id`, `Zone_Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    private static function safeAddColumn($db, $tableName, $columnName, $definition) {
        $check = $db->ExecuteQuery("SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
        if (empty($check) || count($check) == 0) {
            $db->query("ALTER TABLE `$tableName` ADD `$columnName` $definition");
        }
    }
}
?>

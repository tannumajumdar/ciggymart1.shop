-- ========================================================
-- Migration for 1. PIN/Zone Mapping
-- ========================================================

-- tbl_zones already exists (Zone_Id, Zone_Code, Zone_Name). 
-- Standardize constraint to avoid duplicates:
ALTER TABLE tbl_zones ADD UNIQUE (Zone_Name);

-- Add Zone_Id to tbl_destinations (acting as PIN Code master)
ALTER TABLE tbl_destinations ADD COLUMN Zone_Id INT(11) DEFAULT NULL AFTER State_Id;
ALTER TABLE tbl_destinations ADD CONSTRAINT fk_dest_zone FOREIGN KEY (Zone_Id) REFERENCES tbl_zones(Zone_Id);


-- ========================================================
-- Migration for 2. Rate Calculation Rules
-- ========================================================

-- Assuming tbl_rate_master exists, if not we create it:
CREATE TABLE IF NOT EXISTS tbl_rate_master (
    Rate_Id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Customer_Id INT(11) NOT NULL,
    Courier_Id INT(11) DEFAULT NULL,
    Zone_Id INT(11) NOT NULL,
    Base_Weight DECIMAL(10,2) NOT NULL DEFAULT '1.00',
    Base_Rate DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    Additional_Weight DECIMAL(10,2) NOT NULL DEFAULT '1.00',
    Additional_Rate DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    Fuel_Surcharge_Percent DECIMAL(5,2) DEFAULT '0.00',
    FOV_Percent DECIMAL(5,2) DEFAULT '0.00',
    ODA_Charge DECIMAL(10,2) DEFAULT '0.00',
    Volumetric_Divisor INT(11) DEFAULT '5000',
    Effective_From DATE,
    Effective_To DATE,
    FOREIGN KEY (Zone_Id) REFERENCES tbl_zones(Zone_Id)
);


-- ========================================================
-- Migration for 3. Invoice Lifecycle
-- ========================================================

-- Add Status to invoices table
ALTER TABLE tbl_invoices ADD COLUMN Status ENUM('Draft', 'Generated', 'Partially Paid', 'Paid', 'Cancelled', 'Locked') NOT NULL DEFAULT 'Draft';


-- ========================================================
-- Migration for 4. Accounting/Ledger Logic
-- ========================================================

-- Update Customer Master
ALTER TABLE tbl_customers ADD COLUMN Credit_Limit DECIMAL(15,2) DEFAULT '0.00';
ALTER TABLE tbl_customers ADD COLUMN Credit_Days INT(11) DEFAULT '0';
ALTER TABLE tbl_customers ADD COLUMN Opening_Balance DECIMAL(15,2) DEFAULT '0.00';
ALTER TABLE tbl_customers ADD COLUMN Default_Service_Type VARCHAR(50) DEFAULT NULL;

-- Create Payment Receipts
CREATE TABLE IF NOT EXISTS tbl_payment_receipts (
    Receipt_Id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Customer_Id INT(11) NOT NULL,
    Amount_Received DECIMAL(15,2) NOT NULL,
    Payment_Mode VARCHAR(50) NOT NULL,
    Bank_Id INT(11) DEFAULT NULL,
    Reference_No VARCHAR(100) DEFAULT NULL,
    Receipt_Date DATE NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Payment Allocations (1 to many invoices)
CREATE TABLE IF NOT EXISTS tbl_payment_allocations (
    Allocation_Id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Receipt_Id INT(11) NOT NULL,
    Invoice_Id INT(11) NOT NULL,
    Allocated_Amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (Receipt_Id) REFERENCES tbl_payment_receipts(Receipt_Id)
);

-- Create Customer Ledger
CREATE TABLE IF NOT EXISTS tbl_customer_ledger (
    Ledger_Id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Customer_Id INT(11) NOT NULL,
    Transaction_Date DATE NOT NULL,
    Transaction_Type ENUM('Invoice', 'Payment', 'Adjustment', 'Opening Balance') NOT NULL,
    Reference_Id INT(11) DEFAULT NULL,
    Debit DECIMAL(15,2) DEFAULT '0.00',
    Credit DECIMAL(15,2) DEFAULT '0.00',
    Running_Balance DECIMAL(15,2) DEFAULT '0.00',
    Notes TEXT,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=PINCODE_TEMPLATE.csv');

$output = fopen('php://output', 'w');

// Set headers
fputcsv($output, array('Destination Code', 'City Name', 'State Name', 'Pincode', 'Is ODA (1/0)', 'ODA Charge', 'Door Charge'));

// Add some sample data to help user
fputcsv($output, array('BOM01', 'Mumbai', 'Maharashtra', '400001', '0', '0', '0'));
fputcsv($output, array('REM01', 'Remote Hills', 'Karnataka', '560123', '1', '250', '50'));

fclose($output);
?>

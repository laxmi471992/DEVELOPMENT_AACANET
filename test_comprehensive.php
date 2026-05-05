<?php
/**
 * ============================================================================
 * FIRM FEE CHECK DETAIL REPORT - COMPREHENSIVE TEST SCRIPT
 * ============================================================================
 *
 * @description Tests both scenarios:
 *              1. Data present → File should be created
 *              2. No data present → File should NOT be created
 *
 * @author      KEANT Technologies
 * @version     1.0
 * @created     2026-01-26
 */

// Include the updated firmfee.php script
require_once('firmfee.php');

// Mock functions for testing (replace with your actual implementations)
function getResult($query) {
    // Mock database results - modify these to test different scenarios

    // SCENARIO 1: Test with data present
    if (strpos($query, "SELECT DISTINCT PYALORGCD") !== false) {
        // Query 1: Return some client codes
        return array(
            'numRows' => 2,
            'results' => array(
                array('PYALORGCD' => 'CLIENT001'),
                array('PYALORGCD' => 'CLIENT002')
            )
        );
    }

    // SCENARIO 2: Test with no data (uncomment to test no-data scenario)
    /*
    if (strpos($query, "SELECT DISTINCT PYALORGCD") !== false) {
        // Query 1: Return no client codes
        return array(
            'numRows' => 0,
            'results' => array()
        );
    }
    */

    // Query 2: Return actual transaction data for the clients
    if (strpos($query, "WITH FIRMCOSTFEE AS") !== false) {
        return array(
            'numRows' => 3,
            'results' => array(
                array(
                    'Client Code' => 'CLIENT001',
                    'Acct No.' => '12345',
                    'Last Name' => 'Smith',
                    'First Name' => 'John',
                    'TR CD' => '50',
                    'Transaction Date' => '2026-01-20',
                    'Transaction Description' => 'Fee Payment',
                    'Payment Amount' => 1000.00,
                    'Remit Amount' => 950.00,
                    'Fee Requested By Firm' => 50.00,
                    'Fee Paid To Firm' => 45.00,
                    'Firm Invoice No' => 'INV001',
                    'Firm Check Number' => 'CHK001',
                    'Amount Paid To Firm' => 45.00,
                    'Firm Invoice Date' => '2026-01-20',
                    'Firm File No' => 'FILE001',
                    'PYALORGCD' => 'CLIENT001'
                ),
                array(
                    'Client Code' => 'CLIENT001',
                    'Acct No.' => '12346',
                    'Last Name' => 'Johnson',
                    'First Name' => 'Jane',
                    'TR CD' => '51',
                    'Transaction Date' => '2026-01-21',
                    'Transaction Description' => 'Additional Fee',
                    'Payment Amount' => 500.00,
                    'Remit Amount' => 475.00,
                    'Fee Requested By Firm' => 25.00,
                    'Fee Paid To Firm' => 22.50,
                    'Firm Invoice No' => 'INV002',
                    'Firm Check Number' => 'CHK002',
                    'Amount Paid To Firm' => 22.50,
                    'Firm Invoice Date' => '2026-01-21',
                    'Firm File No' => 'FILE002',
                    'PYALORGCD' => 'CLIENT001'
                ),
                array(
                    'Client Code' => 'CLIENT002',
                    'Acct No.' => '67890',
                    'Last Name' => 'Brown',
                    'First Name' => 'Bob',
                    'TR CD' => '52',
                    'Transaction Date' => '2026-01-22',
                    'Transaction Description' => 'Processing Fee',
                    'Payment Amount' => 750.00,
                    'Remit Amount' => 712.50,
                    'Fee Requested By Firm' => 37.50,
                    'Fee Paid To Firm' => 33.75,
                    'Firm Invoice No' => 'INV003',
                    'Firm Check Number' => 'CHK003',
                    'Amount Paid To Firm' => 33.75,
                    'Firm Invoice Date' => '2026-01-22',
                    'Firm File No' => 'FILE003',
                    'PYALORGCD' => 'CLIENT002'
                )
            )
        );
    }

    // Default empty result
    return array('numRows' => 0, 'results' => array());
}

function createDirgetCompanyName($companyPath, $reportBasePath) {
    // Mock company name resolution
    $companyMap = array(
        'COMP001' => 'TestCompany1',
        'COMP002' => 'TestCompany2'
    );

    return isset($companyMap[$companyPath]) ? $companyMap[$companyPath] : 'UnknownCompany';
}

function getCompanyStatus($companyName) {
    return 1; // Active
}

function filterReportName($date, $reportName) {
    return $reportName . '_' . $date . '.xlsx';
}

function getfileSize($filePath) {
    return filesize($filePath) / 1024; // Return KB
}

// Test scenarios
echo "=== FIRM FEE REPORT TEST SCRIPT ===\n\n";

// Test parameters
$testParams = array(
    'path' => 'COMP001,COMP002', // Test companies
    'id' => 'TEST001',
    'reportName' => 'FirmFeeTestReport',
    'code_name' => 'FEE_TEST',
    'userType' => 'admin',
    'userReportName' => 'Test Firm Fee Report',
    'outputName' => 'firm_fee_test',
    'reportDescription' => 'Test report for firm fee functionality',
    'mailNotification' => 0, // Disabled for testing
    'sftpId' => null,
    'mode' => 'test',
    'run_by' => 'test_user',
    'reportBasePath' => './test_reports/', // Local test directory
    'reportDate' => '20260120' // Specific test date
);

// Create test directory if it doesn't exist
if (!is_dir($testParams['reportBasePath'])) {
    mkdir($testParams['reportBasePath'], 0755, true);
    mkdir($testParams['reportBasePath'] . 'COMP001/', 0755, true);
    mkdir($testParams['reportBasePath'] . 'COMP002/', 0755, true);
}

echo "Test Parameters:\n";
echo "- Companies: " . $testParams['path'] . "\n";
echo "- Report Date: " . $testParams['reportDate'] . "\n";
echo "- Output Directory: " . $testParams['reportBasePath'] . "\n\n";

echo "Running Firm Fee Report Test...\n";
echo "================================\n";

// Call the main function
$result = firmFeeCheckDetailToMyDownload(
    $testParams['path'],
    $testParams['id'],
    $testParams['reportName'],
    $testParams['code_name'],
    $testParams['userType'],
    $testParams['userReportName'],
    $testParams['outputName'],
    $testParams['reportDescription'],
    $testParams['mailNotification'],
    $testParams['sftpId'],
    $testParams['mode'],
    $testParams['run_by'],
    $testParams['reportBasePath'],
    $testParams['reportDate']
);

// Display results
echo "\n=== TEST RESULTS ===\n";
echo "Status: " . ($result['status'] == 1 ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['status_msg'] . "\n";
echo "New Status: " . $result['new_status'] . "\n\n";

// Check if files were created
echo "File Creation Check:\n";
echo "===================\n";

$companies = explode(',', $testParams['path']);
foreach ($companies as $company) {
    $company = trim($company);
    $expectedFile = $testParams['reportBasePath'] . $company . '/' . $testParams['reportName'] . '_2026-01-26.xlsx';

    if (file_exists($expectedFile)) {
        $fileSize = filesize($expectedFile);
        echo "✅ File created for $company: " . basename($expectedFile) . " (" . round($fileSize/1024, 2) . " KB)\n";
    } else {
        echo "❌ No file created for $company (as expected if no data)\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";

/*
 * TO TEST "NO DATA" SCENARIO:
 * 1. Uncomment the "SCENARIO 2" block in getResult() function above
 * 2. Comment out the "SCENARIO 1" block
 * 3. Run the test again
 * 4. Verify that NO files are created
 */
?>
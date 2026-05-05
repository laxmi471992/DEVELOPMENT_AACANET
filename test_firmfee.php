<?php

require_once('PHP_XLSXWriter/xlsxwriter.class.php');

/**
 * ===========================
 * MAIN FUNCTION
 * ===========================
 */
function firmFeeCheckDetailToMyDownload($path, $reportBasePath)
{
    $fileName = "TEST_REPORT_" . date('Y-m-d') . ".xlsx";
    $paths = explode(",", $path);

    foreach ($paths as $companyPath) {

        $companyPath = trim($companyPath);

        $writer = new XLSXWriter();
        $companyName = createDirgetCompanyName($companyPath, $reportBasePath);

        echo "Processing Company: $companyName <br>";

        $hasData = false;

        // ================= QUERY 1 =================
        $query1 = "SELECT DISTINCT PYALORGCD FROM RMAACABHS";
        $results1 = getResult($query1);

        if ($results1['numRows'] > 0) {

            $headers = getExcelHeaders();
            $writer->writeSheetHeader('Sheet1', $headers);

            foreach ($results1['results'] as $result) {

                // ================= QUERY 2 =================
                $query2 = "WITH FIRMCOSTFEE AS (...)";
                $results2 = getResult($query2);

                if ($results2['numRows'] > 0) {

                    $hasData = true;

                    foreach ($results2['results'] as $row) {
                        $writer->writeSheetRow('Sheet1', $row);
                    }
                }
            }

            // CREATE FILE ONLY IF DATA EXISTS
            if ($hasData) {

                $filePath = $reportBasePath . $companyPath . '/' . $fileName;

                $writer->writeToFile($filePath);

                echo "File Created: $filePath <br>";
            } else {
                echo "No data found. File NOT created <br>";
            }

        } else {
            echo "No data in Query1 <br>";
        }
    }
}

/**
 * ===========================
 * FAKE DATA (NO DB)
 * ===========================
 */
function getResult($query)
{
    // CHANGE THIS TO TEST
    $testType = "data";  
    // "data" or "nodata"

    // QUERY 1
    if (strpos($query, "SELECT DISTINCT PYALORGCD") !== false) {

        if ($testType == "data") {
            return [
                'numRows' => 1,
                'results' => [
                    ['PYALORGCD' => 'MRAC']
                ]
            ];
        } else {
            return ['numRows' => 0, 'results' => []];
        }
    }

    // QUERY 2
    if (strpos($query, "WITH FIRMCOSTFEE") !== false) {

        if ($testType == "data") {
            return [
                'numRows' => 2,
                'results' => [
                    [
                        'MRAC','123','Patil','Amit','50','2026-01-01','Test',
                        100,80,10,8,'INV1','CHK1',90,'2026-01-01','F001','MRAC'
                    ],
                    [
                        'MRAC','124','Shah','Ravi','51','2026-01-02','Test2',
                        200,150,20,15,'INV2','CHK2',180,'2026-01-02','F002','MRAC'
                    ]
                ]
            ];
        } else {
            return ['numRows' => 0, 'results' => []];
        }
    }

    return ['numRows' => 0, 'results' => []];
}

/**
 * ===========================
 * HELPERS
 * ===========================
 */
function createDirgetCompanyName($companyPath, $basePath)
{
    if (!file_exists($basePath . $companyPath)) {
        mkdir($basePath . $companyPath, 0777, true);
    }
    return $companyPath;
}

function getExcelHeaders()
{
    return [
        'Client Code' => 'string',
        'Acct No.' => 'string',
        'Last Name' => 'string',
        'First Name' => 'string',
        'TR CD' => 'string',
        'Transaction Date' => 'string',
        'Transaction Description' => 'string',
        'Payment Amount' => 'dollar',
        'Remit Amount' => 'dollar',
        'Fee Requested By Firm' => 'dollar',
        'Fee Paid To Firm' => 'dollar',
        'Firm Invoice No' => 'string',
        'Firm Check Number' => 'string',
        'Amount Paid To Firm' => 'dollar',
        'Firm Invoice Date' => 'string',
        'Firm File No' => 'string',
        'PYALORGCD' => 'string'
    ];
}

/**
 * ===========================
 * RUN TEST
 * ===========================
 */

firmFeeCheckDetailToMyDownload(
    "DCON",
    __DIR__ . "/reports/"
);

?>
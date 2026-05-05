<?php

// ================== INCLUDE YOUR MAIN FILE ==================
// change this path to your actual file
require_once('firmfee.php');  


// ================== DUMMY REQUIRED FUNCTIONS ==================
// If these already exist in your project, REMOVE these

function filterReportName($date, $name) {
    return $name . "_" . $date . ".xlsx";
}

function createDirgetCompanyName($companyPath, $basePath) {
    if (!file_exists($basePath . $companyPath)) {
        mkdir($basePath . $companyPath, 0777, true);
    }
    return $companyPath;
}

function getCompanyStatus($companyName) {
    return true;
}

function getfileSize($file) {
    return filesize($file) / 1024;
}

// 🔴 IMPORTANT: connect your DB here
function getResult($query) {
    // $conn = new mysqli("localhost", "root", "", "your_database"); // change DB
$servername = "172.16.13.209";
$username   = "PipewayUAT";
$password   = "Waterkingdom@4321";
$database   = "aaca_live";

$conn = mysqli_connect($servername, $username, $password, $database);
   

    $result = $conn->query($query);

    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    return [
        'numRows' => count($data),
        'results' => $data
    ];
}

// ================== TEST CASE SWITCH ==================
// change this to test

$testType = "data"; 
// "data" → file should create
// "nodata" → file should NOT create

if ($testType == "data") {
    $company = "DCON";      // use real from DB
    $date    = "20260101";  // date where data exists
} else {
    $company = "DCON";
    $date    = "19000101";  // no data
}

// ================== RUN FUNCTION ==================

$result = firmFeeCheckDetailToMyDownload(
    $company,
    1,
    "TEST_REPORT",
    "",
    "",
    "",
    "",
    "",
    "",
    "",
    "",
    "",
    __DIR__ . "/reports/",   // folder will be created
    $date
);

// ================== OUTPUT ==================

echo "<pre>";
print_r($result);
echo "</pre>";

echo "<br>Check folder: /reports/$company/";

?>
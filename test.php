<?php

require_once('PHP_XLSXWriter/xlsxwriter.class.php');
require_once('firmcost.php'); // or firmFee.php

// DB connection
$servername = "172.16.13.209";
$username   = "PipewayUAT";
$password   = "Waterkingdom@4321";
$database   = "aaca_live";

$conn = mysqli_connect($servername, $username, $password, $database);

// common function
function getResult($query) {
    global $conn;
    $result = mysqli_query($conn, $query);

    return [
        'numRows' => mysqli_num_rows($result),
        'results' => mysqli_fetch_all($result, MYSQLI_ASSOC)
    ];
}

// dummy functions (required)
function filterReportName($date, $name){ return $name . "_" . $date . ".xlsx"; }
function createDirgetCompanyName($path, $base){ return $path; }
function getCompanyStatus($name){ return 1; }

// 🔥 CALL YOUR FUNCTION
// $response = firmCostCheckDetailToMyDownload(
//     "TEST", "", "TestReport", "", "", "", "", "", "", "", "", "", "/var/www/html/test/"
// );

$response = firmCostCheckDetailToMyDownload(
    "ROHN", "", "TestReport", "", "", "", "", "", "", "", "", "", "/var/www/html/bi/dist/"
);

echo "msg TEST ";
print_r($response);
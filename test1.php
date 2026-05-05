<?php

require_once('PHP_XLSXWriter/xlsxwriter.class.php');
require_once('firmfee.php');

// DB connection (important)
// Database credentials
$servername = "172.16.13.209";
$username   = "PipewayUAT";
$password   = "Waterkingdom@4321";
$database   = "aaca_live";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

function getResult($query) {
    global $conn;
    $result = mysqli_query($conn, $query);

    return [
        'numRows' => mysqli_num_rows($result),
        'results' => mysqli_fetch_all($result, MYSQLI_ASSOC)
    ];
}

// Dummy required functions (if missing)
function filterReportName($date, $name){ return $name . "_" . $date . ".xlsx"; }
function createDirgetCompanyName($path, $base){ return $path; }
function getCompanyStatus($name){ return 1; }

// CALL FUNCTION
$response = FirmFeeCheckDetailToMyDownload(
    "TEST", "", "TestReport", "", "", "", "", "", "", "", "", "", "/var/www/html/bi/dist/"
);

print_r($response);
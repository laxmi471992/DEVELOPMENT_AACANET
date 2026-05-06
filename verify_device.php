<?php
session_start();
include_once "config.php";

if(!isset($_SESSION['temp_user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['temp_user_id'];
$newIP   = $_SESSION['new_device_ip'];
$device  = $_SESSION['new_device_name'];

$msg = "";

// ================= GET USER DEVICES =================
$query = "SELECT IPaddress FROM tbl_login WHERE id=$user_id";
$result = mysqli_query($conn,$query);
$row = mysqli_fetch_assoc($result);

$currentIPs = $row['IPaddress'] ?? '';
$ipList = !empty($currentIPs) ? explode(',', $currentIPs) : [];
$deviceCount = count($ipList);


// ================= VERIFY OTP =================
if(isset($_POST['verify_otp'])){

    // 🔁 ALWAYS RECHECK FROM DB
    $check = mysqli_query($conn, "SELECT IPaddress FROM tbl_login WHERE id=$user_id");
    $data  = mysqli_fetch_assoc($check);

    $latestList   = !empty($data['IPaddress']) ? explode(',', $data['IPaddress']) : [];
    $currentCount = count($latestList);

    // 🚫 BLOCK if limit reached but no device selected
    if($currentCount >= 5 && !isset($_SESSION['remove_index'])){
        $msg = "Please select a device to remove first";
    }

    // ✅ OTP VALIDATION
    else if(
        isset($_SESSION['otp']) &&
        $_POST['otp'] == $_SESSION['otp'] &&
        (time() - $_SESSION['otp_time']) < 300
    ){

        $browser = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT']);
        $token   = bin2hex(random_bytes(16));

        // ===== CASE 1: ADD NEW DEVICE =====
        if($currentCount < 5){

            $deviceLabel = trim($_POST['device_label']);
            if($deviceLabel == "") $deviceLabel = "New Device";

            $newEntry = $newIP . "|" . $deviceLabel . "|" . $browser . "|" . $token;

            $updatedIPs = !empty($latestList)
                ? implode(',', $latestList) . "," . $newEntry
                : $newEntry;
        }

        // ===== CASE 2: REPLACE DEVICE =====
        else{

            $removeIndex = $_SESSION['remove_index'];

            if(isset($latestList[$removeIndex])){
                unset($latestList[$removeIndex]);
                $latestList = array_values($latestList);
            }

            $updatedIPs = implode(',', $latestList);

            $newEntry = $newIP . "|New Device|" . $browser . "|" . $token;

            $updatedIPs = !empty($updatedIPs)
                ? $updatedIPs . "," . $newEntry
                : $newEntry;
        }

        // ✅ UPDATE DB
        mysqli_query($conn,"UPDATE tbl_login 
            SET IPaddress='".$updatedIPs."' 
            WHERE id=".$user_id);

        // ✅ SAVE TOKEN IN COOKIE
        setcookie("device_token", $token, time() + (86400 * 30), "/");

        // ✅ CLEAR SESSION
        unset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['remove_index']);
        unset($_SESSION['temp_user_id'], $_SESSION['new_device_ip'], $_SESSION['new_device_name']);

        header("Location: inventory_layout");
        exit();

    } else {
        $msg = "Invalid or Expired OTP!";
    }
}


// ================= SELECT DEVICE =================
if(isset($_POST['select_device'])){
        if(!isset($_POST['device_index'])){
        $msg = "Please select a device";
    } else {
        $_SESSION['remove_index'] = (int)$_POST['device_index'];
    }

}


// ================= REJECT =================
if(isset($_POST['reject'])){
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify Device</title>
<style>
body{font-family:Arial;background:#f2f2f2;text-align:center;padding-top:80px;}
.box{background:#fff;padding:30px;width:420px;margin:auto;border-radius:10px;box-shadow:0px 0px 10px #ccc;}
button{padding:10px 20px;margin:10px;border:none;border-radius:5px;cursor:pointer;}
.approve{background:green;color:#fff;}
.reject{background:red;color:#fff;}
</style>
</head>

<body>

<div class="box">

<h2>🔐 Device Verification</h2>

<p><b>Device:</b> <?php echo htmlspecialchars($device); ?></p>
<p><b>IP:</b> <?php echo htmlspecialchars($newIP); ?></p>

<?php if(count($ipList) < 5) { ?>

    <!-- ✅ NORMAL FLOW -->
    <form method="post">
        <input type="text" name="device_label" placeholder="Device Name" required><br><br>
        <input type="text" name="otp" placeholder="Enter OTP" required><br>
        <button type="submit" name="verify_otp" class="approve">Save & Login</button>
    </form>

<?php } else { ?>

    <!-- ❌ LIMIT REACHED -->
    <h3 style="color:red;">Device limit reached (5)</h3>

    <?php if(!isset($_SESSION['remove_index'])){ ?>

        <!-- STEP 1: SELECT DEVICE -->
        <form method="post">
            <p>Select device to remove:</p>

            <?php foreach($ipList as $index => $entry){
                $parts = explode('|', $entry);
            ?>
                <input type="radio" name="device_index" value="<?php echo $index; ?>" required>
                <?php echo htmlspecialchars($parts[1] ?? 'Unknown Device'); ?>
                <br>
            <?php } ?>

            <button type="submit" name="select_device">Continue</button>
        </form>

    <?php } else { ?>

        <!-- STEP 2: OTP -->
        <form method="post">
            <p>Enter OTP to replace device:</p>
            <input type="text" name="otp" placeholder="Enter OTP" required><br>
            <button type="submit" name="verify_otp" class="approve">Verify & Replace</button>
        </form>

    <?php } ?>

<?php } ?>

<form method="post">
    <button type="submit" name="reject" class="reject">Cancel</button>
</form>

<?php if(!empty($msg)){ ?>
    <p style="color:red;"><?php echo $msg; ?></p>
<?php } ?>

<!-- 🔵 TESTING ONLY -->
<?php if(isset($_SESSION['otp'])){ ?>
    <p style="color:blue;">OTP: <?php echo $_SESSION['otp']; ?></p>
<?php } ?>

</div>

</body>
</html>
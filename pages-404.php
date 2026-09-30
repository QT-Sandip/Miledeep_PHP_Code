<?php
include "includes/config.php";
$PageTitle = "Schedule";
$PageTitleFolder = "HRM";
$PageTitleLink = "home";

$ErrorUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$AccountId = $_SESSION["r_id"];
$TimeZone = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$CurrentDateTime = $TimeZone->format("Y-m-d  H:i:s");

$SqlErrorPage = "INSERT INTO `AdminPageErrors`(`UserID`, `DateTime`, `Url`) VALUES 
($AccountId,'$CurrentDateTime','$ErrorUrl')";
$Query = $iconn->query($SqlErrorPage);


?>

<!doctype html>
<html lang="en">

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body>

    <div class="my-5 pt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center my-5">
                        <h1 class="font-weight-bold text-error">4 <span class="error-text">0<img
                                    src="<?php echo $BaseDist ?>assets/images/error-img.png" alt=""
                                    class="error-img"></span> 4</h1>
                        <h3 class="text-uppercase">Sorry,Requested Page not found.</h3>
                        <div class="mt-5 text-center">
                            <a class="btn btn-primary waves-effect waves-light"
                                href="<?php echo $_SESSION["ClientPath"] ?>">Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include($IncludesPath . "vendor-scripts.php"); ?>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

</body>

</html>
<?php
include "includes/config.php";
$PageTitle="Schedule";
$PageTitleFolder="HRM";
$PageTitleLink="home";

$Days=base64_decode($_GET['Q']);

$ErrorUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$AccountId=$_SESSION["r_id"];
$TimeZone = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
$CurrentDateTime=$TimeZone->format("Y-m-d  H:i:s");

$SqlErrorPage = "INSERT INTO `AdminPageErrors`(`UserID`, `DateTime`, `Url`) VALUES 
($AccountId,'$CurrentDateTime','$ErrorUrl')";
$Query = $iconn->query($SqlErrorPage);


?>

<!doctype html>
<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		


            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body>

        <div class="my-5 pt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center my-5">
                            
                            <h3 class="text-uppercase">Service Will be  Expire With In <span class="error-text"> <?php echo $Days ?> Days</span>  Please Clear ReNew.</h3>
                            <div class="mt-5 text-center">
                                <a class="btn btn-primary waves-effect waves-light" href="<?php echo $_SESSION["ClientPath"] ?>">Continue</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include ($IncludesPath."vendor-scripts.php");?>

        <script src="<?php echo $BaseDist?>assets/js/app.js"></script>

    </body>
</html>

<style>
label {
    font-size: 15px;
    color: black;
}
html, body {
    max-width: 100%;
    overflow-x: hidden;
}
</style>



<!-- Bootstrap Css -->
<link href="<?php echo $BaseDist ?>assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="<?php echo $BaseDist ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="<?php echo $BaseDist ?>assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
<?php
if($_SESSION["ClientID"]!='SLS20210044'){  ?>
 <!-- <link rel="stylesheet" href="https://serp.silverlights.in/partials/templateStyles.css"> -->
 <link rel="stylesheet" href="<?php echo $BaseUrl ?>/v2/css/templateStyles.css">
<?php } ?>
<!-- <link href="https://serp.silverlights.in/Clients/DFL/Sales/styles/custom.css" rel="stylesheet" type="text/css" /> -->
 <!-- <script src="https://cdn.tailwindcss.com"></script> -->
<!-- Tailwind Css-->
<link href="<?php echo $BaseDist ?>assets/css/tailwind.css?=<?php echo rand() ?>" id="app-style" rel="stylesheet" type="text/css" />
<link href="<?php echo $BaseUrl ?>/v2/css/custom.css?<?php echo rand() ?>" rel="stylesheet" type="text/css" />
<?php
$enableui = [];
$enableui['v3/ColdStore/'] = 'v3/ColdStore/';
$enableui['v3/Purchase/'] = 'v3/Purchase/';
$enableui['v3/QualityControl/'] = 'v3/QualityControl/';
$enableui['v3/PreProcessing/'] = 'v3/PreProcessing/';
$enableui['v3/Production/'] = 'v3/Production/';
$enableui['v3/Sales/'] = 'v3/Sales/';
if( $enableui[$_SESSION['IncludePath']] == $_SESSION['IncludePath'] ){
    $cs_currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (
            $cs_currentPath !== '/v3/Purchase/Reports/T1/commission-bill-page1' &&
            $cs_currentPath !== '/v3/Purchase/Reports/T1/commission-bill-page2' &&
            $cs_currentPath !== '/v3/Purchase/Reports/T1/bank-payment-voucher'  &&
            $cs_currentPath !== '/v3/QualityControl/Reports/shimp-two-report' &&
            $cs_currentPath !== '/auth-login' &&
            $cs_currentPath !== '/'
        ) {



    ?>
    <link href="<?php echo $BaseUrl ?>/v3/assets/css/purchase-coldstore-theme.css?=<?php echo rand() ?>" rel="stylesheet" type="text/css" />
<?php } }

?>

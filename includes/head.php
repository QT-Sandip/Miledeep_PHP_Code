<meta charset="utf-8" />
<title><?php echo $_SESSION["ClientName"] ?> </title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="SilverLights - <?php echo $_SESSION["ClientName"] ?>" name="description" />

<!-- App favicon -->
<!-- <link rel="shortcut icon" href="<?php echo ROOT_URL ?>assets/images/silverlight_logo.png"> -->
<link rel="shortcut icon" href="<?php echo $AppLogo ?>">



<!-- jquery.vectormap css -->
<link href="<?php echo ROOT_URL ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css"
    rel="stylesheet" type="text/css" />

<!-- DataTables -->
<link href="<?php echo ROOT_URL ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet"
    type="text/css" />
<link href="<?php echo ROOT_URL ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
    rel="stylesheet" type="text/css" />
<link href="<?php echo ROOT_URL ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet"
    type="text/css" />

<!-- Responsive datatable examples -->
<link href="<?php echo ROOT_URL ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
    rel="stylesheet" type="text/css" />

<!-- Bootstrap Css -->
<link href="<?php echo ROOT_URL ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="<?php echo ROOT_URL ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />

<link href="<?php echo ROOT_URL ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css">

<!-- App Css-->
<link href="<?php echo ROOT_URL ?>assets/css/app.min.css" rel="stylesheet" type="text/css" />

<link href="<?php echo ROOT_URL ?>assets/css/topmenu.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ROOT_URL ?>assets/css/newstyle.css?<?php echo rand() ?>" rel="stylesheet" type="text/css" />
 <!-- <script src="https://cdn.tailwindcss.com"></script> -->
<!-- Tailwind Css-->
<link href="<?php echo ROOT_URL ?>/assetsv2/assets/css/tailwind.css?=<?php echo rand() ?>" id="app-style" rel="stylesheet" type="text/css" />
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
        $cs_currentPath !== '/v3/Purchase/Reports/T1/bank-payment-voucher' &&
        $cs_currentPath !== '/v3/QualityControl/Reports/shimp-two-report' &&
            $cs_currentPath !== '/auth-login' &&
            $cs_currentPath !== '/'
    ) {


    ?>
    <link href="<?php echo $BaseUrl ?>/v3/assets/css/purchase-coldstore-theme.css?=<?php echo rand() ?>" rel="stylesheet" type="text/css" />
<?php } }

?>

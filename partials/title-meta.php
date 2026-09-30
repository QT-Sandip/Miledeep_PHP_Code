<?php 
if($_SESSION["ClientName"] ==''){
    $title="ERP Software For Fisheries and Seafood - Camaroo ERP";
}else{
    $title=$_SESSION["ClientName"];
}
?>
<meta charset="utf-8" />
<title><?php echo $title ?> </title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="SilverLights - <?php  echo $_SESSION["ClientName"] ?>" name="description" />
<link rel="shortcut icon" href="<?php echo $AppLogo ?>">
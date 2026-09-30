<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	
 $ClientData= $_POST['ClientData'];
 
 //print_r($ClientData);

 $Data=$ClientData['Data'];
 $ClientID= $Data['ClientID'];
 $PurchaseCode= $Data['PurchaseCode'];
 $Name= $Data['Name'];
 $ShortName= $Data['ShortName'];
 $Address= $Data['Address'];
 $LogoUrl= $Data['LogoUrl'];
 $BGImageUrl= $Data['BGImageUrl'];
 $ContactPerson= $Data['ContactPerson'];
 $MailID= $Data['MailID'];
 $PhoneNumber= $Data['PhoneNumber'];
 $InstallationDate= $Data['InstallationDate'];
 $TrailStartDate= $Data['TrailStartDate'];
 $TrailEndDate= $Data['TrailEndDate'];
 $OpearationMode= $Data['OpearationMode'];
 $ExpiryDate= $Data['ExpiryDate'];
 $AppLogoUrl= $Data['AppLogoUrl'];
 $ThemeUrl= $Data['ThemeUrl'];
 
 
 $SqlValidate="INSERT INTO `AdminClientDetails` ( `PurchaseCode`, `Name`, `ShortName`, 
`Address`, `LogoUrl`, `BGImageUrl`, `ContactPerson`, `MailID`, `PhoneNumber`, `InstallationDate`, 
`TrailStartDate`, `TrailEndDate`, `OpearationMode`, `ExpiryDate`,`AppLogoUrl`,`ThemeUrl`) 
VALUES ('$PurchaseCode', '$Name', '$ShortName', 
'$Address', '$LogoUrl', '$BGImageUrl', '$ContactPerson', '$MailID', '$PhoneNumber', '$InstallationDate', 
'$TrailStartDate', '$TrailEndDate', '$OpearationMode', '$ExpiryDate','$AppLogoUrl','$ThemeUrl')"; 
$StmtValidate=$iconn->query($SqlValidate);

echo "Success";

 
 
?>

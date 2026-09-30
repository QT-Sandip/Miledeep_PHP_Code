<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$Buyer = trim($_POST['Buyer']);
$BrandsFilter='';
$CountryFilter='';
$BrandsFilter.= "<option value=''>Select Brand</option>";
$CountryFilter.= "<option value=''>Select Country</option>";

if($Buyer!=''){
 $SqlLate="SELECT Brand FROM `ScrmPoGradeData` WHERE InvId in (SELECT PoID FROM `ScrmPoData` WHERE ConsigneeID=$Buyer) Group by Brand order by Brand asc";
}else{
    $SqlLate="SELECT Brand FROM `ScrmPoGradeData` Group by Brand order by Brand asc";  
}
$StmtLate=$iconn->query($SqlLate);
while($rowData=$StmtLate->fetch_array()) {
    $Brand=$rowData['Brand'];
    if($Brand==''){
        $Brand='NA';
    }
   
    $BrandsFilter.= '<option value="'.$Brand.'">'.$Brand.'</option>';
}

if($Buyer!=''){
$SqlLate="SELECT id,name as CountryName FROM `ScrmCountriesList` 
WHERE id in (SELECT DestinationCountry FROM `ScrmPoData` WHERE ConsigneeID=$Buyer) Group by CountryName order by CountryName asc;";
}else{
$SqlLate="SELECT id,name as CountryName FROM `ScrmCountriesList` 
    WHERE id in (SELECT DestinationCountry FROM `ScrmPoData` ) Group by CountryName order by CountryName asc;"; 
}
$StmtLate=$iconn->query($SqlLate);
while($rowData=$StmtLate->fetch_array()) {
    $CountryID=$rowData['id'];
    $CountryName=$rowData['CountryName'];

    $CountryFilter.= "<option value=$CountryID>$CountryName</option>";

}

$Data['Brand']=$BrandsFilter;
$Data['Country']=$CountryFilter;

echo json_encode($Data);
	
?>
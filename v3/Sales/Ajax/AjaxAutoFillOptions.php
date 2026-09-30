<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


$Type = $_POST['Type'];
$PoId = $_POST['PoId'];


if ($Type == 'VARIETY') {
	$Variety = $_POST['Variety'];

	$sql = "SELECT * FROM `ScrmPIVarietyList` as  SPV
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	left join ScrmSpeciesMaster as  SSM on SSM.ShortCode =SPV.Species
	left join ScrmPreparationMaster as  SPM on SPM.ShortCode =SPV.PreparationType
	WHERE VarietyID='$Variety'";
	$stmt = $iconn->query($sql);
	$Row = $stmt->fetch_array();
	$Data = $stmt->fetch_array();




	$ProductDescription = checkEmpty($Row['ProductDescription']);
	$SpecDescription = checkEmpty($Row['Description']);
	$PreparationName = checkEmpty($Row['PreparationName']);
	$TypeProduct = checkEmpty($Row['TypeProduct']);
	$ProductFormm = checkEmpty($Row['FullName']);
	$FreezingTypee = checkEmpty($Row['FreezingType']);
	$PreparationTypee = checkEmpty($Row['PreparationType']);
	$GlazePercc = checkEmpty($Row['GlazePerc']);
	$Glazee = checkEmpty($Row['Glaze']);
	$Treatmentt = checkEmpty($Row['Treatment']);
	$Grade = checkEmpty($Row['Grade']);
	$PackingStyle = checkEmpty($Row['PackingStyle']);
	$BrandName = checkEmpty($Row['BrandName']);
	$PreparationDesc = checkEmpty($Row['PreparationDesc']);
	$DeviSUPC = checkEmpty($Row['DeviSUPC']);
	$BuyerProdDescription = checkEmpty($Row['BuyerProdDescription']);

	$HasSauce = $Row['HasSauce'];
	$PackingStyleID = $Row['PCKID'];
	$TotalSlabs = $Row['TotalSlabs'];
	$SlabWeight = $Row['SlabWeight'];

	if ($GlazePercc > 0) {
		$GDesc = '(' . $GlazePercc . '% GLAZE,' . $Glazee . ',' . $Treatmentt . ')';
	} else {
		$GDesc = '(' . $Glazee . ',' . $Treatmentt . ')';
	}
	$GDesc = '';


	$Description = $Grade . '-' . $PreparationDesc . $ProductFormm . $FreezingTypee . $GlazePercc . '%' . $Glazee . $Treatmentt . '-' . $PackingStyle . $BrandName;
	// $VarietyDescription=$DeviSUPC.'-'.$Grade.$PackingStyle.$ProductDescription .$FreezingTypee.$GlazePercc. '%' .$Glazee.$Treatmentt.$BrandName;
	$VarietyDescription = $DeviSUPC . '-' . $Grade . ' - ' . $PackingStyle . ' - ' . $BrandName;

	if (trim($BuyerProdDescription) == '') {
		$DisplayName = $PreparationDesc . $SpecDescription . $ProductFormm . $FreezingTypee . ' SHRIMPS' . $GDesc;
	} else {
		$DisplayName = $BuyerProdDescription;
	}
	//$DisplayName=$PreparationDesc .$ProductFormm;
	$BuyerDescription = $BuyerProdDescription;


	$Data['FullName'] = $DisplayName;
	$Data['Description'] = $Description;
	$Data['VarietyDescription'] = $VarietyDescription;
	$Data['Variety'] = $Variety;
	$Data['BuyerDescription'] = $BuyerDescription;
	$Data['ProductDescription'] = "(" . $DeviSUPC . "-" . $ProductDescription . ")";
	$Data['Grade'] = $Grade;
	$Data['PackingStyle'] = $PackingStyle;
	$Data['BrandName'] = $BrandName;
	$Data['HasSauce'] = $HasSauce;
	$Data['PackingStyleID'] = $PackingStyleID;
	$Data['TotalSlabs'] = $TotalSlabs;
	$Data['SlabWeight'] = $SlabWeight;


	echo json_encode($Data, true);
	//echo $Data;


}


function checkEmpty($Value)
{
	if ($Value == '') {
		$Value = '';
	} else {
		$Value = ' ' . $Value;
	}
	return $Value;
}

?>
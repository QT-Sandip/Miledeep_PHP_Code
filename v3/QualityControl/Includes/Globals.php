<?php
//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


function getCountryName($Country)
{

	if ($Country == '') {
		$Country = 'GENERAL';
	} else {
		$Country;
	}

	return $Country;
}

function getVarityName($PoGradeID, $iconn)
{
	global $TblQcPoVarityList;
	//  $sql="SELECT * FROM ScrmPIVarietyList  WHERE VarietyID='$ID'"; 
	$sql = "SELECT Variety,QcDescription FROM `ScrmPoGradeData` WHERE ID='$PoGradeID'";

	$stmt_REP = $iconn->query($sql);
	$row = $stmt_REP->fetch_array();


	$name = $row['QcDescription'] ? $row['QcDescription'] : $row['Variety'];

	return $name;
}


function getPriceDetails($PoID, $QtyUnit, $iconn)
{

	$SqlQtyUnit = "SELECT QtyUnit from ScrmPoData WHERE PoID='$PoID'";
	$StmtQtyUnit = $iconn->query($SqlQtyUnit);
	$RowQtyUnit = $StmtQtyUnit->fetch_assoc();
	$QtyUnit = $RowQtyUnit['QtyUnit'];

	$Qry = "WHERE InvId in ($PoID)";
	$PriceQry = 'Price';
	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
			sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
			sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
			sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as FrozenWeightKgs,
			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as FrozenWeightLbs";

	$sqlQuery = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
		$PriceQry ,			
		sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
		sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
		sum(Mcs*TotalSlabs*Price ) as PriceBags
		FROM 
		`ScrmPoGradeData` 
		as PGD left join AdminPackingStyles as PS 
		on PGD.PackingStyleID=PS.PCKID $Qry";


	$stmt = $iconn->query($sqlQuery);
	$dataRow = $stmt->fetch_array();

	$ItemData['NetWeightKGS'] = $dataRow['NetWgtKgs'];
	$ItemData['NetWeightLBS'] = $dataRow['NetWgtLbs'];
	$ItemData['GrossWeightKGS'] = $dataRow['GrossWeightKgs'];
	$ItemData['GrossWeightLBS'] = $dataRow['GrossWeightLbs'];
	$ItemData['FrozenWeightKgs'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
	$ItemData['FrozenWeightLbs'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];
	if ($QtyUnit == 'KGS') {
		$ItemData['InvoiceValue'] = round($dataRow['PriceKgs'], 2);
	} else {
		$ItemData['InvoiceValue'] = round($dataRow['PriceLbs'], 2);
	}

	if ($QtyUnit == 'KGS') {
		$NetWgtKgs = $dataRow['NetWgtKgs'];
		$GrossWeightKgs = $dataRow['GrossWeightKgs'];
	} else {
		$NetWgtKgs = $dataRow['NetWgtLbs'] * 0.454;
		$GrossWeightKgs = $dataRow['GrossWeightLbs'] * 0.454;
		$NetWgtLbs = $dataRow['NetWgtLbs'];
		$GrossWeightLbs = $dataRow['GrossWeightLbs'];

	}

	$ItemData['NetWeightKGS'] = $NetWgtKgs;
	$ItemData['NetWeightLBS'] = $NetWgtLbs;
	$ItemData['GrossWeightKGS'] = $GrossWeightKgs;
	$ItemData['GrossWeightLBS'] = $GrossWeightLbs;

	return $ItemData;
}

function getPortDetails($PortID, $iconn)
{
	$sqlFields = " Name,PortCode,CountryCode";

	$sqlQuery = "SELECT $sqlFields  FROM `ScrmPortCities` where PortId ='$PortID'";
	$stmtExec = $iconn->query($sqlQuery);
	if ($stmtExec) {
		$dataRow = $stmtExec->fetch_array();

		$ItemData['PortName'] = strtoupper($dataRow['Name']);
		$ItemData['PortCode'] = strtoupper($dataRow['PortCode']);
		$ItemData['CountryCode'] = strtoupper($dataRow['CountryCode']);

		return $ItemData;
	}

}


function getCountryDetails($CountryID, $iconn)
{
	$sqlFields = " name";//GstCountryCode Not in Table mistake Priya 

	$sqlQuery = "SELECT $sqlFields  FROM `ScrmCountriesList` where id ='$CountryID'";
	$stmtExec = $iconn->query($sqlQuery);
	if ($stmtExec) {
		$dataRow = $stmtExec->fetch_array();

		$ItemData['CountryName'] = $dataRow['name'];
		$ItemData['CountryGstCode'] = $dataRow['GstCountryCode'];

		return $ItemData;
	}

}



function getPoDetails($INV_DATA_ARY, $iconn)
{

	global $TblQcPoVarityList;
	global $TblQcCodeList;
	global $TblAdminPackingStyles;
	global $TblScrmConsigneeDetails;


	$ExpID = $INV_DATA_ARY['ExporterID'];
	$PoID = $INV_DATA_ARY['PoID'];
	$ExporterData = getExporterDetails($ExpID);
	$ConsigneeID = $INV_DATA_ARY['ConsigneeID'];
	$INVOICE_ID = $INV_DATA_ARY['PoID'];

	//$InvNO = getInvoiceNumber($iconn, $ExpID, $PoID);
	$InvNO = $INV_DATA_ARY['CustomsInvNo'];



	$SqlConDetails = "SELECT * FROM ScrmConsigneeDetails  WHERE `ConID`='$ConsigneeID'";
	$StmtConDetails = $iconn->query($SqlConDetails);
	$ConData = $StmtConDetails->fetch_array();
	$ConsigneeName = $ConData['ConsigneeName'];

	$sql = "SELECT Variety,QcDescription FROM `ScrmPoGradeData` WHERE  `InvID`='$INVOICE_ID'";
	$stmt_po_v_list = $iconn->query($sql);

	while ($row = $stmt_po_v_list->fetch_array()) {
		$name = $row['QcDescription'] ? $row['QcDescription'] : $row['Variety'];
		$AryVariety[] = $name;
	}

	$AryVariety = array_unique($AryVariety);
	$FULL_NAME = implode('<br>', $AryVariety);


	$SQL_CODE_LIST = "SELECT * FROM QcCodeList as Qcl
	left join AdminPackingStyles as Aps On Qcl.PackingStyleID=Aps.PCKID
	LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=Qcl.Brand
	where InvID='$INVOICE_ID'";
	$STMT_CODE_LIST = $iconn->query($SQL_CODE_LIST);

	while ($CODE = $STMT_CODE_LIST->fetch_array()) {

		//ROBO
		$Brand = trim($CODE['Brand']);
		$PrintStatus = trim($CODE['PrintStatus']);

		if ($Brand != '' && $PrintStatus != 0) {
			$AryBrands[] = $Brand;
		}

		$AryPackings[] = $CODE['PackingStyle'];
		//$AryBrands[] = trim($CODE['Brand']);
	}

	$AryPackings = array_unique($AryPackings);
	$AryBrands = array_unique($AryBrands);
	$PACKING = implode(',', $AryPackings);
	$Brands = implode(',', $AryBrands);

	if ($Brands == '') {
		$Brands = '--';
	}

	$PO_TIME_STAMP = strtotime($INV_DATA_ARY['CInvDate']);
	//$PO_TIME_STAMP = strtotime($INV_DATA_ARY['InvDate']);
	$INVOICE_DATE = !empty($PO_TIME_STAMP) ? ' & ' . date("d-m-Y", $PO_TIME_STAMP) : '';


	$PO_TIME_STAMP = strtotime($INV_DATA_ARY['PoDate']);
	$PO_DATE = date("d-m-Y", $PO_TIME_STAMP);


	$header_size = 9;
	$size = 30;
	$size2 = 30;
	$size3 = 40;
	$content = '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>BUYER</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $ConsigneeName . '</b></font></th>
                <th  align="left" width="' . ($size3) . '%"><font size="' . $header_size . '"><b>VARIETY: <br>' . $FULL_NAME . '</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="center"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>PO NO & Date</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $INV_DATA_ARY['PoNo'] . ' Date' . $PO_DATE . '</b></font></th>
                <th  align="left" width="' . ($size3) . '%"><font size="' . $header_size . '"><b>PACKING : ' . $PACKING . '</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="center"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>INVOICE NO & DATE</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $InvNO . $INVOICE_DATE . '</b></font></th>
                <th  align="left" width="' . ($size3) . '%"><font size="' . $header_size . '"><b>BRAND:' . $Brands . '</b></font></th>
				</tr>
				</table>';
	return $content;

}
function getPoInformation($INV_DATA_ARY, $iconn)
{

	global $TblQcPoVarityList;
	global $TblQcCodeList;
	global $TblAdminPackingStyles;
	global $TblScrmConsigneeDetails;


	$ExpID = $INV_DATA_ARY['ExporterID'];
	$PoID = $INV_DATA_ARY['PoID'];
	$ExporterData = getExporterDetails($ExpID);
	$ConsigneeID = $INV_DATA_ARY['ConsigneeID'];
	$INVOICE_ID = $INV_DATA_ARY['PoID'];

	//$InvNO = getInvoiceNumber($iconn, $ExpID, $PoID);
	$InvNO = $INV_DATA_ARY['CustomsInvNo'];



	$SqlConDetails = "SELECT * FROM ScrmConsigneeDetails  WHERE `ConID`='$ConsigneeID'";
	$StmtConDetails = $iconn->query($SqlConDetails);
	$ConData = $StmtConDetails->fetch_array();
	$ConsigneeName = $ConData['ConsigneeName'];

	$sql = "SELECT Variety,QcDescription FROM `ScrmPoGradeData` WHERE  `InvID`='$INVOICE_ID'";
	$stmt_po_v_list = $iconn->query($sql);

	while ($row = $stmt_po_v_list->fetch_array()) {
		$name = $row['QcDescription'] ? $row['QcDescription'] : $row['Variety'];
		$AryVariety[] = $name;
	}

	$AryVariety = array_unique($AryVariety);
	$FULL_NAME = implode('<br>', $AryVariety);


	$SQL_CODE_LIST = "SELECT * FROM QcCodeList as Qcl
	left join AdminPackingStyles as Aps On Qcl.PackingStyleID=Aps.PCKID
	LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=Qcl.Brand
	where InvID='$INVOICE_ID'";
	$STMT_CODE_LIST = $iconn->query($SQL_CODE_LIST);

	while ($CODE = $STMT_CODE_LIST->fetch_array()) {

		//ROBO
		$Brand = trim($CODE['Brand']);
		$PrintStatus = trim($CODE['PrintStatus']);

		// if ($Brand != '' && $PrintStatus != 0) {
		if ($Brand != '') {
			$AryBrands[] = $Brand;
		}

		$AryPackings[] = $CODE['PackingStyle'];
		//$AryBrands[] = trim($CODE['Brand']);
	}

	$AryPackings = array_unique($AryPackings);
	$AryBrands = array_unique($AryBrands);
	$PACKING = implode(',', $AryPackings);
	$Brands = implode(',', $AryBrands);

	if ($Brands == '') {
		$Brands = '--';
	}

	$PO_TIME_STAMP = strtotime($INV_DATA_ARY['CInvDate']);
	//$PO_TIME_STAMP = strtotime($INV_DATA_ARY['InvDate']);
	$INVOICE_DATE = !empty($PO_TIME_STAMP) ? ' & ' . date("d-m-Y", $PO_TIME_STAMP) : '';


	$PO_TIME_STAMP = strtotime($INV_DATA_ARY['PoDate']);
	$PO_DATE = date("d-m-Y", $PO_TIME_STAMP);


	$header_size = 9;
	$size = 30;
	$size2 = 30;
	$size3 = 40;
	$content = '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>BUYER</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $ConsigneeName . '</b></font></th>
                <th  align="left" width="' . ($size3) . '%"><font size="' . $header_size . '"><b>VARIETY: <br>' . $FULL_NAME . '</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="center"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>PO NO<!-- & Date--></b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $INV_DATA_ARY['PoNo'] . '<!-- Date' . $PO_DATE . '--></b></font></th>
                <th  align="left" width="' . ($size3) . '%"><font size="' . $header_size . '"><b>PACKING : ' . $PACKING . '</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="center"  width="' . ($size) . '%"><font size="' . $header_size . '"><b>INVOICE NO & DATE</b></font></th>
                <th  align="left" width="' . ($size2) . '%"><font size="' . $header_size . '"><b>' . $InvNO . $INVOICE_DATE . '</b></font></th>
                <th  align="left" width="' . ($size3) . '%"><font size="' . $header_size . '"><b>BRAND:' . $Brands . '</b></font></th>
				</tr>
				</table>';
	return $content;

}


function getTotals($PoID, $iconn, $T_GRADES_ARY, $size)
{

	// global $TblQcCodeList;

	$SqlCodeList = "SELECT sum(McsTotal) as TotalMcs FROM `QcCodeList` WHERE InvID='$PoID'";
	$StmtCodeList = $iconn->query($SqlCodeList);
	$RowData = $StmtCodeList->fetch_assoc();
	$TotalMcs = $RowData['TotalMcs'];

	//exit;	

	// $size = count($T_GRADES_ARY);
	// $size = 100 / $size;
	// $size = 15;
	$header_size = 9;

	$contents .= '<td align="center" width="' . $size . '%"  ><font size="' . $header_size . '"><b>' . $TotalMcs . '</b></font></td>';


	return $contents;

}


function getRowWidth($T_GRADES_ARY, $HEADERS_COUNT)
{
	$size = count($T_GRADES_ARY) + $HEADERS_COUNT;
	$size = 100 / $size;
	return $size;
}

/*
function getRowWidth($T_GRADES_ARY){ 
 
	 $size=count($T_GRADES_ARY)+5;
	 $size=100/$size;	
	 $t_width=0;
		
		foreach($T_GRADES_ARY as $value){
					$t_width=$t_width+$size;
		}		
		return $t_width;
}*/

function getExporterDetails($Id)
{
	global $iconn;
	global $TblAdminExporterDetails;

	$TblAdminExporterDetails;
	$sql_exp = "SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$Id'";
	$stmt_exp = $iconn->query($sql_exp);
	//$row_exp=$stmt_exp->fetch_array();
	return $row_exp = $stmt_exp->fetch_array();


}

function getConsigneeDetails($ConID, $iconn)
{

	$SqlConDetails = "SELECT * FROM ScrmConsigneeDetails  WHERE `ConID`='$ConID'";
	$StmtConDetails = $iconn->query($SqlConDetails);
	$ConData = $StmtConDetails->fetch_array();
	$ConsigneeName['ConsigneeName'] = $ConData['ConsigneeName'];
	;
	return $ConsigneeName;


}


function getInvoiceNumber($iconn, $ExpID, $PoID)
{

	$CustomInvoiceSql = "SELECT CustomsInvNo FROM ScrmCustomInvoices  WHERE PiID='$PoID'";
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();

	return $CustomInvoiceRow['CustomsInvNo'];


}

function getDayCodeDate($Day_Code)
{
	//$Day_Code='9H07';
	$D_C_S = str_split($Day_Code);

	$Year = $D_C_S[0];
	$Month = $D_C_S[1];
	$Day = $D_C_S[2] . $D_C_S[3];

	$Date = '';
	if ($Year != '') {
		$Y = '202' . $Year;
	}

	/*
										   if($Year=='9'){

											   $Y='2019';

										   }
										   else if($Year=='0'){

											   $Y='2020';

										   }else if($Year=='1'){

											   $Y='2021';

										   }else if($Year=='2'){

											   $Y='2022';

										   }else if($Year=='2'){

											   $Y='2023';

										   }else if($Year=='4'){

											   $Y='2024';

										   }
										   */

	if ($Month == 'A') {
		$M = '01';
	} else if ($Month == 'B') {
		$M = '02';
	} else if ($Month == 'C') {
		$M = '03';
	} else if ($Month == 'D') {
		$M = '04';
	} else if ($Month == 'E') {
		$M = '05';
	} else if ($Month == 'F') {
		$M = '06';
	} else if ($Month == 'G') {
		$M = '07';
	} else if ($Month == 'H') {
		$M = '08';
	} else if ($Month == 'J') {
		$M = '09';
	} else if ($Month == 'K') {
		$M = '10';
	} else if ($Month == 'L') {
		$M = '11';
	} else if ($Month == 'M') {

		$M = '12';
	}



	return $Date = $Day . '-' . $M . '-' . $Y;

}
function getUsebyDate($DATE)
{

	$TIME_STAMP = strtotime($DATE);


	$dt = new DateTime($DATE);
	$dt->modify('+730 days');

	$DATE = $dt->format('r') . PHP_EOL;

	$TIME_STAMP = strtotime($DATE);
	return $formated_date = date("d-m-Y", $TIME_STAMP);


	// return $DATE;


}
function getUsebyDateinMDYFormat($DATE)
{

	$TIME_STAMP = strtotime($DATE);


	$dt = new DateTime($DATE);
	$dt->modify('+730 days');

	$DATE = $dt->format('r') . PHP_EOL;

	$TIME_STAMP = strtotime($DATE);
	return $formated_date = date("m-d-Y", $TIME_STAMP);


	// return $DATE;


}


function getProductTypeCode($ProductType, $ProductName)
{

	if ($ProductType == 'COOKED') {
		$ProductName = 'C' . $ProductName;
	}

	return $ProductName;

}


function getSpeciesDetails($ShortCode, $iconn)
{

	$SqlConDetails = "SELECT * FROM `ScrmSpeciesMaster` WHERE ShortCode='$ShortCode'";
	$StmtConDetails = $iconn->query($SqlConDetails);
	$ConData = $StmtConDetails->fetch_array();
	$ConsigneeName['SpeciesName'] = $ConData['SpeciesName'];
	;
	return $ConsigneeName;


}

function getLabMethodName($C_NAME, $iconn)
{
	global $TblQcPoVarityList;
	$sql = "SELECT DefaultValue FROM QcEiaTestResultHeaders WHERE TestID=3 and Country='$C_NAME' and ParamName='Method Of Testing' ORDER BY SortOrder ASC";
	$stmt_HEADER = $iconn->query($sql);
	$RowLabMethod = $stmt_HEADER->fetch_array();
	$MethodName = $RowLabMethod['DefaultValue'];
	return $MethodName;
}

?>
<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$OverAllMcs = 0;



$PackData = $_POST['GRADE'];
$Group = $_POST['H LV-UNT 6 * 1.8 KG'];
$Category = $_POST['HLV 6 * 4 LBS'];
$FreezingType = $_POST['V.PVPD 6 * 2 KG UNT'];
$UNIT = $_POST['V .PDTO 12 * 3.214 SKEWERS'];
$PONmbr = $_POST['V.PDTO 16 * 2.5 LBS 70 % CUT'];
$ColdstoreID = $_POST['ColdstoreID'];
$TenantID = $_POST['TenantID'];
$VarietyID = $_POST['VarietyID'];
$AntiBiotic = $_POST['AntiBiotic'];
$Soaking = $_POST['Soaking'];
$qry = "";

/*if($UNIT!='BOTH'){
	$qry.="and CPID.`UnitID`='$UNIT'";
}*/
if ($PackData != 'BOTH') {
	$qry .= "and CPID.`PackingTypeID`='$PackData'";
}
if ($Group != 'BOTH') {
	$qry .= "and CPID.`ProductionTypeID`='$Group'";
}
if ($Category != 'BOTH') {
	$qry .= "and CPID.`ProductCategoryID`='$Category'";
}
if ($FreezingType != 'BOTH') {
	$qry .= "and CPID.`FreezingType`='$FreezingType'";
}
if ($PONmbr != 'BOTH') {
	$qry .= "and CPID.`PoID`='$PONmbr'";
}
if ($VarietyID != 'BOTH') {
	$VarietyIDQry = "and CPID.`VarietyID`='$VarietyID'";
}

if ($AntiBiotic != 'BOTH') {
	$qry .= " and CPID.`AbStatus` = '$AntiBiotic' ";
}

if ($Soaking != 'BOTH') {
	$qry .= " and CPID.`SockingType`='$Soaking'";
}

if ($TenantID != 'BOTH') {
	$qry .= " and CPID.StockHolderID='$TenantID'";
}


$JoinQuery = " left join CsBarCodes as CBD on CBD.BarCodeID=CPID.BarCodeID ";
$StatusQuery = " and (CBD.BarCodeStatus='1' OR CBD.BarCodeStatus='2')";

$SqlGrades = "SELECT Value as GradeName,Options as GRADES FROM ProductionGrades order by abs(Value) asc";
$StmtGrades = $iconn->query($SqlGrades);



/*$SqlForm="SELECT DISTINCT(CPID.VarietyID),PIVL.ShortName as FORM FROM `CsStockData` as CPID 
  left join ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID 
  where (AvalMcs !='' or LooseBags!=0)   $qry order by VarietyID ASC"; */


$SqlForm = "SELECT DISTINCT(CPID.VarietyID),ShortName as FORM FROM `CsStockData` as CPID 
left join ScrmPIVarietyList as PVL on CPID.VarietyID=PVL.VarietyID 
where CPID.PlantID ='$ColdstoreID' and (AvalMcs !=0 or LooseBags!=0)  $qry $VarietyIDQry
 order by VarietyID ASC";

$StmtForm = $iconn->query($SqlForm);



while ($RowGrades = $StmtGrades->fetch_array()) {
	$AryGradenames[] = $RowGrades['GradeName'];
}

// echo json_encode($AryGradenames, JSON_PRETTY_PRINT);
// exit;



$data = '
			<table id="key-datatable" data-paging="false"  
										class="table  nowrap w-100  " >									
		
';

$data .= '<thead class="thead-light" >
		<th style="font-size: 11px;">Variety</th>
	    <th style="font-size: 11px" >Packing</th>';


foreach ($AryGradenames as $Grades) {

	$data .= '<th style="font-size: 11px" >' . $Grades . '</th>';
}

$data .= '
    <th style="font-size: 10px">Mcs</th>
    <th style="font-size: 10px">Kg</th>
	</thead>';


while ($form = $StmtForm->fetch_array()) {

	$FORM = $form['FORM'];
	$VarietyID = $form['VarietyID'];




	$SqlPacking = "SELECT DISTINCT(`PackingStyleID`),PackingStyle FROM `CsStockData` as CPID
join AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.PCKID
 where VarietyID='$VarietyID' $qry order by Abs(PackingStyle) asc";

	$StmtPacking = $iconn->query($SqlPacking);

	// var_dump($StmtPacking->fetch_array());
	// exit;	

	$packing = array();
	while ($RowPacking = $StmtPacking->fetch_array()) {
		$packing[] = $RowPacking['PackingStyleID'];
		$AryPack[] = $RowPacking['PackingStyle'];
	}

	// print_r($packing);

	$data .= '
		<tbody>';


	$KgsPerGrade = 0;
	$c = 0;
	foreach ($packing as $PackStyleId) {
	$SqlPackStyle = "SELECT * FROM `AdminPackingStyles` WHERE PCKID='$PackStyleId'";
		$StmtPackStyle = $iconn->query($SqlPackStyle);
		$RowPackStyle = $StmtPackStyle->fetch_array();
		$RowPackStyle['PackingStyle'];
		$GradeRow = '<tr><td style="font-weight:bold;font-size:11px;color:red" >' . $FORM . '</td>
<td style="font-size: 11px" >' . $RowPackStyle['PackingStyle'] . '</td>';
		$c++;


		$T_MCS = 0;
		$T_KGS = 0;


		/***************************************************Grade MCS/ kgs*********************************************/

		//foreach($AryGrades as $GRADES){	
		foreach ($AryGradenames as $GRADES) {



	$SqlFormMCS = "SELECT PackingStyle,PackingStyleID,
	 SUM(AvalMcs) as MCS ,
	 SUM(LooseBags) as LooseBags ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE CPID.UnitID ='$ColdstoreID' and VarietyID ='$VarietyID' and PackingStyleID='$PackStyleId' 
	AND Grade ='$GRADES' $qry"; 
			/*
echo '<BR>';	
echo '<BR>';	
echo '<BR>';	
echo '<BR>';	*/

			$SqlFormMCS = $SqlFormMCS  . $StatusQuery;
			$StmtFormMCS = $iconn->query($SqlFormMCS);

			// var_dump($StmtFormMCS->fetch_array());
			// exit;
			$RowFormMCS = $StmtFormMCS->fetch_array();
			$MCS = $RowFormMCS['MCS'];
			$LOOSE = $RowFormMCS['LooseBags'];
			$KGS = $RowFormMCS['KGS'] + $RowFormMCS['LKGS'];

			$T_MCS = $T_MCS + $MCS;
			$T_KGS = $T_KGS + $KGS;
			if (round($MCS) != 0 || round($LOOSE) != 0) {
				$GradeRow .= '<td style="font-size: 11px">' . round($MCS) . '/' . round($LOOSE) . '</td>';
			} else {
				$GradeRow .= '<td style="font-size: 11px"></td>';
			}


			//$GradeRow.='<td style="font-size: 11px">'.round($MCS).'</td>';

		}
		$Kgs = $RowFormMCS['KGS'] + $RowFormMCS['LKGS'];
		$KgsPerGrade += $Kgs;

		if ($T_KGS != 0) {
			$data .=	$GradeRow;
			$data .= '<td style="font-size: 11px">' . round($T_MCS) . '</td>';
			$data .= '<td style="font-size: 11px">' . round($T_KGS) . '</td></tr>';
		} else {
			$data .=	$GradeRow;
			$data .= '<td style="font-size: 11px"></td>';
			$data .= '<td style="font-size: 11px"></td></tr>';
		}

		$data .= '</tr>';
	}
// Row 1  =======================================================

	/***************************************************Grade MCS/ kgs*********************************************/
	/***************************************************TOTAL MCS*********************************************/

	// Total MCS =====================================================
	$data .= ' <tr style="font-weight:bold;font-size:11px;color:blue" >
<th style="font-size: 11px"><b>Total Mcs</b></th><th></th>';

	$T_MCS = 0;
	//foreach($AryGrades as $GRADES){
	foreach ($AryGradenames as $GRADES) {

		$SqlFormMCS = "SELECT PackingStyle,PackingStyleID,SUM(AvalMcs) as MCS ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE CPID.UnitID ='$ColdstoreID' and VarietyID ='$VarietyID' AND Grade ='$GRADES' $qry  $StatusQuery";

		$StmtFormMCS = $iconn->query($SqlFormMCS);
		$RowFormMCS = $StmtFormMCS->fetch_array();
		$MCS = $RowFormMCS['MCS'];
		$KGS = $RowFormMCS['KGS'] + $RowFormMCS['LKGS'];
		$T_MCS = $T_MCS + $MCS;
		$OverAllMcs = $OverAllMcs + $T_MCS;
		$data .= '<th style="
    font-size: 11px">' . round($MCS) . '</th>';
	}
	$data .= '<th>' . round($T_MCS) . 'Mcs</th><th>NA</th></tr>';


	// Total MCS =====================================================

	/***************************************************TOTAL MCS END*********************************************/
	/***************************************************TOTAL KGS*********************************************/

	$data .= ' <tr style="font-weight:bold;font-size:11px;color:#0bb50b" >
<th style="font-size: 11px">Total Kgs</th><th></th>';

	$T_MCS = 0;
	$T_KGS = 0;
	//foreach($AryGrades as $GRADES){	
	foreach ($AryGradenames as $GRADES) {



	$SqlFormMCS = "SELECT PackingStyle,PackingStyleID,SUM(AvalMcs) as MCS ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE CPID.UnitID ='$ColdstoreID' and VarietyID ='$VarietyID' AND Grade ='$GRADES' $qry  $StatusQuery";

		$StmtFormMCS = $iconn->query($SqlFormMCS);
		$RowFormMCS = $StmtFormMCS->fetch_array();
		$MCS = $RowFormMCS['MCS'];
		$KGS = $RowFormMCS['KGS'] + $RowFormMCS['LKGS'];
		$T_KGS = $T_KGS + $KGS;
		$OverAllMcs = $OverAllMcs + $T_MCS;
		$data .= '<th style="
    font-size: 11px">' . round($KGS) . '</th>';
	}
	$data .= '<th>NA</th><th>' . round($T_KGS) . 'Kg</th></tr>';
}

$data .= ' <tr style="font-weight:bold;font-size:11px;color:red" >
<th style="font-size: 11px">Over All MCS</th>
<th></th>
';

$T_MCS = 0;
$T_KGS = 0;


foreach ($AryGradenames as $GRADES) {


	 $SqlFormMCS = "SELECT PackingStyle,PackingStyleID,SUM(AvalMcs) as MCS ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS, 
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE  CPID.UnitID ='$ColdstoreID' and  Grade='$GRADES' $qry $VarietyIDQry $StatusQuery";


	$StmtFormMCS = $iconn->query($SqlFormMCS);
	$RowFormMCS = $StmtFormMCS->fetch_array();
	$MCS = $RowFormMCS['MCS'];
	$KGS = $RowFormMCS['KGS'] + $RowFormMCS['LKGS'];
	$T_KGS = $T_KGS + $KGS;


	if ($MCS == '') {
		$MCS = '-';
	} else {
		$T_MCS = $T_MCS + $MCS;
		$OverAllMcs = $OverAllMcs + $T_MCS;
	}

	//print_r($T_MCS);

	$data .= '<th style="
    font-size: 11px">' . round($MCS) . '</th>';
}


if ($T_KGS != 0) {
	$data .= '<td style="font-size: 11px">' . round($T_MCS) . ' Mcs</td>';
	$data .= '<td style="font-size: 11px">' . round($T_KGS) . ' Kgs</td></tr>';
} else {
	$data .= '<td style="font-size: 11px">0 Mcs</td>';
	$data .= '<td style="font-size: 11px">0 Kgs</td></tr>';
}

echo $data .= '</tbody></table>   </div>
  
			
    </div></div>
 </div></div>
</div>';

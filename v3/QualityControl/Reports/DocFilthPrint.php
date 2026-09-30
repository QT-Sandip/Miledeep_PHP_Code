<?php
session_start();

$IsHeader = 0;
$IsFooter = 0;
$QueryParms = $_GET['Q'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";




$PoID = $_GET['PoID'];
$RID = $_GET['RID'];
$ReportName = $_GET['ReportName'];
$FileType = $_GET['Type'];

$SqlPoData = "SELECT AED.ExpName,AED.ExpAddress FROM `ScrmPoData` as POD left join AdminExporterDetails as AED on POD.ProcessorID=AED.ExpID WHERE POD.PoID='$PoID'";
$StmtPoData = $iconn->query($SqlPoData);
$RowPoData = $StmtPoData->fetch_array();
$ClientName = $RowPoData['ExpName'] . '<br>' . $RowPoData["ExpAddress"];

$SqlPreHarvest = "SELECT * FROM  QcPhAbReports  WHERE ID IN ($RID)";
$StmtPreHarvest = $iconn->query($SqlPreHarvest);
$RowPreHarvest = $StmtPreHarvest->fetch_array();
//EXIT;
$CodeListId = $RowPreHarvest['TracebilityID'];

$Query = '';
if ($CodeListId != null && $CodeListId != '' && $CodeListId != 'null') {
	$Query .= " AND CLID='$CodeListId'";
} else {
	$Query .= " AND CLID IN ($RID)";
}

$QueryTotal = '';
if ($CodeListId != null && $CodeListId != '' && $CodeListId != 'null') {
	$QueryTotal = "";
} else {
	$QueryTotal .= " AND CLID IN ($RID)";
}

//$SqlInvData="SELECT * FROM  ScrmPoData  WHERE PoID='$PoID'"; 
$SqlInvData = "SELECT *,(SELECT GROUP_CONCAT(McsTotal)  FROM `QcCodeList` WHERE InvID='$PoID' $QueryTotal)as TotalMcs
  FROM  ScrmPoData  WHERE PoID='$PoID'"; // sriram
$StmtInvData = $iconn->query($SqlInvData);
$RowInvData = $StmtInvData->fetch_array();
$LotNo = $RowInvData['LotNo'];
$TotalCLMcs = $RowInvData['TotalMcs'];
$TotalCLSamples = (sqrt($TotalCLMcs) + 1) / 2;

//sriram
$TotalCLMcsArr =  explode(',', $TotalCLMcs);
$sumMcs = array_sum($TotalCLMcsArr);
$SqlSampleNumber = "Select NoofSamples from QcFilthSamplesCount where  LowerLimit <= $sumMcs and UpperLimit >= $sumMcs";
	//exit;

	$StmtSampleNumber = $iconn->query($SqlSampleNumber);
	$RowSampleNumber = $StmtSampleNumber->fetch_array();

	$TotalCLSamples = $RowSampleNumber['NoofSamples'];


// old
//$SqlCode="SELECT * FROM `CODE_LIST_CREATE` WHERE PoID='$PoID'and CODE_LIST_ID='$CODE_LIST_ID'"; 
$SqlCode = "SELECT * FROM `QcCodeList` WHERE InvID='$PoID' $Query";

$StmtCode = $iconn->query($SqlCode);


//sriram
$McsTotal = 0;
$TraceIDs = [];
$VarietyIDs = [];

while ($RowCode = $StmtCode->fetch_array()) {
    $TraceIDs[] = $RowCode['TracebilityID'];
    $VarietyIDs[] = $RowCode['VarietyID'];
}

$TraceIDString = implode(',', $TraceIDs);
$VarietyIDString = implode(',', $VarietyIDs);

$QueryTotalMcs = '';
	if ($CodeListId != null && $CodeListId != '' && $CodeListId != 'null') {
		$QueryTotalMcs .= " TracebilityID IN ($TraceIDString) ";
	} else {
		$QueryTotalMcs .= " CLID IN ($RID)";
	}
	
	$sql_code_list = "SELECT *,McsGrade AS McsTotal  FROM `QcCodeList` as Cl 
			
			WHERE Cl.InvID='$PoID' and $QueryTotalMcs";  //AND Cl.VarietyID='$VarietyID'
	
	
	$stmt_code_list = $iconn->query($sql_code_list);
	$McsArr = [];
	while($r_code_list = $stmt_code_list->fetch_array()){
		$McsArr[] = $r_code_list['McsTotal'];
		$McsTotal += $r_code_list['McsTotal'];
	}

$TOTAL_SAMPLES = $TotalCLSamples;//sriram


//sriram 05-02-25
// $RowCode = $StmtCode->fetch_assoc();
// $TraceID = $RowCode['TracebilityID'];
// $VarietyID = $RowCode['VarietyID'];

// $QueryTotalMcs = '';
// if ($CodeListId != null && $CodeListId != '' && $CodeListId != 'null') {
// 	$QueryTotalMcs .= " TracebilityID='$TraceID' ";
// } else {
// 	$QueryTotalMcs .= " CLID='$RID'";
// }

// $sql_code_list = "SELECT *,SUM(McsGrade) AS McsTotal  FROM `QcCodeList` as Cl 
		
// 		WHERE Cl.InvID='$PoID' and $QueryTotalMcs";  //AND Cl.VarietyID='$VarietyID'


// $stmt_code_list = $iconn->query($sql_code_list);
// $r_code_list = $stmt_code_list->fetch_assoc();

// $McsTotal = $r_code_list['McsTotal'];

// //$TOTAL_SAMPLES =(sqrt($McsTotal)+1)/2; 
// // $TOTAL_SAMPLES =round($TOTAL_SAMPLES,2);
// $TOTAL_SAMPLES = ($McsTotal * round($TotalCLSamples)) / $TotalCLMcs;






//require_once('../tcpdf/tcpdf.php'); 

/*
	
class MYPDF extends TCPDF {
	public function Header() {	
	global $LotNo,$ClientName;

		$image_file = K_PATH_IMAGES.'jme_logo.jpg';
		$this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		$this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5,strtoupper($ClientName), 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'REGISTER FOR FILTH TEST - '.$LotNo, 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
		$this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
	}
	
	public function Footer() {
		$this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
   }
}

*/



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);

$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(45);
$pdf->SetHeaderMargin(155);
$pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);

$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage('L');




$table_data = getDS_TABLE3($iconn, $ID);
$pdf->writeHTML($table_data);



//$logo = "images/jme_logo.jpg";
//$file_name='FILTH_REPORT.php'.$row['PO_NUM'];
$file_name = 'FILTH_REPORT.pdf';
$pdf->Cell(95, 5, ' ', 0, 1, 'C', 0);
$pdf->Cell(95, 5, ' ', 0, 1, 'C', 0);

// $pdf->Cell(95, 5, 'Formula:  ', 0, 1, 'L', 0);
// $pdf->Cell(49, 5, 'As Per sampling code (Squareroot(n) + 1)/2 = ' . round($TOTAL_SAMPLES), 0, 1, 'L');
$pdf->Cell(49, 5, 'Sampling Code = ' . round($TOTAL_SAMPLES), 0, 1, 'L');//sriram
// $pdf->Cell(49, 5, 'Where n = Total Master Cartons.', 0, 1, 'L');
$pdf->Cell(49, 5, ' ', 0, 1, 'C', 0);

$pdf->Cell(95, 5, 'Monitered By ', 0, 0, 'L', 0);

$pdf->Cell(95, 5, 'Verified By ', 0, 1, 'R', 0);

ob_end_clean();

$pdf->Output($file_name, 'I');




function getDS_TABLE3($iconn, $ID)
{

	global $ClientName, $CodeListId, $PoID, $TraceID, $VarietyID, $TOTAL_SAMPLES, $QueryTotalMcs;

	//ROBO	
	// $sql_code_list = "SELECT *,SUM(McsGrade) AS McsTotal FROM `QcCodeList` as Cl
	// 	left join ScrmPoGradeData as SPDG on SPDG.ID=Cl.PoGradeID
	// 	left join ScrmPIVarietyList as PVL on PVL.VarietyID=SPDG.VarietyID left join QcTracebilityList as TL on TL.TraceID=Cl.TracebilityID WHERE Cl.InvID='$PoID' and $QueryTotalMcs";  //AND Cl.VarietyID='$VarietyID'

	// // echo   $sql_code_list;   exit;
	// $stmt_code_list = $iconn->query($sql_code_list);
	// $r_code_list = $stmt_code_list->fetch_assoc();


	// $FULL_NAME = $r_code_list['FullName'];
	// $McsTotal = $r_code_list['McsTotal'];
	// $DayCode = $r_code_list['DayCode'];
	// $ShortName = $r_code_list['ShortName'];

	//sriram

	$TOTAL_SAMPLES = round($TOTAL_SAMPLES);

	$sql_code_list_count = "SELECT count(*) AS Total FROM `QcCodeList` as Cl
	left join ScrmPoGradeData as SPDG on SPDG.ID=Cl.PoGradeID
	left join ScrmPIVarietyList as PVL on PVL.VarietyID=SPDG.VarietyID left join QcTracebilityList as TL on TL.TraceID=Cl.TracebilityID WHERE Cl.InvID='$PoID' and $QueryTotalMcs";  //AND Cl.VarietyID='$VarietyID'

$stmt_code_list_count = $iconn->query($sql_code_list_count);
	$r_code_list_count = $stmt_code_list_count->fetch_assoc();
	$TotalCount = $r_code_list_count['Total'];

	$sql_code_list = "SELECT *,McsGrade AS McsTotal FROM `QcCodeList` as Cl
		left join ScrmPoGradeData as SPDG on SPDG.ID=Cl.PoGradeID
		left join ScrmPIVarietyList as PVL on PVL.VarietyID=SPDG.VarietyID left join QcTracebilityList as TL on TL.TraceID=Cl.TracebilityID WHERE Cl.InvID='$PoID' and $QueryTotalMcs";  //AND Cl.VarietyID='$VarietyID'

	// echo   $sql_code_list;   exit;
	$stmt_code_list = $iconn->query($sql_code_list);

	$RowDataArr = [];	
	while($r_code_list = $stmt_code_list->fetch_array()){
		$RowDataArr[] = $r_code_list;
	}



	$content = '';

	$size = 40;
	$size1 = 60;

	$count = '';
	$RESULT = 'Nill';
	$REMARKS = 'GOOD';

	$content .= '<table border="1" cellspacing="0" cellpadding="0">  
	
	 <!-- <tr> 

              <th  align="center" width="100%"><font size="13"> ' . strtoupper($ClientName) . ' <br>' . strtoupper($ClientAdd) . ' <br> REGISTER FOR FILTH TEST </font></th> 
               
                
				</tr> -->
	
	          <tr style="height:100px"> 
           		
                <th  align="center" width="8%"><font size="9"> Date </font></th>
                <th  align="center" width="6%"><font size="9"> Code </font></th>
                <th  align="center" width="8%"><font size="9"> Type of Product </font></th>
                <th  align="center" width="10%"><font size="9"> Regd No.  </font></th>
                <th  align="center" width="8%"><font size="9"> No of Cartons </font></th>
                <th  align="center" width="8%"><font size="9"> No of Samples Drawn </font></th>
                <th  align="center" width="14.8%"><font size="9"></font>
				<table border="0" cellspacing="0" cellpadding="2.52">
				<tr> 
           		
                <th  align="center" width="100%"><font size="9"> Flies(whole or equivalent) </font></th></tr>
				
				
				<tr>
           		
                <th border="1"  align="center" height="50%" width="50%"><font size="9">Incidental Fly</font></th>
                <th border="1"  align="center" width="50%"><font size="9">Filth Fly</font></th>
				</tr>
				</table> </th>
								
               
                <th  align="center" width="7.4%"><font size="9"> Fly Fragments of any Size </font></th>
                <th  align="center" width="7.4%"><font size="9"> Cockroaches(Whole of including even excreta) </font></th>
                <th  align="center"  width="14.8%"><table border="0" cellspacing="0" cellpadding="2">
				<tr> 

                <th align="center" width="100%" >Hairs</th>
				</tr>
				 
				<tr> 
           		
                <th border="1"  align="center" width="50%"><font size="9"> Rat/Mouse</font></th>
                <th border="1" align="center" width="50%"><font size="9">Straited bot
not Rat or
Mouse
</font></th>
				</tr>
				</table> </th>

                <th  align="center" width="7.6%"><font size="9"> Remarks  </font></th>
               
				</tr>


';


	$count = 1;

	for ($i = 1; $i <= $TOTAL_SAMPLES; $i++) { //uma $i=0 // sriram <= total

		if ($i <= $TotalCount) { //uma //sriram 05-02-25
			$rowData = $RowDataArr[$i - 1]; //sriram
			$content .= '	 <tr>
                <th  align="center" width="8%"><font size="9">' . getDayCodeDate($rowData['DayCode']) . '  </font></th>
                <th  align="center" width="6%"><font size="9">' . $rowData['DayCode'] . ' </font></th>
                <th  align="center" width="8%"><font size="9"> ' . $rowData['ShortName'] . ' </font></th>
                <th  align="center" width="10%"><font size="9"> ' . $rowData['TracebilityID'] . ' </font></th>
                <th  align="center" width="8%"><font size="9">' .  $rowData['McsTotal'] . '  </font></th>
                <th  align="center" width="8%"><font size="9"> ' . $count . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9">' . $RESULT . '  </font></th>
                <th  align="center" width="7.6%"><font size="9"> ' . $REMARKS . ' </font></th>
				</tr>';
			$count++;
		}
		else{
		$content .= '	 <tr>
				 <th  align="center" width="8%"><font size="9">  </font></th>
                <th  align="center" width="6%"><font size="9"> </font></th>
                <th  align="center" width="8%"><font size="9"></font></th>
                <th  align="center" width="10%"><font size="9">  </font></th>
                <th  align="center" width="8%"><font size="9"></font></th>
				<th  align="center" width="8%"><font size="9"> ' . $count . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9"> ' . $RESULT . ' </font></th>
                <th  align="center" width="7.4%"><font size="9">' . $RESULT . '  </font></th>
                <th  align="center" width="7.6%"><font size="9">  ' . $REMARKS . ' </font></th>
               
               
                
				</tr>';
		
		$count++;
		}
	}




	$content .= ' 
				
		</table>';

	return $content;
}

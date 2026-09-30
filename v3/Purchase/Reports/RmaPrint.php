<?php
session_start();

$LotID = $_GET['LotID'];
$Download = $_GET['download'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");

$SULPHITE = $row_org_insert['SULPHITE'];



$SqlLotDetails = "SELECT * FROM `RmLotDetatils`AS RLD 
	LEFT JOIN RmPurchaseStations AS RPS ON RPS.PurchaseStationID=RLD.PurchaseStationID
	LEFT JOIN RmUsersList AS RUL ON RUL.ID=RLD.AgentID
    LEFT JOIN RmProcessingStations AS RPRS ON RPRS.ProcessingStationID=RPS.ProcessingStationID	
	WHERE RmLotID=$LotID";
$StmtLotDetails = $iconn->query($SqlLotDetails);
$RowLotDetails = $StmtLotDetails->fetch_array();

$LotNumber = $RowLotDetails['LotNumber'];
$PurchaseCount = $RowLotDetails['PurchaseCount'];
$CompanyLogo = $RowLotDetails['CompanyLogo'];
$CompanyName = $RowLotDetails['FullName'];
$CompanyFooter = $RowLotDetails['Name'];
$AgentName = $RowLotDetails['UserName'];


$SqlABReport = "SELECT * FROM `QcRmAbReport` as QRAR 
	 LEFT JOIN QcABOptions AS QABO ON QRAR.AbStatus=QABO.OptID 
	 WHERE LotID='$LotID'";

$StmtABReport = $iconn->query($SqlABReport);
$RowABReport = $StmtABReport->fetch_array();

$RESULT = $RowABReport['Parameter'];
$SULPHITE = $RowABReport['Sulphite'];
$AntiBioticStatus = $RowABReport['Name'];

$SqlOrgnDtls = "SELECT * FROM `QcRmOrgnDetails` WHERE LotID=$LotID";
$StmtOrgnDtls = $iconn->query($SqlOrgnDtls);
$RowOrgnoDtls = $StmtOrgnDtls->fetch_array();
$CheckedCount = $RowOrgnoDtls['CheckedCount'];

$InspectedBy = $RowOrgnoDtls['PostedUserID'];
$InspectedRemarks = $RowOrgnoDtls['Remarks'];
$Inspected = getUserDetails($InspectedBy);



while ($RowOrgnoDtls = $StmtOrgnDtls->fetch_array()) {

	$arryOrgnoDtls[] = $RowOrgnoDtls;

}


// $SqlInspectedBy="SELECT * FROM `AdminLoginDetails` WHERE UserID='$InspectedBy'";
// $StmtInspectedBy=$iconn->query($SqlInspectedBy); 
// $RowInspectedBy=$StmtInspectedBy->fetch_array();
// $Inspected=$RowInspectedBy['UserName'];


$SqlVerifiedOrgnDtls = "SELECT * FROM `QcRmOrgnReport` WHERE LotID=$LotID";
$StmtVerifiedOrgnDtls = $iconn->query($SqlVerifiedOrgnDtls);
$RowVerifiedOrgnoDtls = $StmtVerifiedOrgnDtls->fetch_array();

$VerifiedBy = $RowVerifiedOrgnoDtls['PostedUserID'];
$VerifiedRemarks = $RowVerifiedOrgnoDtls['Remarks'];
$SuggestedDeductions = $RowVerifiedOrgnoDtls['SuggestedDeductions'];
$ActualDeductions = $RowVerifiedOrgnoDtls['ActualDeductions'];

$Verified = getUserDetails($VerifiedBy);
// $SqlVerifiedBy="SELECT * FROM `AdminLoginDetails` WHERE UserID='$VerifiedBy'";
// $StmtVerifiedBy=$iconn->query($SqlVerifiedBy); 
// $RowVerifiedBy=$StmtVerifiedBy->fetch_array();
// $Verified=$RowVerifiedBy['UserName'];



class MYPDF extends TCPDF
{
	public function Header()
	{

		global $CompanyLogo;
		global $CompanyName;

		$this->Image($CompanyLogo, 10, 10, 35, 30, 'JPG', '', 'T', True, 400, '', false, false, 0, false, false, false);
		$this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, $CompanyName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'INTERNAL QUALITY CHECK', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
	}
}




$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Raw Material Analysis -' . $LotNumber);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(45);
$pdf->SetHeaderMargin(155);
$pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);

$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage('P');

$H1 = '5';
$pdf->Cell(40, $H1, 'Ref No.' . $RowLotDetails['LotNumber'], 0, 0, 'L');
$pdf->Cell(30, $H1, 'Qty.' . $RowLotDetails['PurchaseWeight'], 0, 0, 'L');
$pdf->Cell(60, $H1, 'Purchase Date:' . getFormatedDate($RowLotDetails['PurchaseDate']), 0, 0, 'L');
$pdf->Cell(40, $H1, 'Production Date:' . getFormatedDate($RowLotDetails['CreatedDateTime']), 0, 1, 'L');



$table_data = getDS_TABLE3($iconn, $ID, $arryOrgnoDtls, $PurchaseCount, $AgentName);
$pdf->writeHTML($table_data);

$pdf->Cell(0, $H1, 'Remarks : ' . $InspectedRemarks, 0, 1, 'L');
$pdf->Cell(0, $H1, '', 0, 1, 'L');
$pdf->Cell(90, $H1, 'INSPECTED BY: ' . $Inspected, 1, 0, 'L');
$pdf->Cell(90, $H1, 'Monitered BY:', 1, 1, 'L');
$pdf->Cell(0, $H1, '', 0, 1, 'L');
$pdf->Cell(0, $H1, 'ANTIBIOTIC ANALYSIS REPORT (PRE HARVEST/HARVEST) :', 0, 1, 'L');




$table_data = getDS_TABLE4($iconn, $LotID, $RESULT, $ANTI_BIOTIC_STATUS);
$pdf->writeHTML($table_data);

$pdf->Cell(0, $H1, 'Remarks: ' . $VerifiedRemarks, 0, 1, 'L');
$pdf->Cell(0, $H1, '', 0, 1, 'L');
//$pdf->Cell(180,10,$VerifiedRemarks,1,1,'L');
$pdf->Cell(90, $H1, 'Verified and Certified by:', 1, 0, 'L');
$pdf->Cell(90, $H1, $Verified, 1, 1, 'L');
$pdf->Cell(90, $H1, 'Suggested Deductions:' . $SuggestedDeductions, 1, 0, 'L');
$pdf->Cell(90, $H1, 'Actual Deductions:' . $ActualDeductions, 1, 0, 'L');
ob_end_clean();
$file_name = 'Raw Material Analysis-' . $LotNumber . '.pdf';
$pdf->Output($file_name, $Download ? 'D' : 'I');










function getDS_TABLE3($iconn, $ID, $ary, $PurchaseCount, $AgentName)
{


	//CheckedCount
	$TotalCheckedCount = $ary[0]['CheckedCount'] + $ary[1]['CheckedCount'] + $ary[2]['CheckedCount'];
	$TotalCheckedCount = round($TotalCheckedCount / 3);
	$AverageWeight = '1000' / $TotalCheckedCount;

	//BlackGills
	$BlackGills = $ary[0]['BlackGills'] * $AverageWeight + $ary[1]['BlackGills'] * $AverageWeight + $ary[2]['BlackGills'] * $AverageWeight;
	$BlackGills = ROUND($BlackGills / 30, 1);

	//SoftShell

	$Soft = $ary[0]['Soft'] * $AverageWeight + $ary[1]['Soft'] * $AverageWeight +
		$ary[2]['Soft'] * $AverageWeight;
	$Soft = round($Soft / 30, 1);

	//DroopingHead

	$DroopingHead = $ary[0]['DroopingHead'] * $AverageWeight + $ary[1]['DroopingHead'] * $AverageWeight + $ary[2]['DroopingHead'] *
		$AverageWeight;
	$DroopingHead = round($DroopingHead / 30, 1);

	//BlackSpot	

	$BlackSpot = $ary[0]['BlackSpot'] * $AverageWeight + $ary[1]['BlackSpot'] * $AverageWeight + $ary[2]['BlackSpot'] * $AverageWeight;
	$BlackSpot = round($BlackSpot / 30, 1);

	//Broken

	$Broken = $ary[0]['Broken'] * $AverageWeight + $ary[1]['Broken'] * $AverageWeight + $ary[2]['Broken'] * $AverageWeight;
	$Broken = round($Broken / 30, 1);

	//ImproperLegs	

	$ImproperLegs = $ary[0]['ImproperLegs'] * $AverageWeight + $ary[1]['ImproperLegs'] * $AverageWeight + $ary[2]['ImproperLegs'] * $AverageWeight;
	$ImproperLegs = round($ImproperLegs / 30, 1);

	//Bends

	$Bends = $ary[0]['Bends'] * $AverageWeight + $ary[1]['Bends'] * $AverageWeight + $ary[2]['Bends'] * $AverageWeight;
	$Bends = round($Bends / 30, 1);

	$FitForHonPack = $StmtOrgnDtls['FitForHonPack'];



	$content = '';

	$size = 40;
	$size1 = 60;

	$content .= '<table border="1" cellspacing="0" cellpadding="4">  
	
	<tr> 

                <th  align="center" width="100%"><font size="13"> ORGANELLOPATIC INSPECTION REPORT</font></th>
               
                
				</tr>
	
	          <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Description </font></th>
                <th  align="center" width="12%"><font size="9"> Standard </font></th>
                <th  align="center" width="12%"><font size="9"> 1 </font></th>
                <th  align="center" width="12%"><font size="9"> 2  </font></th>
                <th  align="center" width="12%"><font size="9"> 3 </font></th>
                <th  align="center" width="12%"><font size="9"> Average% </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Farmer Agent</font></th>
                <th  align="center" width="12%"><font size="9">-</font></th>
                <th  align="center" width="48%"><font size="9">' . $AgentName . ' </font></th>

               
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Purchase count/Kg </font></th>
                <th  align="center" width="12%"><font size="9"> - </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Checked count/Kg </font> </th>
                <th  align="center" width="12%"><font size="9"> -  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ary[0]['CheckedCount'] . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ary[1]['CheckedCount'] . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ary[2]['CheckedCount'] . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $TotalCheckedCount . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Red/Black gill in% </font></th>
                <th  align="center" width="12%"><font size="9"> 5% </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['BlackGills'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['BlackGills'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['BlackGills'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $BlackGills . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Soft/Semi Soft/Paper Soft </font></th>
                <th  align="center" width="12%"><font size="9"> 10% </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[0]['Soft'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['Soft'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['Soft'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $Soft . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Dropping Head in %</font></th>
                <th  align="center" width="12%"><font size="9"> 5% </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['DroopingHead'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['DroopingHead'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['DroopingHead'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $DroopingHead . '  </font></th>
                
               
                
				</tr>
				
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Black spot/Black Dot in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[0]['BlackSpot'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[1]['BlackSpot'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[2]['BlackSpot'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $BlackSpot . '  </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Broken/Damages in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['Broken'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['Broken'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['Broken'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $Broken . '  </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Improper Legs/Decomposition/Discoloration in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['ImproperLegs'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['ImproperLegs'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['ImproperLegs'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ImproperLegs . '  </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Bends in %</font></th>
                <th  align="center" width="12%"><font size="9"> 10% </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['Bends'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['Bends'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[2]['Bends'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9">  ' . $Bends . ' </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Material is fit for Head On Packing or not</font></th>
                <th  align="center" width="12%"><font size="9"> -- </font></th>
                
                <th  align="center" width="48%"><font size="9"> ' . $FitForHonPack . '  </font></th>
              
          
				</tr>
				
				
				</table>';


	return $content;
}



function getDS_TABLE4($iconn, $LotID, $RESULT, $ANTI_BIOTIC_STATUS)
{


	$content = '';

	$PARMS = array('CAP', 'AOZ', 'AMOZ', 'AHD', 'SEM');

	$size = 40;
	$size1 = 60;

	$content .= '<table border="1" cellspacing="0" cellpadding="4">  	
		
		 <tr> 
           		
               
                <th  align="center" width="28%"><font size="9"> DESCRIPTION  </font></th>
                <th  align="center" width="18%"><font size="9"> Standard </font></th>
                <th  align="center" width="18%"><font size="9"> Analysis  </font></th>
                <th  align="center" width="18%"><font size="9"> Re-Analysis </font></th>
                <th  align="center" width="18%"><font size="9"> Third Party </font></th>
          
				</tr>
		
		
		<tr> ';

	foreach ($PARMS as $value) {


		if ($ANTI_BIOTIC_STATUS == "0") {
			$parM_val = 'TEST PENDING';
		} else
			if ($value == $RESULT) {
				$CLR = 'red';
				$parM_val = 'POSITIVE';
			} else {
				$CLR = 'black';
				$parM_val = 'NEGATIVE';
			}

		$content .= '    <th  align="center" width="28%"><font size="9"> ' . $value . '  </font></th>
                <th  align="center" width="18%"><font size="9"> NEGATIVE </font></th>
                <th  align="center" width="18%" color="' . $CLR . '"><font size="9"> ' . $parM_val . '  </font></th>
                <th  align="center" width="18%"><font size="9"> </font></th>
                <th  align="center" width="18%"><font size="9">   </font></th>
          
				</tr>
		<tr>';
	}





	$content .= '      
		        <th  align="center" width="28%"><font size="9"> SULPHITE  </font></th>
                <th  align="center" width="18%"><font size="9"> Less Than 50ppm </font></th>
                <th  align="center" width="18%"><font size="9">' . $SULPHITE . ' </font></th>
                <th  align="center" width="18%"><font size="9">  </font></th>
                <th  align="center" width="18%"><font size="9">   </font></th>
          
				</tr>
		
						</table>';

	return $content;


}



?>
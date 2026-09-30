<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
//ABReportParameterCutOffValues

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableTrackCodeInAbReport = $ClientConfigData['EnableTrackCodeInAbReport'];//anil
// include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
$ReportDate=$_GET['ReportDate'];
$SHIFT=$_GET['shift'];
$PlantId = !empty($_GET['PlantId']) ? $_GET['PlantId'] : '';

//echo $TcpdfPath ; exit;
$PlnatHeading = 'DAILY TEST REPORT LAB - AB';
$PlnatLocation = $plantWhere = '';
if(!empty($EnableTrackCodeInAbReport) && (!empty($PlantId) || !empty($_SESSION['UnitID']))){
    if(!empty($PlantId)){
        $plantWhere = " AND PlantId = '$PlantId' ";
    }
    if(empty($PlantId) && !empty($_SESSION['UnitID'])){
        $PlantId = $_SESSION['UnitID'];
    } 
    $plantSql = "SELECT 
                    CASE 
                    WHEN `ReportName` IS NOT NULL THEN `ReportName` ELSE `StationName` END AS `StationName`,
                   -- `StationLocation`
                   ProdCode AS StationLocation
                FROM RmProcessingStations WHERE `ProcessingStationID`=$PlantId";
    $StmtPlant=$iconn->query($plantSql);
    $rowPlant=$StmtPlant->fetch_assoc();
    $PlnatHeading = 'M/s. '.$rowPlant['StationName'];
    $PlnatLocation = $rowPlant['StationLocation'];
}

  $sqlABRpt="SELECT * FROM `QcRmAbReport` AS QRAR
LEFT JOIN QcABOptions AS QAB ON QAB.OptID=QRAR.Shift
WHERE ReportDate='$ReportDate' AND Shift='$SHIFT' 
$plantWhere
ORDER BY `AbRepID` DESC";
$StmtABRpt=$iconn->query($sqlABRpt);
$row=$StmtABRpt->fetch_array();


$TestedBy=$row['TestedBy'];			
$VerifiedBy=$row['VerifiedBy'];	


//$sqlTester="SELECT UserName FROM `QcABUsers`WHERE  UserID='$TestedBy' ";
$sqlTester="SELECT LbuName AS UserName FROM `QcLabTechnologist`WHERE  LUID='$TestedBy' ";
$StmtTester=$iconn->query($sqlTester);
$RowTester=$StmtTester->fetch_array();			
$TestedBy=$RowTester['UserName'];

 //$sqlVerifiedBy="SELECT UserName FROM `AdminLoginDetails`WHERE  UserID='$VerifiedBy' ";
$sqlVerifiedBy="SELECT LbuName AS UserName FROM `QcLabTechnologist`WHERE  LUID='$VerifiedBy' ";
$StmtVerifiedBy=$iconn->query($sqlVerifiedBy);
$RowVerifiedBy=$StmtVerifiedBy->fetch_array();			
$VerifiedBy=$RowVerifiedBy['UserName'];


function generateTableRow($ReportDate,$SHIFT,$iconn){
    global $EnableTrackCodeInAbReport,$plantWhere;
    $join = '';
    if(!empty($EnableTrackCodeInAbReport)){
        $join = "  LEFT JOIN RmArrivalDetatils RAD ON RAD.`RmArrivalID`=RLD.`RmReferenceID`
                   LEFT JOIN RmSuppliers RS ON RS.ID = RAD.RmSupplierID 
                   LEFT JOIN (
                        SELECT RRB.`ArrivalID`,`TrackCode`
                        FROM RmReceivingDetails RD
                        LEFT JOIN RmReceivingBreakup RRB ON RRB.`ReceivingID`=RD.`ReceivingID`
                        GROUP BY RD.`ReceivingID`
                    ) TC ON TC.`ArrivalID` = RAD.`RmArrivalID` 
                    ";
    }

    $SqlABReportList="SELECT * FROM `QcRmAbReport` AS QRAR
    LEFT JOIN QcABOptions AS QAO ON QAO.OptID=QRAR.AbStatus
    LEFT JOIN RmLotDetatils AS RLD ON RLD.RmLotID=QRAR.LotID  
    $join
    WHERE ReportDate='$ReportDate' AND Shift='$SHIFT'
    $plantWhere
    ORDER BY `AbRepID` DESC";
    $StmtABReportList=$iconn->query($SqlABReportList);
                                
    $SNo=0;
                                
    while($RowABReportList=$StmtABReportList->fetch_array()){      		
        if(!empty($EnableTrackCodeInAbReport)){
            $content .= generateTableWithTrackCodeDesign($RowABReportList, ++$SNo);
        }else{
            $content .= generateTableOldDesign($RowABReportList, ++$SNo);
        }
    }
    return $content;
}

function generateTableOldDesign($RowABReportList = [], $SNo = 1){
    $ABStatus=$RowABReportList['Name'];	
    $content ='<tr >';

    $lot='Harvest/'.$RowABReportList['LotNumber'].'/'.$RowABReportList['PurchaseCount'];
    $content .= '<td   align="center"><font size="7">'.$SNo.'</font></td>';
    $content .= '<td   align="left"><font size="7">'.$lot.'</font></td>';
    $content .= '<td   align="center"><font size="7">'.$RowABReportList['Parameter'].'</font></td>';
    $content .= '<td   align="center"><font size="7">'.$ABStatus.'</font></td>';
    $content .= '<td   align="center"><font size="7">'.$RowABReportList['Remarks'].'</font></td>';
    $content .='</tr>';

    return $content;
}
function generateTableWithTrackCodeDesign($RowABReportList = [], $SNo = 1){
   /*  global $iconn;
    $trackSql = "SELECT `TrackCode`
                    FROM RmReceivingDetails RD
                    LEFT JOIN RmReceivingBreakup RRB ON RRB.`ReceivingID`=RD.`ReceivingID`
                    WHERE RRB.`ArrivalID`=".$RowABReportList['RmArrivalID']."
                    LIMIT 1";
    $StmtTrack=$iconn->query($trackSql);
    $TrackCode =  $StmtTrack->fetch_assoc()['TrackCode'];  */                     

    $lot='Harvest/'.$RowABReportList['LotNumber'].'<br> Count :'.$RowABReportList['PurchaseCount'];
    $ArrivalDate = !empty($RowABReportList['ArrivalDate']) ? date("d-m-Y", strtotime($RowABReportList['ArrivalDate'])) : '';
    $content ='<tr >';
        $content .= '<td   align="center"><font size="7">'.$SNo.'</font></td>';
        $content .= '<td   align="center"><font size="7">'.$ArrivalDate.'</font></td>';
        $content .= '<td   align="center">
                        <font size="7">'.trim($RowABReportList['FinancierName']).'<br>
                                        QTY:'.$RowABReportList['PurchaseWeight'].' 
                        </font>
                    </td>';
        $content .= '<td   align="left"><font size="7">'.$lot.'</font></td>';
        $content .= '<td   align="left"><font size="7">'.$RowABReportList['TrackCode'].'</font></td>';
        $content .= '<td   align="center"><font size="7">'.$RowABReportList['CAP'].'</font></td>';
        $content .= '<td   align="center"><font size="7">'.$RowABReportList['AHD'].'</font></td>';
        $content .= '<td   align="center"><font size="7">'.$RowABReportList['AMOZ'].'</font></td>';
        $content .= '<td   align="center"><font size="7">'.$RowABReportList['AOZ'].'</font></td>';
        $content .= '<td   align="center"><font size="7">'.$RowABReportList['SEM'].'</font></td>';
    $content .='</tr>';
    return $content;
}


	//require_once($TcpdfPath); 
	
	
	class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
		
        $image_file = K_PATH_IMAGES.'jme_logo.jpg';
        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		
		
		
		
    }

    // Page footer
    public function Footer() {
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Daily Lab Report :');  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER); 
	$pdf->SetHeaderMargin(145);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
    $content = '';  
   
   // $content .= getHeader($ID,$iconn);  
   
//$pdf->Cell(0 ,5,'JAGADEESH MARINE EXPORTS PVT . LTD',0,1,'C');
if(!empty($EnableTrackCodeInAbReport)){
    $pdf->SetFont('Times','B',14);
    $pdf->Cell(0, 6, $PlnatHeading, 0, 1, 'C');

    $pdf->SetFont('Times','B',11);
    $pdf->Cell(0 ,5,$PlnatLocation,0,1,'C');
   // $pdf->Cell(130 ,5,'',0,1,'C');
    
    $pdf->SetFont('Times','B',11);
    $pdf->Cell(0 ,4,'INTERNAL ANTIBIOTICS TEST REPORT ',0,1,'C');
    //$pdf->Cell(130 ,5,'',0,1,'C');
}else{
    $pdf->Cell(0 ,5,$PlnatHeading,0,1,'C');
    $pdf->Cell(130 ,5,'',0,1,'C');
}



$FONT='';
$HEIGHT=8;
$WIDHT_1='';

$pdf->SetFont('','',10);

//Cell(width , height , text , border , end line , [align] )

$ReportDate = $row['ReportDate'];
$ShiftName = $row['Shift'];

$pdf->Cell(130 ,$HEIGHT,'Daily lab update for testing ( sop...FMT/SOP/119-B)',0,0);
$pdf->Cell(60 ,$HEIGHT,'REPORT DATE :-'.date("d-m-Y", strtotime($ReportDate )),0,1);//end of line

$pdf->Cell(60 ,$HEIGHT,'',0,1);//end of line

$pdf->Cell(130 ,$HEIGHT,'ANALYSIS DATE :-'. date("d-m-Y", strtotime($ReportDate)),'LTB',0);
$pdf->Cell(60 ,$HEIGHT,'SHIFT : '.$ShiftName,'RTB',1);//end of lines

$size=10;


$content = '';  
    $content .= '<table border="1" cellspacing="0"  cellpadding="3">';
    if(!empty($EnableTrackCodeInAbReport)){
        $CAP = $AHD = $AMOZ = $AOZ = $SEM = '';
        if(empty($PlantId) && !empty($_SESSION['UnitID'])){
            $PlantId = $_SESSION['UnitID'];
        }
        $CAP = '0.052 PPB';
        $AHD = '0.201 PPB';
        $AMOZ = '0.206 PPB';
        $AOZ = '0.181 PPB';
        $SEM = '0.217 PPB';
        if(!empty($PlantId) && !empty($ReportDate)){
            $Sql = "SELECT * FROM ABReportParameterCutOffValues WHERE PlantId = '$PlantId' AND EffectedFrom <= '$ReportDate' ORDER BY ID DESC LIMIT 1";
            $Stmt=$iconn->query($Sql);
            $Results = $Stmt->fetch_assoc();
            $rowCount = $Stmt->num_rows;
            if($rowCount > 0){
                $CAP = $Results['CAP'];
                $AHD = $Results['AHD'];
                $AMOZ = $Results['AMOZ'];
                $AOZ = $Results['AOZ'];
                $SEM = $Results['SEM'];
            }
        }
        $content .= '
        <tr> 
            <th width="5%" rowspan="2"   align="center"><font size="7"><b>S.No.</b></font></th>
            <th width="15%" rowspan="2"   align="center"><font size="7"><b>RECEIVED DATE</b></font></th>
            <th  width="35.5%" colspan="3"  align="center">
                <font size="7"><b>SAMPLE DETAILS</b></font>
            </th>
            <th colspan="5"   align="center"><font size="7"><b>ANTIBIOTICS TESTED </b> </font></th>
        </tr>
        <tr>
            <th align="center"> <font size="7"><b>FARMER <br>INFORMATION</b> </font> </th>
            <th align="center"> <font size="7"><b>LOT NO./COUNT </b></font> </th>
            <th align="center"> <font size="7"><b>TRACK CODE</b> </font> </th>
            <th align="center"><font size="7"> <b>CAP<br>'.$CAP.'</b> </font> </th>
            <th align="center"><font size="7"><b> AHD<br>'.$AHD.' </b></font> </th>
            <th align="center"><font size="7"><b>AMOZ<br>'.$AMOZ.'</b></font>   </th>
            <th align="center"><font size="7"><b> AOZ<br>'.$AOZ.' </b></font>  </th>
            <th align="center"><font size="7"><b> SEM<br>'.$SEM.'</b> </font>   </th>
        </tr>';
    }else{
        $content .= '   <tr> 
            <th width="5%"   align="center"><font size="7"><b>S/N</b></font></th>
            <th width="35.5%"  align="center"><font size="7"><b>Sample details / Lot no.s/Count</b></font></th>
            <th   align="center"><font size="7"><b>PARAMETER</b></font></th>
            <th   align="center"><font size="7"><b>LOADING STATUS</b></font></th>
            <th width="25%"  align="center"><font size="7"><b>REMARKS</b></font></th>
        </tr>';  	
    }  

    
    $ReportDate=$row['ReportDate'];
    
    $content .= generateTableRow($ReportDate,$SHIFT,$iconn);		
    $content .= '</table>'; 
    // echo $content;
//$pdf->SetMargins(15, 29, 16,true);
$pdf->writeHTML($content, false); 



$pdf->Cell(60 ,$HEIGHT,'',0,1);//end of line

$pdf->Cell(95,5,'Tested By :'.$TestedBy,1,0,'L',0);
$pdf->Cell(95,5,'Verified By :'.$VerifiedBy,1,1,'L',0);  // cell with left and right borders


$pdf->Cell(95,10,'Signature',1,0,'L',0);
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders						
	
if(empty($EnableTrackCodeInAbReport)){
    $pdf->Ln();	
                                
                                
    $pdf->Cell(46,5,'Procurement Department',1,0,'C',0);
    $pdf->Cell(48,5,'Production Department',1,0,'C',0);  // cell with left and right borders
    $pdf->Cell(48,5,'Store Department',1,0,'C',0);  // cell with left and right borders
    $pdf->Cell(48,5,'Account Department',1,1,'C',0);  // cell with left and right borders // cell with left and right borders

    $pdf->Cell(46,10,'Sign',1,0,'L',0);
    $pdf->Cell(48,10,'Sign',1,0,'L',0);  // cell with left and right borders
    $pdf->Cell(48,10,'Sign',1,0,'L',0);  // cell with left and right borders

    $pdf->Cell(48,10,'Sign',1,1,'L',0); 
}
$pdf->Ln();



 $name = date("d-m-Y");

$pdf->Ln();
//$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);
ob_end_clean();
$pdf->Output('LabReportAb ' .$name, 'I');

?>
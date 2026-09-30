<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
//$ReportDate=$_GET['ReportDate'];
//$SHIFT=$_GET['shift'];

$LotID=$_GET['LotID'];
//echo $TcpdfPath ; exit;

  $sqlABRpt="SELECT * FROM `QcRmAbReport` AS QRAR
LEFT JOIN QcABOptions AS QAB ON QAB.OptID=QRAR.Shift
WHERE LotID='$LotID'
ORDER BY `AbRepID` DESC";
$StmtABRpt=$iconn->query($sqlABRpt);
$row=$StmtABRpt->fetch_array();


$TestedBy=$row['TestedBy'];			
$VerifiedBy=$row['UserID'];	


$sqlTester="SELECT UserName FROM `QcABUsers`WHERE  UserID='$TestedBy' ";
$StmtTester=$iconn->query($sqlTester);
$RowTester=$StmtTester->fetch_array();			
$TestedBy=$RowTester['UserName'];

 $sqlVerifiedBy="SELECT UserName FROM `AdminLoginDetails`WHERE  UserID='$VerifiedBy' ";
$StmtVerifiedBy=$iconn->query($sqlVerifiedBy);
$RowVerifiedBy=$StmtVerifiedBy->fetch_array();			
$VerifiedBy=$RowVerifiedBy['UserName'];


function generateTableRow($LotID,$iconn){


 $SqlABReportList="SELECT * FROM `QcRmAbReport` AS QRAR
LEFT JOIN QcABOptions AS QAO ON QAO.OptID=QRAR.AbStatus
LEFT JOIN RmLotDetatils AS RLD ON RLD.RmLotID=QRAR.LotID
WHERE LotID='$LotID' ORDER BY `AbRepID` DESC";
$StmtABReportList=$iconn->query($SqlABReportList);
							
$SNo=1;
							
while($RowABReportList=$StmtABReportList->fetch_array()){


			$ABStatus=$RowABReportList['Name'];			



$content .='<tr >';

$lot='Harvest/'.$RowABReportList['LotNumber'].'/'.$RowABReportList['PurchaseCount'];



 $content .= '<td   align="center"><font size="8">'.$SNo.'</font></td>';
 $content .= '<td   align="left"><font size="8">'.$lot.'</font></td>';
 $content .= '<td   align="center"><font size="8">'.$RowABReportList['Parameter'].'</font></td>';
 $content .= '<td   align="center"><font size="8">'.$ABStatus.'</font></td>';
 $content .= '<td   align="center"><font size="8">'.$RowABReportList['Remarks'].'</font></td>';

$SNo++;


   $content .='</tr>';
}
		 
		
		


return $content;
}


	// require_once($TcpdfPath); 
	
	
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
$pdf->Cell(0 ,5,'DAILY TEST REPORT LAB - AB',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');


$FONT='';
$HEIGHT=8;
$WIDHT_1='';

$pdf->SetFont('','',10);

//Cell(width , height , text , border , end line , [align] )

$ReportDate = $row['ReportDate'];
$ShiftName = $row['Name'];

$pdf->Cell(130 ,$HEIGHT,'Daily lab update for testing ( sop...FMT/SOP/119-B)',0,0);
$pdf->Cell(60 ,$HEIGHT,'REPORT DATE :-'.date("d-m-Y", strtotime($ReportDate )),0,1);//end of line

$pdf->Cell(60 ,$HEIGHT,'',0,1);//end of line

$pdf->Cell(130 ,$HEIGHT,'ANALYSIS DATE :-'. date("d-m-Y", strtotime($ReportDate)),'LTB',0);
$pdf->Cell(60 ,$HEIGHT,'SHIFT : '.$ShiftName,'RTB',1);//end of lines

$size=10;


		 $content = '';  
		 $content .= '<table border="1" cellspacing="0"  cellpadding="3"><tr>
		 
		 <td width="5%"   align="center"><font size="8">S/N</font></td>
		 <td width="35.5%"  align="center"><font size="8">Sample details / Lot no.s/Count</font></td>
		 <td   align="center"><font size="8">PARAMETER</font></td>
		 <td   align="center"><font size="8">LOADING STATUS</font></td>
		 <td width="25%"  align="center"><font size="8">REMARKS</font></td>
		 
		 ';  	  
		
		 $content .= '</tr>';
		 
		 $ReportDate=$row['ReportDate'];
		 
		 $content .= generateTableRow($LotID,$iconn);		
		 $content .= '</table>'; 
	 // echo $content;
//$pdf->SetMargins(15, 29, 16,true);
$pdf->writeHTML($content, false); 



$pdf->Cell(60 ,$HEIGHT,'',0,1);//end of line

$pdf->Cell(95,5,'Tested By :'.$TestedBy,1,0,'L',0);
$pdf->Cell(95,5,'Verified By :'.$VerifiedBy,1,1,'L',0);  // cell with left and right borders


$pdf->Cell(95,10,'Signature',1,0,'L',0);
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders						
	
$pdf->Ln();	
							
							
$pdf->Cell(46,5,'Procurement Department',1,0,'C',0);
$pdf->Cell(48,5,'Production Department',1,0,'C',0);  // cell with left and right borders
$pdf->Cell(48,5,'Store Department',1,0,'C',0);  // cell with left and right borders
$pdf->Cell(48,5,'Account Department',1,1,'C',0);  // cell with left and right borders // cell with left and right borders

$pdf->Cell(46,10,'Sign',1,0,'L',0);
$pdf->Cell(48,10,'Sign',1,0,'L',0);  // cell with left and right borders
$pdf->Cell(48,10,'Sign',1,0,'L',0);  // cell with left and right borders

$pdf->Cell(48,10,'Sign',1,1,'L',0); 
$pdf->Ln();



 $name = date("d-m-Y");

$pdf->Ln();
//$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);
ob_end_clean();
$pdf->Output('LabReportAb ' .$name, 'I');

?>
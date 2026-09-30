<?php
session_start();

$IsHeader=0;
$IsFooter=0;
$QueryParms=$_GET['Q'];  


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";



 
$PoID=$_GET['PoID'];
$RID=$_GET['RID'];
$ReportName=$_GET['ReportName'];
$FileType=$_GET['Type']; 

$SqlPoData="SELECT AED.ExpName,AED.ExpAddress FROM `ScrmPoData` as POD left join AdminExporterDetails as AED on POD.ProcessorID=AED.ExpID WHERE POD.PoID='$PoID'";
$StmtPoData=$iconn->query($SqlPoData);
$RowPoData=$StmtPoData->fetch_array();
$ClientName=$RowPoData['ExpName'].'<br>'.$RowPoData["ExpAddress"];
   
 $SqlPreHarvest="SELECT * FROM  QcPhAbReports  WHERE ID='$RID'"; 
$StmtPreHarvest=$iconn->query($SqlPreHarvest); 
$RowPreHarvest=$StmtPreHarvest->fetch_array();
//EXIT;
$CodeListId=$RowPreHarvest['TracebilityID'];


  //$SqlInvData="SELECT * FROM  ScrmPoData  WHERE PoID='$PoID'"; 
  $SqlInvData="SELECT *,(SELECT sum(McsTotal)  FROM `QcCodeList` WHERE InvID='$PoID')as TotalMcs
  FROM  ScrmPoData  WHERE PoID='$PoID'"; 
$StmtInvData=$iconn->query($SqlInvData); 
$RowInvData=$StmtInvData->fetch_array();  
$LotNo=$RowInvData['LotNo']; 
$TotalCLMcs=$RowInvData['TotalMcs']; 
$InvNumFull=$RowInvData['InvNumFull']; 
$TotalCLSamples=(sqrt($TotalCLMcs)+1)/2; 

	    //$SqlCode="SELECT * FROM `CODE_LIST_CREATE` WHERE PoID='$PoID'and CODE_LIST_ID='$CODE_LIST_ID'"; 
	    $SqlCode="SELECT * FROM `QcCodeList` WHERE InvID='$PoID' and CLID='$CodeListId'"; 
	    
			$StmtCode=$iconn->query($SqlCode);
			$RowCode = $StmtCode->fetch_assoc();
	
			
			
			$TraceID=$RowCode['TracebilityID'];
			$VarietyID=$RowCode['VarietyID'];
			
			$sql_code_list="SELECT *,SUM(McsGrade) AS McsTotal  FROM `QcCodeList` as Cl 
		
		WHERE Cl.InvID='$PoID' and TracebilityID='$TraceID' AND Cl.VarietyID='$VarietyID'";  
		
	 
	$stmt_code_list=$iconn->query($sql_code_list);
			$r_code_list = $stmt_code_list->fetch_assoc();

			$McsTotal=$r_code_list['McsTotal'];
	 		
	  //$TOTAL_SAMPLES =(sqrt($McsTotal)+1)/2; 
	 // $TOTAL_SAMPLES =round($TOTAL_SAMPLES,2);
	  $TOTAL_SAMPLES =($McsTotal*round($TotalCLSamples))/$TotalCLMcs; 
	  


		

   
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
	$pdf->SetHeaderMargin(50);	
		 
    $pdf->SetMargins(PDF_MARGIN_LEFT,5, PDF_MARGIN_RIGHT,20);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 15);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage('L');  
	

	
$pdf->SetFont($FONT_ARI, 'B', $head);

$pdf->Cell(0, 5, 'COASTAL AQUA PRIVATE LIMITED', 0, 1, 'C');		
$pdf->Cell(0, 5, ' 1-164 PATHARLAGADDA KAKINADA', 0, 1, 'C');		
$pdf->Cell(0, 10, 'FILTH REPORT', 0, 1, 'C');		
$pdf->Cell(0, 5, '', 0, 1, 'C');		

	
	
	$table_data=getDS_TABLE3($iconn,$ID);
	$pdf->writeHTML($table_data);
	
  
  	
//$logo = "images/jme_logo.jpg";
//$file_name='FILTH_REPORT.php'.$row['PO_NUM'];
$file_name='FILTH_REPORT'.$InvNumFull.'.pdf';
$pdf->Cell(95,5,' ',0,1,'C',0);
$pdf->Cell(95,5,' ',0,1,'C',0);

$pdf->Cell(95,5,'Formula:  ',0,1,'L',0);
$pdf->Cell(49 ,5,'As Per sampling code (Squareroot(n) + 1)/2 = '.round($TOTAL_SAMPLES),0,1,'L');
$pdf->Cell(49 ,5,'Where n = Total Master Cartons.',0,1,'L');
$pdf->Cell(49,5,' ',0,1,'C',0);

$pdf->Cell(95,5,'Monitered By ',0,0,'L',0);

$pdf->Cell(95,5,'Verified By ',0,1,'R',0);

ob_end_clean();

$pdf->Output($file_name, 'I');

 


	function getDS_TABLE3($iconn,$ID){

		  global $ClientName,$CodeListId,$PoID,$TraceID,$VarietyID,$TOTAL_SAMPLES;

	//ROBO	
		$sql_code_list="SELECT *,SUM(McsGrade) AS McsTotal  FROM `QcCodeList` as Cl 
		left join QcPoVarietyList as PVL on PVL.VarietyID=Cl.VarietyID
		left join  ScrmPoGradeData as SPGD on SPGD.ID=Cl.VarietyID 
		left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
		left join QcTracebilityList as TL on TL.TraceID=Cl.TracebilityID
		WHERE Cl.InvID='$PoID' and TracebilityID='$TraceID' AND Cl.VarietyID='$VarietyID'";  
		
	 //echo   $sql_code_list;   
	//exit;
	    $stmt_code_list=$iconn->query($sql_code_list);
		$r_code_list = $stmt_code_list->fetch_assoc();
	
			
			$FULL_NAME=$r_code_list['FullName'];
			$McsTotal=$r_code_list['McsTotal'];
			$DayCode=$r_code_list['DayCode']; 
			$ShortName=$r_code_list['ShortName']; 
			
	  $TOTAL_SAMPLES =round($TOTAL_SAMPLES); 



		 
   $Date= getDayCodeDate($DayCode);  

  
	
	$content='';
	
	$size=40;	
	$size1=60;	
 
           $count='';
				$RESULT='Nill';
				$REMARKS='GOOD';
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  
	
	 <!-- <tr> 

              <th  align="center" width="100%"><font size="13"> '.strtoupper($ClientName).' <br>'.strtoupper($ClientAdd).' <br> REGISTER FOR FILTH TEST </font></th> 
               
                
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
				

			$count=1;
				
				for($i=1; $i<$TOTAL_SAMPLES ;$i++)	{//uma $i=0
					
					
					if($i==1){//uma
					 $content .= '	 <tr>
           		
                <th  align="center" width="8%"><font size="9">'.$Date.'  </font></th>
                <th  align="center" width="6%"><font size="9">'.$r_code_list['DayCode'].' </font></th>
                <th  align="center" width="8%"><font size="9"> '.$ShortName.' </font></th>
                <th  align="center" width="10%"><font size="9"> '.$r_code_list['Tracebility'].' </font></th>
                <th  align="center" width="8%"><font size="9">'.$McsTotal.'  </font></th>
                <th  align="center" width="8%"><font size="9"> '.$count.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9">'.$RESULT.'  </font></th>
                <th  align="center" width="7.6%"><font size="9"> '.$REMARKS.' </font></th>
				</tr>';
				$count++;
					}
					
			$content .= '	 <tr>
				 <th  align="center" width="8%"><font size="9">  </font></th>
                <th  align="center" width="6%"><font size="9"> </font></th>
                <th  align="center" width="8%"><font size="9"></font></th>
                <th  align="center" width="10%"><font size="9">  </font></th>
                <th  align="center" width="8%"><font size="9"></font></th>
				<th  align="center" width="8%"><font size="9"> '.$count.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9"> '.$RESULT.' </font></th>
                <th  align="center" width="7.4%"><font size="9">'.$RESULT.'  </font></th>
                <th  align="center" width="7.6%"><font size="9">  '.$REMARKS.' </font></th>
               
               
                
				</tr>';
				
				$count++;
				
	}   
			
				
				
				
	       $content .= ' 
				
		</table>'; 
	
	return $content;
}



?>
<?php
session_start();
$IsHeader=0;
$IsFooter=0;
$QueryParms=$_GET['Q'];  


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");
//include "PrintHeaderInc.php";
 
 
$PoID=$_GET['PoID'];
$ReportName=$_GET['ReportName'];
$FileType=$_GET['Type']; 
$RID=$_GET['RID'];
	
	$SqlScrmPodata="SELECT PoNo FROM `$TblScrmPoData` WHERE PoID='$PoID'";
$StmtPo=$iconn->query($SqlScrmPodata);
$RowPo=$StmtPo->fetch_array();

 $SqlVerified="SELECT VerifiedBy FROM $TblQcCodeList  as CL	WHERE InvID='$PoID' ORDER BY `DayCode` ASC";  
	
	$StmtVerified=$iconn->query($SqlVerified); 
	$RowVerified=$StmtVerified->fetch_array();
	
	
class MYPDF extends TCPDF {
	
    //Page header  
    public function Header() {
        // Logo
		
       
       		include "Constants.php";
        //$image_file = $COMPANY_LOGO;
		//$image_file = 'http://jm.silverlights.in/qc/images/jme_logo.jpg';

        $this->Image($image_file, 10, 5, 25, 20, 'JPG', 'SEAFOOD IMPORT MONITERING PROGRAMME', 'T', false, 400, '',
		false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
       // $this->Cell(0, 15, 'SEAFOOD IMPORT MONITERING PROGRAMME', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		
		
		
		
    }

   
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
   }
}


	
    $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('SIMP Docs Note: '.$row['PoNo'].' - '.$row['PoDate']);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(40); 
	$pdf->SetHeaderMargin(10);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT,45);  
    $pdf->setPrintHeader(TRUE);  
    $pdf->setPrintFooter(false);  
//	$fontname = TCPDF_FONTS::addTTFfont('fonts/cambria.ttf', 'UTF-8', '', 32);

	
    $pdf->SetAutoPageBreak(TRUE, 40);  
    
    $pdf->AddPage();  
	
	//$fontname = $pdf->addTTFfont('/fonts/cambria.ttf', 'TrueTypeUnicode','', 32);
	
		$pdf->SetFont('ZapfDingbats','', 10);
		//$pdf->SetFont($fontname,'', 10);
	 //$str = iconv("UTF-8", "ISO-8859-1", "?");
 $str ="4";
	 $pdf->SetFont('helvetica', '', 11); 
     $pdf->SetFont($fontname,'B', 15);	 
     $pdf->Cell(0 ,15,'SEAFOOD IMPORT MONITERING PROGRAMME',0,1,'C');
	 $pdf->SetFont($fontname,'', 10);	
	  $pdf->SetFont($FONT_ARI,'',$header);
	   $table_data=getPageOne($iconn,$PH_ID,$str,$PoID,$pdf,$RID);
	   $table_data1=explode("&",$table_data[1]);
	   
	        $pdf->SetFont($fontname,'B', 10);	 

		$pdf->Cell(150 ,5,' PO NUMBER : '.$table_data1[0],0,1,'L');
		$pdf->Cell(150 ,5,' PO DATE : '.$table_data1[1],0,0,'L');
		$pdf->Cell(150 ,5,'CONTAINER NUMBER : '.$table_data[2],0,1,'L');
	
	 $pdf->writeHTML($table_data[0]);
	
	$pdf->AddPage();  
	 $pdf->SetFont($fontname,'B', 15);	 
   $pdf->Cell(0 ,15,'SEAFOOD IMPORT MONITERING PROGRAMME',0,1,'C');
	$pdf->SetFont($fontname,'', 10);	
	//$table_data=getDS_TABLE($iconn,$ID,$str);
	$table_data=getDS_TABLE($iconn,$PH_ID,$str,$PoID,$pdf,$RID);
	$pdf->writeHTML($table_data);
	
 
  
$file_name='DS'.$row['PoNo'].'.pdf';

ob_end_clean();
$pdf->Output($file_name, 'I'); 



function getPageOne($iconn,$PH_ID,$str,$PoID,$pdf,$RID){
	 
			
			global $PROCESSOR,$PROCESSOR_PHONE,$PROCESSOR_ADD,$PROCESSOR_EMAIL,$PROCESSOR_APP_NUM;
			
		 	 $SqlPhAb="select * from QcPhAbReports WHERE ID='$RID'"; 
			$StmtPhAb=$iconn->query($SqlPhAb);
			$RowPhAb = $StmtPhAb->fetch_array();
			
			
			
	
	
		  
	
			$CodeList=$RowPhAb['TracebilityID']; 
			$SqlCodeList="select * from QcCodeList  as CL 
			left join  QcTracebilityList as QTL on QTL.TraceID=CL.TracebilityID
			WHERE CLID='$CodeList'";
			$StmtCodeList=$iconn->query($SqlCodeList);
			$RowCodeList = $StmtCodeList->fetch_array();
			
			//$InvID=$RowCodeList['InvID'];
		 	//$ID=$RowCodeList['InvID'];
		 	$Traceability=$RowCodeList['TracebilityID'];
		 	$DayCode=$RowCodeList['DayCode'];
			
		 	$SqlSumEh="select 
			sum(ReceivedQty) as ReceivedQty,
			sum(FinishedWeight) as FinishedWeight from QcPhAbReports AS EH 
			left join QcCodeList as CL on EH.TracebilityID= CL.CLID   WHERE 
			CL.TracebilityID='$Traceability' and CL.DayCode='$DayCode' and CL.InvID='$PoID'";
			
			$SumEhExec=$iconn->query($SqlSumEh);
			$SumEhRow = $SumEhExec->fetch_array();
			
			
			$SqlPoData="select *,SCI.ContainerNo AS ContrnrNo from ScrmPoData  as SP 
			left join QcCoveringLetter as QCL on QCL.InvID=SP.PoID
			left join ScrmCustomInvoices as SCI on SCI.PiID=SP.PoID
			WHERE PoID='$PoID'";
			
			//echo $SqlPoData; exit;
			$StmtPoData=$iconn->query($SqlPoData);
			$RowPoData = $StmtPoData->fetch_array();
			
			$ContainerNo=$RowPoData['ContrnrNo'];

			
		

	$content='';
	
	$header=12 ;	
	$text=8;	
	
 
 $weight=$SumEhRow['ReceivedQty'];
 //$weight=$SumEhRow['ReceivedQty']." Kgs";
 $DateOfLanding=getDayCodeDate($RowCodeList['DayCode']);
 //$weight=$RowCodeList['HEAD_ON_WEIGHT'];
  $FinishedWeight=$SumEhRow['FinishedWeight']." Kgs";
 $Tracebility=$RowCodeList['Tracebility'];
 $FarmLocation=$RowCodeList['FarmerName'].''.$RowCodeList['AddressContinued'];
 $ProductForm='HEAD ON SHRIMP';
 

  


 //$PoDate = date("d-m-Y", strtotime($RowPoData['PoDate']));
 $PoDate = getFormatedDate($RowPoData['PoDate']);
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">	
	  

				<tr style="background-color:#D3D3D3">  
           		<th  align="center"  width="100%"><font size="'.$header.'"><b  >  Catch Certificate for Traceability- Harvest and Landing Receipt </b> </font>
				
				</th>
               </tr>	 

	            <tr>  
           		<th  align="LEFT"  width="45%"><font size="'.$text.'"><b>(1) Production Document Identifier*:</b></font>
				<br><font align="center" size="14"> <b>'.$Tracebility.' </b> </font></th>
                <th  align="LEFT" width="55%"><font face="Arial" size="'.$text.'"> 
				<b>(x)Wild Capture Fishery (Complete Section 2) <br><br><br>
				
				';
				
	
 

 
	
				$content .= ' (<span style="font-family:zapfdingbats;">3</span>)';
				
				$content .= 'Farm Raised(Complete Section 3) </b> </font></th>
                

				</tr>
				
				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"> <b>(2) Complete this Section for Wild
				Capture Fish Products ... NOT APPLICABLE </b> </font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"><b>Flag state of Vessel </b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"><b>Name of Harvesting Vessel:**</b></font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"><b>Vessel Registration or Documentation Number:**</b></font></th>


				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b> Fishing Permit Number** </b> </font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Fishing / catch Area: </b> </font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Fishing Gear: </b> </font></th>
                

				</tr>
				
				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"> <b> (3) Complete this Section for Farm
				raised Fish Products </b> </font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"><b> State of Jurisdriction: </b></font>
				<br><font align="center" size="14"><b>INDIA</b></font>
				</th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Facility License or Authorization**: </b> </font>
				<br><font align="center" size="14"><b>'.$Tracebility.' </b></font>
				</th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b>Name and Location of Aquaculture Facility**: </b><br><b>'.$FarmLocation.'</b></font></th>
                

				</tr>

				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"> <b> (4)Complete this section for receipt of Fishery
				Products: </b></font></th>
               </tr>
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b>Name of Processor: <br>'.$PROCESSOR.' <br> '.$PROCESSOR_ADD.'</b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Telephone: '.$PROCESSOR_PHONE.'<br><br> 
				Email:'.$PROCESSOR_EMAIL.' </b> </font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Business Address: <br>'.$PROCESSOR.'<br>'.$PROCESSOR_ADD.'</b> </font></th>
                

				</tr>    
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b>Receiving Facility :<br>'.$PROCESSOR.'<br> '.$PROCESSOR_ADD.'</b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b>Date of Landing: </b></font> 
				<br><font align="center" size="14"><b>'.$DateOfLanding.' </b></font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Delivery Location: <br>'.$PROCESSOR.'<br> '.$PROCESSOR_ADD.'</b></font></th>
                

				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b> Species of fish (ASFIS 3 alpha code): </b></font>

				
				<br><font align="center" size="14"><b>2280100111</b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Delivered Weight: </b></font>
				<br><font align="center" size="14"><b>'.round($weight,2).' Kgs </b></font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Product Form: </b></font>
				<br><font align="center" size="14"><b>'.$ProductForm.' </b></font></th>
                

				</tr>';
				

				
			$content .= '</table>'; 
	
	$data= array();
	$data[]=$content;
	$data[]=$RowPoData['PoNo'].'& '.$PoDate;
	$data[]=$ContainerNo;
	return $data;	
	
}

function getDS_TABLE($iconn,$PH_ID,$str,$PoID,$pdf,$RID){
	
				global $PROCESSOR,$PROCESSOR_PHONE,$PROCESSOR_ADD,$PROCESSOR_EMAIL,$PROCESSOR_APP_NUM;

			$SqlPhAb="select * from QcPhAbReports WHERE ID='$RID'"; 
			$StmtPhAb=$iconn->query($SqlPhAb);
			$RowPhAb = $StmtPhAb->fetch_array();
			
			
	
	
			$CODE_LIST=$RowPhAb['TracebilityID']; 
			 $SqlCodeList="select * from QcCodeList as CL 
			left join  QcTracebilityList as QTL on QTL.TraceID=CL.TracebilityID
			WHERE CLID='$CODE_LIST'"; 
			//$SqlCodeList="select * from QcCodeList WHERE TracebilityID='AP028222/001' and InvID=74";
			$StmtCodeList=$iconn->query($SqlCodeList);
			$RowCodeList = $StmtCodeList->fetch_array();
			
			
		 	$TracebilityID=$RowCodeList['TracebilityID'];
			
		 	$DayCode=$RowCodeList['DayCode'];
			
		 	 $SqlSumEh="select 
			sum(ReceivedQty) as ReceivedQty,
			sum(FinishedWeight) as FinishedWeight from QcPhAbReports AS PAB 
			left join QcCodeList as CL on PAB.TracebilityID= CL.CLID   WHERE 
			CL.TracebilityID='$TracebilityID' and CL.DayCode='$DayCode' and CL.InvID='$PoID'"; 
			
			$SumEhExec=$iconn->query($SqlSumEh);
			$SumEhRow = $SumEhExec->fetch_array();
			
			//include "Constants.php";
			
			$VarietyId=$RowCodeList['VarietyID'];
			$SqlVariety="select * from QcPoVarietyList WHERE VarietyID='$VarietyId'";
			$StmtVariety=$iconn->query($SqlVariety);
			$RowVariety = $StmtVariety->fetch_array();

	$content='';
	$size=40;	
	$size1=60;	
 	$header=12;	
	$text=8;	  
	
	
	
	
 $DateOfLanding=getDayCodeDate($RowCodeList['DayCode']);
 $weight=$SumEhRow['ReceivedQty']." Kgs";
 $Tracebility=$RowCodeList['Tracebility'];
  $FinishedWeight=$SumEhRow['FinishedWeight'] ." Kgs";;
 $FarmLocation=$RowCodeList['FarmerName'].''.$RowCodeList['AddressContinued'];
 $ProductForm='HEAD ON SHRIMP';
 $product_op_form=$RowVariety['FullName'];
 $PACKING=$RowCodeList['PACKING'];
 $McsTotal=$RowCodeList['McsTotal'];
 //$InvID=$RowCodeList['InvID'];
 
 $McsTotal=0;
 $product_op_form='';
 $PACKING='';
			
			
			 $SqlCodeList="select * from QcCodeList  as CL 
			 left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID
			 
			 WHERE TracebilityID='$TracebilityID' and InvID='$PoID' GROUP BY CLID";
			$StmtCodeList=$iconn->query($SqlCodeList);
			//ECHO	 $SqlCodeList;
			//EXIT;
			while($RowCodeList = $StmtCodeList->fetch_array()){ 
				$McsTotal=$McsTotal + $RowCodeList['McsTotal'];
				
				$VarietyID=$RowCodeList['VarietyID'];
				
			 $SqlVariety="SELECT Variety,QcDescription FROM `ScrmPoGradeData` WHERE InvId='$PoID'";
			$StmtVariety=$iconn->query($SqlVariety);
			$RowVariety = $StmtVariety->fetch_array();
				
				
				
				
				IF($RowVariety['QcDescription']==''){
				$product_op_form[]=$RowVariety['Variety'];	
					
				}ELSE{
					
				$product_op_form[]=$RowVariety['QcDescription'];	
				}
				
				
				$PACKING[]=$RowCodeList['PackingStyle'];
				
			}
			
			$product_op_form=array_unique($product_op_form);
				echo  $product_op_form=implode('<br>', $product_op_form); 
				
				$PACKING=array_unique($PACKING);
				echo  $PACKING=implode('<br>', $PACKING); 
			//ECHO	$product_op_form;
			//EXIT;	

	 $content .= '<table border="1" cellspacing="0" cellpadding="3">	
	 

				<tr  style="background-color:#D3D3D3" >  
           		<th  align="center"  width="100%"><font size="'.$header.'"> <b> Reprocessing Certificate for Traceability- 
				Primary or Secondary Processing</b> </font></th>
               </tr>

<tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"><b>(x)Wild Harvest or 
				
				';
				
	
 

 
	
				$content .= ' (<span style="font-family:zapfdingbats;">3</span>)';
				
				$content .= 'Farm Raised </b> </font></th>
               </tr>			   

	            <tr>  
           		<th  align="LEFT"  width="50%"><font size="'.$text.'"> <b>Unique Catch or Production Document Identifier: </b></font>
				<br><font align="center" size="14"><b>'.$Tracebility.' </b></font>
				</th>
                <th  align="LEFT" width="50%"><font size="'.$text.'"> <b> Company Product Received From: <br>'. $FarmLocation.' </b></font></th>
                
				</tr>
				
				  <tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b> Processor Name: <br><br> '.$PROCESSOR.' </b> </font></th>
                <th  align="LEFT" width="25%"><font size="'.$text.'"> <b> Processor Address: <br>'.$PROCESSOR_ADD.'</b> </font></th>
				<th  align="LEFT" width="23%"><font size="'.$text.'"><b> Processor Approval Number: </b></font>
<br><font align="center" size="14"><b>'.$PROCESSOR_APP_NUM.'</b></font></th>
				<th  align="LEFT" width="27%"><font size="'.$text.'"> <b> Telephone :'.$PROCESSOR_PHONE.' <br> 
				Email:'.$PROCESSOR_EMAIL.'  </b></font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b>Commodity Scientific Name: </b></font>
				<br><font align="center" size="'.$header.'"><b>PENAEUS VANNAMEI  </b></font></th>
				
                <th  align="left" width="48%"><font size="'.$text.'"> <b>Commodity Market Name: </b></font>
				<br><font align="center" size="'.$header.'"><b>SHRIMPS </b></font></th>
                
                <th  align="LEFT" width="27%"><font size="'.$text.'"><b> Product ASFS#:</b></font>
				<br><font align="center" size="'.$header.'"><b>2280100111 </b></font></th>
                

				</tr>
			
				
				<tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b> Received Weight KGS: </b> </font>
				<br><font align="center" size="14"><b>'.round($weight,3).' Kgs </b></font></th>
                <th  align="LEFT" width="25%"><font size="'.$text.'"> <b> Received LOT Identifier(s)A: </b> </font>
				<br><font align="center" size="14"><b>'.$Tracebility.'  </b></font></th>
                <th  align="LEFT" width="50%"><font size="'.$text.'"> <b> Received Product Form: </b></font>
				<br><font align="center" size="14"><b>'.$ProductForm.'  </b></font></th>


				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b> Processed Weight KGS: </b></font>
				<br><font align="center" size="14"><b>'.round($weight,3).' Kgs </b></font></th>
                <th  align="center" width="25%"><font size="'.$text.'"> <b> Finished Product Lot Identifier(s)a: </b></font>
				<br><font align="center" size="14"><b>'.$Tracebility.'  </b></font></th>
                <th  align="LEFT" width="50%"><font size="'.$text.'"> <b>Finished Product Form: <br><br> </b> </font>
				<br><font align="center" size="'.$text.'"><b>'.$product_op_form.'  </b></font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> <b> Finished Product Weight KGS: </b> </font>
				<br><font align="center" size="14"><b>'.$FinishedWeight.' </b></font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="38%"><font size="'.$text.'"> <b> Production Date(s): </b></font>
				<br><font align="center" size="14"><b>'.$DateOfLanding.' </b></font></th>
                <th  align="LEFT" width="32%"><font size="'.$text.'"><b> Total No Of Packages: </b></font>
				<br><font align="center" size="14"><b>'.$McsTotal.' MCS </b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Type of Packaging: </b></font>
				<br><font align="center" size="14"><b>'.$PACKING.' </b></font></th>
                

				</tr>';


				
			$content .= '</table>'; 
	
	return $content;	
	
}


?>
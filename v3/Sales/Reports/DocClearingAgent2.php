<?php
//ini_set( 'display_errors', 1 );

session_start();

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
 $Type=$_GET['Type'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");


class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
				
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 15);
		$this->Cell(190, 0, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		$this->Cell(0, 10, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 15, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	

    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('WILLIAMS CLARKE CO : '.$Header);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 20);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$PoID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
 
$file_name=$InvShortCode.'-SD-'.$CInvNo.'-'.$FinancialYear.'.pdf';

ob_end_clean();
if($Type==''){
$Type='I';
}else{
		$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}$pdf->Output($file_name, $Type);
//$pdf->Ln();


function getDS_TABLE($iconn,$PoID){
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../SalesInculdes/SalesConstants.php"; 

	global $PoDataRow;
	

$SqlScrmPoData="SELECT * FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN AdminExporterDetails as AED on SPD.ProcessorID=AED.ExpID
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 WHERE PoID='$PoID'"; 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();

$ExpID=$PoDataRow['ExporterID'];
$ConsigneeName=$PoDataRow['ConsigneeName'];
$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
$PoNo=$PoDataRow['PoNo'];
$ReferenceNo=$PoDataRow['ReferenceNo'];
$PoDate=getFormatedDateNA($PoDataRow['PoDate']);
$ProcsrName=$PoDataRow['ExpName'];
$ProcsrAddress=$PoDataRow['ExpAddress'];	
	
	
  $CustomInvoiceSql="SELECT * FROM $ScrmCustomInvoices as SCI
  left join`$ScrmShipmentDetails` as  SSD on SSD.PiID=SCI.PiID
  left join $ScrmShipmentDispatchDetails as SD on SD.PIID=SSD.PiID
  WHERE SCI.PiID='$PoID'"; 

$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
$InvDate=$PoDataRow['InvDate'];
$LoadingPort=$PoDataRow['Name'];

$SqlPoGradeData="SELECT 
SUM(Mcs) as MCS,Brand FROM `ScrmPoGradeData`  WHERE InvId='$PoID'"; 
$StmtPoGradeData=$iconn->query($SqlPoGradeData); 
$RowPoGradeData = $StmtPoGradeData->fetch_assoc();

$MCS=$RowPoGradeData['MCS'];
$Brand=$RowPoGradeData['Brand'];

$PaymentData=getPaymentValues($iconn,$PoID);

$ExporterData=getExporterDetails($ExpID);
$PackSealNo=$CustomInvoiceRow['PackSealNo'];
$SealNo=$CustomInvoiceRow['SealNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
$CInvCode=$CustomInvoiceRow['CInvCode'];
$VehicalNumber=$CustomInvoiceRow['VehicalNumber'];
$SBNo=$CustomInvoiceRow['SBNo'];
$BLNo=$CustomInvoiceRow['BLNo'];
$PlaceOfRecipt=$CustomInvoiceRow['PlaceOfRecipt'];
$FinalDestination=$CustomInvoiceRow['FinalDestination'];
$BLDate=getFormatedDateNA($CustomInvoiceRow['BLDate']);

$ETD=getFormatedDateNA($CustomInvoiceRow['ETD']);
$ETA=getFormatedDateNA($CustomInvoiceRow['ETA']);
		
$ExpName=$ExporterData['ExpName'];
$ExpAddress=$ExporterData['ExpAddress'];
$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

$SqlPOgradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PoID'  ";
$StmtPOgradeData=$iconn->query($SqlPOgradeData);

WHILE($RowPOgradeData = $StmtPOgradeData->fetch_assoc()){

$MCS=	$RowPOgradeData['Mcs']; 
   $TotalMcs+=$MCS; 
}

 $SqlTenPlusTwo="SELECT * FROM `$ScrmLogisticsDetails` as STD LEFT JOIN $ScrmLogisticsMaster 
as SL on SL.ID=STD.ConsolidatorNameAddress WHERE PiID='$PoID'"; 
$StmtTenPlusTwo=$iconn->query($SqlTenPlusTwo);
$RowTenPlusTwo=$StmtTenPlusTwo->fetch_assoc();
				
$VesselName=$RowTenPlusTwo['VesselName'];
$VOY=$RowTenPlusTwo['VOY'];
	

		
		
	$content='';

	$size=40;	 
 
	$size1=60;	



			  	
			  
			  
			  $width=100;
			  
              
			  
			  $content .= '
			  <H4 align="Center"><b><u>WILLIAMS CLARKE CO.,INC <BR>
			  IMPORTER SECURITY FILING DATA (ISF-10+2)WORKSHEET TEMPLATE </b></u></H4>
			  <H4 align="Center"><b><u></b></u></H4>
			  
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">


               <tr>  	   
				 <th  align="LEFT" width="50%" ><B>Importer Name:</B> <BR>'.$ConsigneeName.'</th>    
				 <th  align="LEFT" width="35%" >P.O or Ref# <BR>'.$PoNo.' /'.$ReferenceNo.'</th>    
				 <th  align="LEFT" width="15%" >Date: <BR>'.$PoDate.'</th>    
				</tr>
				
			  <tr> 	   
				 <th  align="LEFT" width="50%" >Master B/L w/SCAC: <BR></th>    
				 <th  align="LEFT" width="50%" >* House Way Bill w/SCAC(if applicable): <BR></th> 
			  </tr>
			  
			  <tr> 			   
				 <th  align="LEFT" width="30%" >Originating Importing Vessel:<BR></th>    
				 <th  align="LEFT" width="20%" >Voy# <BR></th> 
				 <th  align="LEFT" width="50%" >Est. date of loading:<BR>
				 '.$BLDate.'('.$LoadingPort.')</th> 
				 
			  </tr>  
				 
			<tr> 	   
				 <th  align="LEFT" width="30%" >Final Importing Vessel:<BR></th>    
				 <th  align="LEFT" width="20%" >Voy# <BR></th> 
				 <th  align="LEFT" width="30%" >Est. date of transshipment<BR></th>    
				 <th  align="LEFT" width="20%" >ETA:<B><BR>
				 '.$FinalDestination.'<br>'.$ETA.'</B></th> 
			</tr> 

           
			   <tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"><B>Container No.
			   (attachment if necessart)<br>'.$ContainerNumber.'</B></th>  
				 
                </tr> 
				<tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"> 1. Importer of record number
			   (IRS #,Customs assigned#,or SS # as applicable): <BR> 
			   </th>  
				
                </tr> 
				
				<tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"> 2. Buyer -compete name & address: 
			   <BR> 
			   <B>'.$ConsigneeName.','.$ConsigneeAddress.'</B><BR></th>  
				
                </tr> 
				<tr> 
				
			   <th  align="LEFT"  width="'.$width.'%">3. Consignee number (IRS #,Customs assigned#,or SS # as applicable): <BR></th>  
				
                </tr> 
				
				<tr> 
				
			   <th  align="LEFT"  width="'.$width.'%">4. Consignee name & address : <BR>
			   <B>'.$ConsigneeName.','.$ConsigneeAddress.'</B><BR></th>  
				
                </tr> 
				
				
				<tr> 				
			     <th  align="LEFT"  width="'.$width.'%">5. Ship to-complete name & address:</th>  
				 
                </tr> 
				
				<tr> 				
			     <th  align="LEFT"  width="'.$width.'%">6. *Manufacture (Or Supplier)-Complete name & address:
				 <b><br>   '.$ProcsrName.'<br>   '.$ProcsrAddress.'</b></th>  
				 
                </tr> 
				
				<tr> 				
			     <th  align="LEFT"  width="'.$width.'%">7. *Seller -Complete name & address:
				 <B><BR>   '.$ExpName.',<BR>  '.$ExpAddress.'<BR></B></th>  
				 
                </tr>
				
				<tr> 				
			     <th  align="LEFT"  width="'.$width.'%">8. *Container Stuffing location-Complete name & address:
				 <b><br>    '.$ProcsrName.'<br>   '.$ProcsrAddress.'</b></th>  
				 
                </tr>
				
				<tr> 				
			     <th  align="LEFT"  width="'.$width.'%">9. *Consolidator(if applicable)-Complete name & address:
				<B><BR>   '.$ExpName.',<BR>  '.$ExpAddress.'<BR></B></th>  
				 
                </tr>
						   
				<tr> 				  
				 <th  align="LEFT" width="'.$width.'%" >10. *Country of Origin : INDIA 
				 <BR></th>    
				
			   </tr>
			   
			    <tr> 				  
				 <th  align="LEFT" width="'.$width.'%" >11. Detailed commodity description or HTS number:
				 
				 <BR>
				 <BR>PACKING :  BRAND :
				 <BR>    <B>TOTAL:</B>'.$TotalMcs.'
				 <BR>    <B>HS CODE:</B>
				 </th>    
				
			   </tr>
				
				
              
                			   
		
			  '; 
			  
	
			$content .= '</table>';


	return $content;	

}

?>
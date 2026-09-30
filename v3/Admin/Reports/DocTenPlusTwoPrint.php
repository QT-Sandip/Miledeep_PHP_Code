<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PoID=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");



 $ID=$_GET['id'];
 $Type=$_GET['Type'];
//include "/QualityControl/PrintHeaderInc.php";
 
$SqlStatus="SELECT * FROM $TblScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();


 $CustomInvoiceSql="SELECT * FROM $ScrmCustomInvoices as SCI
left join $ScrmShipmentDispatchDetails  as SSD on SSD.PIID=SCI.PiID
left join $ScrmShipmentDetails  as SD on SD.PiID=SCI.PiID

 WHERE SCI.PiID='$PoID'";
/* $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCV 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading

 WHERE SCV.PiID='$PoID'";*/
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvoiceRow['IGST'];


 $SqlScrmPoData="SELECT * FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 

$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];



$ExporterData=getExporterDetails($ExpID);
$ProcessorData=getExporterDetails($ProcessorID);

$PaymentTerms=$PoDataRow['PaymentTerms'];
$DeliveryTerms=$PoDataRow['DeliveryTerms'];
$BankID=$PoDataRow['BankID'];
  $Status=$PoStatus['Status']; 


 

	

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status,$IGST
;
	if($IGST=='IGST'){
		$Header2='SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
	}
	else{
		$Header2='';
	}
		if($Status==0){
	$StatusTerm="(Approval Pending)";
}
else if($Status==2){
	$StatusTerm="(cancelled)";
}
	
else if($Status==4){
	$StatusTerm="(Rejected)";
}
global $Header,$ExporterData,$ExpID;		
       // $image_file = 'images/ds_logo.jpg';
		if($ExpID==5){
					$img_file = 'https://sme.silverlights.in/qc/images/sunriselogo.jpeg';
      	$this->Image($img_file, 180, 7, 20, 15, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);

		}
else{
			$img_file = 'https://sme.silverlights.in/qc/images/sme_logo.jpg';
      	$this->Image($img_file, 180, 6, 20, 20, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);

}
		
$ExpArray=str_split($ExporterData['ExpAddress'],52);
		
        $this->SetFont('helvetica', 'B', 10);
		
		$Data='<table style="background-color: #99A3A4;" width="100%" cellspacing="3" cellpadding="5%">
          <tr valign="bottom">
                <td  rowspan="10" align="center" valign="middle"
                      width="89%">'.$ExporterData['ExpName'] .'<br>'.$ExpArray[0].'<br>'.$ExpArray[1].'</td>
          </tr></table>';
		

		
		//uma
 //$this->SetFillColor(169,169,169);
// $this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
 //$this->SetFillColor(130,130,130);
 //$this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
// $this->MultiCell(175, 10, $ExporterData['ExpName'], 1, 'C', 1, 0, '', '', true, 0, true, true, 0);
 $this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
 //$this->Cell(175, 0, ''.$ExporterData['ExpAddress'], 0, 1, 'R', 0, '', 0, false, 'T', 'M');
  $this->SetFont('helvetica', 'B', 9);
 $this->writeHTML($Data);
 //$this->writeHTML('<p>'.$ExporterData['ExpAddress'].'</p>');

		//$this->Cell(130, 0, '', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		        $this->SetFont('helvetica', 'BU', 10);

		$this->Cell(130, 0, 'IMPORTER SECURITY FILING (ISF) DATA SHEET 10+2', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetTextColor(255,0,0);
		
		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');

    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('10+2 Report');  
	
    $pdf->SetTitle(''.$Header2);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10);
	
	$pdf->SetHeaderMargin(8);	
	     
    $pdf->SetMargins(10, 10, 10,5);  
	
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);


    $pdf->SetFont('helvetica', '', 11);  
    //$pdf->ln;  
	
	    $pdf->AddPage();  

	$pdf->SetLineStyle( array( 'width' => 0.1, 'color' => array(5,5,5)));
		$pdf->Rect(6, 6, $pdf->getPageWidth()-12, $pdf->getPageHeight()-12);
		$pdf->Rect(5.4, 5.4, $pdf->getPageWidth()-10.8, $pdf->getPageHeight()-10.8);
	
	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

  
/***$file_name=$ExporterData['InvShortCode'].'_'.$PoDataRow['InvNo'].'.pdf';**/



/***uma 28-07-2022**/
$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   
if($PoDataRow['ExporterID']==5){		
if($CurrentMonth<=3){
	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	
	
} 
else{ 

 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
 
}
		}
		else{		
if($CurrentMonth<=3){
	
 	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	

	
} 
else{ 
 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
		}
/***uma 28-07-2022**/

$file_name=$ExporterData['InvShortCode'].'-TENplusTWO-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
//sme/pi/233/2022-23


//echo $Type;
ob_end_clean();
if($Type==''){
$Type='I';
}else{
	$Fname=__DIR__ ."/TempFolder/";
	$Fname=$_SERVER['DOCUMENT_ROOT']."SalesNew/Reports/TempFolder/";
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );
	$file_name=$Fname.$file_name;
}

$pdf->Output($file_name, $Type);


			


function getDS_TABLE($iconn,$ID){
	
	global $PoDataRow;
	
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
	include "../SalesInculdes/SalesConstants.php"; 

			
			$F_NAME='';
			$T_O_MCS='';
			$DOE='';
			$DOS='';
			$TOTAL_MCS=0;
			$TOTAL_SLAB=0;
			$T_EACH_SLAB=0;
			$TOTAL_NET_WT_KGS=0;
            $TOTAL_NET_WT_LBS=0;			
	        
			
		//uma
		
 $SQL_PO_GRADE_DATA="SELECT * FROM `$ScrmCIGradeData` where InvId='$ID'  ";
$STMT_PO_GRADE_DATA=$iconn->query($SQL_PO_GRADE_DATA);

WHILE($PO_GRADE_DATA = $STMT_PO_GRADE_DATA->fetch_assoc()){

  $SLAB=$PO_GRADE_DATA['SLAB']; 
  $TOTAL_SLAB+=$SLAB;
  
  $EACH_SLAB	=	$PO_GRADE_DATA['EACH_SLAB'];
  $T_EACH_SLAB+=$EACH_SLAB;
  
  $MCS=	$PO_GRADE_DATA['Mcs']; 
   $TOTAL_MCS+=$MCS; 
  
   $UOM=	$PO_GRADE_DATA['UOM']; 
 /*  $BAP=	$PO_GRADE_DATA['BAP']; 
  if($BAP=='NA'){
	 $BAP='';  
   }
  ELSE{
	$BAP=$BAP;  
  }*/
			
  if($UOM=="LBS"){
    $NET_WT_LBS=(($SLAB*($EACH_SLAB))*$MCS) ."LBS"; 
  // $NET_WT_KGS=(($SLAB*($EACH_SLAB*453))*$MCS)/1000 ."KGS"; 
     $NET_WT_KGS=(($SLAB*($EACH_SLAB))*$MCS)/2.2 ."KGS"; 

  
  }
 else if($UOM=="KGS"){
     $NET_WT_KGS=(($SLAB*($EACH_SLAB))*$MCS) ."KGS";  
 
 
   $NET_WT_LBS=(($SLAB*($EACH_SLAB*2.2))*$MCS) ."LBS"; 
  
  }
    $TOTAL_NET_WT_KGS=$NET_WT_KGS+$NET_WT_KGS;    
	   $TOTAL_NET_WT_LBS=$NET_WT_LBS+$NET_WT_LBS;		
	   }	
			$TOTAL_NET_WT_KGS;		
            $TOTAL_NET_WT_LBS;	
	        $TOTAL_MCS; 

			
	
	

	
	
  
global $CustomInvoiceRow,$ExpID ;
global $PoDataRow ;
global $ExporterData ;
global $ProcessorData ;
global $TblAdminBankDetails ;
global $BankID ;
global $TblScrmPaymentTerms ;
global $TblAdminExporterDetails ;
global $SignURL ,$PoID;
	
	//Exporter /Shipper Details
	
			//$ExporterID=$ExporterData['ExpID'];
			$ExporterName=$ExporterData['ExpName'];
			$ExporterAddress=$ExporterData['ExpAddress'];
			$ExporterUsfdaNo=$ExporterData['ExpUsfdaNo'];
			$ExporterApprovalNo=$ExporterData['ExpApprovalNo'];
			$ExporterIeCustomsNo=$ExporterData['ExpIeCustomsNo']; 
			$ExporterGstNo=$ExporterData['ExpGstNo']; 
			$ExporterIeCode=$ExporterData['ExpIeCode'];
			$ExpPanNumber=$ExporterData['ExpPanNumber'];
			$ExpBranchCode=$ExporterData['ExpBranchCode'];
			 $AuthSign=$ExporterData['AuthSign'];	
			 			$InvShortCode=$ExporterData['InvShortCode']; 

	//Processor/Packer

$ProcessorID=$PoDataRow['ProcessorID'];	
			$ProcessorNamee=$ProcessorData['ExpName'];
			IF($ProcessorID==3){
	 $SqlProcessor="select substring_index ('$ProcessorNamee',' ',4) AS STRING  "; 
	$StmtProcessor=$iconn->query($SqlProcessor);
	$RowProcessor=$StmtProcessor->fetch_assoc();
	 $ProcessorName=$RowProcessor['STRING'];
	 
	 $SqlProcessor1="select substring_index ('$ProcessorNamee',' ',-2) AS STRING  "; 
	$StmtProcessor1=$iconn->query($SqlProcessor1);
	$RowProcessor1=$StmtProcessor1->fetch_assoc();
	 $ProcessorName1=$RowProcessor1['STRING']; 
			}
ELSE	{
	$ProcessorName=$ProcessorNamee;
}		
			$ProcessorAddress=$ProcessorData['PackerAddress'];
			$ProcessorUsfdaNo=$ProcessorData['ExpUsfdaNo'];
			$ProcessorApprovalNo=$ProcessorData['ExpApprovalNo'];
			$ProcessorFEI=$ProcessorData['ExpFeiNo'];
		$CurrentMonth = strftime("%m");  
if($CurrentMonth<=3){
	
 $NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%Y")-1;  	
	
} 
else{ 
 $CurrentYear = strftime("%Y");  	
 $NxtYear = strftime("%y")+1;  	
}					$InvNO=$InvShortCode.$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;
 
			
		
			
			$InvDate=getFormatedDate($CustomInvoiceRow['CInvDate']);
			$PoDate=getFormatedDate($CustomInvoiceRow['CpoDate']);
			
			$PlaceOfReceiptBy=$CustomInvoiceRow['PlaceOfRecipt'];
			$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
			$SealNo=$CustomInvoiceRow['SealNo'];
			$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
			$PackSealNo=$CustomInvoiceRow['PackSealNo'];
			$VesselNumber=$CustomInvoiceRow['VesselNumber'];
			$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
			$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
			$CInvCode=$CustomInvoiceRow['CInvCode'];
			$IGST=$CustomInvoiceRow['IGST'];
			$PortOfDischarge=$CustomInvoiceRow['PortofDischarge'];
			$FinalDestination=$CustomInvoiceRow['FinalDestination'];
			$NameofRepresentative=$CustomInvoiceRow['NameofRepresentative'];
			$BLNo=$CustomInvoiceRow['BLNo'];
			$Discount=$CustomInvoiceRow['Discount'];

			
			$SqlUserDetails="SELECT * FROM `$TblAdminUserDetails` WHERE UserID='$NameofRepresentative' ";
		$StmtUserDetails=$iconn->query($SqlUserDetails);
		$RowUserDetails=$StmtUserDetails->fetch_assoc();
		
		
$SqlTenPlusTwo="SELECT *,SLM.LinerName as SteamshipCompany FROM `$ScrmLogisticsDetails` as STD 
LEFT JOIN $ScrmLogisticsMaster as SL on SL.ID=STD.ConsolidatorNameAddress
LEFT JOIN $ScrmLinerMaster as SLM on SLM.LinerID=STD.SteamshipCompany 

 WHERE PiID='$PoID'";
		$StmtTenPlusTwo=$iconn->query($SqlTenPlusTwo);
		$RowTenPlusTwo=$StmtTenPlusTwo->fetch_assoc();
		
		
		$VesselName=$RowTenPlusTwo['VesselName'];
		$VOY=$RowTenPlusTwo['VOY'];
		$VesselDepartureDate=date('d-m-Y',strtotime($RowTenPlusTwo['VesselDepartureDate']));
		$ImportingVessel=$RowTenPlusTwo['ImportingVessel'];
		$ImportingVOY=$RowTenPlusTwo['ImportingVOY'];
		$SteamshipCompany=$RowTenPlusTwo['SteamshipCompany'];
		$ShippingToNameAddress=$RowTenPlusTwo['ShippingToNameAddress'];
		$ConsolidatorNameAddress=$RowTenPlusTwo['Address'];

			$PoNo=$PoDataRow['PoNo'];
			//$PoDate=getFormatedDate($PoDataRow['PoDate']);
			
			$PoDate= "& Dated :".$PoDate;
			IF($PoNo ==''){
				
			$PoDate=' ';	
			}
			
			IF($VesselDepartureDate =='01-01-1970'){
				
			$VesselDepartureDate='NA';	
			
			}
			ELSE{
			$VesselDepartureDate;	
			}
			
			$ConsigneeName=$PoDataRow['ConsigneeName'];
			$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];

			
			/*$PortOfDischarge=$PoDataRow['Name'];
			$FinalDestination=$PoDataRow['FinalDestination'];*/
			$DestinationCountry=$PoDataRow['name'];
			$ByPayment=$PoDataRow['ByPayment'];
			$PreparedBy=$PoDataRow['PreparedBy'];
			$PaymentTerms=$PoDataRow['PaymentName'];
			$DeliveryTerms=$PoDataRow['DeliveryTerms'];
			$ShippingTerms=$PoDataRow['ShippingTerms'];
			$QtyUnit=$PoDataRow['QtyUnit'];


	
	
			
	$content='';
	                                                                  

	$size=45;	
	$size1=55;	
	$FontSize=9;	

	
				$Whitspace='<b style="color:white"> yuhju</b>';

$StyleTbl='Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';
  
  $StyleTd='Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';
  $w1='28%';
$w2='59%';
$w4='10%';
$w3='25%';
$w5='3%';

$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
			 $SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `$ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$ID'"; 
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
							if($QtyUnit=='KGS'){

			$NetWgtKgs = $RowTotal['NetWgtKgs'];
							$GrossWeightKgs = $RowTotal['GrossWeightKgs'];}
		else{	
$NetWgtKgs = $NetWgtLbs*0.454;
		$GrossWeightKgs = $RowTotal['GrossWeightLbs']*0.454;}

			
			if( $PoDataRow['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				//$Weight = $RowTotal['NetWgtLbs']*0.454;	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'];
				//$Weight = $RowTotal['NetWgtKgs'];
				$Price = $RowTotal['PriceLbs'];
			}
			
		 IF($IGST=='DISCOUNT'){
				
				$DiscountAmount=($Price*$Discount)/100;
				$TotalDiscount=$Price-$DiscountAmount;
		 }
		 ELSE{
			$TotalDiscount=$Price; 
		 }
				
 
	 $content .= '<table border="0" cellspacing="0" cellpadding="0">  
							

				<tr> <br> <br> <br><br> <br><br> 
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">1.  </font></th>
           		<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">MANUFACTURER NAME and  ADDRESS </font></th>
                           		<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'"><b>'.$ProcessorName.'</b><BR>'.$ProcessorAddress.'</font></th>				
           </tr>
		   <br> 
		   <tr> 
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">2. </font></th>
		   
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">SELLER NAME and ADDRESS<br>(with zip code if applicable)</font></th>
                           		<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>

				<th width="'.$w2.'" align="left"><font size="'.$FontSize.'"><b> '.$ExporterName.'</b><br>'.$ExporterAddress.'</font></th>				
           </tr>
		   
		  

		   <tr> 
		   <br>           
		   <th width="'.$w5.'" align="left"><font size="'.$FontSize.'">3. </font></th>

		   
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">ULTIMATE CONSIGNEE and ADDRESS</font></th>
                           		<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>
      
				<th width="'.$w2.'" align="left"><font size="'.$FontSize.'"> <B>'.$ConsigneeName.'</B><br>'.$ConsigneeAddress.'</font></th>				
           </tr> 

		   <tr>
<br>		   
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">4.</font></th>

				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">SHIP TO NAME and ADDRESS</font></th>
                           		<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>

				<th width="'.$w2.'" align="left"><font size="'.$FontSize.'">'.$ShippingToNameAddress.'</font></th>				
           </tr>
		   <tr> 
<br>		   
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">5.</font></th>

				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">CONTAINER STUFFING LOCATION</font></th>
                           		<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>

				<th width="'.$w2.'" align="left"><font size="'.$FontSize.'"><b>'.$ProcessorName.'</b><BR>'.$ProcessorAddress.'</font></th>				
           </tr>
		   <tr>
<br>		   
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">6.</font></th>

				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">CONSOLIDATOR NAME,(stuffer) and ADDRESS (or freight forwader) </font></th>
                           		<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>

			   <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">'.$ConsolidatorNameAddress.'</font></th>				
           </tr> 
		   
		  
		   
		  
		   <tr> 
<br>
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">7.</font></th>
		   
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">IMPORTER OF RECORD NUMBER </font></th>
                           		<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>

				<th width="'.$w2.'" align="left"><font size="'.$FontSize.'">054601-00407</font></th>				
           </tr>
		   <tr> 
<br>		  
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">8.</font></th>
 
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">ULTIMATE CONSIGNEE NUMBER</font></th>
                 <th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>
  
				<th width="'.$w2.'" align="left"><font size="'.$FontSize.'">N.A</font></th>				
           </tr> 
		   
		  <tr> 
<br>		  
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">9.</font></th>

				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">COUNTRY OF ORIGIN</font></th> 
				<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>

                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">INDIA</font></th>				
           </tr>
		   
		   <tr> 
<br>		   
           		<th width="'.$w5.'" align="left"><font size="'.$FontSize.'">10.</font></th>

				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">HTSUS NUMBER(6 DIGIT)</font></th>
				<th width="'.$w4.'" align="left"><font size="'.$FontSize.'">  : </font></th>

                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">'.$CInvCode.'</font></th>				
           </tr>
		 
		   
		   
		</table>
				</th>
				
				
				<th width="100%"><br>
				
<div class="row">
  <div class="column">
  <table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="CENTER"  ><font size="7" ><b>VESSEL NAME</b>
				</font></th>
				<th  align="CENTER" ><font size="7"><b> VOY#</b></font></th>
				<th  align="CENTER" ><font size="7"><b>VESSEL DEPARTURE DATE</b>
				</font></th>
				<th  align="CENTER"  ><font size="7" ><b>FINAL IMPORTING VESSEL</b>
				</font></th>
				<th  align="CENTER" ><font size="7"><b> VOY#</b>
				</font></th>
				
	
				</tr>
				
				<tr> 
				
				<th  align="CENTER"  ><font size="'.$FontSize.'" >'.$VesselName.'</font></th>
				<th  align="CENTER"  ><font size="'.$FontSize.'" >'.$VOY.'</font></th>
				<th  align="CENTER"  ><font size="'.$FontSize.'" >'.$VesselDepartureDate.'</font></th>
				<th  align="CENTER"  ><font size="'.$FontSize.'" >'.$ImportingVessel.'</font></th>
				<th  align="CENTER"  ><font size="'.$FontSize.'" >'.$ImportingVOY.'</font></th>
				
				</tr>';
			
			
				
			$content .= '	
				
				
				</table>
			
</div>
  </div>
					</th>
				
				</tr> 
				
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
	
 <tr>  			

				<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"> <B>PO NUMBER</B></font></th>
                <th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.$PoNo.'</font></th>
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>TOTAL NET WEIGHT(LBS)</B></font>	</TH>			
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.number_format($NetWgtLbs,3).'</font>	</TH>			
           </tr>
	<tr>  
				<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>BILL OF LADING NUMBER</B></font></th>
                <th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.$BLNo.'</font></th>	
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>TOTAL NET WEIGHT(KG)</B></font>	</TH>			
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.number_format($NetWgtKgs,3).'</font>	</TH>			
				
           </tr>
	<tr>  
				<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>CONTAINER NUMBER</B></font></th>
                <th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.$ContainerNumber.'</font></th>	
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>TOTAL GROSS WEIGHT(LBS)</B></font>	</TH>			
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.number_format($GrossWeightLbs,3).'</font>	</TH>			
				
           </tr>					   		

	<tr>  
				<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>STEAMSHIP COMPANY</B></font></th>
                <th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.$SteamshipCompany.'</font></th>	
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>TOTAL GROSS WEIGHT(KG)</B></font>	</TH>			
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.number_format($GrossWeightKgs,3).'</font>	</TH>			
				
           </tr>
		 
	<tr>  
				<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>PIECE COUNT</B></font></th>
                <th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.$TOTAL_MCS.'MCS</font></th>	
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'"><B>TOTAL INVOICE VALUE US$</B></font>	</TH>			
<th width="'.$w3.'" align="left"><font size="'.$FontSize.'">: '.number_format($TotalDiscount,2).'</font>	</TH>			
				
           </tr>
		 
	            
				
				
				
				
				</table>
				</th>
				</tr>
				
				
				</table>
				</th>
				</tr>
				
				';
				
				
			$content.='</table>';
			  return $content;
	
	}


?>
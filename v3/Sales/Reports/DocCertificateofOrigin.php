<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
/** Folder Includes **/
 $PoID=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."SalesInculdes/SalesConstants.php");
//include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");


 $ID=$_GET['id'];
 $Type=$_GET['Type'];

 
$SqlStatus="SELECT * FROM $TblScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();


//$CustomInvoiceSql="SELECT * FROM ScrmCustomInvoice  WHERE PiID='$PoID'";
 $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading 
FROM $ScrmCustomInvoices as SCV 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
 left JOIN $ScrmShipmentDispatchDetails as SSDD on SSDD.PIID=SCV.PiID
   left JOIN $ScrmLogisticsDetails as SLD on SLD.PiID=SCV.PiID

 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN $TblScrmCountriesList as SL on SL.id=SP.Country

 WHERE SCV.PiID='$PoID'"; 
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();

 $SqlScrmPoData="SELECT *,SCDB.ConsigneeName as BuyerName,SCDB.ConsigneeAddress as BuyerAddress,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress
 FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmConsigneeDetails as SCDB on SPD.BuyerID=SCDB.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];


/*$SqlLogin="SELECT * FROM login  WHERE USER_ID='$ApprovedBy'";
$StmtLogin=$iconn->query($SqlLogin); 
$RowLogin = $StmtLogin->fetch_assoc();
$SignURL=$RowLogin['SignURL'];*/


$ExporterData=getExporterDetails($ExpID);
$ProcessorData=getExporterDetails($ProcessorID);

$PaymentTerms=$PoDataRow['PaymentTerms'];
$DeliveryTerms=$PoDataRow['DeliveryTerms'];
$BankID=$PoDataRow['BankID'];
  $Status=$PoStatus['Status']; 


 

//require_once('../tcpdf/tcpdf.php'); 
	

class MYPDF extends TCPDF { 
 
    public function Header() {
		
global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 12.5);
		$this->Cell(110, 0, 'CERTIFICATE OF ORIGIN'.$Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		$this->Cell(110, 0, '   '.$Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetTextColor(255,0,0);
		
		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');	
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}

if($Type==''){
$Type='I';
//$Header=' -  VIEW ONLY';
//$Header=' -  VIEW ONLY';
}
	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('CERTIFICATE OF ORIGIN'.$Header);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

  
//$file_name=$ExporterData['InvShortCode'].'_'.$PoDataRow['InvNo'].'.pdf';

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

$file_name=$ExporterData['InvShortCode'].'-PL-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
//sme/pi/233/2022-23

//echo $Type;

ob_end_clean();
$pdf->Output($file_name, $Type);


			


function getDS_TABLE($iconn,$ID){
	
	global $PoDataRow,$PoID;
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
/** Folder Includes **/
 
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
global $SignURL ;
		 $CountryName=$PoDataRow['name']; 

	//Exporter /Shipper Details
	
			//$ExporterID=$ExporterData['ExpID'];
			$ExporterName=$ExporterData['ExpName'];
			$ExporterAddress=$ExporterData['ExpAddress'];
			$ExporterUsfdaNo=$ExporterData['ExpUsfdaNo'];
			$ExporterApprovalNo=$ExporterData['ExpApprovalNo'];
			$ExporterIeCustomsNo=$ExporterData['ExpIeCustomsNo'];
			$ExporterRecAdd=$ExporterData['ImporterRecordAddress'];
            $ExporterGstNo=$ExporterData['ExpGstNo']; 
			$ExporterIeCode=$ExporterData['ExpIeCode'];		
            $AuthSign=$ExporterData['AuthSign'];
            $ProcessorIeCustomsNo=$ProcessorData['ExpIeCustomsNo'];
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
	 //$ProcessorName1=$RowProcessor1['STRING']; 
			}
ELSE	{
	$ProcessorName=$ProcessorNamee;
}		$ProcessorName=$ProcessorData['ExpName'];

			//$ProcessorAddress=$ProcessorData['ExpAddress'];
			$ProcessorAddress=$ProcessorData['PackerAddress'];
			$ProcessorUsfdaNo=$ProcessorData['ExpUsfdaNo'];
			$ProcessorApprovalNo=$ProcessorData['ExpApprovalNo'];
			$ProcessorFEI=$ProcessorData['ExpFeiNo'];
		
		
//$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

$InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);			
			
			$InvDate=getFormatedDate($CustomInvoiceRow['CInvDate']);
			$PoDate=getFormatedDate($CustomInvoiceRow['CpoDate']);
			if($InvDate=='01-01-1970'){
				
				$InvDate=': na';
			}
			//$PreCarriageBy=$CustomInvoiceRow['CInvNo'];
			
            $PortOfDischarge=$CustomInvoiceRow['PortofDischarge'];
			$FinalDestination=$CustomInvoiceRow['FinalDestination'];
			$SealNo=$CustomInvoiceRow['SealNo'];
			$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
			$SealNo=$CustomInvoiceRow['SealNo'];
			$QCertificateNo=$CustomInvoiceRow['QCertificateNo'];
			$VesselNumber=$CustomInvoiceRow['VesselNumber'];
			/*if($CountryName!='CHINA'){

			$VesselNumber=$CustomInvoiceRow['VesselName'].'/'.$CustomInvoiceRow['VOY'];}
			else{
				
						$VesselNumber=$CustomInvoiceRow['VesselName'].' '.$CustomInvoiceRow['VOY'];}*/
						

			if($VesselNumber==''){
            $VesselNumber="BY SEA";
			}
            else{
				$VesselNumber;
             }
			 
			 if($DestinationCountryId!='26'){

			$VesselNumber=$CustomInvoiceRow['VesselName'].'/'.$CustomInvoiceRow['VOY'];}
			else{
				
			$VesselNumber=$CustomInvoiceRow['VesselName'].' '.$CustomInvoiceRow['VOY'];
			}

			$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
			$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
			$BLNo=$CustomInvoiceRow['BLNo'];
			$SBNo=$CustomInvoiceRow['SBNo'];
			$BLDate=date('d-m-Y',strtotime($CustomInvoiceRow['BLDate']));
			$SBDate=date('d-m-Y',strtotime($CustomInvoiceRow['SBDate']));
			
			$NoOfDays=$CustomInvoiceRow['NoOfDays'];
			$PackDescription=$CustomInvoiceRow['PackDescription'];
			
			
			if($NoOfDays==0){
				$NoOfDays='';
			}
			else{
				$NoOfDays=$NoOfDays.'DAYS FROM BL';
			}
			
			if($BLDate=='01-01-1970'){
				$BLDate='NA';
			}
			else{
		$BLDate=$BLDate;

			}
			if($SBDate=='01-01-1970'){
				$SBDate='NA';
			}
			else{
				$SBDate=$SBDate;
			}
			
			$PoNo=$PoDataRow['PoNo'];
			$PoDate=getFormatedDate($PoDataRow['PoDate']);
			
			$PoDate= "& Dated :".$PoDate;
			IF($PoNo ==''){
				
			$PoDate=' ';	
			}
			
			$ConsigneeName=$PoDataRow['ConsigneeName'];
			$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
			$BuyerName=$PoDataRow['BuyerName'];
			$BuyerAddress=$PoDataRow['BuyerAddress'];
			$ConsigneeID=$PoDataRow['ConsigneeID'];
			$BuyerID=$PoDataRow['BuyerID'];
			
			$DestinationCountryId=$PoDataRow['DestinationCountry'];

			
			
			$DestinationCountry=$PoDataRow['name'];
			$ByPayment=$PoDataRow['ByPayment'];
			$PreparedBy=$PoDataRow['PreparedBy'];
			$PaymentTerms=$PoDataRow['PaymentName'];
			$DeliveryTerms=$PoDataRow['DeliveryTerms'];
			$ShippingTerms=$PoDataRow['ShippingTerms'];
			$QtyUnit=$PoDataRow['QtyUnit'];

           if($DestinationCountryId==26){
			$PlaceOfReceiptBy=str_ireplace("PORT"," ",$CustomInvoiceRow['PlaceOfRecipt']);
			$PortOfLoading=str_ireplace("PORT"," ",$CustomInvoiceRow['PortOfLoading']);
			}
			ELSE{
				$PlaceOfReceiptBy=$CustomInvoiceRow['PlaceOfRecipt'];
			$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
			}
	
	
			
	$content='';
	                                                                  

	$size=45;	
	$size1=55;	

	

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
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  
				
				 <tr>  
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';
				
		/*if($PoDataRow['ExporterID']==5){		
		$content .= '<tr>
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'
				<BR>
				IE CODE  : '.$ExporterIeCode.'<BR>
				GST NO  : '.$ExporterGstNo.'
				</font></th>
		</tr>';}
				else{	*/	
		$content .= '<tr> 
           		
				<th  align="LEFT" ><font size="7"><b>Exporter:</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'<BR>';
				if($CountryName!='CHINA'){
					$content .= 'USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>
				IE CODE  : '.$ExporterIeCode.'<BR>GST NO  : '.$ExporterGstNo.'</font></th>
				</tr>';}
					
					
					
				else{
			$content .= '</font></th></tr>';}
//}
				
				if($ConsigneeID!=$BuyerID){
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				<br><b><u>Consignee</u></b><br>
				'.$BuyerName.',<br>'.$BuyerAddress.'
				
				
				</font></th>
				</tr>';}
				else{
				$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				
				
				</font></th>
				</tr>';	
				}
				
				$content .= '<tr>  
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">'.$PlaceOfReceiptBy.'</font></th>
				
				</tr>
				<tr>  
           		
				<!--<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR align="center">'.$VesselNumber.'  
				</font></th>-->
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR align="center">'.$VesselNumber.' 
				</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">'.$PortOfLoading.', '.strtoupper($CustomInvoiceRow['PortOfLoadingCountry']).'</font></th>
				
				</tr>
				
				<tr>  

				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">'.strtoupper($PortOfDischarge).' '.strtoupper($CustomInvoiceRow['CountryCode']).', '.strtoupper($CustomInvoiceRow['PortofDischargeCountry']).'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">'.$FinalDestination.'
				</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> '.$InvNO.',Dated '.$InvDate.' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> '.$PoNo.$PoDate.' </font></th>
				</tr>';
			
				$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$ProcessorApprovalNo.'</b>
				<br>
				<u><b> Processed & Packed By</u> :</b><br> '.$ProcessorName.'<br> '.$ProcessorAddress.' ';
		if($CountryName!='CHINA'){
			$content .= '
				
				<br> USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo.'<BR> FEI # :  '.$ProcessorFEI.'';
		}
		$content .= '</font></th>
			</tr>';
				
			
				
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  '.strtoupper($DestinationCountry).'</b></font></th>
				</tr>
				
			<tr> 
				<th  align="LEFT"  width="100%"><font size="7"><b><u>Terms of Delivery and Payment:</u>
				</b><br><BR> ' .$PaymentTerms .'<BR> SHIPMENT TERM : '.$ShippingTerms .' '.strtoupper($FinalDestination) .'
				';
				if($DestinationCountryId!=26){
				
				
				$content .= '('.$DeliveryTerms.')<br>';
				}
				
				$content .= '
				<br> BL NUMBER : '.$BLNo.'    ,DATED :'.$BLDate.' <br> SB NUMBER : '.$SBNo.'  ,DATED : '.$SBDate.'</font> </th>
				</TR>
				
				</table>
				</th>
				
				</tr> ';
				if($DestinationCountryId==26){
					$LBS='KGS';
				}	
else{
$LBS='LBS'	;
}				
				
				
		 $SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP,ProductionDate,ExpiryDate FROM `$ScrmCIGradeData` 
			left join `$TblAdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand"; 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);	
while($RowVarietyMcs=$StmtVarietyMcs->fetch_array()){
	$ProductionDate[]=getFormatedDate($RowVarietyMcs['ProductionDate']);
	$ExpiryDate[]=getFormatedDate($RowVarietyMcs['ExpiryDate']);
	
	$ProductionDates=implode(',',$ProductionDate);
	$ExpiryDates=implode(',',$ExpiryDate);
	
}	


if($ProductionDates!=''&&$ExpiryDates!=''){
	$Remarks='<th  align="center" width="14%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">Production Dates </font></th>
			<th  align="center" width="14%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">Expiry Dates </font></th>';
	$EmptyFields='<th  align="center" width="14%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7"> </font></th>
			<th  align="center" width="14%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7"> </font></th>';
}
elseif($ProductionDates!=''&&$ExpiryDates==''){
	$Remarks='<th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">Production dates </font></th>';
	$EmptyFields='<th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7"></font></th>';
}
else{
	$Remarks='<th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">Remarks</font></th>';
	$EmptyFields='<th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7"></font></th>';
}
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);		
				
				
				
			$content .= '	<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> '.$TOTAL_MCS.'</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><font size="10"><b align="center">'.$ContainerNumber.'</b></font></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><font size="10"><b align="center">'.$SealNo.'</b></font></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in '.$LBS.' </font></th>';
					if($DestinationCountryId==26){
						
						$content .= '  <th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">Gross Weight<br>in KGS </font></th>';}

             
				else{

               $content .= $Remarks ;}
               
          $content .= '      </tr>
				
				</table>
				</th>
				</tr>
				
				';
				
if($CountryName!='CHINA'){
				$description_head='<B><u>Description of Goods: </u></B><BR><BR>';}
				else{
				$description_head='<B><u>Description of Goods: </u></B><BR>';}
								$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='<tr >
				
			<table border="0" cellspacing="0" cellpadding="1">
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$description_head.' </font></td>
			<td width="13.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" > </font></td>';
			$content.=$EmptyFields;
			$content.='</tr>';
			

			  
			while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
				
				
				$Variety=$VarietyMcsRow['Variety'];
				$Grade=$VarietyMcsRow['Grade'];
				$Brand=$VarietyMcsRow['Brand'];
				 $BAP=	$VarietyMcsRow['BAP']; 
				 $PackingStyleID=	$VarietyMcsRow['PackingStyleID']; 
  if($BAP=='NA'){
	 $BAP='';  
   }
  ELSE{
	$BAP=$BAP;  
  }
			if($CustomInvoiceRow['Description']!=''){	
			$varietyDescription=$CustomInvoiceRow['Description'];
				
			}
			else{
							$varietyDescription=' CARTONS OF '.$VarietyMcsRow['Variety'];

				}
				$varietyDescription=' CARTONS OF '.$VarietyMcsRow['Variety'];
				
				$content.='
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" > '
			.$VarietyMcsRow['VarietyMcs'].'  '.$varietyDescription.'<br>
			PACKING : '.$VarietyMcsRow['PackingStyle'].' 
			
			
			';
			
			if($DestinationCountryId!=26){
			$content.='NET WEIGHT PER CARTON.'.$Whitspace.' <B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}
			
							
		$content.='</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>';
			$content.=$EmptyFields;
			$content.='</tr>
			
			

			
			<tr>';
			
if($DestinationCountryId==26){

						$content.='	<th align="center" width="40%" ><font size="'.$FontSize.'"> <u>SIZE(PCS/LB)</u></font></th>';}
	else{

						$content.='	<th align="center" width="40%" ><font size="'.$FontSize.'"> </font></th>';}			
						$content.='<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>';
			$content.=$EmptyFields;
			$content.='</tr>';
			
			//UMA
				  $SqlVariety="SELECT * FROM `$ScrmCIGradeData` as PGD
			left join $TblAdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$ID' 
			and Variety='$Variety' and Brand='$Brand' 
		and	PackingStyleID='$PackingStyleID' order by abs(Grade)";  
		// AND Grade='$Grade'
			$StmtVariety=$iconn->query($SqlVariety);
				
			while($RowVariety=$StmtVariety->fetch_assoc()){
				
				// 26 ID CHINA
				$ProductionDate=getFormatedDate($RowVariety['ProductionDate']);
			$ExpiryDate=getFormatedDate($RowVariety['ExpiryDate']);
			
				if( $PoDataRow['QtyUnit']=='KGS'){
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];
				$UsdValue=($Qty)*$RowVariety['Price'];				
				}else{
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['LbsFactor'];										
				 $UsdValue=($Qty)*$RowVariety['Price'];
				}
				
				if($ProductionDates!=''&&$ExpiryDates!=''){
	$ProdandExpiryDates='<th  align="center" width="14%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">'.$ProductionDate.'</font></th>
			<th  align="center" width="14%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">'.$ExpiryDate.'</font></th>';
}
elseif($ProductionDates!=''&&$ExpiryDates==''){
	$ProdandExpiryDates='<th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7">'.$ProductionDate.'</font></th>';
}
else{
	$ProdandExpiryDates='<th  align="center" width="28%" style="border-right: 1px solid black;border-bottom: none">
											<font size="7"></font></th>';
}

				
				/*$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];
				$UsdValue=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] *$RowVariety['Price'];	
			*/
			$content.='
				<tr>
				
			<!--	<td align="center" width="12.5%" ><font size="7">
				'.$RowVariety['Variety'].'</font></td> -->
				<td align="center" width="40%"  ><font size="'.$FontSize.'">
				</font></td>
				<!--<td align="center" width="22.5%"><font size="7">
				'.$RowVariety['PackingStyle'].'</font></td> -->
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$tt.'</font></td>';
				
				$content.= $ProdandExpiryDates;
				
				
				$content.= '</tr>';
			}


			}//UMA
			
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
			
			$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			
			if( $PoDataRow['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'];
				//$Weight = $RowTotal['NetWgtKgs'];
				$Price = $RowTotal['PriceLbs'];
			}
			
			$content.='
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				'.$RowTotal['MCS'].'</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.$Weight.'.000</font></td>';
				if($DestinationCountryId==26){
			$content.='	<td align="center" width="28%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">'.number_format($GrossWeightKgs,3,'.','').'
				</font></td>';}
				else{
			$content.='	<td align="center" width="28%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>';}
				
				 
				$McsInWords=numberToMcswords($RowTotal['MCS']);
	
				
			$content.='	</tr>';
			
			$content.= '</table>
			  </tr>';

  $SqlBank="SELECT * FROM $TblAdminBankDetails WHERE ID='$BankID'";
$StmtBank=$iconn->query($SqlBank); 
$RowBank = $StmtBank->fetch_assoc();
$BenfName=$RowBank['AccntName'];
$BenfAccNo=$RowBank['AccountNumber'];
$BenfBankName=$RowBank['BankName'];
$BenfBankAddress=$RowBank['BankAddress'];
$BenfBankCode=$RowBank['SwiftCode'];
	 

				$BILL_WORD=numberTowords($Price);
				
				
				$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				if($DestinationCountryId==26){
                     $ShippingTerms=$PoDataRow['ShippingTerms'] ;
							}
							else{
								$ShippingTerms=='';
							}
				
								$Whitspace='<b style="color:white"> yu</b>';

			$content.='<tr> '.
           		 $TOTAL.'
				 
				 
	
				</tr>

				<tr>  
           		<th  align="LEFT"  width="100%" ><font size="7">  ('.$McsInWords.') 
				<br>SHIPPING TERMS   : '.$ShippingTerms.' '.$FinalDestination .'
				
				'.str_replace(array("\r\n"," \r", " \n"), "<br>",$PackDescription).' 
				
				';
				
				
				$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
				
				
				
				if($QtyUnit=='KGS'){
				$content.='<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: '.number_format($NetWgtKgs, 3, '.', '').' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: '.number_format($GrossWeightKgs,3,'.','').' KGS</b> 
				';	
				}else{
				$content.='<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>'.number_format($NetWgtLbs, 3, '.', '').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($NetWgtLbs*0.454),3,'.','').' KGS</b>
				<BR> GROSS WEIGHT<b style="color:white">------------------------------</b>: 
				<b>'.number_format($GrossWeightLbs,3,'.','').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($GrossWeightLbs*0.454),3,'.','').' KGS</b> 
				';	
				}
				$content.='</font></th></tr>
				
				
               
				
						
	        
				
<tr>';

$content.='<th  align="bottom"  width="70%"><font size="8"> <b>CERTIFICATION</b><BR> 
<br> It is hereby certified that to the best of our knowledge and behalf the above mentioned goods are of Indian Origin. </font></th>';
  if($DestinationCountryId!=26){
  $content.='<th  align="center"  width="30%"><font size="7"> Signature:<br>
  For the Vizagapatam Chamber of Commerce & Industry
  <br>
  				

				<B> '.$AuthSign.'</B><BR><BR><BR><BR>
				 
				<!--<img src="'.$SignURL.'" 
				 width="100" height="10"style="width: 100" align="center" align="middle"/>-->
								
				
				<p STYLE="text-align:center" ><b> Authorised Signature  </b></p></font></th>';}
		else{
  $content.='<th  align="center"  width="30%"><font size="7"> Signature:<br><br><br><br><br><br>
  				

			</font></th>';}
				
				
$content.='</tr>			
				
				</table>'; 
	
	return $content;
	
	}


?>
<?php
session_start();
$PoID=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/configg.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
//include ($_SERVER['DOCUMENT_ROOT']."/Clients/Sme/Sales/Includes/IncludePage.php");
include ($cp."Includes/IncludePage.php");

 $ID=$_GET['id'];
 $Type=$_GET['Type'];


 
$SqlStatus="SELECT * FROM $TblScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();


  //$CustomInvoiceSql="SELECT * FROM $ScrmCustomInvoice  WHERE PiID='$PoID'";
  $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCV 
  left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID

 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN $TblScrmCountriesList as SL on SL.id=SP.Country

 WHERE SCV.PiID='$PoID'";
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvoiceRow['IGST'];


 $SqlScrmPoData="SELECT * FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
 
 
 $SqlPaymentTerms="SELECT * FROM $TblScrmPoData as SPD 
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
 
 
$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);
$RowPaymentTerms = $StmtPaymentTerms->fetch_assoc();

 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];




$ExporterData=getExporterDetails($ExpID);
$ProcessorData=getExporterDetails($ProcessorID);

//$PaymentTerms=$PoDataRow['PaymentTerms'];

$DeliveryTerms=$PoDataRow['DeliveryTerms'];
$BankID=$PoDataRow['BankID'];
  $Status=$PoStatus['Status']; 


 

//require_once('../tcpdf/tcpdf.php'); 
	

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
global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 9);
		$this->Cell(100, 0, 'INVOICE'.$Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', ' ', 8);

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
	
    $pdf->SetTitle('INVOICE'.$Header);  
    $pdf->SetTitle(''.$Header2);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10);
	
	$pdf->SetHeaderMargin(4);	
	     
    $pdf->SetMargins(10, 10, 10,5);  
	
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);

    $pdf->SetFont('helvetica', '', 11);  
    //$pdf->ln;  
	
	    $pdf->AddPage();  

	
	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

  

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
		
$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;
		
		
/***uma 28-07-2022**/

$file_name=$ExporterData['InvShortCode'].'-QI-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
//ob_end_clean();

if($Type==''){
$Type='I';
//$Header=' -  VIEW ONLY';
}
else{
	//$Fname=__DIR__ ."/TempFolder/";
	$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName']; 
	$file_name = str_replace('/','', $file_name );
	 $file_name=$Fname.$file_name;
}
$pdf->Output($file_name, $Type);

			


function getDS_TABLE($iconn,$ID){
	
	global $PoDataRow,$RowPaymentTerms,$PoID;

	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
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
		
 $SQL_PO_GRADE_DATA="SELECT * FROM `ScrmCIGradeData` where InvId='$ID'  ";
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


	
	//Exporter /Shipper Details
	
			//$ExporterID=$ExporterData['ExpID'];
			$ExporterName=$ExporterData['ExpName'];
			$ExporterAddress=$ExporterData['ExpAddress'];
			$ExporterUsfdaNo=$ExporterData['ExpUsfdaNo'];
			$ExporterApprovalNo=$ExporterData['ExpApprovalNo'];
			$ExporterIeCustomsNo=$ExporterData['ExpIeCustomsNo']; 
			$ExporterGstNo=$ExporterData['ExpGstNo']; 
			$ExporterIeCode=$ExporterData['ExpIeCode'];
			$ExporterRecAdd=$ExporterData['ImporterRecordAddress'];
			$AuthSign=$ExporterData['AuthSign'];
			
	//Processor/Packer
	$ProcessorIeCustomsNo=$ProcessorData['ExpIeCustomsNo'];

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
	// $ProcessorName1=$RowProcessor1['STRING']; 
			}
ELSE	{
	$ProcessorName=$ProcessorNamee;
}		
$ProcessorName=$ProcessorData['ExpName'];
			//$ProcessorAddress=$ProcessorData['ExpAddress'];
			$ProcessorAddress=$ProcessorData['PackerAddress'];
			$ProcessorUsfdaNo=$ProcessorData['ExpUsfdaNo'];
			$ProcessorApprovalNo=$ProcessorData['ExpApprovalNo'];
			$ProcessorFEI=$ProcessorData['ExpFeiNo'];
				 //$DestinationCountryId=$PoDataRow['name']; 

		
		$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

			
			$InvDate=getFormatedDate($CustomInvoiceRow['CInvDate']);
			$PoDate=getFormatedDate($CustomInvoiceRow['CpoDate']);
			
			if($InvDate=='01-01-1970'){
				
				$InvDate=': na';
			}
			//$PreCarriageBy=$CustomInvoiceRow['CInvNo'];
			$PlaceOfReceiptBy=$CustomInvoiceRow['PlaceOfRecipt'];
			$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
			$SealNo=$CustomInvoiceRow['SealNo'];
			$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
			$SealNo=$CustomInvoiceRow['SealNo'];
			$VesselNumber=$CustomInvoiceRow['VesselNumber'];
			$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
			$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
			$CInvCode=$CustomInvoiceRow['CInvCode'];
			$IGST=$CustomInvoiceRow['IGST'];
			$PortOfDischarge=$CustomInvoiceRow['PortofDischarge'];
			$FinalDestination=$CustomInvoiceRow['FinalDestination'];
			$NoOfDays=$CustomInvoiceRow['NoOfDays'];
			$Discount=$CustomInvoiceRow['Discount'];
			
			if($NoOfDays==0){
				$NoOfDays='';
			}
			else{
				$NoOfDays=$NoOfDays.'DAYS FROM BL';
			}
			
			
			$PoNo=$PoDataRow['PoNo'];
			$PoDate=getFormatedDate($PoDataRow['PoDate']);
			
			$PoDate= "& Dated :".$PoDate;
			IF($PoNo ==''){
				
			$PoDate=' ';	
			}
			
			$ConsigneeName=$PoDataRow['ConsigneeName'];
			$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
			$DestinationCountryId=$PoDataRow['DestinationCountry'];

			
			/*$PortOfDischarge=$PoDataRow['Name'];
			$FinalDestination=$PoDataRow['FinalDestination'];*/
			$DestinationCountry=$PoDataRow['name'];
			$ByPayment=$PoDataRow['ByPayment'];
			$PreparedBy=$PoDataRow['PreparedBy'];
			//$PaymentTerms=$PoDataRow['PaymentName'];
			$PaymentTerms=$RowPaymentTerms['PaymentName'];

			$DeliveryTerms=$PoDataRow['DeliveryTerms'];
			$ShippingTerms=$PoDataRow['ShippingTerms'];
			$QtyUnit=$PoDataRow['QtyUnit']; 
			$ShowFrightCharge=$PoDataRow['ShowFrightCharge'];


	
	
			
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
 
	 $content .= '<br><br><table border="1" cellspacing="0" cellpadding="0">  
				
				 <tr> 
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';
				
		if($PoDataRow['ExporterID']==5){		
		$content .= '<tr>
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'<BR>
				IE CODE  : '.$ExporterIeCode.'<BR>
				GST NO  : '.$ExporterGstNo.'
				</font></th>
		</tr>';}
				else{		
		$content .= '<tr> 
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'<BR>';
				if($DestinationCountryId=='26'){
					/*$content .= 'IE CODE  : '.$ExporterIeCode.'
					<BR>GST NO  : '.$ExporterGstNo.'</font></th></tr>';*/
					
					$content .= '</font></th></tr>';}
				
				
				else{
			$content .= 'USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>
				IE CODE  : '.$ExporterIeCode.'<BR>GST NO  : '.$ExporterGstNo.'</font></th>
				</tr>';}}
				
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				<br>
				</font></th>
			
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">'.$PlaceOfReceiptBy.'</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR align="center">  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">'.strtoupper($PortOfLoading).','.strtoupper($CustomInvoiceRow['PortOfLoadingCountry']).'</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">'.strtoupper($PortOfDischarge).' '.strtoupper($CustomInvoiceRow['CountryCode']).', '.strtoupper($CustomInvoiceRow['PortofDischargeCountry']).'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">'.$FinalDestination.'
				<br></font></th>
				
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
			if($ProcessorID==3){	
			$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$ProcessorApprovalNo.'</b>
				<br>
				<u><b> Packer</u> :</b><br> '.$ProcessorName.' '.$ProcessorName1.'<br> '.$ProcessorAddress.' ';
		if($DestinationCountryId!='26'){
			$content .= '
				
				<br> USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo.'<BR> FEI # :  '.$ProcessorFEI.'';
		}
		$content .= '</font></th>
			</tr>';}
			else{
				$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$ProcessorApprovalNo.'</b>
				<br>
				<u><b> Packer</u> :</b><br> '.$ProcessorName.'<br> '.$ProcessorAddress.' ';
		if($DestinationCountryId!='26'){
			$content .= '
				
				<br> USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo.'<BR> FEI # :  '.$ProcessorFEI.'';
		}
		$content .= '</font></th>
			</tr>';
				
			}
			
				
	
			 if($PoDataRow['ShippingTerms']=='DDP'){
				
			 $content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br>
				
				</font></th>
				
				</tr>';}
				/*<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN  # '.$ProcessorIeCustomsNo.'<br> '.$ExporterRecAdd .'*/


		else{
				
			 $content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br></font></th>
				</tr>';}
				
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  '.$DestinationCountry.'</b></font></th>
				</tr>
				<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' .$PaymentTerms .' ' .$NoOfDays .'<br>
				 SHIPMENT TERM : '.$ShippingTerms .' '.strtoupper($FinalDestination).' ';
				if($DestinationCountryId!=26){
				
				
				$content .= '('.$DeliveryTerms.')<br>';
				}
				
				$content .= ' <br></font> </th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> '.$TOTAL_MCS.'</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><font size="10"><b align="center">'.$ContainerNumber.'</b></font></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><font size="10"><b align="center">'.$SealNo.'</b></font></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in LBS </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>USD/KGS '.$ShippingTerms .' 
				<br><b>'.strtoupper($FinalDestination).' </b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in U.S.D <br>'.$ShippingTerms .'
				<br><b> '.strtoupper($FinalDestination).'</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>
				
				';
				
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL IN FROZEN FORM OTHER THAN AFD';
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='<tr >
				
			<table border="0" cellspacing="0" cellpadding="1">
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$description_head.' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			
			</tr>
			'
			;
			

			  $SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmCIGradeData` 
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand"; 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
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
				
				$content.='
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].'<br>
			PACKING : '.$VarietyMcsRow['PackingStyle'].' NET WEIGHT PER CARTON.'.$Whitspace.' 
			';
			
			if($DestinationCountryId!=26){
			$content.='<B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}
			
							
		$content.='</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="40%" ><font size="'.$FontSize.'"> <u>SIZE</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>';
			
			//UMA
				  $SqlVariety="SELECT * FROM `ScrmCIGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$ID' 
			and Variety='$Variety' and Brand='$Brand' 
		and	PackingStyleID='$PackingStyleID' order by abs(Grade)";  
			$StmtVariety=$iconn->query($SqlVariety);
				
			while($RowVariety=$StmtVariety->fetch_assoc()){
				
				// 26 ID 26
								if($DestinationCountryId=='26'){

				if( $PoDataRow['QtyUnit']=='KGS'){
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];
				$UsdValue=($Qty)*$RowVariety['Price'];				
				}else{
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];										
				 $UsdValue=($Qty)*$RowVariety['Price'];
				}
								}
								else{
				
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] *  $RowVariety['LbsFactor'];
				 $UsdValue=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] *$RowVariety['Price']*$RowVariety['LbsFactor'];	
								}
			$content.='
				<tr>
				
			<!--	<td align="center" width="12.5%" ><font size="7">
				'.$RowVariety['Variety'].'</font></td> -->
				<td align="center" width="40%"  ><font size="'.$FontSize.'">
				'.$RowVariety['Grade'].'</font></td>
				<!--<td align="center" width="22.5%"><font size="7">
				'.$RowVariety['PackingStyle'].'</font></td> -->
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$RowVariety['Mcs'].'</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$Qty.'.000</font></td>';
				
			$content.='	<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
			'.$RowVariety['Price'].'</font></td>';
				
			$content.='
				
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.round($UsdValue).'.00</font></td>
				
				</tr>
				
				';
			}


			}//UMA
			
							$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			$SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmCIGradeData` 
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
				'.$Weight.'.000</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.round($Price).'.00</font></td>
				
				
				</tr>';
							$content.= '</table>
			  </tr>';

  $SqlBank="SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
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
				
				
			$content.='<tr> '.
           		 $TOTAL.'
				 
				 
	
				</tr>

				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> SHIPPING TERMS   : '.$PoDataRow['ShippingTerms'].'  '.$FinalDestination.'
				<!--<BR> TOLERANCE           : QUANTITY/AMOUNT 10% PLUS OR MINUS
				<BR> SHIPMENT DATE :  '.$SHIPMENT_DATE.' OR EARLY<BR> <BR>-->
				';
				/**uma(16/01/22)**/
				if($PoDataRow['ShippingTerms']=='CIF'){
									$InvPrice= ($Price*0.11)/100;
									$Price=$Price-$InvPrice;
								 }
								 else{
								$Price=$Price;	 
								 }
								 
							/**uma(16/01/22)**/

				$DiscountAmount=($Price*$Discount)/100;
				$TotalDiscount=$Price-$DiscountAmount;
				
					IF($IGST=='DISCOUNT'){
$DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=round(($DutiesPrice+125),2);
				
				if($ShowFrightCharge==0||$ShippingTerms=='CNF'){
			$content.='	<BR>'.$W_SPACE.' 
				 <b>   FOB VALUE    :'.round((($TotalDiscount-$FrieghtCharge)*$ExchangeRate)).'.00</b>';
				}
				else{
				$content.='	<BR> '.$W_SPACE.' 
				 <b>   FOB VALUE    :'.round((($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate)).'.00</b>';	
				}
				
					}
					ELSE if($PoDataRow['ShippingTerms']=='FOB'){
				$content.='<BR>'.$W_SPACE.' 	<b> FOB VALUE    :'.round((($Price-$FrieghtCharge)*$ExchangeRate)).'.00</b>';
				}
					
					ELSE{
$DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				if($ShowFrightCharge==0||$ShippingTerms=='CNF'){
			$content.='	<BR>'.$W_SPACE.' 
				 <b>   FOB VALUE    :'.round((($Price-$FrieghtCharge)*$ExchangeRate)).'.00</b>';
				}
				else{
				$content.='	<BR> '.$W_SPACE.' 
				 <b>   FOB VALUE    :'.round((($Price-$FrieghtCharge-$DutiesPrice)*$ExchangeRate)).'.00</b>';	
				}
				
					}
					
		/*	ELSE{
	
					$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				if($DestinationCountryId!='26'){
				
			$content.='<br> '.$W_SPACE.'    FOB VALUE    :'.round(($Price-$FrieghtCharge-$DutiesPrice)*$ExchangeRate).'.00</b>';}
else{
				
			$content.='	
				 <BR> '.$W_SPACE.'    FOB VALUE    :'.round(($Price-$FrieghtCharge)*$ExchangeRate).'.00</b>';}


}							
		
					
	/*			
				
	IF($IGST=='DISCOUNT'){
					
					$DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				if($DestinationCountryId=='26'){
				$content.='<br> '.$W_SPACE.'    FOB VALUE    :'.round(($TotalDiscount-$FrieghtCharge)*$ExchangeRate).'.00</b>';}

			else{
			$content.='<br> '.$W_SPACE.'    FOB VALUE    :'.round(($TotalDiscount-$FrieghtCharge-$DutiesPrice)*$ExchangeRate).'.00</b>';}
	
			}
			
ELSE{
	
					$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				if($DestinationCountryId!='26'){
				
			$content.='<br> '.$W_SPACE.'    FOB VALUE    :'.round(($Price-$FrieghtCharge-$DutiesPrice)*$ExchangeRate).'.00</b>';}
else{
				
			$content.='	
				 <BR> '.$W_SPACE.'    FOB VALUE    :'.round(($Price-$FrieghtCharge)*$ExchangeRate).'.00</b>';}


}							
				
			*/	
				
				
				
				
				
				
				
				
				
				$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
				
				
				
				if($QtyUnit=='KGS'){
				$content.='<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: '.$NetWgtKgs.' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: '.$GrossWeightKgs.' KGS</b> 
				</font></th>';	
				}else{
				$content.='<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>'.number_format($NetWgtLbs, 3, '.', '').'LBS</b>
				<b style="color:white">-------------------------</b><b>'.number_format(($NetWgtLbs*0.454),3,'.','').' KGS</b>
				<BR> GROSS WEIGHT<b style="color:white">------------------------------</b>: 
				<b>'.number_format($GrossWeightLbs,3,'.','').'LBS</b>
				<b style="color:white">-------------------------</b><b>'.number_format(($GrossWeightLbs*0.454),3,'.','').' KGS</b> 
				</font></th>';	
				}
				
				
				
               $content.='</tr>
				
				
<tr>
<th  align="bottom"  width="70%"><font size="8"><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>
  <th  align="center"  width="30%"><font size="7"> Signature:<br>
				<B> '.$AuthSign.',</B><BR><BR>
				 
				<!--<img src="'.$SignURL.'" 
				 width="100" height="10"style="width: 100" align="center" align="middle"/>-->
								
				
				<p STYLE="text-align:center" > Authorised Signature  </p></font></th>
</tr>
				
				
				</table><p STYLE="text-align:left"><font size="7">CC: QC </font></p>'; 
	
	return $content;
	
	}


?>
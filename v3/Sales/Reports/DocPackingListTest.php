<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

$style = '<style>
th {
    font-size: 8px;
	padding : 10px;
}
td {
    font-size: 8px;
	padding : 10px;
}
p {
	//line-height: 0.01;
}
</style>
';


$PoID=$_GET['id'];
$Type=$_GET['Type'];
$PType=$_GET['PType']; 
if($PType==''){
	$PType='LBS';
}





$SqlCustom="SELECT DestinationCountry,ConsigneeID,IGST,SPD.ExporterID as ExporterID,CustomsInvNo,CInvDate FROM `ScrmCustomInvoices`  as SCV
left join ScrmPoData as SPD on  SPD.PoID=SCV.PiID
WHERE PiID='$PoID'";
$StmtCustom=$iconn->query($SqlCustom);
$RowCustom=$StmtCustom->fetch_assoc();
$IGST=$RowCustom['IGST'];
$ExpID=$RowCustom['ExporterID'];
//$Status=$RowCustom['Status']; 
$DocType='PKL';
$CountryID=$RowCustom['DestinationCountry'];
$BuyerID=$RowCustom['ConsigneeID'];
$CustomsInvNo=$RowCustom['CustomsInvNo'];
$CustInvDate=date("d-m-Y", strtotime($RowCustom['CInvDate']));


$AryLables=getInvoiceLables($DocType,$CountryID,$BuyerID);
$AryShowConditions=getInvoiceShowConditions($DocType,$CountryID,$BuyerID);

class MYPDF extends TCPDF { 
 
    public function Header() {		
	//	
	/*getCustomInvData($iconn,$PoID);
	$IGST=$AryData['IGST'];*/
	global  $IGST;
	/*if($IGST=='IGST'){
		$Header2='SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST';
	}
	else{
		$Header2='';
	}*/

		global $Header;		
        $image_file = 'images/ds_logo.jpg';
		if($this->page == 1){  // show header in page1 only
			$this->SetFont('helvetica', 'B', 12);
			$this->Cell(0, 0, 'PACKING LIST'.$Header, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
			$this->SetFont('helvetica', ' ', 8);
			$this->Cell(130, 0, ''.$Header2, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

			$this->SetTextColor(255,0,0);
			
			$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		}
    }
	
    public function Footer() {
		global  $CustomsInvNo,$CustInvDate;
        $this->SetFont('helvetica', 'I', 8);
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'('.$CustomsInvNo.' / Dt.'.$CustInvDate.')', 0, false, 'C', 0, '', 0, false, 'T', 'M');

		$this->SetY(-5);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	$pdf->SetCreator(PDF_CREATOR); 	
    $pdf->SetTitle('PACKING LIST'.$Header);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(7);	
	$pdf->SetHeaderMargin(5);	     
    $pdf->SetMargins(10, 15, 10,5);  	
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
    $pdf->SetAutoPageBreak(false, 5);  
	$pdf->AddPage();  

	$AryData=getReportData($iconn,$PoID);
	$HeaderTable=getHeaderTable($iconn,$PoID);
	$BreakUpTable=getBreakTable($iconn,$PoID);
	$FooterTable=getFooterTable($iconn,$PoID);
	
	$pdf->writeHTML($HeaderTable.$BreakUpTable.$FooterTable);
	
	/*$TableData=getCustomInvData($iconn,$PoID);
	$pdf->writeHTML($TableData);*/
	$pdf->SetFont('helvetica', '', 6);


/*$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$ProcessorInvShortCode=$ArrayFilename['ProcessorInvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];*/
 
//$file_name=$InvShortCode.'-'.$ProcessorInvShortCode.'-'.$CInvNo.'-'.$FinancialYear.'-PL'.'.pdf';

$file_name = str_replace('/','_', $CustomsInvNo );
$Fname='-PL';
$file_name=$file_name.$Fname.'.pdf';   
ob_end_clean();

if($Type==''){
$Type='I';
}else{
	$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','_', $file_name );;
	$file_name=$Fname.$file_name;
}

$pdf->Output($file_name, $Type);


function getHeaderTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData,$style,$PType,$AryLables;
					
	$content='';
	                                                                  

	$size=45;	
	$size1=55;	


		
		
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		$ExporterGstNo=$AryData['ExporterGstNo'];
		$DataLogger=$AryData['DataLogger'];
		$DataLoggerPositions=$AryData['DataLoggerPositions'];
		$ShowDataLogger=$AryData['ShowDataLogger'];
		
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$ConsigneeCountry=$AryData['ConsigneeCountry'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		 $VesselNumber=$AryData['VesselNumber'];
		$DestinationCountryName=$AryData['DestinationCountryName'];
		$OrgVesselName=$AryData['OrgVesselName'];
		$OrgVOYNO=$AryData['OrgVOYNO'];

		
		$ShowCWiseLc=$AryData['ShowCWiseLc']; 
		$ShowCWiseLcDate=$AryData['ShowCWiseLcDate']; 
		$ShowCWiseLcIssuedBy=$AryData['ShowCWiseLcIssuedBy']; 
		$ShowCWiseWeightPerctText=$AryData['ShowCWiseWeightPerctText']; 
		$ShowCWiseNotifyPrty=$AryData['ShowCWiseNotifyPrty']; 
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$ReferenceNo=$AryData['ReferenceNo'];

		$PoDate=$AryData['PoDate'];
		 $ShowBank=$AryData['ShowBank']; 
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$showBuyer=$AryData['showBuyer'];
		$showImporter=$AryData['showImporter'];
		$ShowCWiseBuyer=$AryData['ShowCWiseBuyer'];
		$showNotifyParty=$AryData['showNotifyParty'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		//$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$NotifyPartyName=$AryData['NotifyPartyName']; 
		$NPartyAddress=$AryData['NPartyAddress']; 
		$NPartyContactNumber=$AryData['NPartyContactNumber']; 
		$ImporterName=$AryData['ImporterName']; 
		$ImporterAddress=$AryData['ImporterAddress'];
		
        $PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
 		$PortofLoading=$AryData['PortOfLoading']; 
		$DestinationCountry=$AryData['DestinationCountry'];
		//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$IRNNumber=$AryData['IRNNumber'];
		$QtyUnit=$AryData['QtyUnit'];
		 $UnitCurrency=$AryData['UnitCurrency']; 
		$TotalMcs=$AryData['TotalMcs'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$Remarks=$AryData['Remarks'];
		$showRemarks=$AryData['showRemarks'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		
		$ShowCWiseApprovalNo=$AryData['ShowCWiseApprovalNo'];
		$ShowCWiseUsfda=$AryData['ShowCWiseUsfda'];
		$showProFEI=$AryData['showProFEI'];
		$ByRoad=$AryData['ByRoad'];
		
		$ContainerNo=$AryData['ContainerNo'];
		$SealNo=$AryData['SealNo'];
		$BLDate=getFormatedDateDMMY($AryData['BLDate']); 
		$SBDate=$AryData['SBDate']; 
		$BLNo=$AryData['BLNo']; 
		$SBNo=$AryData['SBNo']; 
		$ShowBLNo=$AryData['ShowBLNo'];
		$ShowBLDate=$AryData['ShowBLDate'];


 

	 $content .= $style; 
	 $content .= '
	 
	 <table border="1" cellspacing="0" cellpadding="0">  ';
	 
			$content .= '	 <tr>
				 
				
				
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';
				
				$content .= '
				
				<tr>
				<th  align="LEFT" >   <b>Exporter :</b>
				<br>'.$ExporterName.' 
				<BR> '.nl2br($ExporterAddress).'' ;
				
			/*	if($showExpUsFda){
				$content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';
				}
				
				if($showExpIECode){
				$content .= ' IE CODE  : '.$ExporterIeCode;
								
				}
				
				if($showExpGSTNo){
				$content .= '<BR> GST NO  : '.$ExporterGstNo;
				}*/
				
				
				$content .= '   </th></tr>';
				
		
		
		
		
		
			$content .= '
				<tr> 
				<th  align="LEFT" >   <b><u>Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.nl2br($ConsigneeAddress).'   </th>
				</tr>';
			if($ShowCWiseNotifyPrty){
				if($showNotifyParty){
				$content .= '		<tr>  
           		
				<th  align="LEFT" >   <b> <u>Notify Party</u></b>
				<br>'.$NotifyPartyName.',<br>'.nl2br($NPartyAddress).'
				
				
				   </th>
				</tr>';	
			}}
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgfgtyhjmnkih</b>';
				/*<tr>  
           		
				<th  align="LEFT" width="50%" >   Pre-Carriage by
				<br  align="center">REFER CONTAINER   </th>
				<th  align="LEFT" width="50%" >   Place of Receipt by
				<br align="center">ANY PORT, INDIA   </th>
				below lines
				<th  align="left"  width="12.5%"style="border-right: 1px solid black;">   Shipping Marks: <br><br><BR align="center"> NO MARKS   </th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;">   No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS   </th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;">   Container Number <br><br><br><b align="center">1 X 40 FCL</b>   </th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;">   Seal Number <br><br><br><b align="center">'.$SealNo.'</b>   </th>
                
				</tr>*/
				$content .= '<tr>  
           		
				<th  align="LEFT" width="50%" style="font-size: 7px;">   Vessel / Flight No. 
               <BR align="left"> '.$OrgVesselName.' <br>   '.$OrgVOYNO.' 
				<br>   </th>
				<th  align="LEFT" width="50%" style="font-size: 7px;">   Port of Loading
				<br align="center">'.$PortofLoading.'   </th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" >   Port of Discharge
				<br align="center">'.$PortOfDischarge.'   </th>
				<th  align="LEFT" width="50%" >   Final Destination
				<br align="center">'.$FinalDestination.','.$DestinationCountryName.'   </th>
				
				</tr>
				';
				if($ShowDataLogger){
				$content .= '<tr>  
           		
				<th  align="LEFT" width="100%" >   <b><u>DATA LOGGERS : '.$DataLogger.'
				<br align="left">PLACED AT :'.$DataLoggerPositions.'</b></u>   </th>
				
				
				</tr>';}
				
			$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" >   Marks & Nos
				<br align="center">   </th>
				<th  align="LEFT" width="50%" >   No.& Kind of Pkgs
				<br align="center">'.$TotalMcs.'M/Cs.   </th>
				
				</tr>
			
			
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><b> Invoice No. & Date.
				 '.$InvNo.',Dt. '.$InvDate.'</b>    </th>
				</tr>
				
				<tr> 
				<th  align="LEFT" >   <b> Purchase Order No. & Date. 
				<br> '.$PoNo.$PoDate.' </b>   </th>
				</tr>';
				
				
				
				
				
				
				
				$content .= '		<tr > 
				<th  align="LEFT"  ><b> Other Reference(s) :
				'.$ReferenceNo.'</b>
				
				   </th></tr >';
				if($showImporter){
				
				$content .= '	<tr> 
           		
		
                <th  align="left" width="100%">   
				<b><u> Importer of Record:</u></b> <br>  '.$ImporterName.'
				<br> '.nl2br($ImporterAddress).'    </th>
				</tr>';
				}if($ShowCWiseBuyer){
					if($BuyerID!=0){
				
			 $content .= '	<tr> 
           		
                <th  align="left" width="100%">    <b><u>Buyer 
				(if other than consignee) :</b></u><br>'.$BuyerName.',<br>'.nl2br($BuyerAddress).'<br>   </th>
				</tr>';
				}
				}
				
				$content .= '<tr >
				<th  align="LEFT"  >
				 <u><b>'.$AryLables['Manufacturer'].'</u> :</b><br> '.$ProcessorName.'<br> '.nl2br($ProcessorAddress).'
				 ';
				IF($ShowCWiseApprovalNo){
				$content .= '<br> '.$AryLables['ApprovalNo'].''.$ProcessorApprovalNo.'';
				}
				
				if($ShowCWiseUsfda){			
				$content .= '<br> BIO TERRORISM / USFDA REGISTRATION NO.  '.$ProcessorUsfdaNo;
				}
				
				/*if($showProFEI){			
				$content .= '<BR> FEI # :  '.$ProcessorFEI;
				}
		*/
				$content .= '   </th></tr>';
	
	
			 
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="49%">    Country of Origin of Goods 	
				<br><b align="center"> INDIA</b>   </th>
				
				<th border="1"  align="left" width="51%">    Country of Final Destination  
				<br><b align="center">  '.$DestinationCountry.'</b>   </th>
				
				
				
               
				</tr>
				<tr> 
				
			
				
				<th  align="LEFT"  width="100%" >   <b> <u>Terms of Payment:</u>
				</b><br><BR> ' .$PaymentTerms .' <br>
				 
				 <!--SHIPMENT TERM : '.$ShippingTerms .' '.strtoupper($PortOfDischarge).', '.
				strtoupper($DestinationCountry).' -->
				';
				
				
				
				
				$content .= '    </th>
				</tr>
			<tr>  
           		
				<th  align="LEFT" >   <b><u> CONTAINER NO.  
				'.$ContainerNo.'</b></u> <b style="color:white">_______________</b>   <b><u>SEAL NO. '.$SealNo.'</b></u>
                 <br><br> ';if($ShowBLNo){
					$content .= '<b>'.$AryLables['BLNo'].' '.$BLNo.'</b>';}
					if($ShowBLDate){
						$content .= '<b style="color:white">_____________________</b><b>'.$AryLables['BLDate'].' '.$BLDate.'</b>';}
				
				
						$content .= '   </th>
				</tr>
				
				</table>
				</th>
				
				</tr> ';
				
				
				
				
				$content.='</table>'; 
	
	return $content;
	
	}

function getBreakTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData,$style,$PType,$AryLables;			
	$content=$style;
	                                                                  

	$size=45;	
	$size1=55;	


		
		
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		$ExporterGstNo=$AryData['ExporterGstNo'];
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		$TotalGrossWtKgs=$AryData['TotalGrossWtKgs'];
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$PoDate=$AryData['PoDate'];
		 $ShowBank=$AryData['ShowBank']; 
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$showBuyer=$AryData['showBuyer'];
		$showImporter=$AryData['showImporter'];
		$showNotifyParty=$AryData['showNotifyParty'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		//$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$NotifyPartyName=$AryData['NotifyPartyName']; 
		$NPartyAddress=$AryData['NPartyAddress']; 
		$NPartyContactNumber=$AryData['NPartyContactNumber']; 
		$ImporterName=$AryData['ImporterName']; 
		$ImporterAddress=$AryData['ImporterAddress'];
		
        $PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$PortofLoading=$AryData['PortofLoading'];
		$DestinationCountry=$AryData['DestinationCountry'];
		//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$QtyUnit=$AryData['QtyUnit'];
		 $UnitCurrency=$AryData['UnitCurrency']; 
		$TotalMcs=$AryData['TotalMcs'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$Remarks=$AryData['Remarks'];
		$showRemarks=$AryData['showRemarks'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		
		$ShowCWiseUsfda=$AryData['ShowCWiseUsfda'];
		$showProFEI=$AryData['showProFEI'];
		$showIgst=$AryData['showIgst'];
		$showDiscount=$AryData['showDiscount'];
			$TextSize=$AryData['TextSize'];
				
			if($QtyUnit=='KGS'){
				$Unit='KG';
			}
				elseif($QtyUnit=='LBS'){
					$Unit='LB';
				}
				ELSE{
					$Unit=$QtyUnit;
			}
				
				
				
				$content='
				<table border="1" cellspacing="0" cellpadding="2">
				<tr>
				<th  align="LEFT" width="59%" >     Description of Goods <b align="center">'.$SealNo.'</b>   </th>
                <th  align="center" width="21%" >
				   Quantity in <br>'.$QtyUnit.' '.$AryLables['UnitInBreakUp'].'   </th>
                <th  align="center" width="20%" >      </th>
              <!--  <th  align="center" width="14.5%" >   Amount in '.$UnitCurrency.'    </th>-->
                </tr>';
			
			
			$CurrentCount=0;

			//   $SqlVarietyMcs="
			// SELECT sum(McS) As VarietyMcs, BuyerItemCode, 
			// (SELECT CIDescription from ScrmCIGradeData WHERE PoGradeID=SPG.ID) as Description,SPG.Variety,SPG.Treatment,SPG.Grade,Aps.PackingStyle,SPG.Brand,SPG.PackingStyleID,BAP,SpeciesName FROM `ScrmPoGradeData` as SPG
			// left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			// left join `ScrmPIVarietyList` as SPV on SPV.VarietyID=SPG.VarietyID
			// left join `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPV.Species
			//  WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand order by SPG.ID";
            $SqlVarietyMcs="SELECT sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
			CIDescription as Description,Variety,Treatment,Grade,
			 PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmCIGradeData` 
			 left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
			 WHERE InvId='$ID' GROUP by CIDescription
			 Order By ID asc";

			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
			$RowCount=mysqli_num_rows($StmtVarietyMcs); 

			while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
			$CurrentCount++;
	
				
				$Variety=$VarietyMcsRow['Variety'];
				$Grade=$VarietyMcsRow['Grade'];
				$Brand=$VarietyMcsRow['Brand'];
				 $BAP=	$VarietyMcsRow['BAP']; 
				 $BuyerItemCode=	$VarietyMcsRow['BuyerItemCode']; 
				 $BreakUpDescription=	$VarietyMcsRow['Description']; 
				 $PackingStyleID=	$VarietyMcsRow['PackingStyleID']; 
				  $Treatment=checkValueAval($VarietyMcsRow['Treatment']);
				  $SpeciesName=checkValueAval($VarietyMcsRow['SpeciesName']); 
  if($BAP=='NA'){
	 $BAP='';  
   }
  ELSE{
	$BAP=$BAP;  
  }
  
   
  $FontSize="7px";
  
  
				
			/*	
			$content.='<tr>';	
				/*if($BuyerItemCode!=''){
			$content.='
			<th align="center" width="15%" style="border-right:1px solid white;"  >    <u>Item Code</u>   </th>
			<th align="center" width="15%" style="border-right:1px solid white;"  >    <u>SIZE</u>   </th>';
			}else{*/
		/*	$content.='<th align="center" width="30%" style="border-right:1px solid white;" >    <u>SIZE</u>   </th>';
			//}
			$content.='<th align="center" width="29%" style="border-right:1px solid black;" >   <u>NO. OF CTNS</u>   </th>
			<th align="center" width="21%" style="border-right:1px solid black;">      </th>
			
			</tr>';*/
				
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='
				
			
			
			'
			;
			
  
  
		//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$BreakUpDescription.'<br>';
		
		if($BreakUpDescription!=''){
	 $Description= $BreakUpDescription.'<br>';
  }else{
		//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>';
		$Description=$VarietyMcsRow['Variety'].'<br>';
  }	
		
		if($DestinationCountryId!=26){
			$Description.='<u>PACKING : '.$VarietyMcsRow['PackingStyle'].' '.$SpeciesName.'</u>

			 <B><br>'.$BAP.'</B>';
			}
		else{
			$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].' '.$SpeciesName.''.$Whitspace.' ';
		}	
			
			if($BreakUpDescription!=''){
	     $Description= $BreakUpDescription.'<br>';
		}
				 
				$content.='
			<tr>
			<td width="59%" style="border-right: 1px solid black;" >   '
			.nl2br($Description).'   </td>
			<td align="center" width="21%" style="border-right: 1px solid black;">      </td>
			<td align="center" width="13.5%" style="border-right: 1px solid white;">      </td>
			</tr>';
			if($CurrentCount < 2){
			$content.='<tr>';	
				/*if($BuyerItemCode!=''){
			$content.='
			<th align="center" width="15%" style="border-right:1px solid white;"  >    <u>Item Code</u>   </th>
			<th align="center" width="15%" style="border-right:1px solid white;"  >    <u>SIZE</u>   </th>';
			}else{*/
			$content.='<th align="center" width="30%" style="border-right:1px solid white;" >    <u>SIZE</u>   </th>';
			//}
			$content.='<th align="center" width="29%" style="border-right:1px solid black;" >   <u>NO. OF CTNS</u>   </th>
			<th align="center" width="21%" style="border-right:1px solid black;">      </th>
			
			</tr>';
			}
			
			//   $SqlVariety="SELECT * FROM `ScrmPoGradeData` as PGD
			// left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			// InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
			// and	PackingStyleID='$PackingStyleID' order by abs(Grade)";   

            $SqlVariety="SELECT * FROM `ScrmCIGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and CIDescription='$BreakUpDescription' and Brand='$Brand' 
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";   
		
			$StmtVariety=$iconn->query($SqlVariety);
			$ArySubBreakUp=array();
				
			while($RowVariety=$StmtVariety->fetch_assoc()){ 
			
				//$ArySubBreakUp[]=$RowVariety['ID'];
                $ArySubBreakUp[]=$RowVariety['PoGradeID'];//uma
				//$BuyerItemCode=$RowVariety['BuyerItemCode'];
				//echo $BuyerItemCode;$RowVariety['BuyerItemCode']; exit;
				/*$AryPriceBreakUp=getBreakUpData($iconn,$RowVariety['ID']);
				$Qty=$AryPriceBreakUp['Qty'];
				$UsdValue=$AryPriceBreakUp['UsdValue']; 
				$TotalMcs=$AryPriceBreakUp['TotalMcs'];
				$Price=$AryPriceBreakUp['Price'];*/
					
				$AryPriceBreakUp=getBreakUpData($iconn,$RowVariety['PoGradeID']);
				$Qty=$AryPriceBreakUp['Qty'];
				$UsdValue=$AryPriceBreakUp['UsdValue']; 
				$TotalMcs=$AryPriceBreakUp['TotalMcs'];
				$Price=$AryPriceBreakUp['Price'];
				$UnitPrice=$AryPriceBreakUp['UnitPrice'];
				$AryPaymentData=getPaymentValues($iconn,$ID);
				
		
				$InvValueUSD=$AryPaymentData['InvValueUSD'];
				$InvValueInr=$AryPaymentData['InvValueINR'];
				$ExchangeRate=$AryPaymentData['ExchangeRate'];
				$DiscountPerc=$AryPaymentData['DiscountPerc'];
				$DiscountAmount=$AryPaymentData['DiscountAmount'];
				$IgstPerc=$AryPaymentData['IgstPerc'];
				$IgstValue=$AryPaymentData['IgstValue'];
				
				//print_r($AryPriceBreakUp); exit;
				
				if($PType=='KGS'){
					$Price=sprintf("%0.7f",$UnitPrice);
				}else{
					$Price=sprintf("%0.2f",$Price);
				}
				
				//print_r($AryPriceBreakUp); exit;
				
				
			$content.='<tr>';
			
			/*if($BuyerItemCode!=''){
			$content.='
			<td align="center" width="15%" style="border-right:1px solid white;"  >   
				 <b>'.$RowVariety['BuyerItemCode'].'</b> 
				   </td><td align="center" width="15%" style="border-right:1px solid white;"  >   
				 <b>'.$RowVariety['Grade'].'</b> 
				   </td>';
			}else{*/
			$content.='<td align="center" width="30%"  style="border-right:1px solid white;" >   
				 '.$RowVariety['Grade'].'
				   </td>';
			//}
				
				
			$content.='
				<td align="center" width="29%" style="border-right:1px solid black;"  >   
				'.$TotalMcs.'     </td>
				<!--<td align="center" width="20%" style="border-right:1px solid black;"  >   
				'.$RowVariety['Brand'].'     </td>-->
				<td align="center" width="21%" style="border-right:1px solid black;"  >   
				'.number_format($Qty,3).'    </td>
				<td align="center" width="20%" style="border-right:1px solid black;"  >   
				   </td>
				
				<!--<td align="center" width="14.5%" style="border-right:1px solid black;" >   
				'.number_format($UsdValue,3).'   </td>-->
				
				</tr>';
			}
			
			 $SubVarityBrkUp=implode(',',$ArySubBreakUp); 
			
				$AryPriceData=getBreakUpData($iconn,$SubVarityBrkUp);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				 $TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
			
			if( $RowCount > 1){
			$content.='
				<tr>
				<td align="center" width="30%"  style="border-right:1px solid white;" >
				   
				<b>Total</b	>
				   </td>
				<td align="center" width="29%" style="
				border-top:1px solid black;
				border-bottom:1px solid black;
				" >
														
														   
				'.$TotalMcs.'   </td>
				
				<!--<td align="center" width="20%" style="border-right:1px solid black;">
														
														      </td>-->
				<td align="center" width="21%" >   
				'.number_format($Qty,3).'   </td>
				<td align="center" width="20%" style="border-right:1px solid black;">
														      </td>
				<!--<td align="center" width="14.5%" style="border-top:1px solid black;
				border-bottom:1px solid black;">
														   
				'.number_format($UsdValue,2).'   </td>-->
				
				</tr>';


			}
			}
			
				$AryPriceData=getBreakUpData($iconn,$RowVariety['PoGradeID'],$ID);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				$TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
				
			
			$content.=' 
				<tr>
				
				<td align="center" width="30%" style="border-top: 1px solid black;" >   <B>
				GRAND TOTAL </B>   </td>
				<td align="center" width="29%" style="border-top: 1px solid black; " >   
				'.$TotalMcs.'   </td>
			<!--	<td align="center" width="20%" style="border-top: 1px solid black; border-right: 1px solid black;" >   
				   </td>-->
				<td align="center" width="21%" style="border-top: 1px solid black;border-right: 1px solid black;">   
				'.number_format($Qty,3).'   </td>
				<td align="center" width="20%" style="border-top: 1px solid black;border-right: 1px solid black;" >   
				   </td>
				<!--<td align="center" width="14.5%" style="
				border-top: 1px solid black;
				border-bottom: 1px solid black;
				border-right: 1px solid black;">   
				'.number_format($UsdValue,2).'   </td>-->
				
				
				</tr>';
			
			
			
				
				
			
			$content.= '</table>';
 
	
	return $content;
	
	}			


function getFooterTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
					
	global $AryData,$style;			
	$content=$style;
	                                                                  

	$size=45;	
	$size1=55;	


		
				
				$AryPriceData=getBreakUpData($iconn,$RowVariety['PoGradeID'],$ID);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				$TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
				$GlazePerc=$AryPriceData['GlazePerc'];
			 	$ProductWeightWOGLbs=$AryPriceData['ProductWeightWOGLbs'];
			 	$ProductWeightWOGKgs=$AryPriceData['ProductWeightWOGKgs'];
				$WtPerCtnTxt=$AryPriceData['WtPerCtnTxt'];
		
		//print_r($AryPriceData); exit;
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		$ExporterGstNo=$AryData['ExporterGstNo'];
		$TotalGrossWtKgs=$AryData['TotalGrossWtKgs'];
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$PoDate=$AryData['PoDate'];
		 $ShowBank=$AryData['ShowBank']; 
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$showBuyer=$AryData['showBuyer'];
		$showImporter=$AryData['showImporter'];
		$showNotifyParty=$AryData['showNotifyParty'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		//$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$NotifyPartyName=$AryData['NotifyPartyName']; 
		$NPartyAddress=$AryData['NPartyAddress']; 
		$NPartyContactNumber=$AryData['NPartyContactNumber']; 
		$ImporterName=$AryData['ImporterName']; 
		$ImporterAddress=$AryData['ImporterAddress'];
		
        $PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$PortofLoading=$AryData['PortofLoading'];
		$DestinationCountry=$AryData['DestinationCountry'];
		//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$QtyUnit=$AryData['QtyUnit'];
		 $UnitCurrency=$AryData['UnitCurrency']; 
		$TotalMcs=$AryData['TotalMcs'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$Remarks=$AryData['Remarks'];
		$showRemarks=$AryData['showRemarks'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		$ShowCWiseWeightPerctText=$AryData['ShowCWiseWeightPerctText']; 
        $ShowCWiseComSplNote=$AryData['ShowCWiseComSplNote']; 
		$ShowCWiseComDesNote=$AryData['ShowCWiseComDesNote']; 
		$ShowCWiseProductSpecification=$AryData['ShowCWiseProductSpecification']; 
		$ShowCWiseSpecification=$AryData['ShowCWiseSpecification']; 
		$ShowCWiseNetWeight=$AryData['ShowCWiseNetWeight']; 
		$ShowCWiseGrossWeight=$AryData['ShowCWiseGrossWeight']; 
		$ComDescriptionSpl=$AryData['ComInvDescription']; 
		$PackingRemarks=$AryData['PackingRemarks']; 

		
		$ShowCWiseUsfda=$AryData['ShowCWiseUsfda'];
		$showProFEI=$AryData['showProFEI'];
		 $ShowBank=$AryData['ShowBank']; 
		 $ShowInvoicevalue=$AryData['ShowInvoicevalue']; 
		 $ShowFrightCharge=$AryData['ShowFrightCharge']; 
		


 

$AryBankData=getBankDetails($iconn,$BankID);

//print_r($AryBankData); exit;


$BenfName=$AryBankData['AccntName'];
$BenfAccNo=$AryBankData['AccountNumber'];
$BenfBankName=$AryBankData['BankName'];
$BenfBankAddress=$AryBankData['BankAddress'];
$BenfBankCode=$AryBankData['SwiftCode'];

$BankDetails=$AryBankData['BankDetails'];
$RoutingThrough=$AryBankData['RoutingThrough'];
$NegotiatingBank=$AryBankData['NegotiatingBank'];
	 

				$BILL_WORD=numberTowords($UsdValue);

			
						$NetWgtKgs=$AryPriceData['NetWgtKgs'];
						$NetWgtLbs=$AryPriceData['NetWgtLbs'];
						$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
						$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
						if($QtyUnit=='KGS'){
			$GrossWeightKgs=$TotalGrossWtKgs;
			$GrossWeightLbs=$TotalGrossWtKgs*2.204; 
			}else{
			$GrossWeightKgs=$TotalGrossWtKgs*0.454;
			$GrossWeightLbs=$TotalGrossWtKgs;
			}
						$AryPaymentData=getPaymentValues($iconn,$ID);
						
						//print_r($AryPaymentData); exit;
$FrieghtCharge=$AryPaymentData['FrieghtCharge'];
$DiscountAmount=$AryPaymentData['DiscountAmount'];
$InvValueUSDWithOutDuties=$AryPaymentData['InvValueUSDWithOutDuties'];
$InvValueINRWithOutDuties=$AryPaymentData['InvValueINRWithOutDuties'];
 $DutiesPrice=$AryPaymentData['DutiesPrice'];
$FOBValueUSD=$AryPaymentData['FOBValueUSD'];
$AntiDumpingDuty=round($AryPaymentData['AntiDumpingDuty'],2);
$MerchantDuty=round($AryPaymentData['MerchantDuty'],2);
$HarborDuty=round($AryPaymentData['HarborDuty'],2);
$FOBValueINR=round($AryPaymentData['FOBValueINR']);
$InvValueUSD=$AryPaymentData['InvValueUSD'];


				
			$content.='
			<table border="1" cellspacing="0" cellpadding="2">
			
			<tr>';
			
			
			$content.='<td width="100%">
			
			<table border="0" cellspacing="0" cellpadding="2">';
			
			if($QtyUnit=='KGS'){
				$content.='<tr>
			<td align="left" width="30%">   <b>GROSS WEIGHT:</b>    </td>
			<td align="left" width="35%" >    <b>'.number_format($GrossWeightKgs,3).' KGS</b>   </td>
			</tr>';	
			$content.='<tr>
			<td align="left" width="30%">   <b>NET WEIGHT: </b>   </td>
			<td align="left" width="35%" >    <b>'.number_format($NetWgtKgs,3).' KGS</b>   </td>
			</tr>';
			
			if($GlazePerc!=0){
				$content.='<tr>
			<td align="left" width="30%">   <b>NET WEIGHT WITHOUT GLAZE </b>   </td>
			<td align="left" width="35%" >    <b> '.number_format($ProductWeightWOGKgs,3).' KGS</b>   </td>
			</tr>';
			}
				
			}else{
				$content.='<tr>
			<td align="left" width="30%">   GROSS WEIGHT    </td>
			<td align="left" width="35%" >    <b>: '.number_format($GrossWeightLbs,3).' LBS</b>   </td>';
			if($ShowCWiseGrossWeight){
			$content.='<td align="left" width="35%" >    <b> '.number_format($GrossWeightKgs,3).' KGS</b>   </td>';
			
			}$content.='</tr>';
			
			$content.='<tr>
			<td align="left" width="30%">   NET WEIGHT    </td>
			<td align="left" width="35%" >    <b>: '.number_format($NetWgtLbs,3).' LBS</b>   </td>';
			if($ShowCWiseGrossWeight){
			$content.='
			<td align="left" width="35%" >    <b> '.number_format($NetWgtKgs,3).' KGS</b>   </td>';
			}
			$content.='</tr>';
			
			if($GlazePerc!=0){
				$content.='<tr>
			<td align="left" width="30%">   <b>NET WEIGHT WITHOUT GLAZE</b>    </td>
			<td align="left" width="35%" >    <b> '.number_format($ProductWeightWOGLbs,3).' LBS</b>   </td>
			</tr>';
			}
			}
			
			
			
			$content.='</table>';
			
			
			
			$content.='</td></tr></table>'; 
			
				
				
				
				
				
				$content.='<table border="1" cellspacing="0" cellpadding="5"><tr>';
				 
		$formated_date = date("Y-m-d", strtotime($InvDate));
        $AryDescription=getInvoiceDescription($iconn,$formated_date);
		$CommercialNote=$AryDescription['CommercialNote']; 
				
		$ArrayProductDescription=getProductSpec($iconn,$ID);

			
 $content.='</tr>';
 
 /*if($CustomNote!=''){
				$content.='<tr>  
           		<th  align="LEFT"  width="100%"><font size="8"> '.nl2br($CustomNote).'<BR><BR> 
				
				   
				</th> 
				</tr>';
 }*/
$content.='<tr>
				<th  align="bottom"  width="100%">   ';
				if($ShowCWiseWeightPerctText){
					 $content.= ''.nl2br($WtPerCtnTxt).'<br><br>';
				}
				// $ComDescriptionSpl=str_replace(' ', '&nbsp;', $ComDescriptionSpl);
				if($CommercialNote!=''&&$ShowCWiseComDesNote){
		//echo $CommercialNote; 
				$content.=''.nl2br($CommercialNote).'<br><br>';}
				if($ComDescriptionSpl!=''&&$ShowCWiseComSplNote){
					//$content.=nl2br($ComDescriptionSpl).'<br>';
					}
					if($ShowCWiseProductSpecification)	{
						//$content.=getProductSpec($iconn,$ID);
						$content.=$ArrayProductDescription['Desc'];
						
						}
		
	
						
				if($PackingRemarks!=''){
					$PackingRemarks=str_replace(' ', '&nbsp;', $PackingRemarks);
				$content.=nl2br($PackingRemarks).'<br><br>';	
				}
				if($ShowCWiseSpecification)	{
					$content.=$ArrayProductDescription['Speciesdesc'];
					
					}
					
	//}
				$content.='   </th>
           
		  
</tr><tr> <th  align="center"  width="70%"></th><th  align="center"  width="30%">    
<B> for '.$AuthSign.',</B><BR><BR><BR>
 
			

<p STYLE="text-align:center" > <b>AUTHORISED SIGNATORY <br> Signature & Date </b></p>   </th></tr>
				
				
				</table>';
	
	return $content;
	
	}
	

 

 
 

 

	
		
function getReportData($iconn,$PoID){
		
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
global $AryShowConditions,$DocType;

        $ShowCWiseLc=$AryShowConditions['LcNo']; 
		$ShowCWiseLcDate=$AryShowConditions['LcDate']; 
		$ShowCWiseLcIssuedBy=$AryShowConditions['LcIssuedBy'];
        $ShowCWiseComSplNote=$AryShowConditions['ComSplNote'];
		$ShowCWiseComDesNote=$AryShowConditions['ComDesNote'];
		$ShowCWiseBuyer=$AryShowConditions['Buyer'];
		$ShowCWiseProductSpecification=$AryShowConditions['ProductSpecification'];
		$ShowCWiseSpecification=$AryShowConditions['Species'];
		$ShowCWiseGrossWeight=$AryShowConditions['GrossweightKgs'];
		$ShowCWiseNetWeight=$AryShowConditions['NetweightKgs'];


	 
	 
		
	   $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
        SC.name as PortofDischargeCountry,
		 SCD.LcNo as CLcNo,
        SCD.LcDate as CLcDate,
		SCD.ShowBLNo,
        SCD.ShowBLDate,
        SCD.IssuedBy as CIssuedBy,
		SCD.ComDescriptionSpl as CComDescriptionSpl,ComRemarks as PackingRemarks,
		SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading ,
		SCV.ContainerNo	as ContainerNo ,SPCF.Name as FinalDestination,SCV.ByRoad,SCV.CustomsInvNo
		FROM ScrmCustomInvoices as SCV 
		left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID 
		 left JOIN ScrmCountriesList as SCL on SSD.DestinationCountry=SCL.id
	    left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
        left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
		left JOIN ScrmPortCities as SPCF on SSD.FinalDestination=SPCF.PortId
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SL on SL.id=SP.Country
		 left JOIN ScrmISFDetails as SID on SID.PIID=SCV.PiID
		  left JOIN ScrmCommercialData as SCD on SCD.PoID=SCV.PiID
		  		 left JOIN ScrmPaymentTerms as SPT on SCD.ComPaymentTerms=SPT.ID

		WHERE SCV.PiID='$PoID'";
			$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
			$CustomInvData = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvData['IGST'];
			$OverSeasCommission=$CustomInvData['OverSeasCommission'];
			$CInvDate=$CustomInvData['CInvDate'];
			$CInvCode=$CustomInvData['CInvCode'];
			$NoOfDays=$CustomInvData['NoOfDays'];
			$TotalGrossWt=$CustomInvData['TotalGrossWt'];
			$ComInvDescription=$CustomInvData['CComDescriptionSpl'];
			$PackingRemarks=$CustomInvData['PackingRemarks'];
			$VesselNumber=$CustomInvData['OriginatingVessel'].'<br> ' .$CustomInvData['VoyOrg'];
          $BLDate=getFormatedDateDMMY($CustomInvData['BLDate']);
		$SBDate=getFormatedDateNA($CustomInvData['SBDate']);
		$BLNo=$CustomInvData['BLNo'];
		$SBNo=$CustomInvData['SBNo'];
		$DataLogger=$CustomInvData['DataLogger'];
		$DataLoggerPositions=$CustomInvData['DataLoggerPositions'];
		$LcNo=$CustomInvData['CLcNo'];
		 $LcDate=getFormatedDateNA($CustomInvData['CLcDate']); 
		$LcIssuedBy=($CustomInvData['CIssuedBy']);
		$ComDeliveryTerms=$CustomInvData['ComDeliveryTerms'];
		$PortOfLoading=$CustomInvData['PortOfLoading'];
		 $ComPaymentTerms=$CustomInvData['PaymentName']; 
		 $ByRoad=$CustomInvData['ByRoad']; 
		 $ShowBLDate=$CustomInvData['ShowBLDate'];
		$ShowBLNo=$CustomInvData['ShowBLNo'];
	
 $SqlOrgVessel="SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
FromTOPID='$PortOfLoading' and PIID='$PoID'";
//exit;
$StmtOrgVessel=$iconn->query($SqlOrgVessel); 
$RowOrgVessel= $StmtOrgVessel->fetch_assoc();

$OrgVesselName=$RowOrgVessel['VesselName'];	
$OrgVOYNO=$RowOrgVessel['VOYNO'];		
		
		
		//	$ComPaymentTerms=$CustomInvData['PaymentName'];
			if($NoOfDays==0){
				$NoOfDays='';

			}
			else{

				$NoOfDays=$NoOfDays.' DAYS FROM BL';
			}

  $SqlMcsTotal="SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
  $StmtMcsTotal=$iconn->query($SqlMcsTotal);
  $RowMcs=$StmtMcsTotal->fetch_assoc();
  $TotalMcs=$RowMcs['Mcs'];		
  



  $SqlScrmPoData="SELECT *,SPD.Status as PoStatus,ALD.SignURL,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,
SCC.name as ConsigneeCountry,
ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode ,SCL.name as DestinationCountryName
	FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
  left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
  left JOIN ScrmCountriesList as SCC on SCC.id=SCD.ConsigneeCountry
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
  left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 WHERE PoID='$PoID'";  
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];
$CountryID=$PoDataRow['DestinationCountry'];

 $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
$StmtLogin=$iconn->query($SqlLogin); 
$RowLogin = $StmtLogin->fetch_assoc();
$SignURL=$RowLogin['SignURL'];
		
		$ExporterData=getExporterDetails($ExpID);
		//$ProcessorData=getExporterDetails($ProcessorID);
		$ProcessorData=getExporterDetails($ProcessorID,$CountryID,$DocType);
		
		
		if($PoDataRow['NotifyPartyID']!=0){
		$AryData['showNotifyParty']=true;	
		}else{
		$AryData['showNotifyParty']=false;	
		}
		
		if($CustomInvData['Discount']!=0){
		$AryData['showDiscount']=true;	
		}else{
		$AryData['showDiscount']=false;	
		}		
		
		/*if($CustomInvData['Discount']=='DISCOUNT'){
		$AryData['showDiscount']=true;	
		}else{
		$AryData['showDiscount']=false;	
		}*/
		
		if($CustomInvData['IGST']=='IGST'){
		$AryData['showIgst']=true;	
		}else{
		$AryData['showIgst']=false;	
		}
		
		
		
		if($PoDataRow['ShippingTerms']=='DDP'){
		$AryData['ShowShippingTerms']=false;	//uma
		}else{
		$AryData['ShowShippingTerms']=false;	
		}
		
		if($PoDataRow['ShippingTerms']=='FOB'){
		$AryData['ShowInvoicevalue']=true;	//uma
		}else{
		$AryData['ShowInvoicevalue']=true;	
		}
		
		if($CustomInvData['DataLogger']!=''){
		$AryData['ShowDataLogger']=true;	//uma
		}else{
		$AryData['ShowDataLogger']=false;	
		}
		
		
		
		
		//if($PoDataRow['DestinationCountry']==26){
		if($PoDataRow['DestinationCountry']!=26 && $PoDataRow['ShippingTerms']!='FOB'){  //uma
		$AryData['ShowDeliveryTerms']=true;	
		}else{
		$AryData['ShowDeliveryTerms']=false;	
		}
		
if($PoDataRow['ImporterID']!=0){
		$AryData['showImporter']=true;	
		}else{
		$AryData['showImporter']=false;	
		}
		if($ExpID!=5 && $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpUsFda']=false;	
		}
		/*else if($ExpID==5 && $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpUsFda']=true;	
		}
		*/else{
		$AryData['showExpUsFda']=false;	
		}
		$AryData['showExpIECode']=true;
		$AryData['showExpGSTNo']=true;
		/*if($ExpID==5){
		$AryData['showExpIECode']=true;	
		}else{
		$AryData['showExpIECode']=false;	
		}*/
		
		/*if($ExpID==5){
		$AryData['showExpGSTNo']=true;	
		}else{
		$AryData['showExpGSTNo']=false;	
		}*/
		
		if($PoDataRow['ShowBank']!=0){
		$AryData['ShowBank']=true;	
		}else{
		$AryData['ShowBank']=false;	
		}

		if($ShowBLNo==1){
			$AryData['ShowBLNo']=true;	
			}else{
			$AryData['ShowBLNo']=false;	
			}
			
			if($ShowBLDate==1){
			$AryData['ShowBLDate']=true;	
			}else{
			$AryData['ShowBLDate']=false;	
			}
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showProFEI']=true;	
		}else{
		$AryData['showProFEI']=false;	
		}
		
		if($PoDataRow['ShowBrand']==1){
		$AryData['ShowBrand']=false;	
		}else{
		$AryData['ShowBrand']=false;	
		}
		//if($PoDataRow['ShowFrightCharge']==1 && $PoDataRow['ShowFrieght']==1){
		if($PoDataRow['ShowFrightCharge']==1){
		$AryData['ShowFrightCharge']=true;	
		}
		
		else{
		$AryData['ShowFrightCharge']=false;	
		}
		
		if($PoDataRow['Insurance']!=0){
		$AryData['ShowInsurance']=true;	
		}else{
		$AryData['ShowInsurance']=false;	
		}
		
		$AryData['ShowCWiseWeightPerctText']=$AryShowConditions['WeightPerctText'];
		
		
		
		if($PoDataRow['PoNo'] ==''){			
		$AryData['PoDate']='';	
		}else{
		$AryData['PoDate']= "& Dated :".getFormatedDate($PoDataRow['PoDate']);
		}
		if($IGST !='DISCOUNT'){			
		 $AryData['ShowNoofDays']=true;	
		}else{
		$AryData['ShowNoofDays']=false;
		}
		
		
		 if($ComPaymentTerms!=''){
   $PaymentTerms=$CustomInvData['PaymentName'];			
		}
		else{
		$PaymentTerms=$PoDataRow['PaymentName']	;
		}
		//$PaymentTerms=$PoDataRow['PaymentName']	;
		
		
		/* Return Data*/
		$AryData['TotalGrossWtKgs']=$TotalGrossWt; 
		$AryData['PoStatus']=$PoDataRow['PoStatus']; 
		$AryData['ApprovedBy']=$ApprovedBy; 
		//$AryData['PaymentTerms']=$PaymentTerms; 
		$AryData['DeliveryTerms']=$DeliveryTerms; 
		$AryData['TotalMcs']=$TotalMcs; 
		$AryData['ExporterID']=$ExpID; 
		$AryData['DataLoggerPositions']=$DataLoggerPositions; 
		$AryData['DataLogger']=$DataLogger;
$AryData['ConsigneeCountry']=$PoDataRow['ConsigneeCountry'];		
		
		
		$AryData['ExporterName']=$ExporterData['ExpName'];;
		$AryData['ExporterAddress']=$ExporterData['ExpAddress'];
		$AryData['ExporterUsfdaNo']=$ExporterData['ExpUsfdaNo'];
		$AryData['ExporterIeCode']=$ExporterData['ExpIeCode'];
		$AryData['ExporterGstNo']=$ExporterData['ExpGstNo'];
		$AryData['DestinationCountryId']=$ExporterData['DestinationCountry'];
		$AryData['ExporterRecAdd']=$ExporterData['ImporterRecordAddress']; 
		$AryData['AuthSign']=$ExporterData['AuthSign']; 
		$AryData['ShowCWiseLc']=$ShowCWiseLc; 
		$AryData['ComInvDescription']=$ComInvDescription; 
		$AryData['PackingRemarks']=$PackingRemarks; 
		$AryData['ShowCWiseLcDate']=$ShowCWiseLcDate; 
		$AryData['ShowCWiseLcIssuedBy']=$ShowCWiseLcIssuedBy; 
		$AryData['ShowCWiseWeightPerctText']=$AryShowConditions['WeightPerctText']; 
		$AryData['ShowCWiseNotifyPrty']=$AryShowConditions['NotifyPrty']; 
		$AryData['ShowCWiseComDesNote']=$ShowCWiseComDesNote; 
		$AryData['ShowCWiseComSplNote']=$ShowCWiseComSplNote; 
		$AryData['ShowCWiseBuyer']=$ShowCWiseBuyer;
		
		$AryData['OrgVesselName']=$OrgVesselName;
		$AryData['OrgVOYNO']=$OrgVOYNO;
		$AryData['ByRoad']=$ByRoad;
		$AryData['ShowCWiseProductSpecification']=$ShowCWiseProductSpecification; 
		$AryData['ShowCWiseSpecification']=$ShowCWiseSpecification;
		$AryData['ShowCWiseGrossWeight']=$ShowCWiseGrossWeight; 
		$AryData['ShowCWiseNetWeight']=$ShowCWiseNetWeight; 
		$AryData['ShowCWiseUsfda']=$AryShowConditions['Usfda'];
		$AryData['ShowCWiseApprovalNo']=$AryShowConditions['ApprovalNo'];

		

		if($ShowCWiseLc&&$ShowCWiseLcDate&&$ShowCWiseLcIssuedBy){
			//$AryData['PaymentTerms']=$PaymentTerms.' LC NO.'.$LcNo.' DATED '.$LcDate.','.$LcIssuedBy;
		if($LcNo==''){
			$AryData['PaymentTerms']=$PaymentTerms;
			}else{				
			$AryData['PaymentTerms']=$PaymentTerms.' LC NO.'.$LcNo.', DATED '.$LcDate.','.$LcIssuedBy;	
			}
		}
		else{
		$AryData['PaymentTerms']=$PaymentTerms;

		}
		
		if($ByRoad!=''){
			$FinalDestination=$ByRoad;
		}
		else{
			$FinalDestination=$CustomInvData['FinalDestination'];
		}
		
		$AryData['ConsigneeID']=$PoDataRow['ConsigneeID'];
		$AryData['ReferenceNo']=$PoDataRow['ReferenceNo'];
		$AryData['BuyerID']=$PoDataRow['BuyerID'];
		$AryData['ConsigneeName']=$PoDataRow['ConsigneeName'];
		$AryData['ConsigneeAddress']=$PoDataRow['ConsigneeAddress'];
		$AryData['BuyerName']=$PoDataRow['BuyerName'];
		$AryData['BuyerAddress']=$PoDataRow['BuyerAddress'];
		$AryData['DestinationCountryName']=$PoDataRow['DestinationCountryName'];
		
		
		
		$AryData['PortOfDischarge']=strtoupper($CustomInvData['PortofDischarge']).' '.strtoupper($CustomInvData['CountryCode']).', '.$CustomInvData['PortofDischargeCountry'];
		
		$AryData['PlaceOfReceiptBy']=$CustomInvData['PlaceOfRecipt'];
		$AryData['PortOfLoading']=strtoupper($CustomInvData['PortOfLoading']).','.strtoupper($CustomInvData['PortOfLoadingCountry']);
	 	$AryData['FinalDestination']=strtoupper($FinalDestination);
        $AryData['VesselNumber']=$VesselNumber; 
		
		$AryData['InvNo']=$CustomInvData['CustomsInvNo'] ;
		$AryData['InvDate']=getFormatedDate($CInvDate);			
		$AryData['PoNo']=$PoDataRow['PoNo'];
		
		
		
		$AryData['ProcessorName']=$ProcessorData['ExpName'];
		//$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
		$AryData['ProcessorAddress']=$ProcessorData['ExpAddress'];
		$AryData['ProcessorUsfdaNo']=$ProcessorData['ExpUsfdaNo'];
		$AryData['ProcessorFEI']=$ProcessorData['ExpFeiNo'];
		$AryData['ProcessorApprovalNo']=$ProcessorData['ExpApprovalNo'];
		$AryData['ProcessorIeCustomsNo']=$ProcessorData['ExpIeCustomsNo'];
		
		
		$AryData['ShippingTerms']=$PoDataRow['ShippingTerms'];
		$AryData['DestinationCountry']=$PoDataRow['name'];
		$AryData['DeliveryTerms']=$PoDataRow['DeliveryTerms'];
		$AryData['QtyUnit']=$PoDataRow['QtyUnit'];
		$AryData['ShipByDate']=$PoDataRow['ShipByDate'];
		$AryData['SignURL']=$SignURL;
		$AryData['PreparedBy']=$PoDataRow['PreparedBy'];
		$AryData['BankID']=$PoDataRow['BankID'];
		$AryData['IGST']=$IGST;
		$AryData['CInvCode']=$CInvCode;
		$AryData['NoOfDays']=$NoOfDays;
		$AryData['UnitCurrency']=$PoDataRow['UnitCurrency'];
					
					
		$AryData['NotifyPartyName']=$PoDataRow['NotifyPartyName']; 
		$AryData['NPartyAddress']=$PoDataRow['NPartyAddress']; 
		$AryData['NPartyContactNumber']=$PoDataRow['NPartyContactNumber'];
        $AryData['ImporterName']=$PoDataRow['ImporterName']; 
		$AryData['ImporterAddress']=$PoDataRow['ImporterAddress']; 
		
		
		$AryData['ContainerNo']=$CustomInvData['ContainerNo'];
		 $AryData['SealNo']=$CustomInvData['SealNo'];
		$AryData['IRNNumber']=$CustomInvData['IRNNumber'];
		$AryData['BLDate']=$BLDate; 
		$AryData['SBDate']=$SBDate; 
		$AryData['BLNo']=$BLNo; 
		$AryData['SBNo']=$SBNo;
		
        
		return $AryData;
	}
	
	
function getBreakUpData($iconn,$GradeID,$PiID){
	global $PType;
		
		if($PiID==''){
			$Qry="WHERE PGD.ID in ($GradeID)";
		}else{
		$Qry="WHERE InvId='$PiID'";	
		}
		
		$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
				sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as GlazeWeightKgs,
				 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as GlazeWeightLbs";
							
			/*$SqlTotal="SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry"; */
			
			$SqlTotal="SELECT sum(Mcs) as MCS,InvId,GlazePerc,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 	as PGD 
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID
			
			$Qry"; 
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();	
			$InvId=$RowTotal['InvId'];
            $GlazePerc=$RowTotal['GlazePerc'];



if($PiID!=''){
			$SqlWtPerCtn="SELECT round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,round(GrossWt/Sum(Mcs),2) as GrossWt FROM `ScrmPoGradeData` as SPGD 
				left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID 
				WHERE InvId='$InvId' GROUP by PackingStyleID;"; 
			$StmtWtPerCtn=$iconn->query($SqlWtPerCtn);
			$RowWtPerCtn=$StmtWtPerCtn->fetch_assoc();
			$NetWtPerCtn=$RowWtPerCtn['NetWtLbs'];
			$GrossWtPerCtn=$RowWtPerCtn['GrossWt'];
			$WtPerCtnTxt='EACH M/CTNS. CONSISTS OF NET WT. '.$NetWtPerCtn.' LBS AND GROSS WT. '.$GrossWtPerCtn.' LBS.';
			}


			$SqlQtyUnit="SELECT QtyUnit from ScrmPoData WHERE PoID='$InvId'"; 
			$StmtQtyUnit=$iconn->query($SqlQtyUnit);
			$RowQtyUnit=$StmtQtyUnit->fetch_assoc();
			 $QtyUnit=$RowQtyUnit['QtyUnit'];
			 
			/*	if($PType=='KGS'){
					$QtyUnit='KGS';
				}
			*/
				
			if($QtyUnit=='KGS'){
			$Qty=$RowTotal['NetWgtKgs'];
			$UsdValue=$RowTotal['PriceKgs'];
			$UnitPrice=number_format($RowTotal['Price'],2);
			}else{
			//$Qty=$RowTotal['NetWgtLbs']*0.454;// Changed cause Lb and Kg Separate
			$Qty=$RowTotal['NetWgtLbs'];
			$UsdValue=$RowTotal['PriceLbs'];
			$UnitPrice=round(($UsdValue/$Qty),7);
			}
			if($QtyUnit=='KGS'){
				  $NetWgtKgs=$RowTotal['NetWgtKgs'];
				$GrossWeightKgs=$RowTotal['GrossWeightKgs'];
				 $GlazeWeight=$RowTotal['GlazeWeightKgs'];
				//echo $AryPriceData['ProductWeightWOG']=$NetWgtKgs-$GlazeWeightKgs;exit;
			

			}
			
			else{
				$NetWgtKgs=$RowTotal['NetWgtLbs']*0.454;
				$GrossWeightKgs=$RowTotal['GrossWeightLbs']*0.454;
				$NetWgtLbs=$RowTotal['NetWgtLbs'];
				$GrossWeightLbs=$RowTotal['GrossWeightLbs'];
				$GlazeWeight=$RowTotal['GlazeWeightLbs'];
			//$AryPriceData['ProductWeightWOG']=$NetWgtLbs-$GlazeWeightLbs;
			}
			
			/*if($QtyUnit=='KGS'){
				//$AryPriceData['Qty']=$Qty;
				$AryPriceData['Qty']=$RowTotal['NetWgtKgs'];
			}
			
			else{
				$AryPriceData['Qty']=$RowTotal['NetWgtLbs']; 
			}
			*/
			$AryPriceData['Price']=$RowTotal['Price'];
			$AryPriceData['TotalMcs']=$RowTotal['MCS'];
			
			$AryPriceData['UsdValue']=$UsdValue;
			$AryPriceData['UnitPrice']=$UnitPrice;
			$AryPriceData['Qty']=$Qty;
			$AryPriceData['GlazePerc']=$GlazePerc;
			
			
			/*$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
			$AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
			$AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
			$AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];*/
			
            $AryPriceData['NetWgtKgs']=$NetWgtKgs;
			$AryPriceData['NetWgtLbs']=$NetWgtLbs;
			$AryPriceData['GrossWeightKgs']=$GrossWeightKgs;
			$AryPriceData['GrossWeightLbs']=$GrossWeightLbs;
            $GlazeWeightKgs=$RowTotal['GlazeWeightKgs'];
			$GlazeWeightLbs=$RowTotal['GlazeWeightLbs'];
			 $AryPriceData['ProductWeightWOGKgs']=$NetWgtKgs-$GlazeWeightKgs;
			$AryPriceData['ProductWeightWOGLbs']=$NetWgtLbs-$GlazeWeightLbs;
			$AryPriceData['WtPerCtnTxt']=$WtPerCtnTxt;
			//$AryPriceData['ProductWeightWOG']=$Qty-$GlazeWeight;
			
return 	$AryPriceData;		
		
	}


function getBankDetails($iconn,$BankID){
	
 $SqlBank="SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
$StmtBank=$iconn->query($SqlBank); 
$AryBankData = $StmtBank->fetch_assoc();

return $AryBankData;

}	
function checkValueAval($Value){
	 if($Value==''){
	 $Value='';  
   }
  ELSE{
	$Value='('.$Value.')';  
  } 
  return $Value;
  }
  
function getProductSpec($iconn,$PoID){
	
 $SqlSpecies="SELECT GROUP_CONCAT(DISTINCT(SpeciesName)) as SpeciesName FROM `ScrmPoGradeData` as SPDG 
LEFT JOIN ScrmPIVarietyList as SPVL ON SPDG.VarietyID=SPVL.VarietyID 
left join ScrmSpeciesMaster as SSM on SSM.ShortCode=SPVL.Species WHERE InvId='$PoID'";
$StmtSpecies=$iconn->query($SqlSpecies); 
$AryStmtSpecies = $StmtSpecies->fetch_assoc();
$Desc='<BR> THIS PRODUCT HAS NOT BEEN REFUSED BY ANY COUNTRY PRIOR TO SHIPMENT TO THE U.S.A.<br>';
$Speciesdesc='SPECIES : '.$AryStmtSpecies['SpeciesName'];
$ArrayDescription['Desc']=$Desc;
$ArrayDescription['Speciesdesc']=$Speciesdesc;

return $ArrayDescription;


}
?>
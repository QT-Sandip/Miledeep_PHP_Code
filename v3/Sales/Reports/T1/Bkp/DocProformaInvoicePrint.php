<?php
  $PoID=$_GET['id'];
//ini_set( 'display_errors', 1 );
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($cp."Includes/IncludePage.php");
//include ("../../RestAPI.php");
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include_once ROOT.'config/api-call.php';
if(isset($_GET['ID']) && !empty($_GET['ID'])){

    $ProformaID = trim($_GET['ID']);

    $api = PostProfromaPrint;

    $bodyContent = array("ProformaID" => $ProformaID);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

//	print_r($resPostProfromaPrint);
    $result = $resPostProfromaPrint->Data;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;
     
}else{

}


 
 
 
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
  
$style = '<style>
th {
    font-size: 6px;
	padding : 10px;
}
td {
    font-size: 6px;
	padding : 10px;
}
p {
	//line-height: 0.01;
}
</style>
';


class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
if($Status==0){
	$StatusTerm="(Approval Pending)";
}else if($Status==2){
	$StatusTerm="(Cancelled)";
}else if($Status==4){
	$StatusTerm="(Rejected)";
}
$StatusTerm='';
	global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 8);
		$this->Cell(110, 0, 'PROFORMA INVOICE '.$Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetTextColor(255,0,0);
		$this->SetFont('helvetica', '', 8);
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
    $pdf->SetTitle('Proforma Invoice: '.$Header);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
//	$AryData=getReportData($iconn,$ID);
	
	
	//$table_data=getProformaDetails($iconn,$ID);
	$HeaderTable=getHeaderTable($iconn,$ID);
	$BreakUpTable=getBreakTable($iconn,$ID);
	$FooterTable=getFooterTable($iconn,$ID);
	
	
	
	$pdf->writeHTML($HeaderTable.$BreakUpTable.$FooterTable);
	/*$pdf->writeHTML($HeaderTable);
	$pdf->writeHTML($BreakUpTable);
	$pdf->writeHTML($FooterTable);*/
	//$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
//echo $pdf->getAliasNbPages(); exit;

$file_name=getPIInvoiceNumber($iconn,$ExpID,$PoID); 
$file_name = str_replace('/','', $file_name );

$file_name=$Fname.$file_name.'.pdf';
// $file_name="DFL-".$Fname."-".$file_name.'.pdf';
 


ob_end_clean();
if($Type==''){
$Type='I';
}else{
	$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}


$pdf->Output($file_name, $Type);


function getHeaderTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $resLabels,$result;
	

	$Labels=$resLabels;
	$resData=$result;
				
	$content='';                                                                
	$size=45;	
	$size1=55;	

 

	 $content .= $style; 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  
				
				 
				 
				 <tr>

				 
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';
				
				$content .= '
				<tr>
				<th  align="LEFT" ><font size="7"><b>'.$Labels->Exporter .' :</b>
				<br>'.$resData->ExporterName.' 
				<BR> '.$resData->ExporterAddress.'' ;
				
			
				
				
				$content .= '</font></th></tr>';
				
		
		
		
		
		
			$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>'.$Labels->Consignee .'</u></b>
				<br>'.$resData->ConsigneeName.',<br>'.$resData->ConsigneeAddress.'</font></th>
				</tr>';
			if($showNotifyParty){
				$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b> <u>'.$Labels->Consignee .'</u></b>
				<br>'.$resData->NotifyPartyName.',<br>'.$resData->NPartyAddress.'<br>TEL:'.$resData->NPartyContactNumber.'
				
				
				</font></th>
				</tr>';	
			}
				
				$content .= '<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">'.$Labels->NotifyPrty .'  <BR>  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">'.$Labels->PortOfLoading .'
				<br align="center">'.$resData->PortofLoading.'</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">'.$Labels->PortOfDischrg .'
				<br align="center">'.$resData->PortOfDischarge.'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">'.$Labels->FinlDestintn .'
				<br align="center">'.$resData->FinalDestination.'</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b>'.$Labels->InvoiceNoDt .'</b>
				 '.$resData->InvNo.',Dated '.$resData->InvDate.' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b>'.$Labels->PurchseOrderNo .'</b>
				<br> '.$resData->PoNo.$resData->PoDate.' </font></th>
				</tr>';
			
				
				$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b>'.$Labels->ReferenceNo .'  :
				'.$resData->ReferenceNo.'  </b>
				
				</font></th></tr >';
				if($showImporter){
				
				$content .= '	<tr> 
           		
		
                <th  align="left" width="100%"><font size="7">
				<b><u> '.$Labels->ImporterOfRcrd .'  :</u></b> <br>  '.$resData->ImporterName.'
				<br> '.$$resData->ImporterAddress.' </font></th>
				</tr>';
				}if($showBuyer){
				
				$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> <b><u>'.$Labels->ImporterOfRcrd .' :</b></u><br>'.$resData->BuyerName.',<br>'.$resData->BuyerAddress.'<br></font></th>
				</tr>';
				}
				
				
				$content .= '<tr >
				<th  align="LEFT"  ><font size="7" > <u><b>'.$Labels->Manufacturer .'</b></u>
				<br> '.$resData->ProcessorName.'<br> '.$resData->ProcessorAddress.'<br>
				'.$Labels->ApprovalNo .$resData->ProcessorApprovalNo.'';
				
				
				
				
				if($ShowCWiseUsfda){			
				$content .= '<br><u>BIO TERRORISM /USFDA REGISTRATION NO  : '.$resData->ProcessorUsfdaNo .'</u>';
				}
				
				
				$content .= '</font></th></tr>';
	
	
			 
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
				
				<th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  '.$resData->DestinationCountry.'</b></font></th>
				
				
				
               
				</tr>
				<tr> ';
				
				
				$content .= '<th border="1"  align="left" width="50%"><font size="7"> Terms of Delivery  ';
				if($ShowCWiseDeliveryTerms){
				$content .= '	<br><b align="center">  '.$resData->DeliveryTerms.'</b>';
				}
				if($ShowCWiseDeliveryTermCode){
				$content .= '	<b><br align="center">'.$resData->DeliveryTermCode.'</b>';
				}
				$content .= '</font></th>';
				
				$content .= '<th  align="LEFT"  width="50%" ><font size="7"><b> <u>Terms of Payment:</u>
				</b><br><BR> ' .$resData->PaymentTerms .' <br>
				<!--
				 SHIPMENT TERM : '.$resData->ShippingTerms .' 
				 '.strtoupper($resData->PortOfDischarge).', '.
				strtoupper($resData->DestinationCountry).' -->';
				
				
				
				
				$content .= '</font> </th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> ';
				
				
				
				
				$content.='</table>'; 
	
	return $content;
	
	}
	
function getBreakTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData,$style,$ItemsGroup;			
	$content=$style;
	                                                                  

	$size=45;	
	$size1=55;	

				
				$content='
				<table border="1" cellspacing="0" cellpadding="2">
				<tr>
				<th  align="LEFT" width="58.5%" ><font size="7">  Description of Goods <b align="center">'.$SealNo.'</b></font></th>
                <th  align="center" width="13.5%" >
				<font size="7">Quantity in '.$QtyUnit.' </font></th>
                <th  align="center" width="13.5%" ><font size="7">Rate per <br>'.$QtyUnit .'/'.$ShippingTerms .'<b>'.strtoupper($w).'</b></font></th>
                <th  align="center" width="14.5%" ><font size="7">Amount in '.$UnitCurrency.' '.'<b> '.strtoupper($w).'</b></font></th>
                </tr>';
			

				
				$FontSize="7px";
				
			
			$CurrentCount=0;
			$Whitspace='<b style="color:white">    </b>';
			foreach($ItemsGroup as $Item){

			$Item->ItemDescription; 
			
			$content.='
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$Item->ItemDescription.'</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>';
				
			$FontSize="7";
			$content.='';
			$content.='<tr>';
			
			
			$content.='
				<td align="center" width="38.5%" style="border-right:1px solid white;"  ><font size="'.$FontSize.'">
				'.$Item->ItemDescription.'  </font></td>';
				
		//	foreach($Items->ItemsList as $RowItem){
				$content.='<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				'.$RowItem->Grade.'  </font></td>';
				if($ShowCWiseBrand){
				$content.='<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				'.$RowItem->Grade.'  </font></td>';
				}else{
				$content.='<td align="center" width="20%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				</font></td>';
				}
				$content.='<td align="center" width="13.5%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				'.number_format($Qty,3).' </font></td>
				<td align="center" width="13.5%" style="border-right:1px solid black;"  ><font size="'.$FontSize.'">
				'.$Price.'</font></td>
				<td align="center" width="14.5%" style="border-right:1px solid black;" ><font size="'.$FontSize.'">
				'.number_format($UsdValue,2).'</font></td>
				
				</tr>';
			//}
			}
			
			
			$content.= '</table>';
 
	
	return $content;
	
	}
	
	
function getFooterTable($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
					
	global $AryData,$style;			
	$content=$style;
	                                                                  

	$size=45;	
	$size1=55;	


		
				
				//$AryPriceData=getBreakUpData($iconn,$RowVariety['ID'],$ID);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				$TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
		       $WtPerCtnTxt=$AryPriceData['WtPerCtnTxt']; 
				
		
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
		$ShowCWiseWeightPerctText=$AryData['ShowCWiseWeightPerctText']; 


 

//$AryBankData=getBankDetails($iconn,$BankID);

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
			$content.='
				<table border="1" cellspacing="0" cellpadding="2">
			
					

					<tr>  
					<td ><font size="7">
				
				<BR> SHIPMENT DATE :  '.getFormatedDate($ShipByDate).' <BR> <BR>
				';
				
				if($ShowBank){
				$content.='<p style="margin-left: 1em;"><b>BANK DETAILS  </b>'.$BankDetails.'</p>';
				$content.='<p><b>Negotiating BANK  </b>'.$NegotiatingBank.'</p>';
				$content.='<p><b>ROUTING THROUGH   </b>'.$RoutingThrough.'</p>';
				}
				
				$content.='</font></td></tr>';
			
						$NetWgtKgs=$AryPriceData['NetWgtKgs'];
						$NetWgtLbs=$AryPriceData['NetWgtLbs'];
						$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
						$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];

			
	
				
				
				$formated_date = date("Y-m-d", strtotime($InvDate));
              
				$AryDescription=getInvoiceDescription($iconn,$formated_date,$ExporterID);
				  $ProformaNote=$AryDescription['ProformaNote'];
				
				
               $content.='
				<tr>  
           		<th  align="LEFT"  width="70%"><font size="7"> AMOUNT CHARGABLE (IN WORDS) ('.$UnitCurrency.' : <b>'.$BILL_WORD.' ONLY )</b><BR> <BR> ';
				if($ShowCWiseWeightPerctText){
					$content.= '<font size="6.5">'.nl2br($WtPerCtnTxt).'<br></font>';
				}
				$content.= '<BR> <br> <b><font size="9">'.$ProformaNote.'</font></b>';
                
				if($showRemarks){
				$content.='<br><br>'.$Remarks.'';	
				}
				
			$content.='</font></th>  	<th  align="center"  width="30%"><font size="7"> For <b>'.$AuthSign.'</b> <BR> <BR> <BR> 
						 
				<img src="'.$SignURL.'" 
				 width="100" height="30"style="width: 100" align="center"     valign="middle"/>				
				<p STYLE="text-align:center" > AUTHORISED SIGNATORY <BR> Signature & Date </p></font></th>
				</tr>';
				
				
				
				$content.='</table>'; 
	
	return $content;
	
	}
?>
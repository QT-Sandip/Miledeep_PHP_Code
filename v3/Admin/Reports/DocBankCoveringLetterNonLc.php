<?php
//ini_set( 'display_errors', 1 );

session_start();

$PoID=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");


 $ID=$_GET['id'];
 $Type=$_GET['Type'];
include "PrintHeaderInc.php";
 
$SqlStatus="SELECT * FROM $TblScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();


  $CustomInvoiceSql="SELECT *,(Select ADCode from ScrmBankMaster where BankID=SED.Bank) as ADCode,
(Select PortCode from ScrmPortCities where PortId=SSD.PortOfLoading) as PortOfLoading

FROM ScrmCustomInvoices as SCV left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID 
left JOIN ScrmShipmentDispatchDetails as SD on SD.PIID=SCV.PiID  
left JOIN ScrmLogisticsDetails as SLD on SLD.PiID=SCV.PiID 
left JOIN ScrmLinerMaster as SL on SL.LinerID=SLD.SteamshipCompany 
left JOIN ScrmEIRBankNFieghtDetaails as SED on SED.PoRefNumber=SCV.PiID WHERE SCV.PiID='$PoID'"; 
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvoiceRow['IGST'];
			$BLDate=getFormatedDate($CustomInvoiceRow['BLDate']);
			$BankletterDate=getFormatedMonthDate($CustomInvoiceRow['BankDate']);
			
			

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
		
		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 10);
		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Bank Letter NonLc '.$row['PO_NUM'].' - '.$row['PO_DATE']);  
    //$pdf->SetTitle(''.$Header2);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10);
	
	$pdf->SetHeaderMargin(8);	
	     
    $pdf->SetMargins(15, 10, 10,5);  
	
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);

    $pdf->SetFont('helvetica', '', 11);  
    //$pdf->ln;  
	
	    $pdf->AddPage();  
$pdf->Cell(150 ,5,'',0,1,'L');
$pdf->Cell(150 ,5,'',0,1,'L');
$pdf->Cell(550 ,5,'',0,1,'L');
$pdf->Cell(550 ,5,'',0,1,'L');
$pdf->Cell(550 ,5,'',0,1,'L');
$pdf->Cell(550 ,5,'',0,1,'L');
$pdf->Cell(550 ,5,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(130 ,5,'To,',0,0,'L');		
$pdf->Cell(80 ,5,'Date:'.$BankletterDate,0,1,'L');
$pdf->Cell(150 ,5,'The Asst. general manager,',0,1,'L');
$pdf->Cell(150 ,5,'State Bank of India,',0,1,'L');
$pdf->Cell(150 ,5,'SME BRANCH',0,1,'L');
$pdf->Cell(150 ,5,'BHIMAVARAM',0,1,'L');




$pdf->writeHTML($contents);
//$pdf->Cell($W1 ,$H1,'',0,1,'L');

$W1=100;
$W2=90;
$H1=5;
$pdf->SetFont($FONT_ARI,'',$sub_header);



	
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

$file_name=$ExporterData['InvShortCode'].'-BankLetterNonLc-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
//sme/pi/233/2022-23

//echo $Type;

ob_end_clean();
if($Type==''){
$Type='I';
}else{
	$Fname=__DIR__ ."/TempFolder/";
	$Fname=$_SERVER['DOCUMENT_ROOT']."SalesNew/Reports/TempFolder/";
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}
$pdf->Output($file_name, $Type);


			


function getDS_TABLE($iconn,$ID){
	
	global $PoDataRow,$PoID;
	
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

  
  $MCS=	$PO_GRADE_DATA['Mcs']; 
   $TOTAL_MCS+=$MCS; 
  
   $UOM=	$PO_GRADE_DATA['UOM']; 
 

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
	
	
			 			$InvShortCode=$ExporterData['InvShortCode']; 

	
							$InvNO=$InvShortCode.$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;
 
			
		
			
			$InvDate=getFormatedDate($CustomInvoiceRow['CInvDate']);
			$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
			$ADCode=$CustomInvoiceRow['ADCode'];
			$SBValue=$CustomInvoiceRow['SBValue'];
            $SBValueFOB=$CustomInvoiceRow['SBValueFOB'];
			$AddOnPoint=explode(',',$CustomInvoiceRow['AddOnPoint']);


			$DocumentsList=explode(',',$CustomInvoiceRow['DocumentsList']);

			
			$PlaceOfReceiptBy=$CustomInvoiceRow['PlaceOfRecipt'];
			
			
			$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
			$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
			$CInvCode=$CustomInvoiceRow['CInvCode'];
			$IGST=$CustomInvoiceRow['IGST'];
			
			$LinerName=$CustomInvoiceRow['LinerName'];
			$SBNo=$CustomInvoiceRow['SBNo'];
			$SBDate=date('d-m-Y',strtotime($CustomInvoiceRow['SBDate']));
			if($SBDate=='01-01-1970'){
			$SBDate='';	
			}
			$SBNoDate=$SBNo.'/'.$SBDate;
			
			
			$SqlUserDetails="SELECT * FROM `$TblAdminUserDetails` WHERE UserID='$NameofRepresentative' ";
		$StmtUserDetails=$iconn->query($SqlUserDetails);
		$RowUserDetails=$StmtUserDetails->fetch_assoc();

			$PoNo=$PoDataRow['PoNo'];
			$PoDate=getFormatedDate($PoDataRow['PoDate']);
			
			
			
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

	

$StyleTbl='Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 20px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';
  
  $StyleTd='Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';
  $w1='30%';
$w2='19%';
$Size='9';
$Space='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
 
	 $contents .='<p><br/>Respected Madam / Sir,<B>' .$RowPoData['QCertNo'] .' </p>
          
		<p> '.$Space.' We are herewith submitting the following documents of '.$TOTAL_MCS.' Master CASES <br>
Inv.no : ' .$InvNO.', Dt. ' .$InvDate .' 
 

 of the Fresh Frozen Indian Shrimps for Negotiation against<br>PO NO : '.$PoNo.' , DT.: '.$PoDate.', issued of '.$ConsigneeName.'
</p>';
	$contents .='		<table border="0" cellspacing="0" cellpadding="1">
<tr>
<th width="100%"><font size="'.$Size.'"><b><u>List of documents presented:</b></u></font></th>
</tr>

';

if($CustomInvoiceRow['DocumentsList']!=''){
foreach($DocumentsList as $i =>$key) {
$i=$i+1;
$SqlCopies="SELECT * FROM `ScrmDocumentsList` where DocName='$key'";
$StmtCopies=$iconn->query($SqlCopies);
$RowCopies=$StmtCopies->fetch_array();
if($RowCopies['NoOfOrginals']==1){
	$Text='ORIGINAL';
}
else{
	$Text='ORIGINALS';
}


$contents .='<tr>
<th width="50%"><font size="'.$Size.'">'.$i.'.'.$key.'</font></th>
<th width="5%"><font size="'.$Size.'">: </font></th>
<th width="45%"><font size="'.$Size.'">'.$RowCopies['NoOfOrginals'].' '.$Text.'</font></th>
</tr>
<!--</tr>-->';
}
}
$contents .='</table><p></p> <br><br>';

	$contents .='		<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="CENTER"  width="'.$w2.'"><font size="'.$Size.'" ><b> SB NUMBER & DATE.</b>
				</font></th>
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'"><b> SB VALUE IN US$</b>
				</font></th>
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'"><b>SB VALUE FOB IN USD</b>
				</font></th>
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'"><b>PORT OF LOADING</b>
				</font></th>
				
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'"><b>AD CODE</b>
				</font></th>
				</tr>
				
				<tr> 
				
				<th  align="CENTER"  width="'.$w2.'"><font size="'.$Size.'" >'.$SBNoDate.'
				</font></th>
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'">'.$SBValue.'</font></th>
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'">'.$SBValueFOB.'</font></th>
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'">'.$PortOfLoading.'</font></th>
				
				<th  align="CENTER" width="'.$w2.'"><font size="'.$Size.'">'.$ADCode.'</font></th>
				
				</tr>';
			
			
				
			$contents .= '	
				
				
				</table>';	

$contents .='<p>HS CODE: '.$CInvCode.'<BR/>
			SHIPPING LINER : <B>'.$LinerName.' </B><BR/>
			Further we solemnly affirm that:<BR/>
			1. We have not availed of any Advance / Packing Credit facility from any bank against the order / Letter of Credit.
			<BR/>
			2. We request you to negotiate the documents. Please transfer to the credit of our
			<B> EPC/PCFC </B>  A/c with your branch.<BR/>
			3. All bank charges with respect of the negotiation may please be debited directly to our Current 
			Account Number : <B> 36718635634 </B>		<BR/>
			4. Please hold the original documents until further our confirmation<BR/>
			'.$AddOnPoint[0].'<br>
			'.$AddOnPoint[1].'<br>
			
		</p>';				
$contents.='<p><br>Thanking you sir,</p>';
			  return $contents;
	
	}


?>
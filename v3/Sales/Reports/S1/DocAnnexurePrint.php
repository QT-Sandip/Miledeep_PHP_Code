<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";
 $ID=$_GET['id'];
 $Type=$_GET['Type'];
 
  $PoID=$_GET['id'];
  $DocType='ANC'; 

 $SqlStatus="SELECT * FROM $TblScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();


//  $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCV 
//   left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
//   left JOIN $ScrmShipmentDispatchDetails as SD on SD.PIID=SCV.PiID

//  left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
//  left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading


//  WHERE SCV.PiID='$PoID'"; 
 
 $CustomInvoiceSql="SELECT *,SPCFD.Name AS FinalDst,SPCFD.CountryCode AS CountryCode,SPC.Name as PortofDischarge,
 SP.Name as PortOfLoading,SCV.ContainerNo
  FROM $ScrmCustomInvoices as SCV 
  left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID

 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SPCFD on SSD.FinalDestination=SPCFD.PortId
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN ScrmShippingBillDetails as SSBD on SSBD.InvID=SCV.PiID
 left JOIN ScrmISFDetails as ISF on ISF.PIID=SCV.PiID
 WHERE SCV.PiID='$PoID'"; 
 //echo $CustomInvoiceSql; exit;
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
			$IGST=$CustomInvoiceRow['IGST'];


 $SqlScrmPoData="SELECT *,SCDB.BuyerName as BuyerName,SCDB.BuyerAddress as BuyerAddress,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress 
 FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];
$CountryID=$PoDataRow['DestinationCountry'];



// $ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
// $ProcessorData=getExporterDetails($ProcessorID,$PoID,$CountryID,$DocType);

$ExporterData=getExporterDetails($ExpID,$CountryID,$DocType);
$ProcessorData=getExporterDetails($ProcessorID,$CountryID,$DocType);


/*$ExporterData=getExporterDetails($ExpID);
$ProcessorData=getExporterDetails($ProcessorID);*/

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
global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 10);
		$this->Cell(150, 0, 'ANNEXURE "C" TO TRADE FACILITY CIRCULAR No: 01/2017'.$Header, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		        $this->SetFont('helvetica', '', 10);

		$this->Cell(110, 0, 'Dated.06.09.2017', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
		$this->Cell(125, 0, 'Examination Report for Self-sealed container', 0, 1, 'R', 0, '', 0, false, 'T', 'M');
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
    $pdf->SetTitle('ANNEXURE "C" TO TRADE FACILITY CIRCULAR No:01/2017,Dated.06.09.2017 '.$Header);  
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

$file_name=$ExporterData['InvShortCode'].'-A.C-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
//sme/pi/233/2022-23

//echo $Type;

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
		
 //$SQL_PO_GRADE_DATA="SELECT * FROM `$ScrmCIGradeData` where InvId='$ID'  ";
 $SQL_PO_GRADE_DATA="SELECT * FROM `ScrmPoGradeData` where InvId='$ID'  ";
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
			$ExpPanNumber=$ExporterData['ExpPanNumber'];
			$ExpBranchCode=$ExporterData['ExpBranchCode'];
			$AuthSign=$ExporterData['AuthSign'];	
			$InvShortCode=$ExporterData['InvShortCode']; 
			$ProcessorName=$ProcessorData['ExpName'];
			//$ProcessorAddress=$ProcessorData['ExpAddress'];
			$ProcessorAddress=$ProcessorData['PackerAddress'];


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
			$ProcessorAddress=$ProcessorData['ExpAddress'];
			$ProcessorUsfdaNo=$ProcessorData['ExpUsfdaNo'];
			$ProcessorApprovalNo=$ProcessorData['ExpApprovalNo'];
			$ProcessorFEI=$ProcessorData['ExpFeiNo'];
			
	
							//$InvNO=$InvShortCode.$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;
 
			//$InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);
			$InvNO=$CustomInvoiceRow['CustomsInvNo'];

		
			
			$InvDate=getFormatedDate($CustomInvoiceRow['CInvDate']);
			$PoDate=getFormatedDate($CustomInvoiceRow['CpoDate']);
			
			$PlaceOfReceiptBy=$CustomInvoiceRow['PlaceOfRecipt'];
			$PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
			$SealNo=$CustomInvoiceRow['LinerSealNo'];
			$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
			$PackSealNo=$CustomInvoiceRow['CustomSealNo'];
			$VesselNumber=$CustomInvoiceRow['VesselNumber'];
			$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
			$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
			$CInvCode=$CustomInvoiceRow['CInvCode'];
			$IGST=$CustomInvoiceRow['IGST'];
			$PortOfDischarge=$CustomInvoiceRow['PortofDischarge'];
			$FinalDestination=$CustomInvoiceRow['FinalDestination'];
			$FinalDst=$CustomInvoiceRow['FinalDst'];
			$CountryCode=$CustomInvoiceRow['CountryCode'];
			$NameofRepresentative=$CustomInvoiceRow['AuthoritySign'];
			$SBNo=$CustomInvoiceRow['ShippingBillNo'];
			//$SBDate=date('d-m-Y',strtotime($CustomInvoiceRow['SBDate']));
			$SBDate=getFormatedDate($CustomInvoiceRow['ShippingBillDate']);
			// if($SBDate=='01-01-1970'){
			// $SBDate='';	
			// }
			$SBNoDate=$SBNo.'/'.$SBDate;
			
			
			$SqlUserDetails="SELECT * FROM `AdminUserDetails` WHERE UserID='$NameofRepresentative' ";
		$StmtUserDetails=$iconn->query($SqlUserDetails);
		$RowUserDetails=$StmtUserDetails->fetch_assoc();

			$PoNo=$PoDataRow['PoNo'];
			//$PoDate=getFormatedDate($PoDataRow['PoDate']);
			
			$PoDate= "& Dated :".$PoDate;
			IF($PoNo ==''){
				
			$PoDate=' ';	
			}
			
		
			$ConsigneeID=$PoDataRow['ConsigneeID'];
			$BuyerID=$PoDataRow['BuyerID'];
			
			
            
			
			// if ($BuyerID == '' || $BuyerID == 0) { //commented by approval of lokesh
			// $ConsigneeName=$PoDataRow['ConsigneeName'];
			// $ConsigneeAddress=$PoDataRow['ConsigneeAddress'];	
			// }
			
			// else if($ConsigneeID!=$BuyerID){
			// $ConsigneeName=$PoDataRow['BuyerName'];
			// $ConsigneeAddress=$PoDataRow['BuyerAddress'];	
			// }
			
			// ELSE{
				$ConsigneeName=$PoDataRow['ConsigneeName'];
			$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
			
			// }
			
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
  $w1='30%';
$w2='70%';
 
	 $content .= '<table border="0" cellspacing="0" cellpadding="0">  
				
				<tr> <br> <br> <br> 
           		<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">Shipping Bill No</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">:  '.$SBNoDate.'</font></th>				
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">1. Name of the Exporter</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">:  '.$ExporterName.'</font></th>				
           </tr>
		   
		  

		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">2. (a) IEC No </font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$ExporterIeCode.'</font></th>				
           </tr> 
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">(b) Branch Code</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'"><b>: '.$ExpBranchCode.'</b></font></th>				
           </tr>

		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">(c) BIN (PAN based business identification number of the Exporter):</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'"><b>: '.$ExpPanNumber.'</b></font></th>				
           </tr> 
           <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">(d) GSTIN</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'"><b>: '.$ExporterGstNo.'</b></font></th>				
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">3. Factory / Place of stuffing (Full address) </font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$ProcessorName.'</font><br><font size="7">'.nl2br($ProcessorAddress).'</font></th>				
           </tr>
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">4. Date of examination / stuffing</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$SciName.'</font></th>				
           </tr>
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">5. Time of stuffing Starting time</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$PackStyle.'</font></th>				
           </tr> 
		   
		  
		    <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">'.$Whitspace.'Completion time </font> </th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: </font></th>				
           </tr>
		    <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">'.$Whitspace.'Time taken for stuffing </font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: </font></th>				
           </tr>
		   
		  
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">6. Description of cargo with quantity </font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$TOTAL_MCS.' CARTONS OF FROZEN SHRIMPS</font></th>				
           </tr>
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">7. Country of final destination</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.strtoupper($FinalDst).''.strtoupper($CountryCode).', '.
				strtoupper($DestinationCountry).'</font></th>				
           </tr> 
		   
		  <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">8. Name & Designation of the Authorized Signatory</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: <b>'.$RowUserDetails['UserName'].' - '.$RowUserDetails['Designation'].'</b></font></th>				
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">9. Particulars of Export Invoice</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'"></font></th>				
           </tr><tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">(a) GST Invoice</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$InvNO.'</font></th>				
           </tr><tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">(b) Export document No</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$InvNO.'</font></th>				
           </tr><tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">(c) Total No. of packages </font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$TOTAL_MCS.' CARTONS</font></th>				
           </tr>
		   <tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">(d) Name & address of the consignee abroad</font></th>
                <th width="'.$w2.'" align="left"><font size="'.$FontSize.'">: '.$ConsigneeName.',<br>'.nl2br($ConsigneeAddress).'</font></th>				
           </tr>
		   <tr>  
				<th width="70%" align="left"><font size="'.$FontSize.'">10. Is the description of the goods, the quantity and their value as per particulars furnished in the 
 Export Document / GST invoice </font></th>
                <th width="30%" align="left"><font size="'.$FontSize.'">: '.$RowSampling['SampleDrawnBy'].'</font></th>				
           </tr>
		   
		<tr>  
				<th width="'.$w1.'" align="left"><font size="'.$FontSize.'">11. Container particulars<br></font></th>
           </tr>   
		   
		</table>
				</th>
				
				
				<th width="100%"><br>
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="CENTER"  ><font size="7" ><b> CONTAINER NO.</b>
				</font></th>
				<th  align="CENTER" ><font size="7"><b> SIZE</b>
				</font></th>
				<th  align="CENTER" ><font size="7"><b>SEAL NO`S</b>
				</font></th>
				
				<th  align="CENTER" ><font size="7"><b>NO. OF PACKAGES STUFFED IN 
CONTAINER</b>
				</font></th>
				</tr>
				
				<tr> 
				
				<th  align="CENTER"  ><font size="'.$FontSize.'" >'.$ContainerNumber.'
				</font></th>
				<th  align="CENTER" ><font size="'.$FontSize.'">40 FCL</font></th>
				<th  align="CENTER" ><font size="'.$FontSize.'">'.$SealNo.'</font></th>
				
				<th  align="CENTER" ><font size="'.$FontSize.'">'.$TOTAL_MCS.'  CARTONS OF FROZEN SHRIMPS</font></th>
				
				</tr><tr> 
				
				<th  align="CENTER"  ><font size="7" >
				</font></th>
				<th  align="CENTER" ><font size="7"></font></th>
				<th  align="CENTER" ><font size="'.$FontSize.'">'.$PackSealNo.'</font></th>
				
				<th  align="CENTER" ><font size="7"></font></th>
				
				</tr>';
			
			
				
			$content .= '	
				
				
				</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
				<th width="90%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 	<br> 	<br> 	<br>			
           		<th  align="left"  width="100%"><font size="9">12. Starting time (moving the container to CFS/ICD/Port): …………'.$PortOfLoading.' 
I have examined the goods and the same are found to be as per the declaration. The goods are stuffed in the container and
the container was sealed with “RFID tamper proof one-time-bolt seal” of specified standard. The seal number is
<U>'.$PackSealNo.'</U>The IEC (Importer Exporter Code), Shipping Bill Number, Shipping Bill Date: e-seal number, Date of
sealing, Time of sealing, Destination Customs Station for export, Container Number, Trailer-Truck Number is tagged 
to the seal. I undertake full responsibility for any difference in description, quality or quantity of the goods.</font></th>
                
                </tr>
				
				</table>
				</th>
				</tr>
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           	<br>	<th  align="Right"  width="100%"><font size="8">'.$AuthSign.'<br><br><br><br><b>'.$RowUserDetails['UserName'].'</b> <br> '.$RowUserDetails['Designation'].'<br>AUTHORISED SIGNATORY OF THE EXPORTER</font></th>
                
                </tr>
				
				
				
				</table>
				</th>
				</tr>
				
				';
				
				
			$content.='</table>';
			  return $content;
	
	}


?>
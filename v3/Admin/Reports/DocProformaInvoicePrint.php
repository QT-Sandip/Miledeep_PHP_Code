<?php
  $PoID=$_GET['id'];

//ini_set( 'display_errors', 1 );
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");

/** Folder Includes **/


 
 $ID=$_GET['id'];
 $Type=$_GET['Type'];

 
$SqlStatus="SELECT * FROM $TblScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();

 $SqlScrmPoData="SELECT *,SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
  left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country

 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];


 $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
$StmtLogin=$iconn->query($SqlLogin); 
$RowLogin = $StmtLogin->fetch_assoc();
$SignURL=$RowLogin['SignURL'];


$ExporterData=getExporterDetails($ExpID);
$ProcessorData=getExporterDetails($ProcessorID);

//print_r($ProcessorData);

$PaymentTerms=$PoDataRow['PaymentTerms'];
$DeliveryTerms=$PoDataRow['DeliveryTerms'];
$BankID=$PoDataRow['BankID'];
  $Status=$PoStatus['Status']; 


 

//require_once('../tcpdf/tcpdf.php'); 
	

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
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
	
	
	
	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
/***uma 28-07-2022**/
  $CurrentMonth = date('n',strtotime($PoDataRow['InvDate']));   
if($PoDataRow['ExporterID']==5){		
if($CurrentMonth<=3){
	
 
 $NxtYear = date('y',strtotime($PoDataRow['InvDate']));  	
 $CurrentYear = date('y',strtotime($PoDataRow['InvDate']))-1;  	
	
} 
else{ 

 $CurrentYear = date('y',strtotime($PoDataRow['InvDate']));  	 
$NxtYear = date('y',strtotime($PoDataRow['InvDate']))+1;  	
 
}
		}
		else{		
if($CurrentMonth<=3){
	
 	
 
 $NxtYear = date('y',strtotime($PoDataRow['InvDate']));  	
 $CurrentYear = date('Y',strtotime($PoDataRow['InvDate']))-1;  	

	
} 
else{ 
 
$CurrentYear = date('Y',strtotime($PoDataRow['InvDate']));  	 
$NxtYear = date('y',strtotime($PoDataRow['InvDate']))+1;  	
  
}
		}
/***uma 28-07-2022**/

$file_name=$ExporterData['InvShortCode'].'-PI-'.$PoDataRow['InvNo'].'-'.$CurrentYear.'-'.$NxtYear.'.pdf';
$file_name = str_replace('/','', $file_name );;
$file_name=$Fname.$file_name;
 
//sme/pi/233/2022-23


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
		
  $SQL_PO_GRADE_DATA="SELECT * FROM `$TblScrmPoGradeData` where InvId='$ID'  ";
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

				 $SHIPMENT_DATE=date('d-m-Y',strtotime($PoDataRow['ShipByDate']));

	
	


	
	
  
global $PoDataRow ,$ExpID;
global $ExporterData ;
global $ProcessorData ;
global $TblAdminBankDetails ;
global $BankID ;
global $TblScrmPaymentTerms ;
global $TblAdminExporterDetails ;
global $SignURL,$ExpID ;

	 $CountryName=$PoDataRow['name']; 

	
	//Exporter /Shipper Details
	
			//$ExporterID=$ExporterData['ExpID'];
			$ExporterName=$ExporterData['ExpName'];
			$ExporterAddress=$ExporterData['ExpAddress'];
			$ExporterUsfdaNo=$ExporterData['ExpUsfdaNo'];
			$ExporterApprovalNo=$ExporterData['ExpApprovalNo'];
			$ExporterIeCustomsNo=$ExporterData['ExpIeCustomsNo']; 
			$ExporterGstNo=$ExporterData['ExpGstNo']; 
			$ExporterIeCode=$ExporterData['ExpIeCode']; 
			$AuthSign=$ExporterData['AuthSign']; 
			$ExporterRecAdd=$ExporterData['ImporterRecordAddress']; 
			$InvShortCode=$ExporterData['InvShortCode']; 
	//Processor/Packer

			$ProcessorID=$PoDataRow['ProcessorID'];	
			$ProcessorNamee=$ProcessorData['ExpName'];
			$ProcessorIeCustomsNo=$ProcessorData['ExpIeCustomsNo'];
			$DestinationCountryId=$PoDataRow['DestinationCountry'];
			
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
// $InvDate=date('m',strtotime($PoDataRow['InvDate'])); 

 // $CurrentMonth = strftime("%m");  
   $CurrentMonth = date('n',strtotime($PoDataRow['InvDate']));   
if($PoDataRow['ExporterID']==5){		
if($CurrentMonth<=3){
	
 /*$NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%y")-1; */
 $NxtYear = date('y',strtotime($PoDataRow['InvDate']));  	
 $CurrentYear = date('y',strtotime($PoDataRow['InvDate']))-1;  	
	
} 
else{ 
/* $CurrentYear = strftime("%Y");  	
 $NxtYear = strftime("%y")+1; */
 $CurrentYear = date('y',strtotime($PoDataRow['InvDate']));  	 
$NxtYear = date('y',strtotime($PoDataRow['InvDate']))+1;  	
 
}
		}
		else{		
if($CurrentMonth<=3){
	
 /*$NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%Y")-1;  */	
 
 $NxtYear = date('y',strtotime($PoDataRow['InvDate']));  	
 $CurrentYear = date('Y',strtotime($PoDataRow['InvDate']))-1;  	

	
} 
else{ 
 /*$CurrentYear = strftime("%Y");  	
 $NxtYear = strftime("%y")+1; */ 
$CurrentYear = date('Y',strtotime($PoDataRow['InvDate']));  	 
$NxtYear = date('y',strtotime($PoDataRow['InvDate']))+1;  	
  
}
		}
		
$ProcessorName=$ProcessorData['ExpName'];
			//$ProcessorAddress=$ProcessorData['ExpAddress'];
			$ProcessorAddress=$ProcessorData['PackerAddress'];
			$ProcessorUsfdaNo=$ProcessorData['ExpUsfdaNo'];
			$ProcessorApprovalNo=$ProcessorData['ExpApprovalNo'];
			$ProcessorFEI=$ProcessorData['ExpFeiNo'];
			
			//$InvNO='SME/PI/'.$PoDataRow['InvNo'].'/2021-22';

			
			/*if($ExpID==5){
							$InvNO=$InvShortCode.$PoDataRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear;
			}*/
			//else{
							$InvNO=$InvShortCode.'PI/'.$PoDataRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear;

			//}
			$InvDate=getFormatedDate($PoDataRow['InvDate']);
			
			$PoNo=$PoDataRow['PoNo'];
			$PoDate=getFormatedDate($PoDataRow['PoDate']);
			$PoDate= "& Dated :".$PoDate;
			IF($PoNo ==''){
				
			$PoDate=' ';	
			}
			
			
			$ConsigneeName=$PoDataRow['ConsigneeName'];
			$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
			/*09/08/2022*/
			$BuyerName=$PoDataRow['BuyerName'];
			$BuyerAddress=$PoDataRow['BuyerAddress'];
			$ConsigneeID=$PoDataRow['ConsigneeID'];
			$BuyerID=$PoDataRow['BuyerID'];
            /*09/08/2022*/
			
			$PortOfDischarge=$PoDataRow['Name'];
			$PortofDischargeCountry=$PoDataRow['PortofDischargeCountry'];
			$FinalDestination=$PoDataRow['FinalDestination'];
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
					/*$content .= '/*IE CODE  : '.$ExporterIeCode.'<BR>
				GST NO  : '.$ExporterGstNo.'</font></th></tr>';*/
				$content .= '</font></th></tr>';}
				else{
			$content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>
				IE CODE  : '.$ExporterIeCode.'<BR>
				GST NO  : '.$ExporterGstNo.'</font></th>
				</tr>';}}
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
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				
				
				</font></th>
				</tr>';	
				}
				
			$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">ANY PORT, INDIA</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>'.$VESSEL_FREIGHT_NO.'  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">ANY PORT, INDIA</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">'.strtoupper($PortOfDischarge).' '.strtoupper($PoDataRow['CountryCode']).', '.$PortofDischargeCountry.'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">'.$FinalDestination.'</font></th>
				
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
				<u><b> Packer</u> :</b><br> '.$ProcessorName.''.$ProcessorName1.'<br> '.$ProcessorAddress.' ';
		if($DestinationCountryId!='26'){
			$content .= '
				
				<br> USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo.'<BR> FEI # :  '.$ProcessorFEI.'';
		}
		$content .= '</font></th>
			</tr>';

		}
			else{
				$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$ProcessorApprovalNo.'</b>
				<br>
				<u><b> Packer</u> :</b><br> '.$ProcessorName.'<br> '.$ProcessorAddress.'  
				';
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
				<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN # '.$ProcessorIeCustomsNo.'
				<br> '.$ExporterRecAdd.' </font></th>
				</tr>';}
				


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
				</b><br><BR> ' .$PaymentTerms .' <br>
				 SHIPMENT TERM : '.$ShippingTerms .' '.strtoupper($PortOfDischarge).', '.
				strtoupper($DestinationCountry).' ';
				if($DestinationCountryId!=26){
				
				
				$content .= '('.$DeliveryTerms.')<br>';
				}
				
				$content .= '</font> </th>
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
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><b align="center">1 X 40 FCL</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><b align="center">'.$SEAL_NO.'</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in '.$QtyUnit.' </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>USD/'.$QtyUnit .' '.$PoDataRow['ShippingTerms'] .' <br><b>'.strtoupper($PortOfDischarge).', '.
				strtoupper($DestinationCountry).'</b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in U.S.D '.$PoDataRow['ShippingTerms'] .'<br><b> '.strtoupper($PortOfDischarge).', '.
				strtoupper($DestinationCountry).'</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>
				
				';
				
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='<tr  >
				
			<table border="0" cellspacing="0" cellpadding="1">
			
			'
			;
			

			  $SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP FROM `$TblScrmPoGradeData` 
			left join `$TblAdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
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
  
  $Description=$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].'<br>';
  if($DestinationCountryId!=26){
			$Description.='PACKING : '.$VarietyMcsRow['PackingStyle'].' NET WEIGHT PER CARTON.'.$Whitspace.' 

			<B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}
  else{
		$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';
  }	
			
			
				
				$content.='
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$Description.'</font></td>
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
				  $SqlVariety="SELECT * FROM `$TblScrmPoGradeData` as PGD
			left join $TblAdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
		and	PackingStyleID='$PackingStyleID' order by abs(Grade)";  
		
		// AND Grade='$Grade'
			$StmtVariety=$iconn->query($SqlVariety);
				
			while($RowVariety=$StmtVariety->fetch_assoc()){ 
				
				// 26 ID China
				if( $PoDataRow['QtyUnit']=='KGS'){
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];
				$UsdValue=($Qty)*$RowVariety['Price'];				
				}else{
				$Qty=$RowVariety['Mcs']*$RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['LbsFactor'];										
				 $UsdValue=($Qty)*$RowVariety['Price'];
				}
			
			$content.='
				<tr>
				
			<!--	<td align="center" width="12.5%" ><font size="7">
				'.$RowVariety['Variety'].'</font></td> -->
				<td align="center" width="40%"  ><font size="'.$FontSize.'">
				'.$RowVariety['SKUNo'].' <b>'.$RowVariety['Grade'].'</b> '.$RowVariety['Treatment'].'</font></td>
				<!--<td align="center" width="22.5%"><font size="7">
				'.$RowVariety['PackingStyle'].'</font></td> -->
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$RowVariety['Mcs'].'</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($Qty,3).'</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$RowVariety['Price'].'</font></td>
				
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($UsdValue,2).'</font></td>
				
				</tr>';
			}


			}//UMA
			
			/*$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*TotalSlabs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*TotalSlabs*GrossWeight*LbsFactor) as GrossWeightLbs";*/
				$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
							
							/*$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight) as GrossWeightLbs";
				*/
							
			
			
			
			//echo $PoDataRow['QtyUnit'];exit;
			$SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmPoGradeData` 
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
				$Price = $RowTotal['PriceLbs'];
			}
			
			$content.='
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				'.$RowTotal['MCS'].'</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.number_format($Weight,3).'</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.number_format($Price,2).'</font></td>
				
				
				</tr>';
			
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
			$content.='<tr > '.
           		 $TOTAL.'
            
				</tr>

				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> SHIPPING TERMS   : '.$PoDataRow['ShippingTerms'].'  '.$FinalDestination.'
				<BR> TOLERANCE           : QUANTITY/AMOUNT 10% PLUS OR MINUS
				<BR> SHIPMENT DATE :  '.$SHIPMENT_DATE.' OR EARLY<BR> <BR><B><U style="background-color:grey">OUR BANKING DETAILS        : </U></B><br>
				
				<br> BENEFICIARY NAME <b style="color:white">-----------------------</b>: <b>'.$RowBank['AccntName'].'</b>
				<BR> BENEFICIARY ACCOUNT NUMBER<b style="color:white">----</b>: <b>'.$RowBank['AccountNumber'].'</b> 
				<BR> BANK NAME AND ADDRESS<b style="color:white">-------------</b>: <b>'.$RowBank['BankName'].','.$RowBank['BankAddress'].' </b>
				<BR> SWIFT CODE<b style="color:white">-----------------------------------</b>: <b>'.$RowBank['SwiftCode'].'</b>';
				
				
				$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
				
				
				
				if($QtyUnit=='KGS'){
				$content.='<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: '.number_format($NetWgtKgs, 3, '.', '').' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: '.number_format($GrossWeightKgs, 3, '.', '').' KGS</b> 
				</font></th>';	
				}else{
				$content.='<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>'.number_format($NetWgtLbs, 3, '.', '').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($NetWgtLbs*0.454),3,'.','').' KGS</b>
				<BR> GROSS WEIGHT<b style="color:white">------------------------------</b>: 
				<b>'.number_format($GrossWeightLbs,3,'.','').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($GrossWeightLbs*0.454),3,'.','').' KGS</b> 
				</font></th>';	
				}
				
               $content.='</tr>
				
						
	        
				<tr>  
           		<th  align="LEFT"  width="70%"><font size="7"> AMOUNT CHARGABLE IN US.$: <b>'.$BILL_WORD.'</b><BR> <BR> 
				<BR> PREPARED BY:  '.$PreparedBy.'<BR><BR><BR><B><U>DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>    
				
				 
				
                <th  align="center"  width="30%"><font size="7"> Signature:<br><br>
				<B> '.$AuthSign.',</B>
				 
				<img src="'.$SignURL.'" 
				 width="100" height="30"style="width: 100" align="center"     valign="middle"/>
								
				
				<p STYLE="text-align:center" ><b> Authorised Signature </b> </p></font></th>
</tr>
				
				
				</table>'; 
	
	return $content;
	
	}


?>
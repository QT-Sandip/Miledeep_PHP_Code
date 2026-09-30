<?php
//ini_set('display_errors', 1); 
session_start();
$IsHeader=0;
$IsFooter=0;
$QueryParms=$_GET['Q'];
$CID=$_GET['id'];
  

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");

$PoID=$_GET['PoID'];
$ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];



$sql="SELECT * FROM $TblQcSamplingLettersEIA  WHERE `ID`='$CID'";
$stmt_REP=$iconn->query($sql);
$RowIhRep=$stmt_REP->fetch_array();


$sql_Variety="SELECT * FROM $TblQcPoVarityList  WHERE `InvID`='$PoID'";
$stmt_Variety=$iconn->query($sql_Variety);
$rowVariety=$stmt_Variety->fetch_array();



$TYPE=$rowVariety['TypeProduct'];


 $sql_invoice_data="SELECT * FROM $TblScrmPoData  WHERE `PoID`='$PoID'";
$STMT_INVOICE_DATA=$iconn->query($sql_invoice_data);
$RowPo=$STMT_INVOICE_DATA->fetch_array();

$IMPORTER=$RowPo['ConsigneeID'];

$SCIENTIFIC_NAME=$RowPo['SciName'];


 $SQL_VARITY_LIST="SELECT * FROM $TblQcPoVarityList WHERE InvID='$PoID' AND TypeProduct='$TYPE'";
$STMT_VARITY_LIST=$iconn->query($SQL_VARITY_LIST);
 

class MYPDF extends TCPDF {
	}

	$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
	$pdf->SetCreator(PDF_CREATOR);    
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica'); 
	$pdf->SetHeaderMargin(145);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();


$logo = "images/jme_logo.jpg";
$footer = "images/jme_footer.jpg";
$eia_logo = "images/eia_logo-min.jpg";
$imgPath = "images/eia_logo-min.jpg";

$FONT_ARI='';
$HEIGHT=8;
$WIDHT_1='';

$header=11;
$sub_header=8.4;
$text=6;

	
        $pdf->Cell(2,3, $pdf->Image($eia_logo, $pdf->GetX(), $pdf->GetY(), 20,20) ,0,0,'C',0);
		$pdf->SetFont($FONT_ARI,'B',$header);
        $pdf->Cell(0,5,'EXPORT INSPECTION AGENCY-CHENNAI',0,1,'C');
		$pdf->SetFont($FONT_ARI,'B',$header);
        $pdf->Cell(0,5,'(MINISTRY OF COMMERCE AND INDUSTRY,GOVT.OF INDIA)',0,1,'C');
		$pdf->SetFont($FONT_ARI,'BU',$header);
		
		

$TEST_DATE=strtotime($row['DATE_TIME_OF_REC_SAMPLE']);
$DATE_TIME_OF_REC_SAMPLE=date("d-m-Y", $TEST_DATE);

        $pdf->Cell(0,5,'Sample Covering Note & Registration Details(Fish & Fishery Products)',0,1,'C');
		$pdf->SetFont($FONT_ARI,'BU',$sub_header);
		$pdf->Cell(30,5,'',0,0,'C');
		$pdf->Cell(120,5,'REFERENCE NUMBER : EIA/BVM/'.$row['REFERENCE_NUMBER'],0,0,'C');
		$pdf->SetFont($FONT_ARI,'B',$sub_header);
		$pdf->Cell(0,5,'DATE :'.$DATE_TIME_OF_REC_SAMPLE,0,0,'R');
		$pdf->Ln();
		$pdf->Ln();
		  
$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$sub_header);




$W1=10;
$W2=60;
$W3=120;
$H1=5;

$w1='34%';
$w3='6%';
$w2='60%';
$Sno=0;
$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">

			<tr>  
           		<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
           		<th width="'.$w1.'" align="left"><B>Name & Address of the processor</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['ReportNo'].' & '.getFormatedDate($RowIhRep['ReportDate']).'</B></th>				
           </tr>
		   <tr>  
		   <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
           		<th width="'.$w1.'" align="left"><B>Name & Address of the exporter</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['FarmerName'].','.$RowIhRep['AddressContinued'].'</B></th>				
           </tr>
		   
		   <tr>  
		   <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Approval No</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['Tracebility'].'/'.$row['REG_AUTH'].'</B></th>				
                				
           </tr>
		   
		  

		   <tr>  
		        <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Consignee details</B></th>
               <th width="'.$w2.'" align="left"><B>'.$RowIhRep['Tracebility'].' </B></th>			
           </tr> 
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Name of the country where the consignment is exported</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['MiddleMan'].' </B></th>				
           </tr> 
		   		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>TYPE & NATURE OF PRODUCT</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['SciName'].'</B></th>				
           </tr> 
		   
		   		   		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Production codes</B></th>
                <th width="'.$w2.'" align="left"><B>'.$EXPORTOR.'&'.$EXPORTOR_ADD.'</B></th>				
           </tr> 
		   
		   
		   	 <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Species Name</B></th>
                <th width="'.$w2.'" align="left"><B>'.round(($RowIhRep['FinishedWeight']/$RowIhRep['Yield'])*100).' Kgs</B></th>				
           </tr> 
		   
		   	  <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Place, Date and Time of sampling</B></th>
                <th width="'.$w2.'" align="left"><B>'.$PROCESSOR_APP_NUM.'</B></th>				
           </tr> 
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Brand</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['LabRep'].'</B></th>				
           </tr> 
		   
		   
		   		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Date and time of sample collection </B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDate($RowIhRep['SamplingDate']).'& '.$RowIhRep['SamplingTime'].'</B></th>				
           </tr> 
		   
		    	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Invoice /Proforma invoice No./P.O No</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['SampleID'].'</B></th>				
           </tr> 
		   
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Total number of cases in consignment</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDate($RowIhRep['AnalysisDate']).'</B></th>				
           </tr> 
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Type of packing & grades</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDate($RowIhRep['DateIssuence']).'</B></th>				
           </tr> 
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>No. of cartons selected for sampling ( pl. attach the /
separate worksheet for selection of code-wise
/ grade-wise master cartons)</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 	
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Whether the CDPR & Despatch register (for these production codes)are verified, stamped & signed?</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 

		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Cold storage temp</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr>
		   
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Product core temp</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr>
		   
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Farm/ fishing vessel registration number</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Quantity and No. of sample for testing</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Quantity and No. of sample for reference</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Sampled carton seal No. </B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Sample seal No</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Sample Box seal No.</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Details of Testing Fee paid</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Sampled by</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Signature of EIA official:</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Signature of the representative of the establishment:</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>To the Laboratory, EIA-CHENNAI (LAB)</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Date & Time of receipt of sample, </B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Signature</B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDateTime($RowIhRep['HarvestingDate']).'</B></th>				
           </tr> 
		   
</table> ';


$pdf->writeHTML($FARMER_NAME);

$pdf->Ln();
 //$pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);
     ob_clean();          
if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowPo['PoNo'].'.pdf';
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName, 'I');
}else{
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName;
	$pdf->Output($FileName, $FileType);

}
?>
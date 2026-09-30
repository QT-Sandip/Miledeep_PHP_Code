<?php
//ini_set('display_errors', 1); 

session_start();
$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q']; 



include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

$PoID=$_GET['PoID']; 
 $ReportName=$_GET['ReportName']; 
 $TYPE=$_GET['TypeProduct'];  
$FileType=$_GET['Type'];
$SAMP_ID=$_GET['RID'];

 $SqlSamplingLtr="SELECT * FROM $TblQcSamplingLettersExt as SL
left join $TblScrmPoData as PD on PD.PoID=SL.InvID
left join $TblQcLabTechnologist as QLT on QLT.LbuName=SL.VerifiedBy
left join $TblScrmConsigneeDetails as CD on PD.ConsigneeID=CD.ConID WHERE SL.SMPLID='$SAMP_ID'";
$StmtSamplingLtr=$iconn->query($SqlSamplingLtr);
$RowSampling=$StmtSamplingLtr->fetch_array();

 $Type=$RowSampling['TypeProduct'];


if($Type==''){
$Qry=""; 	
}else{
	$Qry="and TypeProduct='$Type'";
}

 $TYPE=$row['TYPE_N_NATURE'];

$sql="SELECT * FROM QcEiaAnalysisRequired WHERE `ReportId`='$SAMP_ID'";
$stmt_HEADER=$iconn->query($sql);


/*

 echo  $SqlOtherData="SELECT Brand,ShortName,Tracebility,Grades,McsTotal,DayCode,VerifiedBy,PackingStyle,FullName FROM `QcCodeList` AS CL 
left join $TblAdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN $TblQcPoVarityList as VL on CL.VarietyID=VL.VarietyID
left join $TblQcTracebilityList as TL on TL.TraceID=CL.TracebilityID WHERE CL.InvID='$PoID' $Qry";*/

 $SqlOtherData="SELECT SPGD.Brand AS BrandNm,VL.Species,ShortName,Tracebility,
 Grades,McsTotal,DayCode,VerifiedBy,PL.PackingStyle,FullName,PrintStatus FROM `QcCodeList` AS CL 
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
LEFT join ScrmPoGradeData as SPGD on SPGD.ID=CL.PoGradeID
left JOIN ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
-- LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
LEFT JOIN (
	SELECT PrintStatus,BrandName FROM ScrmBrandsMaster WHERE `Status` = 1 GROUP BY BrandName
) SBM ON SBM.BrandName=CL.Brand
left join QcTracebilityList as TL on TL.TraceID=CL.TracebilityID WHERE CL.InvID='$PoID' $Qry";
$StmtOtherData=$iconn->query($SqlOtherData);
$McsTotall=0;

//echo '<pre>'.$SqlOtherData; exit;
while($RowOtherData=$StmtOtherData->fetch_array()){
	
		$Brand = trim($RowOtherData['BrandNm']); 
	 $PrintStatus = trim($RowOtherData['PrintStatus']); 

	if($Brand!='' && $PrintStatus!=0){
		$AryBrand[] = $Brand;
		}
	$Species=$RowOtherData['Species'];
	$AryMcsTotal[]=$RowOtherData['McsTotal'];
	$AryDayCode[]=$RowOtherData['DayCode'];
	$AryVerifiedBy[]=$RowOtherData['VerifiedBy'];
	$AryPackStyle[]=$RowOtherData['PackingStyle'];
	$AryShortName[]=$RowOtherData['ShortName'];
	$AryFullName[]=$RowOtherData['FullName'];
	$AryGrades[]=$RowOtherData['Grades'];
	$AryTracebility[]=$RowOtherData['Tracebility'];
	$McsTotall+=$RowOtherData['McsTotal'];
}

$Tracebility=implode(",",array_unique($AryTracebility));
$Brand=implode(",",array_unique($AryBrand));
$Grades=implode(",",array_unique($AryGrades));
$Grades=implode(",",array_unique(explode(",",$Grades)));
$McsTotal=implode(",",array_unique($AryMcsTotal));
$DayCodes=implode(",",array_unique($AryDayCode));
$PackStyle=implode(",",array_unique($AryPackStyle));
$VerifiedBy=implode(",",array_unique($AryVerifiedBy));
$ShortName=implode(",",array_unique($AryShortName));
$FullName=implode("<br>",array_unique($AryFullName));
$PoNo=$RowSampling['PoNo'];
$PoDate=getFormatedDate($RowSampling['PoDate']);
$SciName=$RowSampling['SciName'];
$Importer=$RowSampling['ConsigneeName'].','.$RowSampling['ConsigneeAddress'];

//echo $Brand; exit;
IF($Brand==''){
	$Brand='--'; 
 }


$HEIGHT=8;
$WIDHT_1='Arial';

$header=12;
$sub_header=8;
$text=6;


$TEST_TMSP=strtotime($row['DATE']);
$TEST_DATE=date("d-m-Y", $TEST_TMSP);

 
 

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0,5,'SAMPLE REGISTRATION FORM',0,1,'C');
		
$pdf->Cell(120,5,'Ref :'.$RowSampling['SampleNumber'],0,'C');
$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell(0,5,'DATE :'.getFormatedDate($RowSampling['Date']),0,0,'R');
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$TestingLab=$RowSampling['TestingLab'];
		  
$SqlEiaLabs="SELECT * FROM `$QcEiaLabNames` WHERE LabName='$TestingLab'"; 
$StmtEiaLabs=$iconn->query($SqlEiaLabs);
$RowLab=$StmtEiaLabs->fetch_array();

$pdf->SetFont($FONT_ARI,'',$sub_header);
$pdf->Cell(0 ,5,'To',0,1,'L');
$pdf->Cell(0 ,5,'THE LAB INCHARGE,',0,1,'L');
$pdf->Cell(130 ,5,$RowSampling['TestingLab'].',',0,1,'L');
$pdf->Cell(0 ,5,$RowLab['Address'].'.',0,1,'L');
$pdf->Cell(0 ,5,'Sub : ANALYSIS OF SAMPLES-Reg. ',0,1,'L');
$pdf->Cell(0 ,5,'Dear Sir,',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',9);
$pdf->Cell(0 ,5,'We are sending here with the samples for the mentioned parameters testing to be required details are given below',0,1,'L');
$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->SetFont($FONT_ARI,'',$sub_header);

$w1='30%';
$w2='70%';
$SqlProductList = "SELECT Species,TypeProduct,QcDescription,Variety FROM `ScrmPoGradeData` as SPGD 
left join ScrmPIVarietyList SPVL on SPVL.VarietyID=SPGD.VarietyID 
 WHERE InvId='$PoID' $Qry"; 
$StmtProductist = $iconn->query($SqlProductList);
//ECHO $SqlProductList; EXIT;
while($RowProductName=$StmtProductist->fetch_assoc()){
	
	$QcDescription=$RowProductName['QcDescription'] ? $RowProductName['QcDescription'] : $RowProductName['Variety'];
	$AryProductName[]=trim($QcDescription);
}

 $AryProductName=array_unique($AryProductName); 
 $ProductNameList=implode(" ,",array_unique($AryProductName)); 


$SpeciesName=getSpeciesDetails($Species,$iconn)['SpeciesName'];

$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">

			<tr>  
           		<th width="'.$w1.'" align="left">EIC APPROVAL NO</th>
                <th width="'.$w2.'" align="left"><b>'.$PROCESSOR_APP_NUM.'</b></th>				
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" align="left"> EXPORTER</th>
                <th width="'.$w2.'" align="left">'.$EXPORTOR.'.'.$EXPORTOR_ADD.'</th>				
           </tr>
		   
		  

		   <tr>  
				<th width="'.$w1.'" align="left">LOCATION OF SAMPLING(PROCESSOR & PACKER)</th>
                <th width="'.$w2.'" align="left">'.$PROCESSOR.'.'.$PROCESSOR_ADD.'</th>				
           </tr> 
		   <tr>  
				<th width="'.$w1.'" align="left">PURCHASE ORDER NO</th>
                <th width="'.$w2.'" align="left"><b>'.$PoNo.'</b></th>				
           </tr>

		   <tr>  
				<th width="'.$w1.'" align="left">CONSIGNMENT INTENDED FOR EXPORT TO</th>
                <th width="'.$w2.'" align="left"><b>'.$Importer.'</b></th>				
           </tr> 
           <tr>  
				<th width="'.$w1.'" align="left">BRAND</th>
                <th width="'.$w2.'" align="left"><b>'.$Brand.'</b></th>				
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" align="left">TYPE & NATURE OF PRODUCT </th>
                <th width="'.$w2.'" align="left">'.$ProductNameList.'</th>				
           </tr>
		   <tr>  
				<th width="'.$w1.'" align="left">SPECIES</th>
                <th width="'.$w2.'" align="left">'.$SpeciesName.'</th>				
           </tr>
		   <tr>  
				<th width="'.$w1.'" align="left">TYPE OF PACKING</th>
                <th width="'.$w2.'" align="left">'.$PackStyle.'</th>				
           </tr> 
		   
		  
		    <tr>  
				<th width="'.$w1.'" align="left">CODES COVERED IN THE CONSIGNMENT </th>
                <th width="'.$w2.'" align="left">'.$DayCodes.'</th>				
           </tr>
		    <tr>  
				<th width="'.$w1.'" align="left">GRADE COVERED IN THE CONSIGNMENT </th>
                <th width="'.$w2.'" align="left">'.$Grades.'</th>				
           </tr>
		   
		  
		   <tr>  
				<th width="'.$w1.'" align="left">NO OF MASTER CARTONS </th>
                <th width="'.$w2.'" align="left">'.$McsTotall.'</th>				
           </tr>
		   <tr>  
				<th width="'.$w1.'" align="left">REGISTER NO OF AQUACULTURE POND/FARM</th>
                <th width="'.$w2.'" align="left">'.$Tracebility.'</th>				
           </tr> 
		   
		<!--   <tr>  
				<th width="'.$w1.'" align="left">SAMPLE DRAWN BY</th>
                <th width="'.$w2.'" align="left">'.$RowSampling['SampleDrawnBy'].'</th>				
           </tr>-->
		   
		   
		   
		   </table>  
		';


$pdf->writeHTML($FARMER_NAME);


$pdf->SetFont($FONT_ARI,'B',$header);

//$pdf->Ln();

$pdf->Cell($W2 ,$H1,'SAMPLE  '.$RowSampling['SampleDrawnBy'],0,1,'L');
//$pdf->Cell($W2 ,$H1,'SAMPLE  ',0,1,'L');

//$pdf->Ln();

$pdf->SetFont($FONT_ARI,'',$header);


$W1=65;
$W2=125;

$TEST_TYPE_COUNT=0;
$TEST_COUNT=1;
$TEST_NAME='hlo';

$pdf->SetFont($FONT_ARI,'',$sub_header);
$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">';

while($roww=$stmt_HEADER->fetch_array()){
	
		
	if($TEST_TYPE_COUNT==0){
		$TEST_TYPE = $roww['Type'];	
		
		$FARMER_NAME .= '<tr>  
				<th width="'.$w1.'" align="left">'.$roww['Type'].'</th>
                <th width="'.$w2.'" align="left">'.$TEST_COUNT .'.'.$roww['TestName'].'</th>				
           </tr>';
			
	}else if($TEST_TYPE == $roww['Type']){
			$TEST_COUNT++;
		
			$FARMER_NAME .= '<tr>  
				<th width="'.$w1.'" align="left"></th>
                <th width="'.$w2.'" align="left">'.$TEST_COUNT .'.'.$roww['TestName'].'</th>				
           </tr>';
			
	}else{
			$TEST_COUNT=1	;	
			$TEST_TYPE = $roww['Type'];	
		
			
			$FARMER_NAME .= '<tr>  
				<th width="'.$w1.'" align="left">'.$TEST_TYPE.'</th>
                <th width="'.$w2.'" align="left">'.$TEST_COUNT .'.'.$roww['TestName'].'</th>				
           </tr>';
	
}
$TEST_TYPE_COUNT++;


}

$FARMER_NAME .= '</table>';
$VerifiedBy=$RowSampling['VerifiedBy'];
$pdf->writeHTML($FARMER_NAME);

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell(190 ,$H1,'','T',1,'L');
//$pdf->Ln();
$pdf->Cell(190 ,$H1,'FOR '. $PROCESSOR ,0,1,'L');
//$pdf->Ln();
$pdf->Cell(95,5,''.$VerifiedBy,0,1,'L',0);
$pdf->Cell(95,5,''.$RowSampling['LbuDesignation'],0,1,'L',0);
//$pdf->Cell(95,5,'Qc Technologist',0,1,'L',0);
$pdf->Ln();
        
ob_end_clean(); 
       
if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowSampling['PoNo'].'.pdf';
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName, 'I');
}else{
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName;
	$pdf->Output($FileName, $FileType);

}?>
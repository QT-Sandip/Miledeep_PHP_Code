<?php
//ini_set('display_errors', 1);


 include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

$ID=$_GET['RID'];
$PoID=$_GET['PoID'];
  $SqlSalmonella="SELECT *,SR.VerifiedBy as VerifiedBy FROM $TblQcSalmonellaReport  as SR
left join $TblScrmPoData as PD on PD.PoID=SR.InvID
LEFT JOIN $TblQcCoveringLetter as cv on cv.InvID=PD.PoID

left join $TblScrmConsigneeDetails as CD on PD.ConsigneeID=CD.ConID WHERE SR.ID='$ID'"; 
$StmtSalmonella=$iconn->query($SqlSalmonella);
$RowSampling=$StmtSalmonella->fetch_array();

$SqlQty="SELECT round(sum(McsTotal*TotalSlabs*SlabWeight),3) as NetWeightLbs,round(sum(McsTotal*TotalSlabs*SlabWeight*MFactor),3) as NetWeightKgs FROM `QcCodeList` as Qcl
left JOIN AdminPackingStyles as Aps on Aps.PCKID=Qcl.PackingStyleID
WHERE InvID=$PoID";
$StmtQty=$iconn->query($SqlQty);
$RowQty=$StmtQty->fetch_array();
$QUANTITY_IN_KGS=$RowQty['NetWeightLbs'].'LBS/'.getPriceDetails($PoID,'KGS',$iconn)['NetWeightKGS'] .'Kgs';

 $SqlOtherData="SELECT SPGD.Brand,ShortName,SPGD.Variety,SPGD.QcDescription,Tracebility,Grades,McsTotal,DayCode,VerifiedBy, PL.PackingStyle FROM `QcCodeList` AS CL 
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN ScrmPoGradeData as SPGD on SPGD.ID =CL.PoGradeID
left JOIN ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
left join QcTracebilityList as TL on TL.TraceID=CL.TracebilityID WHERE CL.InvID='$PoID' order by DayCode Asc"; 
$StmtOtherData=$iconn->query($SqlOtherData);
//echo $SqlOtherData; exit;
while($RowOtherData=$StmtOtherData->fetch_array()){
	$AryBrand[]=$RowOtherData['Brand'];
	$AryMcsTotal[]=$RowOtherData['McsTotal'];
	$AryDayCode[]=$RowOtherData['DayCode'];
	$AryDayCodeTracabilty[]=$RowOtherData['DayCode'] .' & ' .$RowOtherData['Tracebility'];
	$AryVerifiedBy[]=$RowOtherData['VerifiedBy'];
	$AryPackStyle[]=$RowOtherData['PackingStyle'];
	$AryShortName[]=$RowOtherData['ShortName'];
	$FullName=$RowOtherData['QcDescription'] ? $RowOtherData['QcDescription'] : $RowOtherData['Variety'];
	$AryFullName[]=trim($FullName);
	$AryGrades[]=$RowOtherData['Grades'];
	$AryTracebility[]=$RowOtherData['Tracebility'];
	
	//ROBO;
	$SumOfmcs+=$RowOtherData['McsTotal'];
}

$DayCodeTracabilty=implode(",",array_unique($AryDayCodeTracabilty));
$Tracebility=implode(",",array_unique($AryTracebility));
$Brand=implode(",",array_unique($AryBrand));
$Grades=implode(",",array_unique($AryGrades));
$Grades=implode(",",array_unique(explode(",",$Grades)));
$McsTotal=array_sum($AryMcsTotal);
$DayCodes=implode(",",array_unique($AryDayCode));
$PackStyle=implode(",",array_unique($AryPackStyle));
$VerifiedBy=implode(",",array_unique($AryVerifiedBy));
$ShortName=implode(",",array_unique($AryShortName));
 $FullName=implode(",",array_unique($AryFullName)); 
 $LabSampleID=$RowSampling['LabSampleID']; 
 $LabSampleID=explode(' & ',$LabSampleID);
  $Sampledate=date('d-m-Y',strtotime($LabSampleID[1])); 
$PoNo=$RowSampling['PoNo'];
$TestedBy=$RowSampling['TestedBy'];
$VerifiedBy=$RowSampling['VerifiedBy'];
$PoDate=getFormatedDate($RowSampling['PoDate']);
$AnalysisDate=getFormatedDate($RowSampling['AnalysisDate']);
$CompletionDate=getFormatedDate($RowSampling['CompletionDate']);
$SciName=$RowSampling['SciName'];
$Importer=$RowSampling['ConsigneeName'].','.$RowSampling['ConsigneeAddress'];

   

$sql="SELECT * FROM $TblQcSalmonellaResult WHERE ReportId='$ID'";
$stmt=$iconn->query($sql);

/************************************************TEST *********************************************/
$w1='50%';
$w2='50%';
$header=12;
$sub_header=8;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0 ,5,'INTERNAL BACTERIOLOGICAL ANALYSIS REPORT FOR SALMONELLA',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');

$pdf->SetFont($FONT_ARI,'B',$header);
$pdf->Cell(180 ,$HEIGHT,'VARIETY :',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$header);
 $VARITY=$DATA['F_NAMES'];

$pdf->SetFont($FONT_ARI,'',$header);

$myArray = explode(',', $FullName);
$myArray=array_unique($myArray);

foreach ($myArray  as $value){
	$pdf->Cell(180 ,5,$value,0,1,'L');
}

$pdf->Ln();
$pdf->SetFont($FONT_ARI,'B',$header);
$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,1,'L');


$pdf->SetFont($FONT_ARI,'',$header);


$pdf->SetFont($FONT_ARI,'B',$sub_header);

$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">




		<!--	<tr>  
           		<th width="'.$w1.'" align="left">LAB ID</th>
                <th width="'.$w2.'" align="left">'.$LabSampleID[0].' & '.$Sampledate. '</th>				
           </tr>-->
		   <tr>  
				<th width="'.$w1.'" align="left">P.O NO & Date</th>
                <th width="'.$w2.'" align="left">'.$PoNo.' & '.$PoDate.'</th>				
           </tr>
		   
		   <tr>  
				<th width="'.$w1.'" align="left">PRODUCTION CODES</th>
                <th width="'.$w2.'" align="left">'.$DayCodes.'</th>				
           </tr>
		  
		   <tr>  
				<th width="'.$w1.'" align="left">TYPE OF PACKING</th>
                <th width="'.$w2.'" align="left">'.$PackStyle.'</th>				
           </tr> 
		   <tr>  
				<th width="'.$w1.'" align="left">TOTAL MASTER CARTONS</th>
                <th width="'.$w2.'" align="left">'.$SumOfmcs.'</th>				
           </tr> 
		   
		   <tr>  
				<th width="'.$w1.'" align="left">TOTAL NET WEIGHT</th>
                <th width="'.$w2.'" align="left">'.$QUANTITY_IN_KGS.'</th>				
           </tr>
		   
		   
		  
		   
		   
		   
		   </table>  
		';


$pdf->writeHTML($FARMER_NAME);


$PROD_REG_NO=$DATA['PROD_REG_NO'];

$myArray = explode(',', $DayCodeTracabilty);

$roww=$stmt->fetch_array();
	
$P_CODE=explode('&' ,$roww['P_CODES_REG_NUM']);	
$DOA_DOC=explode('/', $roww['DOA_DOC']);	


$text_size=8;

$pdf->Cell(180 ,$HEIGHT,'TEST RESULT :',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',$sub_header);

$DOA_DOC=explode('/', $roww['DOA_DOC']);	


$DOA_TIME_STAMP = strtotime($DOA_DOC[0]);
$doa_date = date("d-m-Y", $DOA_TIME_STAMP);	



$DOC_TIME_STAMP = strtotime($DOA_DOC[1]);
$doc_date = date("d-m-Y", $DOC_TIME_STAMP);		

$FARMER_NAME = '<table width="100%" cellpadding="3" border="1">

			<tr>  
           		<th width="20%" align="center">Production Codes & Reg No</th>
                <th width="20%" align="center">Variety</th>	
			
                <th width="20%" align="center">Date of analysis</th>					
                <th width="20%" align="center">Date of completed</th>					
                <th width="20%" align="center">Result</th>					
			</tr>
			
			<tr>
        
			<td width="20%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">';
			
			foreach ($myArray  as $value){
			$FARMER_NAME .=	 '<tr><td align="center"><font size="'.$text_size.'">'.$value.'</font></td></tr>';
			}
            $FARMER_NAME .=	 ' </table>    
			</td>

       
			<td width="20%" align="center" style="line-height: 100%;">
            <table width="100%" border="0">
                <tr><td align="center"><font size="'.$text_size.'">'.$ShortName.'</font></td></tr>       
                <tr><td align="center"><font size="'.$text_size.'">(COMPOSITE SAMPLE)</font></td></tr>       
                </table>   
			</td>
		
		<td width="20%" align="center" style="line-height: 100%;">
            <table width="100%" border="0">
                <tr><td align="center"><font size="'.$text_size.'">'.$AnalysisDate.'</font></td></tr>       
                
             
            </table>   
        </td><td width="20%">
            <table width="100%" border="0">
                <tr><td align="center"><font size="'.$text_size.'">'.$CompletionDate.'</font></td></tr>       
                
             
            </table>   
        </td><td width="20%">
            <table width="100%" border="0">
                <tr><td align="center"><font size="'.$text_size.'">Absent / 375 GMS</font></td></tr>       
                
             
            </table>   
        </td>
    </tr>
</table>';



$pdf->writeHTML($FARMER_NAME);


   
$pdf->Ln();

$HEIGHT=5;
$pdf->Cell(130 ,$HEIGHT,'THE  ABOVE CONSIGNMENT HAS BEEN TESTED FOR  MICROBIOLOGICALLY AS PER USFDA BAM 8TH ED METHOD AND  FOUND ',0,1);
$pdf->Cell(130 ,$HEIGHT,' SATISFACTORY & I ALSO CERTIFY THAT THE ABOVE CONSIGNMENT ORGANOLEPTICALLY PASSED AND EXPORT WORTHY ',0,1);

$pdf->Ln();

$pdf->SetFont($FONT_ARI,'B',$sub_header);


$pdf->Cell(95,5,'Tested By :'.$TestedBy,1,0,'L',0);
$pdf->Cell(95,5,'Verified By :'.$VerifiedBy,1,1,'L',0);  // cell with left and right borders


$pdf->Cell(95,10,'Signature',1,0,'L',0);
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders
			
ob_end_clean(); 
      
$pdf->Output('SALMONELLA REORT.pdf', 'I');
?>
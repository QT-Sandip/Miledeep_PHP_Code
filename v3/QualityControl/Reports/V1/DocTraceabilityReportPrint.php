<?php

session_start();
$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q'];  
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET); 
  }

$PoID=$_GET['PoID'];
$TYPE=$_GET['TYPE']; 
$HEADERS_COUNT=4;
include "Constants.php";
include "PrintHeaderInc.php";

 $PROCESSOR_APP_NUM;

$SqlTraceability="SELECT DISTINCT TracebilityID FROM `QcCodeList` WHERE InvID='$PoID'";
$StmtTraceability=$iconn->query($SqlTraceability);

$sql="SELECT * FROM ScrmPoData  WHERE `PoID`='$PoID'";  
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

while($RowTraceability=$StmtTraceability->fetch_array()){
	$TracebilityID=$RowTraceability['TracebilityID'];
	
 $SqlCodeList="SELECT * FROM `QcCodeList` as CL
 left join QcPoVarietyList as QPV on QPV.VarietyID=CL.VarietyID
 left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID
 WHERE CL.InvID='$PoID' and TracebilityID='$TracebilityID' ORDER BY DayCode"; 
$StmtCodeList=$iconn->query($SqlCodeList);
$RowCodeList=$StmtCodeList->fetch_array();
$Brand=$RowCodeList['Brand'];
$ShortName=$RowCodeList['ShortName'];
 $PackingStyle=$RowCodeList['PackingStyle'];

 if(in_array($PackingStyle,$AryPackingStyle,true)){
	  }
	  else{
		  $AryPackingStyle[]=$PackingStyle;

	  IF($TotalPackingStyle==''){
		  $TotalPackingStyle=$PackingStyle;
	  }
	  else{
		  $TotalPackingStyle=$TotalPackingStyle.','.$PackingStyle;
	  }
	  }

	  if(in_array($Brand,$AryBrand,true)){
	  }
	  else{
		  $AryBrand[]=$Brand;

	  IF($TotalBrand==''){
		  $TotalBrand=$Brand;
	  }
	  else{
		  $TotalBrand=$TotalBrand.','.$Brand;
	  }
	  }
if(in_array($ShortName,$AryShortName,true)){
	  }
	  else{
		  $AryShortName[]=$ShortName;

	  IF($TotalShortName==''){
		  $TotalShortName=$ShortName;
	  }
	  else{
		  $TotalShortName=$TotalShortName.','.$ShortName;
	  }
	  }


}
$varity_ids=array();


$header=11;
$sub_header=10;
$head=16;

$pdf->SetFont($FONT_ARI,'BU',$head);
$pdf->Cell(0 ,10,'TRACEABILITY REPORT',0,1,'C');
$pdf->SetFont($FONT_ARI,'',$header);
$pdf->SetFont($FONT_ARI,'BU',$header);

$pdf->Cell(40 ,5,'BRAND: '.$TotalBrand.','.$TotalShortName.'('.$TotalPackingStyle.')',0,0,'L');

$pdf->Cell(0 ,10,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$size=30;
$FontSizeHeader=10;
$FontSizeSubHeader=8;
$FontSizeText=7;

				

$size=getRowWidth($T_GRADES_ARY,$HEADERS_COUNT);

    $content = '';
    $content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="'.$size.'%"><font size="7"><b>CODE </b></font></th>
                <th  align="center" width="'.($size).'%"><font size="7"><b>TRACEABILITY</b></font></th>
				<th  align="center" width="'.($size).'%"><font size="7"><b>FARMER NAME & ADDRESS</b></font></th>
				<th  align="center" width="'.($size).'%"><font size="7"><b>TOTAL</b></font></th>
</tr>
';

				

	  $content .= generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$VarityName,100,$TYPE,$HEADERS_COUNT);

	 


			$content .= '</table>' ;
			
			$pdf->writeHTML($content,false);




$pdf->Ln();

$COUNT=1;

function generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$nameee,$t_width,$TYPE,$HEADERS_COUNT){
 
 global $PoID;
 
  $SqlTraceability="SELECT DISTINCT TracebilityID,DayCode FROM `QcCodeList` WHERE InvID='$PoID'"; 
$StmtTraceability=$iconn->query($SqlTraceability);
$McsTotal=0;
while($RowTraceability=$StmtTraceability->fetch_array()){
$TracebilityID=$RowTraceability['TracebilityID'];
$DayCode=$RowTraceability['DayCode'];

	 
	  $SqlCl="SELECT sum(McsGrade) as TotalMcs,DayCode,Tracebility,FarmerName,AddressContinued,FarmerCode FROM QcCodeList as CL
						left join QcPoVarietyList  as VL on CL.VarietyID=VL.VarietyID
						left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.TracebilityID='$TracebilityID' AND DayCode='$DayCode' and CL.InvID='$PoID' ORDER BY `CLID` ASC"; 
	$StmtCl=$iconn->query($SqlCl);
	$RowCl = $StmtCl->fetch_assoc();
	$TotalMcs=$RowCl['TotalMcs'];
    $McsTotal+=$TotalMcs;



			$McsGrade=$RowCl['McsGrade'];
			$McsGrade = explode(',', $McsGrade);

			$ARY_GRADES=$RowCl['Grades'];
			$ARY_GRADES = explode(',', $ARY_GRADES);

			if($RowCl['FarmerCode']!=''){
				$FarmerCode=$RowCl['FarmerCode'].' - ';
			}
			$Tracebility = $FarmerCode.$RowCl['Tracebility'];

			global $size;

			$contents .= '
				<tr>
				<td align="center"  width="'.$size.'%"><font size="8">'.$RowCl['DayCode'].'</font></td>
				<td align="center"  width="'.($size).'%" ><font size="8">'.$Tracebility.' </font></td>
				<td align="center"  width="'.($size).'%" ><font size="7">'.$RowCl['FarmerName'].'<br> '.$RowCl['AddressContinued']. ' </font></td>
				<td align="center"  width="'.($size).'%" ><font size="7">'.$RowCl['TotalMcs'].' Mcs</font></td></tr>
';
}
//echo $McsTotal; exit;
$contents .= '<tr>
			<th  align="center"  width="75%"><font size="7"> TOTAL </font></th>

			<th  align="center"  width="25%"><font size="7">'.$McsTotal.'Mcs</font></th>
			</tr>' ;
		return $contents;
	}


$pdf->Cell(49 ,5,'',0,1,'L');




$pdf->Output('Traceability_Report-'.$row['PoNo'].'.pdf', 'I');

?>

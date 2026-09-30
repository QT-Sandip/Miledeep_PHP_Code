<?php
//ini_set('display_errors', 1);
$PoID=$_GET['PoID'];
session_start();
include "../includes/config.php";
include "Constants.php";
include "PrintHeaderInc.php";

$HEADERS_COUNT=4;

  $sql_code_list="SELECT *  FROM `$TblQcCodeList`
WHERE InvID='$PoID' ORDER BY `CLID` ASC";
$stmt_code=$iconn->query($sql_code_list);
$TotalMcs=0;
$varity_ids=array();


while($row=$stmt_code->fetch_array()){
	 $VERIFIED_BY=$row['VerifiedBy'];
	$varity_ids[]=$row['VarietyID'];
	$AryMcs[]=$row['McsGrade'];
		$AryBrand[]=$row['Brand'];

}
 $Brand=implode(",",array_unique($AryBrand)); 



$AryMcs=implode(",",$AryMcs);
$AryMcs=explode(",",$AryMcs);
$AryMcs=array_unique($AryMcs);
 $TotalMcs=array_sum($AryMcs);

$varity_ids=array_unique($varity_ids);

 $TotalSamples =(sqrt($TotalMcs)+1)/2;
 $TotalSamples =round($TotalSamples,2);


$sql="SELECT * FROM `$TblScrmPoData`
 join $TblScrmConsigneeDetails on $TblScrmPoData.ConsigneeID=$TblScrmConsigneeDetails.ConID
 WHERE $TblScrmPoData.PoID='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$SqlGrades="SELECT DISTINCT(Grades) FROM $TblQcCodeList WHERE InvId='$PoID' ORDER BY ABS(Grades)";
$StmtGrades=$iconn->query($SqlGrades);
$T_GRADES_ARY=array();

while($RowGrades=$StmtGrades->fetch_array()){
$TempGrades=explode(",",$RowGrades['Grades']);
$T_GRADES_ARY=array_merge($T_GRADES_ARY,$TempGrades);
}

natsort($T_GRADES_ARY);
$T_GRADES_ARY=array_unique($T_GRADES_ARY);


$sql_varity_list="SELECT * FROM $TblQcPoVarityList WHERE InvID='$PoID'";
$stmt_varity_list=$iconn->query($sql_varity_list);


$from_title = date('M d, Y');
$to_title = date('M d, Y');


$header=11;
$sub_header=10;




$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(0 ,10,'SAMPLE WORK SHEET ',0,1,'C');
$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(40 ,5,'Buyer',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['ConsigneeName'],0,1,'L');
$pdf->Cell(40 ,5,'Approval Number',0,0,'L');
$pdf->Cell(0 ,5,': '.$PROCESSOR_APP_NUM,0,1,'L');
$pdf->Cell(40 ,5,'P.O Number',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['PoNo'],0,1,'L');
$pdf->Cell(40 ,5,'Brand',0,0,'L');
$pdf->Cell(0 ,5,': '.$Brand,0,1,'L');
$pdf->Cell(40 ,5,'Destination',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['ConsigneePort'].','.$row['ConsigneeCountry'],0,1,'L');


$pdf->Cell(0 ,0.5,'',0,1,'L');
$size=$size=getRowWidth($T_GRADES_ARY,$HEADERS_COUNT);


    $content = '';
    $content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="'.$size.'%"><font size="7"> DAY CODE </font></th>
                <th  align="center" width="'.($size).'%"><font size="7">VARITY TYPE OF PACKING</font></th>
				<th  align="center" width="'.($size).'%"><font size="7">TRACEABILITY</font></th>';

				$content .= generateTableHead($T_GRADES_ARY);
				$content .= '<th  align="center" width="'.$size.'%"><font size="7"> TOTAL MCS </font></th></tr>';


				foreach($varity_ids as $VARIETY_ID){

			  $VarityName=getVarityName($VARIETY_ID,$iconn);
			  $content .= generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$VarityName,100,$TYPE,$HEADERS_COUNT);

			  }


			$content .= '<tr>
			<th  align="center"  width="'.($size*3).'%"><font size="7"> TOTAL </font></th>
			' ;
			$content .= getTotals($PoID,$iconn,$T_GRADES_ARY,$S_W_T_MCS);
			$content .= '</tr>' ;
			$content .= '</table>';
			$pdf->writeHTML($content,false);




$pdf->Ln();

$COUNT=1;

$pdf->Cell(49 ,5,'As Per sampling code (Squareroot(n) + 1)/2 = '.floor($TotalSamples),0,1,'L');
$pdf->Cell(49 ,5,'Where n = Total Master Cartons.',0,1,'L');

$pdf->Ln();
$pdf->Cell(150 ,5,'Sample Drawn by : (Q.C. Technologist)',0,1,'L');





$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Cell(0 ,5,$row['VARIFIED_ID'],0,1,'L');
$pdf->Cell(0 ,5,'Q.C. Technologist',0,1,'L');






function getVarityName($VARIETY_ID,$iconn){
global $TblQcPoVarityList;
$sql="SELECT * FROM $TblQcPoVarityList  WHERE `VarietyID`='$VARIETY_ID'";  
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
  $name=$row['FullName'];
 return $name;
}


function generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$nameee,$t_width,$TYPE,$HEADERS_COUNT){

			global $size;
			global $TotalMcs;
			global $TotalSamples;
			global $TblQcCodeList;
			global $TblQcTracebilityList;
			global $TblQcPoVarityList;
			global $TblAdminPackingStyles;
			
	$TOTAL_SAMPLES =(sqrt($S_W_T_MCS)+1)/2;
	$TOTAL_SAMPLES =round($TOTAL_SAMPLES);



 	$sql_code_list="SELECT * FROM $TblQcCodeList WHERE VarietyID='$VARIETY_ID'
	ORDER BY `CLID` ASC";
	$stmt_code_list=$iconn->query($sql_code_list);

	$contents .= '<tr>
				  <td align="left"  width="'.$t_width.'%" >
				  <font size="8"><b> VARIETY :: </b>'.$nameee.'</font></td>
				  </tr>';


		while($row = $stmt_code_list->fetch_assoc()){
		$FARM_ID=$row['TracebilityID'];
		$PackingStyleID=$row['PackingStyleID'];

			$sql_FARM_ID="SELECT * FROM $TblQcTracebilityList WHERE TraceID='$FARM_ID'";
			$stmt_FARM_ID=$iconn->query($sql_FARM_ID);
			$r_FARM_ID = $stmt_FARM_ID->fetch_assoc();

			$FarmTraceID=$r_FARM_ID['Tracebility'];


			$sql_varity_list="SELECT * FROM $TblQcPoVarityList WHERE VarietyID='$VARIETY_ID'";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$r_varity_list = $stmt_varity_list->fetch_assoc();
			
			$SqlPackStyle="SELECT * FROM $TblAdminPackingStyles WHERE PCKID='$PackingStyleID'";
			$StmtPackStyle=$iconn->query($SqlPackStyle);
			$RowPackStyle = $StmtPackStyle->fetch_assoc();


			$ARY_MCS=$row['McsGrade'];
			$ARY_MCS = explode(',', $ARY_MCS);

			$ARY_GRADES=$row['Grades'];
			$ARY_GRADES = explode(',', $ARY_GRADES);



			$contents .= '
				<tr>
				<td align="center"  width="'.$size.'%"><font size="8">'.$row['DayCode'].'</font></td>
				<td align="center"  width="'.($size).'%" ><font size="8">'.$r_varity_list['ShortName'].'<br> 
				'.$RowPackStyle['PackingStyle'].  $GLAZE . '  </font></td>

				<td align="center"  width="'.($size).'%" ><font size="7">'.$FarmTraceID.'</font></td>
			';

		//	print_r($T_GRADES_ARY);

	$v_sample=0;
		foreach($T_GRADES_ARY as $ItemGrade){
			if(in_array($ItemGrade,$ARY_GRADES,true)){
			 $Index = array_search($ItemGrade, $ARY_GRADES);
			 $MCS=$ARY_MCS[$Index];
			 $each_sample=($MCS/$TotalMcs)*$TotalSamples;
			 $each_sample=round($each_sample);
				$v_sample=$v_sample+$each_sample;
			$contents .= '<td align="center" width="'.$size.'%"><font size="8">'.$each_sample.'</font></td>';
			}else{
			$contents .= '<td align="center" width="'.$size.'%"><font size="8"> *** </font></td>';
			}
		}
		$contents .= '<td align="center" width="'.$size.'%" ><font size="8">'.$v_sample.'</font></td></tr>';
		}

		return $contents;
	}


	function generateTableHead($T_GRADES_ARY){
		global $size;



		foreach($T_GRADES_ARY as $value){
			$contents .= '<th width="'.$size.'%" align="center"><font size="7">'.$value.'</font></th>';
		}
		return $contents;
	}


function getRowWidth($T_GRADES_ARY,$HEADERS_COUNT){
	 $size=count($T_GRADES_ARY)+$HEADERS_COUNT;
	 $size=100/$size;
		return $size;
	}

	function getTotals($PoID,$iconn,$T_GRADES_ARY,$S_W_T_MCS){
		global $size;
		global $TotalMcs;
		global $TotalSamples;
		global $TblQcCodeList;


			 $TOTAL_SAMPLES =(sqrt($S_W_T_MCS)+1)/2;
	$TOTAL_SAMPLES =round($TOTAL_SAMPLES);



			$sql_code_list="SELECT * FROM $TblQcCodeList WHERE InvID='$PoID' ORDER BY `CLID` ASC";
	$stmt_code_list=$iconn->query($sql_code_list);

	$stmt_code_list=$iconn->query($sql_code_list);
	$AryData=array();
	$AryTMcs=array();


	while($row = $stmt_code_list->fetch_assoc()){
		$AryData[]=$row;
	}

	foreach($T_GRADES_ARY as $ItemGrade){
		$MCS=0;
		foreach($AryData as $RowData){
		$ARY_MCS= explode(',', $RowData['McsGrade']);
		$ARY_GRADES = explode(',', $RowData['Grades']);

			if(in_array($ItemGrade,$ARY_GRADES,true)){
				$Index = array_search($ItemGrade, $ARY_GRADES);
			$MCS+=$ARY_MCS[$Index];
		}
		}

		$each_sample=($MCS/$TotalMcs)*$TotalSamples;
		 $each_sample=round($each_sample);

		$AryTMcs[]=$each_sample;
		$contents .= '<td align="center"  width="'.$size.'%"><font size="8">'.$each_sample.'</font></td>';
		}
		$contents .= '<td align="center"  width="'.$size.'%"><font size="8">'.array_sum($AryTMcs).'</font></td>';
	return $contents;

	}


ob_end_clean(); 

$pdf->Output('SampleWorkSheet.pdf', 'I');

?>

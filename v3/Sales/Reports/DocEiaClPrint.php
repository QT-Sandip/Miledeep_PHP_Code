<?php
 //ini_set('display_errors', 1);
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
//include ($_SERVER['DOCUMENT_ROOT']."/qc/constants.php");
include "../SalesInculdes/SalesConstants.php"; 

include "../PrintHeaderInc.php";
 $PoID=$_GET['PoID'];
 $TYPE=$_GET['TypeProduct']; 
//$HEADERS_COUNT=5;
include "../Constants.php";


include ($_SERVER['DOCUMENT_ROOT']."/tcpdf/tcpdf.php");





IF($TYPE==''){
$sql_code_list="SELECT DISTINCT(VarietyID) as VarietyID FROM `$TblQcCodeList` WHERE InvID='$PoID' ORDER BY `CLID` ASC";
}ELSE{
  $sql_code_list="SELECT DISTINCT($TblQcCodeList.VarietyID) as VarietyID FROM `$TblQcCodeList`
 LEFT JOIN $TblQcPoVarityList on $TblQcCodeList.VarietyID= $TblQcPoVarityList.VarietyID 
 WHERE $TblQcCodeList.InvID='$PoID' AND TypeProduct='$TYPE' ORDER BY `CLID` ASC";
}
//echo $sql_code_list; exit;
$stmt_code=$iconn->query($sql_code_list);
$varity_ids=array();


while($row=$stmt_code->fetch_array()){
	//$VERIFIED_BY=$row['VERIFIED_BY'];
	 $varity_ids[]=$row['VarietyID'];
	 
}


   $SqlVarifiedBy="SELECT VerifiedBy,Brand,LbuDesignation FROM `$TblQcCodeList` 
    as CL LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
WHERE InvID='$PoID' ORDER BY `CLID` ASC"; 
$StmtVarifiedBy=$iconn->query($SqlVarifiedBy);
$RowVarifiedBy=$StmtVarifiedBy->fetch_array();
 $VERIFIED_BY=$RowVarifiedBy['VerifiedBy'];
$Designation=$RowVarifiedBy['LbuDesignation'];
 

$StmtVarifiedBy=$iconn->query($SqlVarifiedBy);
while($rowBrand=$StmtVarifiedBy->fetch_array()){
	$AryBrand[]=$rowBrand['Brand'];
	
}
 $Brand=implode(",",array_unique($AryBrand)); 


 	$sql="SELECT * FROM `$TblScrmPoData`
 join $TblScrmConsigneeDetails on $TblScrmPoData.ConsigneeID=$TblScrmConsigneeDetails.ConID
 WHERE $TblScrmPoData.PoID='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$DestinationCountry=$row['DestinationCountry'];
$ProcessorID=$row['ProcessorID'];
if($DestinationCountry==26){
	$HEADERS_COUNT=4;
}
else{
$HEADERS_COUNT=5;
}



$T_GRADES_ARY=array();




$SqlGrades="SELECT DISTINCT(Grades) FROM `$TblQcCodeList`
 WHERE InvID='$PoID' ORDER BY ABS(Grades)";
$StmtGrades=$iconn->query($SqlGrades);

while($RowGrades=$StmtGrades->fetch_array()){
$TempGrades=explode(",",$RowGrades['Grades']);
$T_GRADES_ARY=array_merge($T_GRADES_ARY,$TempGrades);
}

natsort($T_GRADES_ARY);
$T_GRADES_ARY=array_unique($T_GRADES_ARY);


$header=11;
$sub_header=10;
$head=16;

//echo $FONT_ARI;

//$pdf->SetFont($FONT_ARI,'BU',$head);
$pdf->Cell(0 ,10,'CODELIST',0,1,'C');
//$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(40 ,5,'BUYER',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['ConsigneeName'],0,1,'L');
$pdf->Cell(40 ,5,'APPROVAL NUMBER',0,0,'L');
$pdf->Cell(0 ,5,': '.$PROCESSOR_APP_NUM,0,1,'L');
$pdf->Cell(40 ,5,'P.O NUMBER',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['PoNo'],0,1,'L');
if($DestinationCountry!=26){
$pdf->Cell(40 ,5,'BRAND',0,0,'L');
$pdf->Cell(0 ,5,': '.$Brand,0,1,'L');
}
$pdf->Cell(40 ,5,'DESTINATION',0,0,'L');
$pdf->Cell(0 ,5,': '.$row['FinalDestination'],0,1,'L');
$pdf->Cell(0 ,0.5,'',0,1,'L');
//$pdf->SetAutoPageBreak(TRUE, 10);
//$pdf->AddPage();

$size=getRowWidth($T_GRADES_ARY,$HEADERS_COUNT);

    $content = '';
    $content .= '

      	<table border="1" cellspacing="0" cellpadding="3">
				<tr>
           		<th  align="center"  width="'.$size.'%"><font size="7"> DAY CODE </font></th>
                <th  align="center" width="'.($size).'%"><font size="7">VARIETY TYPE OF PACKING</font></th>';
	if($DestinationCountry!=26){
	$content .='				<th  align="center" width="'.($size).'%"><font size="7">BRAND</font></th>';}
				$content .='<th  align="center" width="'.($size).'%"><font size="7">TRACEABILITY</font></th>';

				$content .= generateTableHead($T_GRADES_ARY,$HEADERS_COUNT);
				$content .= '<th  align="center" width="'.$size.'%"><font size="7"> TOTAL MCS </font></th>
				</tr>';


	  foreach($varity_ids as $VARIETY_ID){

	  $VarityName=getVarityName($VARIETY_ID,$iconn);
	  $content .= generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$VarityName,100,$TYPE,$HEADERS_COUNT);

	  }


			$content .= '<tr>
			<th  align="center"  width="'.($size*($HEADERS_COUNT-1)).'%"><font size="7"> TOTAL </font></th>
			' ;
			$content .= getTotals($PoID,$iconn,$T_GRADES_ARY,$TYPE,$HEADERS_COUNT);
			$content .= '</tr>' ;
			$content .= '</table>';
			$pdf->writeHTML($content,false);


//$pdf->AddPage();


$pdf->Ln();

$COUNT=1;








function generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$nameee,$t_width,$TYPE,$HEADERS_COUNT){
 global $TblQcCodeList;
 global $TblQcPoVarityList;
 global $TblQcTracebilityList;
 global $TblAdminPackingStyles,$DestinationCountry;
	 
	 $SqlCl="SELECT * FROM $TblQcCodeList as CL
						left join $TblQcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
						left join $TblAdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
						left join $TblQcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.VarietyID='$VARIETY_ID' 
						ORDER BY `CLID` ASC";
						
/* Grades Grouping for same daycode*/	
	
	$SqlCl="SELECT *,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as McsGrade_New
	FROM $TblQcCodeList as CL
						left join $TblQcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
						left join $TblAdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
						left join $TblQcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.VarietyID='$VARIETY_ID' GROUP by DayCode,PackingStyleID,TracebilityID
						ORDER BY `CLID` ASC";
						
						
						
	$StmtCl=$iconn->query($SqlCl);

	$contents .= '<tr>
				 <!-- <td align="left"  width="'.$t_width.'%" >-->
				  <td align="left"  width="100%" >
				  <font size="8"><b> VARIETY :: </b>'.$nameee.'</font></td>
				  </tr>';


		while($RowCl = $StmtCl->fetch_assoc()){
		


			$McsGrade=$RowCl['McsGrade'];
			$McsGrade=$RowCl['McsGrade_New'];
			$McsGrade = explode(',', $McsGrade);

			$ARY_GRADES=$RowCl['Grades'];
			$ARY_GRADES=$RowCl['Grades_New'];
			$ARY_GRADES = explode(',', $ARY_GRADES);

			global $size;

			$contents .= '
				<tr>
				<td align="center"  width="'.$size.'%"><font size="8">'.$RowCl['DayCode'].'</font></td>
				<td align="center"  width="'.($size).'%" ><font size="8">'.$RowCl['ShortName'].'<br> '.
				$RowCl['PackingStyle'].  $GLAZE . '  </font></td>';
					if($DestinationCountry!=26){
				$contents .='
					<td align="center"  width="'.($size).'%" ><font size="7">'.$RowCl['Brand'].'</font></td>';}
			$contents .='	<td align="center"  width="'.($size).'%" ><font size="7">'.$RowCl['Tracebility'].'</font></td>
			';



		foreach($T_GRADES_ARY as $ItemGrade){
			if(in_array($ItemGrade,$ARY_GRADES,true)){
			 $Index = array_search($ItemGrade, $ARY_GRADES);
			 $MCS=$McsGrade[$Index];
			$contents .= '<td align="center" width="'.$size.'%"><font size="8">'.$MCS.'</font></td>';
			}else{
			$contents .= '<td align="center" width="'.$size.'%"><font size="8"> *** </font></td>';
			}
		}
		$contents .= '<td align="center" width="'.$size.'%" ><font size="8">'.array_sum($McsGrade).'</font></td></tr>';
		}
		return $contents;
	}
	

function generateTableHead($T_GRADES_ARY,$HEADERS_COUNT){


	 global $size;
		foreach($T_GRADES_ARY as $value){
			$contents .= '<th width="'.$size.'%" align="center"><font size="7">'.$value.'</font></th>';
		}
		return $contents;
	}



function getTotals($PoID,$iconn,$T_GRADES_ARY,$TYPE,$HEADERS_COUNT){
		global $size;
		global $TblQcCodeList;
		global $TblQcPoVarityList;

		IF($TYPE==''){
			$sql_code_list="SELECT McsGrade,Grades FROM `$TblQcCodeList` WHERE InvID='$PoID'  ORDER BY `CLID` ASC";
		}ELSE{
			
			$sql_code_list="SELECT McsGrade,Grades FROM `$TblQcCodeList` as Qcl
left join `$TblQcPoVarityList`  as Qpl on Qcl.VarietyID=Qpl.VarietyID

WHERE Qcl.InvID='$PoID' AND TypeProduct='$TYPE' ORDER BY `CLID` ASC";
		}

		//echo $sql_code_list; exit;
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
			$AryTMcs[]=$MCS;
			$contents .= '<td align="center"  width="'.$size.'%"><font size="8">'.$MCS.'</font></td>';
			}
			$contents .= '<td align="center"  width="'.$size.'%"><font size="8">'.array_sum($AryTMcs).'</font></td>';
		return $contents;
	}

function getVarityName($VARIETY_ID,$iconn){
	global $TblQcPoVarityList;
  $sql="SELECT * FROM `$TblQcPoVarityList`  WHERE `VarietyID`='$VARIETY_ID'";  
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
  $name=$row['FullName'];
 return $name;
}

function getRowWidth($T_GRADES_ARY,$HEADERS_COUNT){
	 $size=count($T_GRADES_ARY)+$HEADERS_COUNT;
	 $size=100/$size;
		return $size;
	}

$pdf->Cell(49 ,15,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(49 ,5,'For '.$EXPORTOR,0,1,'L');
$pdf->Cell(49 ,15,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$header);

$pdf->Cell(49 ,5,$VERIFIED_BY,0,1,'L');
$pdf->Cell(49 ,5,$Designation,0,1,'L');


ob_end_clean();
$pdf->Output('CodeList: po-'.$row['PoNo'], 'I');
?>

<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();
$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q'];  

 
 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
$PoID=$_GET['PoID']; 
include "PrintHeaderInc.php";


  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET); 
  }    

 $PoID=$_GET['PoID']; 
 $ReportName=$_GET['ReportName']; 
$TYPE=$_GET['RID']; 
$FileType=$_GET['Type'];
/*
IF($TYPE==''){
$sql_code_list="SELECT DISTINCT(VarietyID) as VarietyID,PoGradeID FROM `$TblQcCodeList` WHERE InvID='$PoID' ORDER BY `CLID` ASC";
}ELSE{
  $sql_code_list="SELECT DISTINCT($TblQcCodeList.VarietyID) as VarietyID,PoGradeID FROM `$TblQcCodeList`
 LEFT JOIN $TblQcPoVarityList on $TblQcCodeList.VarietyID= $TblQcPoVarityList.VarietyID 
 WHERE $TblQcCodeList.InvID='$PoID' AND TypeProduct='$TYPE' ORDER BY `CLID` ASC";
}

$sql_code_list="SELECT  VarietyID FROM `ScrmPoGradeData` WHERE InvId='$PoID' GROUP by VarietyID";

//echo $sql_code_list; exit;
*/
/*
if($TYPE==''){
	$sql_code_list="SELECT  ID as PoGradeID, VarietyID FROM `ScrmPoGradeData` as SPGD
	WHERE InvId='$PoID' GROUP by VarietyID ORDER BY  SPGD.ID ASC";
   }ELSE{
	 $sql_code_list="SELECT ID as PoGradeID, SPGD.VarietyID FROM `ScrmPoGradeData` as SPGD 
	 left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
	 WHERE InvId='$PoID' and SPVL.TypeProduct='$TYPE' GROUP by SPGD.VarietyID ORDER BY SPGD.ID ASC";
   }
 */  
   
   if($TYPE==''){
	// $sql_code_list="SELECT  ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName FROM `ScrmPoGradeData` as SPGD
	//  left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
	// WHERE InvId='$PoID' GROUP by SPVL.ShortName ORDER BY  SPGD.ID ASC";
	$sql_code_list="SELECT  ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName,TypeProduct FROM `ScrmPoGradeData` as SPGD
  left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
	 WHERE InvId='$PoID' GROUP by SPVL.ShortName,SPVL.TypeProduct  ORDER BY  SPGD.ID ASC"; //new query uma 24-04-2024
	//echo "hi";
   }ELSE{
	 $sql_code_list="SELECT ID as PoGradeID, SPGD.VarietyID,SPVL.ShortName,TypeProduct FROM `ScrmPoGradeData` as SPGD 
	 left Join  ScrmPIVarietyList As SPVL on SPVL.VarietyID=SPGD.VarietyID
	 WHERE InvId='$PoID' and SPVL.TypeProduct='$TYPE' GROUP by SPVL.ShortName ORDER BY SPGD.ID ASC";
   }
//   echo $sql_code_list; exit;
   
$stmt_code=$iconn->query($sql_code_list);
$varity_ids=array();

while($row=$stmt_code->fetch_array()){
	$Item['VarietyID'] = $row['VarietyID'];
	$Item['PoGradeID'] = $row['PoGradeID'];
	$Item['ShortName'] = $row['ShortName'];
	$Item['TypeProduct'] = $row['TypeProduct']; // uma 24-04-2024
	$varity_ids[]=$Item;
	
	 
}


   $SqlVarifiedBy="SELECT VerifiedBy,Brand,LbuDesignation,ProductReleaseRemarks,PrintStatus
   FROM `QcCodeList` 
    as CL LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
	LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandName=CL.Brand
WHERE InvID='$PoID' ORDER BY `CLID` ASC";
//ECHO $SqlVarifiedBy; EXIT;
 
$StmtVarifiedBy=$iconn->query($SqlVarifiedBy);
$RowVarifiedBy=$StmtVarifiedBy->fetch_array();
 $VERIFIED_BY=$RowVarifiedBy['VerifiedBy'];
$Designation=$RowVarifiedBy['LbuDesignation'];
$ProductReleaseRemarks=$RowVarifiedBy['ProductReleaseRemarks'];
 

$StmtVarifiedBy=$iconn->query($SqlVarifiedBy);
while($rowBrand=$StmtVarifiedBy->fetch_array()){
	
		//ROBO
	$Brand = trim($rowBrand['Brand']);
	 $PrintStatus = trim($rowBrand['PrintStatus']); 

	if($Brand!='' && $PrintStatus!=0){
		$AryBrand[] = $Brand;
	}
	
	//$AryBrand[]=$rowBrand['Brand'];
	
}
 $Brand=implode(",",array_unique($AryBrand)); 

IF($Brand==''){
	$Brand='--'; 
 }
 	$sql="SELECT * FROM `ScrmPoData`
 join ScrmConsigneeDetails on ScrmPoData.ConsigneeID=ScrmConsigneeDetails.ConID
 WHERE ScrmPoData.PoID='$PoID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$IntLotNo="PRODUCT RELEASE ";//

$DestinationCountry=$row['DestinationCountry'];

/*
    $SqlCountryList="SELECT * FROM $TblScrmCountriesList where id='$DestinationCountry'";
	$StmtCountryList=$iconn->query($SqlCountryList); 
	$RowCountry=$StmtCountryList->fetch_array();
	$DestinationCountryName=$RowCountry['name'];*/
	//$RowCountry['name'];
$ProcessorID=$row['ProcessorID'];
if($DestinationCountry==26){
	$HEADERS_COUNT=4;
}
else{
$HEADERS_COUNT=5;
}



$T_GRADES_ARY=array();




$SqlGrades="SELECT DISTINCT(Grades) FROM `QcCodeList`
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
$head=13;

$PortOfLoading = getPortDetails($row['PortOfLoading'],$iconn)['PortName'];
$FinalDestination = getPortDetails($row['FinalDestination'],$iconn)['PortName'];
$CountryName =  getCountryDetails($row['ConsigneeCountry'],$iconn)['CountryName'];

$SqlCodeList="SELECT ProductReleaseDate FROM `QcCodeList` WHERE InvID='$PoID' ORDER BY `CLID` ASC";
$StmtCodeList=$iconn->query($SqlCodeList);
$rowdate=$StmtCodeList->fetch_array();
$ProductReleaseDate=date("d-m-Y",strtotime($rowdate['ProductReleaseDate']));
IF($ProductReleaseDate=='01-01-1970'){
	$ProductReleaseDate='';
}

$pdf->SetFont($FONT_ARI,'B',$head);

$pdf->Cell(0 ,10,$IntLotNo,0,1,'C');
$pdf->Cell(0 ,10,'Date : '.$ProductReleaseDate,0,1,'R');
$pdf->SetFont($FONT_ARI,'',$header);
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
$pdf->Cell(0 ,5,': '.strtoupper($FinalDestination).','.$CountryName,0,1,'L');
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
	/*	$Item['VarietyID'] = $row['VarietyID'];
		$Item['PoGradeID'] = $row['PoGradeID'];
	  $VarityName=getVarityName($VARIETY_ID,$iconn);
	  $content .= generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$VarityName,100,$TYPE,$HEADERS_COUNT);*/
	  $VarityName = getVarityName($VARIETY_ID['PoGradeID'], $iconn);
	//   $content .= generateRow($VARIETY_ID['VarietyID'], $iconn, $T_GRADES_ARY, $VarityName, 100, $TYPE, $HEADERS_COUNT,$VARIETY_ID['ShortName']);
	  $content .= generateRow($VARIETY_ID['VarietyID'], $iconn, $T_GRADES_ARY, $VarityName, 100, $TYPE, $HEADERS_COUNT,$VARIETY_ID['ShortName'],$VARIETY_ID['TypeProduct']);// uma 24-04-2024

	  }


			$content .= '<tr>
			<th  align="center"  width="'.($size*($HEADERS_COUNT-1)).'%"><font size="7"> TOTAL </font></th>
			' ;
			$content .= getGradeTotal($PoID,$iconn,$T_GRADES_ARY,$TYPE,$HEADERS_COUNT);
			$content .= '</tr>' ;
			$content .= '</table>';
			$pdf->writeHTML($content,false);


//$pdf->AddPage();


$pdf->Ln();

$COUNT=1;








// function generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$nameee,$t_width,$TYPE,$HEADERS_COUNT,$ShortName){
function generateRow($VARIETY_ID,$iconn,$T_GRADES_ARY,$nameee,$t_width,$TYPE,$HEADERS_COUNT,$ShortName,$TypeProduct){// uma 24-04-2024
//  global $TblQcCodeList;
//  global $TblQcPoVarityList;
//  global $TblQcTracebilityList;
//  global $TblAdminPackingStyles,$DestinationCountry,$PoID;
 global $DestinationCountry,$PoID;
	 
	//  $SqlCl="SELECT * FROM QcCodeList as CL
	// 					left join QcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
	// 					left join AdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
	// 					left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID
	// 					WHERE CL.VarietyID='$VARIETY_ID' 
	// 					ORDER BY `CLID` ASC"; 
/*						
$SqlCl="SELECT * FROM QcCodeList as CL 
left join  ScrmPoGradeData as SPGD on SPGD.ID=CL.VarietyID 
left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID 
left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID 
WHERE SPGD.VarietyID='$VARIETY_ID' and CL.InvId='$PoID' ORDER BY `CLID` ASC";
						
*/						
						
						
//robo						
//   $SqlCl = "SELECT *,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as McsGrade_New FROM QcCodeList as CL 
// left join  ScrmPoGradeData as SPGD on SPGD.ID=CL.VarietyID 
// left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
// left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID 
// left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID 
// WHERE  CL.InvId='$PoID'   and VL.ShortName='$ShortName'  GROUP by DayCode,CL.PackingStyleID,CL.Brand,TracebilityID ORDER BY `CLID` ASC"; 

// and and SPGD.VarietyID='$VARIETY_ID' 
$SqlCl = "SELECT *,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as McsGrade_New FROM QcCodeList as CL 
left join  ScrmPoGradeData as SPGD on SPGD.ID=CL.VarietyID 
left join  ScrmPIVarietyList as VL on SPGD.VarietyID=VL.VarietyID 
left join AdminPackingStyles as APS on APS.PCKID=CL.PackingStyleID 
left join QcTracebilityList as TL on CL.TracebilityID=TL.TraceID 
WHERE  CL.InvId='$PoID'   and VL.ShortName='$ShortName' and VL.TypeProduct='$TypeProduct'  GROUP by TypeProduct,DayCode,CL.PackingStyleID,CL.Brand,TracebilityID ORDER BY `CLID` ASC"; // //new query uma 24-04-2024


 
						echo '<br>'; 
						$contents='';
						
/* Grades Grouping for same daycode*/	
	
/*	$SqlCl="SELECT *,GROUP_CONCAT(Grades) as Grades_New, GROUP_CONCAT(McsGrade) as McsGrade_New
	FROM $TblQcCodeList as CL
						left join $TblQcPoVarityList  as VL on CL.VarietyID=VL.VarietyID
						left join $TblAdminPackingStyles  as APS on APS.PCKID=CL.PackingStyleID
						left join $TblQcTracebilityList as TL on CL.TracebilityID=TL.TraceID
						WHERE CL.VarietyID='$VARIETY_ID' GROUP by DayCode,PackingStyleID,TracebilityID
						ORDER BY `CLID` ASC";
	*/					
						
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
			$TypeProduct=$RowCl['TypeProduct']; 
			$ShortName=$RowCl['ShortName']; 
	
			$RowCl['ShortName']= getProductTypeCode($TypeProduct,$ShortName);
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


var_dump($ARY_GRADES);
		foreach($T_GRADES_ARY as $ItemGrade){
			
			
		//	echo $ItemGrade;
			if(in_array($ItemGrade,$ARY_GRADES,true)){
			 $Index = array_search($ItemGrade, $ARY_GRADES);
			echo $MCS=$McsGrade[$Index];
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



function getGradeTotal($PoID,$iconn,$T_GRADES_ARY,$TYPE,$HEADERS_COUNT){
		global $size;
		global $TblQcCodeList;
		global $TblQcPoVarityList,$ProductReleaseRemarks;
		global $EXPORTOR,$VERIFIED_BY,$Designation;

		IF($TYPE==''){
			$sql_code_list="SELECT McsGrade,Grades FROM `QcCodeList` WHERE InvID='$PoID'  ORDER BY `CLID` ASC";
		}ELSE{
			
			$sql_code_list="SELECT McsGrade,Grades FROM `QcCodeList` as Qcl
left join `QcPoVarityList`  as Qpl on Qcl.VarietyID=Qpl.VarietyID

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

			if($ProductReleaseRemarks!=''){
			$contents .= '<p>'.nl2br($ProductReleaseRemarks).' <br>The Above Product has passed all microbiology and organoleptic parameters, 
			all the ccp’s & cqp’s are also found satisfactory and the product is export worthy </p><br><b>For '.$EXPORTOR.'</b><br><br>'.$VERIFIED_BY.'<br>'.$Designation.'';
			}else{
				$contents .= '<br>
								<b>For '.$EXPORTOR.'</b><br><br>'.$VERIFIED_BY.'<br>'.$Designation.'';
			}
		return $contents;
	}

/*
$pdf->Cell(49 ,15,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(49 ,5,'For '.$EXPORTOR,0,1,'L');
$pdf->Cell(49 ,15,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$header);

$pdf->Cell(49 ,5,$VERIFIED_BY,0,1,'L');
$pdf->Cell(49 ,5,$Designation,0,1,'L');
*/

ob_end_clean();

if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$row['PoNo'];
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

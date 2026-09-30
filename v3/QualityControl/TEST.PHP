<?php 
//echo "We Will Updated Soon";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
/*include "../includes/config.php";*/

ECHO $sql="SELECT * FROM `CODE_LIST_CREATE` ";
$StmtSql=$iconn->query($sql);
while($FarmData=$StmtSql->fetch_array()){
	$CODE_LIST_ID=$FarmData['CODE_LIST_ID'];
	$Tracebility=$FarmData['FARM_REG_ID'];
	$FarmerName=$FarmData['NAME_OF_FARMER'];
	$AddressContinued=$FarmData['ADD_OF_FARMER'];
	
	$TracebilityID=$CLData['TracebilityID'];
	
 	echo $SqlSearch="select *from  QcTracebilityList  where Tracebility='$Tracebility' limit 1"; echo '<br>';
	$StmtSearch=$iconn->query($SqlSearch);
	$TlData=$StmtSearch->fetch_array();
	$TraceID=$TlData['TraceID'];
	
	
	if($TraceID!=''){
	
	$oldRow = legacyAuditFetchRowById('QcCodeList', 'CLID', $CODE_LIST_ID, 'QualityControl');
	ECHO $SqlUpdate="UPDATE  `QcCodeList` set TracebilityID='$TraceID' WHERE CLID='$CODE_LIST_ID'";
	$StmtUpSql=$iconn->query($SqlUpdate);
	legacyAuditLogUpdateById('QcCodeList', 'CLID', $CODE_LIST_ID, $oldRow, $_POST, ['Remarks' => 'Code List Tracebility Updated'], 'QualityControl');
	
	}else{
		$C++;
	}
	
	
}

echo $C;

/*
$sql="SELECT * FROM `FARM_TRACE_NEW`";
$StmtSql=$iconn->query($sql);
while($RowData=$StmtSql->fetch_array()){
	
	$TraceID=$RowData['ID'];
	$FarmerName=$RowData['Farmer_Name'];
	$AddressContinued=$RowData['Address_continued'];
	//$LicenceAuth=$RowData['ID'];
	$Address=$RowData['Address'];
	$Tracebility=$RowData['Farm_ID'];
	$FarmTraceNo=$RowData['TRACE_ID'];
	//$Area=$RowData['ID'];
	$NoOfPond=$RowData['No_of_Ponds'];
	//$TotalArea=$RowData['ID'];
	/*$Taluk=$RowData['ID'];
	$Village=$RowData['ID'];
	$RegAuthority=$RowData['ID'];*/
	/*$LastUsedDate=$RowData['LAST_USED_DATE'];
	
	
	$SqlTrace="INSERT INTO `QcTracebilityList`(`TraceID`, 
	`FarmerName`, `AddressContinued`,  `Address`, `Tracebility`, `FarmTraceNo`,  `NoOfPond`,
	 `LastUsedDate`) VALUES ('$TraceID','$FarmerName','$AddressContinued','$Address','$Tracebility','$FarmTraceNo','$NoOfPond','$LastUsedDate')";
	
	$stmt_count=$iconn->query($SqlTrace);
if($stmt_count){
	echo "hi";
}	

}


/*
$Sno=0;
$SqlQcCodeList="SELECT * FROM `QcCodeList` WHERE TracebilityID=0";
$StmtSqlQcCodeList=$iconn->query($SqlQcCodeList);
while($RowQcClData=$StmtSqlQcCodeList->fetch_array()){
	
	$Clid=$RowQcClData['CLID'];
	$DayCode=$RowQcClData['DayCode'];
	
	$SqlOldCodeList="SELECT * FROM `CODE_LIST_CREATE` WHERE CODE_LIST_ID='$Clid'";
	$StmtSqlOldCodeList=$iconn->query($SqlOldCodeList);
	$RowOldClData=$StmtSqlOldCodeList->fetch_array();
	
	$OldDayCOde=$RowOldClData['DAY_CODE'];
	
	$FARM_REG_ID=$RowOldClData['FARM_REG_ID'];
	$NAME_OF_FARMER=$RowOldClData['NAME_OF_FARMER'];
	$FARM_TRACE_ID=$RowOldClData['FARM_TRACE_ID'];
	$ADD_OF_FARMER=$RowOldClData['ADD_OF_FARMER'];
	$PackingStyleID=$RowOldClData['PackingStyleID'];
	
	if($DayCode==$OldDayCOde){
	$SqlTrace="SELECT * FROM `QcTracebilityList` WHERE Tracebility='$FARM_REG_ID'";
	$StmtSqlTrace=$iconn->query($SqlTrace);
	$RowSqlTrace=$StmtSqlTrace->fetch_array();
	$TraceID=$RowSqlTrace['TraceID'];
	
	if($TraceID==''){
		$SqlInsTrace="INSERT INTO $TblQcTracebilityList
	 (`FarmerName`, `AddressContinued`, `LicenceAuth`,  `Tracebility`,`FarmTraceNo`, `RegAuthority`) VALUES 
		('$NAME_OF_FARMER','$ADD_OF_FARMER','-','$FARM_REG_ID','$FARM_TRACE_ID','-')";
	

	$StmtInsTrace=$iconn->query($SqlInsTrace);
	 $TraceID = $iconn->insert_id; 
	}
	
	 $SqlUpdate="update QcCodeList set TracebilityID='$TraceID',PackingStyleID='$PackingStyleID' where CLID='$Clid'";
	

	$StmtInsTrace=$iconn->query($SqlUpdate); 
	$TraceID = $iconn->insert_id;
	
	
	$Sno++;	
		
	}
	
	echo $Sno;
	
	
}
*/

/*
$sqloldph="SELECT * FROM `BUYER_MB_REPORT`";
$Stmtsqloldph=$iconn->query($sqloldph);
while($RowDataoldph=$Stmtsqloldph->fetch_array()){
	
	$ID=$RowDataoldph['ID'];
	$VARITY=$RowDataoldph['VARITY'];
	$LAB_TEST_ID=$RowDataoldph['LAB_TEST_ID'];
	$COUNTRY=$RowDataoldph['COUNTRY'];
	$TESTED_BY=$RowDataoldph['TESTED_BY'];
	$VERIFIED_BY=$RowDataoldph['VERIFIED_BY'];
	$INVOICE_ID=$RowDataoldph['INVOICE_ID'];
	
	
	$Sqlnewph="INSERT INTO `QcMBReport`(`ID`, `TypeProduct`, `ReportID`, `Country`, `TestedBy`, `VerifiedBy`, `InvID`) 
	VALUES ('$ID','$VARITY','$LAB_TEST_ID','$COUNTRY','$TESTED_BY','$VERIFIED_BY','$INVOICE_ID')";
	$stmtnewph=$iconn->query($Sqlnewph);
if($stmtnewph){
	echo "hi";
}	

}



$sqloldph="SELECT * FROM `SAMPLE_REG_FORM_DATA`";
$Stmtsqloldph=$iconn->query($sqloldph);
while($RowDataoldph=$Stmtsqloldph->fetch_array()){
	
	$ID=$RowDataoldph['ID'];
	$REFERENCE_NUMBER=$RowDataoldph['REFERENCE_NUMBER'];
	$TYPE_N_NATURE=$RowDataoldph['TYPE_N_NATURE'];
	$SAMPLE_DRAWN_BY=$RowDataoldph['SAMPLE_DRAWN_BY'];
	$ANALYSIS_REQ=$RowDataoldph['ANALYSIS_REQ'];
	$LAB_NAME=$RowDataoldph['LAB_NAME'];
	$DATE=$RowDataoldph['DATE'];
	$VERIFIED_BY=$RowDataoldph['VERIFIED_BY'];
	$INVOICE_ID=$RowDataoldph['INVOICE_ID'];
	$InvAmount=$RowDataoldph['InvAmount'];
	$QuantityTaken=$RowDataoldph['QuantityTaken'];
	
	
	 $Sqlnewph="INSERT INTO `QcSamplingLettersExt`(`SMPLID`, `SampleNumber`, `SampleLocation`, `TypeProduct`, 
	`SampleDrawnBy`, `AnalysisReq`, `TestingLab`, `Date`, `VerifiedBy`, `InvID`,`InvAmount`,`QuantityTaken`)
	VALUES ('$ID','$REFERENCE_NUMBER','SURYAMITRA EXIM PVT LTD','$TYPE_N_NATURE','$SAMPLE_DRAWN_BY','$ANALYSIS_REQ','$LAB_NAME',
	'$DATE','$VERIFIED_BY','$INVOICE_ID','$InvAmount','$QuantityTaken')";
	$stmtnewph=$iconn->query($Sqlnewph); 
}

*/

/*
$sqloldph="SELECT * FROM `IH_ELSIA_TEST_REPORT`";
$Stmtsqloldph=$iconn->query($sqloldph);
while($RowDataoldph=$Stmtsqloldph->fetch_array()){
	
	$ID=$RowDataoldph['ID'];
	$TEST_REPORT_NUMBER=$RowDataoldph['TEST_REPORT_NUMBER'];
	$TEST_REPORT_DATE=$RowDataoldph['TEST_REPORT_DATE'];
	$FARM_REG_NO=$RowDataoldph['FARM_REG_NO'];
	$INTERNAL_LOT_NUM=$RowDataoldph['INTERNAL_LOT_NUM'];
	$SAMPLE_NAME=$RowDataoldph['SAMPLE_NAME'];
	$DO_SAMPLING=$RowDataoldph['DO_SAMPLING'];
	$NAME_OF_REP=$RowDataoldph['NAME_OF_REP'];
	$LAB_SAMPLE_ID=$RowDataoldph['LAB_SAMPLE_ID'];
	$DATE_OF_ANALYSIS=$RowDataoldph['DATE_OF_ANALYSIS'];
	$DATE_OF_ISSUANCE=$RowDataoldph['DATE_OF_ISSUANCE'];
	$TESTED_ID=$RowDataoldph['TESTED_ID'];
	$VARIFIED_ID=$RowDataoldph['VARIFIED_ID'];
	$INVOICE_ID=$RowDataoldph['INVOICE_ID'];
	
	
	 $Sqlnewph="INSERT INTO `QcInHouseAbReport`(`ID`, `ReportNo`, `ReportDate`, `TracebilityID`, `SamplingDate`, 
	 `Representation`, `SampleID`, `AnalysisDate`, `IssuanceDate`, `TestedBy`, `VerifiedBy`, `InvID`)
	 VALUES ('$ID','$TEST_REPORT_NUMBER','$TEST_REPORT_DATE',
	 '$FARM_REG_NO','$DO_SAMPLING','$NAME_OF_REP','$LAB_SAMPLE_ID','$DATE_OF_ANALYSIS','$DATE_OF_ISSUANCE',
	 '$TESTED_ID','$VARIFIED_ID','$INVOICE_ID')";
	$stmtnewph=$iconn->query($Sqlnewph);

if($stmtnewph){
	echo 'hi';
}	
}
*/

/*
$SqlMbResult="SELECT * FROM `BUYER_MB_RESULT`";
$Stmtsqloldph=$iconn->query($SqlMbResult);
while($RowDataoldph=$Stmtsqloldph->fetch_array()){
	
	$ID=$RowDataoldph['ID'];
	$MB_REPORT_ID=$RowDataoldph['MB_REPORT_ID'];
	$P_CODES_REG_NUM=$RowDataoldph['P_CODES_REG_NUM'];
	$DOA_DOC=$RowDataoldph['DOA_DOC'];
	$TPC=$RowDataoldph['TPC'];
	
	$Clid=explode('&',$P_CODES_REG_NUM);
	$DOA_DOC=explode('/',$DOA_DOC);
	$Clid=$Clid[1];	
	$Doa=$DOA_DOC[0];
	$Doc=$DOA_DOC[1];
	
	echo $SqlMbRelNew="INSERT INTO `QcMBResult`(`ID`, `MbReportID`, `CLID`, `DateOfAnalysis`, `DateOfIssuance`, `Tpc`)
								VALUES ('$ID','$MB_REPORT_ID','$Clid','$Doa','$Doc','$TPC')";
	$StmtMbNew=$iconn->query($SqlMbRelNew);

	
}

*/
?>

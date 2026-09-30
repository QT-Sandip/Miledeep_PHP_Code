<?php 
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
$PageTitle="Proforma Invoice List";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

updateSupc();

exit;


function updateSupc(){

	global $iconn;

$SqlOrderStatus="select * from ScrmPIVarietyList order by TypeProduct asc,abs(Grade) asc ";;
$OrdersExec=$iconn->query($SqlOrderStatus);
while($OrderStatusRow=$OrdersExec->fetch_array()){

$TypeProduct=$OrderStatusRow['TypeProduct'];
$FreezingType=$OrderStatusRow['FreezingType'];
$PreparationType=$OrderStatusRow['PreparationType'];
$VarietyID=$OrderStatusRow['VarietyID'];

if($FreezingType=='IQF'){
	$FreezingType='IQF';
}else if($FreezingType=='SIQF'){
	$FreezingType='IQF';
}

 $SqlClientCode="SELECT * FROM `ScrmPreparationMaster` 
			WHERE ShortCode='$PreparationType' 
			and ItemType='$TypeProduct' and FType='$FreezingType'";
$StmtProduction=$iconn->query($SqlClientCode);

 $CombinationCount=$StmtProduction->num_rows;

if($CombinationCount==0){
	$ValidCombo=false;
}else{
	$ValidCombo=true;
}
	

// if($TypeProduct=='RAW'){
// 	$CodeStart=5;
// 	$ColumnName="Where FreezingType='$FreezingType' and TypeProduct='RAW'";	
// }else

 if($TypeProduct=='COOKED'){
	$CodeStart=3;
	$ColumnName="Where FreezingType='$FreezingType' and TypeProduct='COOKED'";	
}else if($TypeProduct=='BLANCHED'){
	$CodeStart=4;
	$ColumnName="Where FreezingType='$FreezingType' and TypeProduct='BLANCHED'";
}else if($FreezingType=='IQF'){
	$CodeStart=5;
	$ColumnName="Where FreezingType='$FreezingType' and TypeProduct='$TypeProduct'";	
}else if($FreezingType=='SIQF'){
	$CodeStart=5;
	$ColumnName="Where FreezingType='IQF'  and TypeProduct='$TypeProduct'";	
}else if($FreezingType=='BLOCK'){
	$CodeStart=1;
	$ColumnName="Where FreezingType='BLOCK'  and TypeProduct='$TypeProduct'";	
}else{
	$CodeStart='NA';
}

//$ColumnName="Where FreezingType='$FreezingType' and TypeProduct='$TypeProduct'";
 echo $SqlClientCode="SELECT max(DeviSUPC) as ClientCode FROM `ScrmPIVarietyList` $ColumnName "; 

$StmtProduction=$iconn->query($SqlClientCode);	
$RowData=$StmtProduction->fetch_array();
$ClientCode=  $RowData['ClientCode']+1;

$Error=null;
if(!$ValidCombo ){
 $Error=  'InValid Combination for  '.$FreezingType  . ' and '.$TypeProduct .' and '.$PreparationType;
}else if($ClientCode==1){
 $Code= $CodeStart. '001';	
}else{		
 $Code= $ClientCode;		
}

if($Error==null){
	echo $SqlSPIVal="update `ScrmPIVarietyList` set DeviSUPC='$Code' where VarietyID='$VarietyID' ";
	$SqlSPDValexec=$iconn->query($SqlSPIVal);
	echo '<br>';
}
}


/*
$Data['Error']=$Error;
$Data['Code']=$Code;

echo json_encode($Data);
*/


}

/*
 $SqlOrderStatus="SELECT * FROM ScrmPoGradeData  "; 

$OrdersExec=$iconn->query($SqlOrderStatus);
while($OrderStatusRow=$OrdersExec->fetch_array()){
	
	$Grade=$OrderStatusRow['Grade'];
	$FreezingType=$OrderStatusRow['FreezingType'];
	$PackingStyleID=$OrderStatusRow['PackingStyleID'];
	$GlazeType=$OrderStatusRow['GlazeType'];
	$Variety=$OrderStatusRow['CInvFull'];
	$Species=$OrderStatusRow['Description'];
	$GlazePerc=$OrderStatusRow['GlazePerc'];
	$Treatment=$OrderStatusRow['Treatment'];
	$Brand=trim($OrderStatusRow['Brand']);

	if($Brand!=''){
	 $SqlBrand="SELECT * FROM `ScrmBrandsMaster` WHERE BrandName='$Brand' ";
	$StmtBrand=$iconn->query($SqlBrand);
	$BrandRow=$StmtBrand->fetch_array();
	 $BrandID=$BrandRow['BrandID'];
	if($BrandID==''){
		echo $SqlBrand;
	}
    }else{
		//echo 'Hii';
		$BrandID=1;	
	}

	 $SqlVal="SELECT * FROM `ScrmPIVarietyList` WHERE Grade='$Grade' and FreezingType='$FreezingType' 
			and PackingStyle='$PackingStyleID' and Glaze ='$GlazeType' and GlazePerc='$GlazePerc' and Treatment='$Treatment'  and Species='$Species'
			 and BrandName='$BrandID' and ShortName='$Variety'";
	$StmtVal=$iconn->query($SqlVal);
	$VRow=$StmtVal->fetch_array();

	 $VarietyID=$VRow['VarietyID'];
	if($VarietyID==''){
	    echo  $SqlVal;
		echo '<br>';
		echo $OrderStatusRow['InvId'];
		echo '<br>';
		echo $C++;
	}else{
		$ID = $OrderStatusRow['ID'];
		  $SqlSPDVal="update `ScrmPoGradeData` set VarietyID='$VarietyID' where ID='$ID'";
		 $SqlSPIVal="update `ScrmPIVarietyList` set Category='1' where VarietyID='$VarietyID' ";
		$SqlSPDValexec=$iconn->query($SqlSPDVal);
		$SqlSPDValexec=$iconn->query($SqlSPIVal);
	}

}

exit;

*/


$SqlOrderStatus="select * from ScrmPIVarietyList ";
//$SqlOrderStatus="SELECT * FROM ScrmPoGradeData  ";
$OrdersExec=$iconn->query($SqlOrderStatus);
while($OrderStatusRow=$OrdersExec->fetch_array()){
	
	 $Grade=$OrderStatusRow['Grade'];
	 $FreezingType=$OrderStatusRow['FreezingType'];
	$PackingStyleID=$OrderStatusRow['PackingStyleID'];
	 $Glaze=$OrderStatusRow['Glaze'];
	$Variety=$OrderStatusRow['Variety'];
	 $VarietyID=$OrderStatusRow['VarietyID'];
	 $GlazePerc=$OrderStatusRow['GlazePerc'];
	  $Treatment=$OrderStatusRow['Treatment'];
	   $Species=$OrderStatusRow['Species'];
	 	$ShortName=$OrderStatusRow['ShortName'];
	$PreparationType=$OrderStatusRow['PreparationType'];
	$Brand=trim($OrderStatusRow['Brand']);





	

	

	if($GlazePerc>0){
		 $Desc= $Species.'-'.$ShortName.'-'.$PreparationType.'-'.$GlazePerc.'%'.''.$Glaze.'-'.$Treatment.'-'.$FreezingType;
	  }else{
	  	$Desc= $Species.'-'.$ShortName.'-'.$PreparationType.'-'.$Glaze.'-'.$Treatment.'-'.$FreezingType;
	  }
	
	// $ProductDescription=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt; 
   echo $SqlInsert="
   update `ScrmPIVarietyList` set 
   `ProductDescription`='$Desc'
    where  VarietyID ='$VarietyID'"; 
	echo '<br>';
	  $StmtInsert=$iconn->query($SqlInsert);
   
   

   
   
  



	
}

exit;



 //$SqlOrderStatus="select * from ScrmPoGradeData WHERE VarietyID NOT in( SELECT VarietyID FROM `ScrmPIVarietyList` ) and Grade !=''";

	


exit;
$SqlOrderStatus="SELECT * FROM ScrmCustomInvoices as SCI left join ScrmPoData as SPD on SCI.PiID=SPD.PoID ";
//$SqlOrderStatus="SELECT * FROM ScrmPoData  ";
$OrdersExec=$iconn->query($SqlOrderStatus);
while($OrderStatusRow=$OrdersExec->fetch_array()){
	
	
	 $PoID=$OrderStatusRow['PoID'];
	 $PoID=$OrderStatusRow['PiID'];
	 $ShipByDate=$OrderStatusRow['ShipByDate'];
	 $ShipByDate=date("Y-m-d",strtotime($ShipByDate));
	 $ExpID=$OrderStatusRow['ExporterID'];
	 $ProcessorID=$OrderStatusRow['ProcessorID'];
	 $InvDate=$OrderStatusRow['CInvDate'];
	 $InvNo=$OrderStatusRow['CInvNo'];

	// $InvDate=$OrderStatusRow['InvDate'];
	// $InvNo=$OrderStatusRow['InvNo'];
	// $InvNumFull=getPInvoiceNumber($iconn,$ExpID,$PoID);
	//getInvoiceNumber($ExpID,$ProcessorID,$InvDate,$InvNo)
echo	$InvNumFull= getInvoiceNumberr($iconn,$ExpID,$ProcessorID,$InvDate,$InvNo);
echo '<br>';
/*	 $InvNumFull=$OrderStatusRow['InvNumFull'];
	$AryInv=explode('/',$InvNumFull);
	$InvNo=$AryInv[2]; 
	$PoDate=$OrderStatusRow['PoDate'];
	$ShipByDate=date("Y-m-d",strtotime($ShipByDate));
	$PoRefId=++$C;*/
	/*

 echo $SqlUpdate="update ScrmCustomInvoices set CustomsInvNo='$InvNumFull' where PiID=$PoID";
 //echo $SqlUpdate="update ScrmPoData set InvNumFull='$InvNumFull' where PoID=$PoID";
	$StmtUpdate=$iconn->query($SqlUpdate);
}

exit;

/*

 $SqlGradeData="SELECT * FROM `ScrmPoGradeData` where InvId='1982'  ORDER BY ID ASC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
   while($RowGradeData=$StmtGradeData->fetch_array()){

	
	$PoGradeID=$RowGradeData['ID'];
	$PoGrade=$RowGradeData['Grade'];
	$PoMcs=$RowGradeData['Mcs'];
	$PIId=$RowGradeData['InvId'];
	$PoFreezingType=$RowGradeData['FreezingType'];
	$PoPackingStyleID=$RowGradeData['PackingStyleID'];
	$PoGlazeType=$RowGradeData['GlazeType'];
	$PoVariety=$RowGradeData['Variety'];
	$PoGlazePerc=$RowGradeData['GlazePerc'];
	$PoTreatment=$RowGradeData['Treatment'];
	$PoBrand=$RowGradeData['Brand'];
	$PoPrice=$RowGradeData['Price'];
	$PoBAP=$RowGradeData['BAP'];
	$PoVarietyID=$RowGradeData['VarietyID'];
	$BuyerItemCode=$RowGradeData['BuyerItemCode'];
		
		
		
		
	echo 	$SqlGradeInsert="INSERT INTO `ScrmCIGradeData`(`PoGradeID`,`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, 
	 `Price`,`BAP`,`VarietyID`,`BuyerItemCode`) VALUES ('$PoGradeID',
	'$PoGrade','$PoMcs','$PoFreezingType','$PIId','$PoPackingStyleID','$PoGlazeType','$PoVariety','$PoGlazePerc',
	 '$PoTreatment','$PoBrand','$PoPrice','$PoBAP','$PoVarietyID','$BuyerItemCode')"; 
		$StmtGradeInsert=$iconn->query($SqlGradeInsert); 
		
   }
	/*$SqlOrderStatus="SELECT * FROM `dfllinemasterdetailsv1` 
						WHERE INVNum  in (SELECT CustomsInvNo from ScrmCustomInvoices) 
						GROUP by INVNum;";*/
	/*					
	$SqlOrderStatus="SELECT SPD.ID,INVNum,CInvFull,DFL.LineNo,SPD.LineNo,DFL.DEVISUPC,SPD.CodeType FROM `dfllinemasterdetailsv1`  as DFL 
left join  ScrmPoGradeData  as SPD ON DFL.INVNum=SPD.CInvFull 
WHERE DFL.LineNo=SPD.LineNo and   
						 INVNum  in (SELECT CInvFull from ScrmCustomInvoices)";
	$OrdersExec=$iconn->query($SqlOrderStatus);
	
	while($OrderStatusRow=$OrdersExec->fetch_array()){
		
		$ID=$OrderStatusRow['ID'];
		$DEVISUPC=$OrderStatusRow['DEVISUPC'];
		

   echo $SqlUpdate="update ScrmPoGradeData set 
						DeviSupc='$DEVISUPC' where ID=$ID";
		$StmtUpdate=$iconn->query($SqlUpdate);
	}
	
	*/
	
	//SELECT * FROM `ScrmPoGradeData`
	/*
	  if(GlazePerc>0){
		var Desc=Species+'-'+ShortName+'-'+PreparationType+'-'+GlazePerc+'%'+'-'+Glaze+'-'+Treatment;
	  }else{
	  var Desc=Species+'-'+ShortName+'-'+PreparationType+'-'+Glaze+'-'+Treatment;
	  }
	*/	
		/*
		 $DEVISUPC=$OrderStatusRow['DEVISUPC'];
		 $VarietyID=$OrderStatusRow['VarietyID'];
		 $ProductDescription=$OrderStatusRow['ProductDescription'];
		 $GlazePerc=$OrderStatusRow['GlazePerc'];
		 $Species=$OrderStatusRow['Species'];
		 $ShortName=$OrderStatusRow['ShortName'];
		 $PreparationType=$OrderStatusRow['PreparationType'];
		 $Glaze=$OrderStatusRow['Glaze'];
		 $Treatment=$OrderStatusRow['Treatment'];
		 
		 if($GlazePerc>0){
			 
			$Description=$Species.'-'.$ShortName.'-'.$PreparationType.'-'.$GlazePerc.'%'.$Glaze.'-'.$Treatment;
		 }else{
			 
			$Description=$Species.'-'.$ShortName.'-'.$PreparationType.'-'.$Glaze.'-'.$Treatment; 
		 }*/
		

	/*}*/
	
	/*


	$SqlOrderStatus="SELECT * FROM `ScrmPIData` where ShipByDate!=''";
	$OrdersExec=$iconn->query($SqlOrderStatus);
	while($OrderStatusRow=$OrdersExec->fetch_array()){
		
		
		 $PoID=$OrderStatusRow['PoID'];
		 $ShipByDate=$OrderStatusRow['ShipByDate'];
		 $ShipByDate=date("Y-m-d",strtotime($ShipByDate));
		 $ExpID=$OrderStatusRow['ExporterID'];
		// $InvNumFull=getPInvoiceNumber($iconn,$ExpID,$PoID);
		/* $InvNumFull=$OrderStatusRow['InvNumFull'];
		$AryInv=explode('/',$InvNumFull);
		$InvNo=$AryInv[2];
		$PoDate=$OrderStatusRow['PoDate'];
		$ShipByDate=date("Y-m-d",strtotime($ShipByDate));
		$PoRefId=++$C;
		*/
	/*echo $SqlUpdate="update ScrmPIData set ShipByDate='$ShipByDate' where PoID=$PoID";
		$StmtUpdate=$iconn->query($SqlUpdate);
	}


*/

/*

	$SqlOrderStatus="SELECT * FROM ScrmPoGradeData Where DeviSupc!=''";
	$OrdersExec=$iconn->query($SqlOrderStatus);
	while($OrderStatusRow=$OrdersExec->fetch_array()){
	
		$ID=$OrderStatusRow['ID'];
		$DeviSupc=$OrderStatusRow['DeviSupc'];
		
	  $SqlPIVarietyList="SELECT * FROM `ScrmPIVarietyList` as SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	WHERE DeviSUPC='$DeviSupc'";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);
$RowPIVarietyList=$StmtPIVarietyList->fetch_array();

 
  $sql="SELECT * FROM `ScrmPIVarietyList` as  SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	left join ScrmSpeciesMaster as  SSM on SSM.ShortCode =SPV.Species
	left join ScrmPreparationMaster as  SPM on SPM.ShortCode =SPV.PreparationType
	WHERE DeviSUPC='$DeviSupc'";
$stmt=$iconn->query($sql);
$Row=$stmt->fetch_array();

	$SpecDescription=checkEmpty($Row['Description']);
	$PreparationName=checkEmpty($Row['PreparationName']);
	$TypeProduct=checkEmpty($Row['TypeProduct']);
	$ProductFormm=checkEmpty($Row['FullName']);
	$FreezingTypee=checkEmpty($Row['FreezingType']);
	$PreparationTypee=checkEmpty($Row['PreparationType']);
	$GlazePercc=checkEmpty($Row['GlazePerc']);
	$Glazee=checkEmpty($Row['Glaze']);
	$Treatmentt=checkEmpty($Row['Treatment']);
	$Grade=checkEmpty($Row['Grade']);
	$PackingStyle=checkEmpty($Row['PackingStyle']);
	$BrandName=checkEmpty($Row['BrandName']);
	$PreparationDesc=checkEmpty($Row['PreparationDesc']);
	$DeviSUPC=checkEmpty($Row['DeviSUPC']);
	$BuyerProdDescription=checkEmpty($Row['BuyerProdDescription']);

	 //$VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
	 if($RowPIVarietyList['BuyerProdDescription']!=''){
		  $VarietyName=$RowPIVarietyList['BuyerProdDescription']; 
	 }else{
		  $VarietyName=$RowPIVarietyList['FullName']; 
	 }
	
	$DisplayName=$PreparationDesc .$SpecDescription.$ProductFormm.$FreezingTypee.' SHRIMPS'.$GDesc; 
	
	 $VarietyName=trim($DisplayName); 
	 $Glaze=$RowPIVarietyList['Glaze'];
	 $GlazePerc=$RowPIVarietyList['GlazePerc'];
	 $Treatment=$RowPIVarietyList['Treatment'];
	 $FreezingType=$RowPIVarietyList['FreezingType'];
	 $GRADE=$RowPIVarietyList['Grade'];
	 $PackingStyleID=$RowPIVarietyList['PCKID'];
	 $BRAND=$RowPIVarietyList['BrandName'];
	 $VarietyID=$RowPIVarietyList['VarietyID'];
		
		//Variety='$VarietyName'
		$newDate = date("Y-m-d", strtotime($CInvDate));
	 echo   $SqlUpdate="update ScrmPoGradeData set 
		FreezingType='$FreezingType',
		PackingStyleID='$PackingStyleID',
		GlazeType='$Glaze',
		GlazePerc='$GlazePerc',
		Treatment='$Treatment',
		Brand='$BRAND',
		Variety='$VarietyName',
		Description='',
		VarietyID='$VarietyID'
		
		where ID=$ID";
		$StmtUpdate=$iconn->query($SqlUpdate);
		
	}
	
	function checkEmpty($Value){
		if($Value==''){
		$Value='';	
		}else{
		$Value=' '.$Value;	
		}
		return $Value;
	}

	*/
	
	function getPInvoiceNumber($iconn,$ExpID,$PoID){
  $ExporterData=getExporterDetails($ExpID);
  $InvShortCode=$ExporterData['InvShortCode'];
  $InvDateFormat=$ExporterData['InvDateFormat'];

 $CustomInvoiceSql="SELECT * FROM ScrmPIData   WHERE PoID='$PoID'";
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
$CurrentMonth = date('n',strtotime($CustomInvoiceRow['InvDate']));   
if($InvDateFormat=='Short'){		
if($CurrentMonth<=3){
 $NxtYear = date("y",strtotime($CustomInvoiceRow['InvDate']));  	
 $CurrentYear = date("y",strtotime($CustomInvoiceRow['InvDate']))-1;  	
} 
else{ 
$CurrentYear = date("y",strtotime($CustomInvoiceRow['InvDate']));  	 
$NxtYear = date("y",strtotime($CustomInvoiceRow['InvDate']))+1;  	
 
}
}else{	

if($CurrentMonth<=3){
 $NxtYear = date('y',strtotime($CustomInvoiceRow['InvDate']));  	
 $CurrentYear = date('y',strtotime($CustomInvoiceRow['InvDate']))-1;  		
}else{ 
$CurrentYear = date('y',strtotime($CustomInvoiceRow['InvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['InvDate']))+1;  	
  
}
}
 
 return $InvShortCode.'PI/'. sprintf( '%03d',$CustomInvoiceRow['InvNo']).'/'.$CurrentYear.'-'.$NxtYear; 
  
	
}


function getInvoiceNumberr($iconn,$ExporterID,$ProcessorID,$InvDate,$InvNo){
	
	$ProcessorData=getExporterDetails($ProcessorID );
	$ExporterData=getExporterDetails($ExporterID);
	$ProcShortCode=$ProcessorData['InvShortCode'];
	$ExpShortCode=$ExporterData['InvShortCode'];
	$InvDateFormat=$ExporterData['InvDateFormat'];

	$CurrentMonth = date('n',strtotime($InvDate));  

if($InvDateFormat=='Short'){		
	if($CurrentMonth<=3){
	 $NxtYear = date("y",strtotime($InvDate));  	
	 $CurrentYear = date("y",strtotime($InvDate))-1;  	
	} 
	else{ 
	$CurrentYear = date("y",strtotime($InvDate));  	 
	$NxtYear = date("y",strtotime($InvDate))+1;  	
	 
	}
	}else{	
	
	if($CurrentMonth<=3){
	 $NxtYear = date('y',strtotime($InvDate));  	
	 $CurrentYear = date('Y',strtotime($InvDate))-1;  		
	}else{ 
	$CurrentYear = date('Y',strtotime($InvDate));  	 
	$NxtYear = date('y',strtotime($InvDate))+1;  	
	  
	}
	}

$AryElements=array();
$AryElements['ExpShortCode']=$ExpShortCode;
$AryElements['ProcShortCode']=$ProcShortCode;
$AryElements['CurrentYear']=$CurrentYear;
 $AryElements['NxtYear']=$NxtYear;
 $AryElements['InvNo']=$InvNo;
 
//var_dump($AryElements);

//$CustomsInvNo=$ExpShortCode.$ProcShortCode.$CurrentYear.$NxtYear.'/'.$InvNo;

	$InvNo="";
	 $SqlInvFormat="SELECT * FROM ScrmInvNumberPattern Where ExporterID='$ExporterID' and DocType='CI' order by SortOrder asc";
	$InvFormatExec=$iconn->query($SqlInvFormat);
	while($OrderStatusRow=$InvFormatExec->fetch_array()){
		//echo $OrderStatusRow['DateFormat'];
		if($OrderStatusRow['Static']==1){
		$InvNo.=$OrderStatusRow['ElementName'];
		}else if($OrderStatusRow['DateFormat']!=''){
			if($OrderStatusRow['DateFormat']=='YY'){
				$Year=$AryElements['CurrentYear'];
			}else{
				$Year=$AryElements['CurrentYear'].$OrderStatusRow['DateSeperator'].$AryElements['NxtYear'];
			}
			
			 $InvNo.=$OrderStatusRow['NormalSeparator'].$Year;
			// echo "hii";
		}else if($OrderStatusRow['ElementName']=='InvNo'){
			$LeadingZeros=$OrderStatusRow['LeadingZeros'];
			$LeadingZeros='%0'.$LeadingZeros.'d';
			$InvNo.=$OrderStatusRow['NormalSeparator'].sprintf( $LeadingZeros,$AryElements['InvNo']);
		}else{
			$ElementName=$OrderStatusRow['ElementName'];
			$InvNo.=$AryElements[$ElementName].$OrderStatusRow['NormalSeparator'];
			/*if($OrderStatusRow['ElementName']==1){
			$InvNo.=$OrderStatusRow['NormalSeparator'].$AryElements[$ElementName];
			}else{
				$InvNo.=$OrderStatusRow['NormalSeparator'].$AryElements[$ElementName];
			}
			*/
		}

		//ECHO $InvNo; ECHO '<BR>';


	}

	return $InvNo;
}


function getInvoiceFullNumbertosavee($ExporterID,$ProcessorID,$InvDate,$InvNo){


	$ProcessorData=getExporterDetails($ProcessorID );
	$ExporterData=getExporterDetails($ExporterID);
	$ProcShortCode=$ProcessorData['InvShortCode'];
	$ExpShortCode=$ExporterData['InvShortCode'];
	$InvDateFormat=$ExporterData['InvDateFormat'];
	
$CurrentMonth = date('n',strtotime($InvDate));  

if($InvDateFormat=='Short'){		
	if($CurrentMonth<=3){
	 $NxtYear = date("y",strtotime($InvDate));  	
	 $CurrentYear = date("y",strtotime($InvDate))-1;  	
	} 
	else{ 
	$CurrentYear = date("y",strtotime($InvDate));  	 
	$NxtYear = date("y",strtotime($InvDate))+1;  	
	 
	}
	}else{	
	
	if($CurrentMonth<=3){
	 $NxtYear = date('y',strtotime($InvDate));  	
	 $CurrentYear = date('Y',strtotime($InvDate))-1;  		
	}else{ 
	$CurrentYear = date('Y',strtotime($InvDate));  	 
	$NxtYear = date('y',strtotime($InvDate))+1;  	
	  
	}
	}



$CustomsInvNo=$ExpShortCode.$ProcShortCode.$CurrentYear.$NxtYear.'/'.$InvNo;



return $CustomsInvNo; 
  
	
}
}

?>
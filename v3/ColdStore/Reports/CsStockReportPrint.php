<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$OverAllMcs=0;

		 

$PackData=$_GET['PackData'];
$Group=$_GET['Group'];
$Category=$_GET['Category'];
$FreezingType=$_GET['FreezingType'];
$UNIT=$_GET['UNIT'];
$PONmbr=$_GET['PONmbr'];
$ColdstoreID=$_GET['ColdstoreID']; 
 $TenantID=$_GET['TenantID']; 
$VarietyID=$_GET['VarietyID'];
$AntiBiotic=$_GET['AntiBiotic'];
$Soaking=$_GET['Soaking'];
$qry="";

/*if($UNIT!='BOTH'){
	$qry.="and CPID.`UnitID`='$UNIT'";
}*/
if($PackData!='BOTH'){
	$qry.="and CPID.`PackingTypeID`='$PackData'";
}
if($Group!='BOTH'){
	$qry.="and CPID.`ProductionTypeID`='$Group'";
}
if($Category!='BOTH'){
	$qry.="and CPID.`ProductCategoryID`='$Category'";
}
if($FreezingType !='BOTH'){
	$qry.="and CPID.`FreezingType`='$FreezingType'";
}if($PONmbr !='BOTH'){
	$qry.="and CPID.`PoID`='$PONmbr'";
}

if($VarietyID !='BOTH'){
	$VarietyIDQry="and CPID.`VarietyID`='$VarietyID'";
}

if($AntiBiotic !='BOTH'){
$qry.=" and CPID.`AbStatus` = '$AntiBiotic' ";
}

if($Soaking !='BOTH'){
	$qry.=" and CPID.`SockingType`='$Soaking'";
}

if($TenantID!='BOTH'){
         $qry .=" and CPID.StockHolderID='$TenantID'";
         }

$JoinQuery=" left join CsBarCodes as CBD on CBD.BarCodeID=CPID.BarCodeID ";
$StatusQuery=" and (CBD.BarCodeStatus='1' OR CBD.BarCodeStatus='2')";
		
$SqlGrades="SELECT Value as GradeName,OptID as GRADES FROM AdminGeneralOptions where OptionType ='GRADES' order by SortOrder asc";
$StmtGrades=$iconn->query($SqlGrades);

  /* $SqlForm="SELECT DISTINCT(VarietyID) as FORM FROM `CsStockData` as CPID where 
(AvalMcs !='' or  LooseBags!=0)  $qry order by VarietyID ASC"; */
 
 
 $SqlForm="SELECT DISTINCT(CPID.VarietyID),ShortName as FORM FROM `CsStockData` as CPID 
left join ScrmPIVarietyList as PVL on CPID.VarietyID=PVL.VarietyID 
where CPID.UnitID ='$ColdstoreID' and (AvalMcs !=0 or LooseBags!=0)  $qry $VarietyIDQry
 order by VarietyID ASC";
$StmtForm=$iconn->query($SqlForm);


$AryGrades=array();

while($RowGrades=$StmtGrades->fetch_array()){
	$AryGrades[] = $RowGrades['GRADES'];
	$AryGradenames[] = $RowGrades['GradeName'];
}

//echo implode("', '",$AryGrades);


       $data='
		<table id="example1" border="1" cellspacing="0" cellpadding="7"">
        <thead></thead>
		<tbody>';
	
	
while($form=$StmtForm->fetch_array()){	

$FORM = $form['FORM'];
$VarietyID = $form['VarietyID'];


  /*$SqlPacking="SELECT DISTINCT(`PackingStyleID`),PackingStyle FROM `CsStockData` as CPID
join AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.ID
 where FORM='$FORM' $qry order by Abs(PackingStyle) asc";*/
 
  $SqlPacking="SELECT DISTINCT(`PackingStyleID`),PackingStyle FROM `CsStockData` as CPID
join AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.PCKID
 where VarietyID='$VarietyID' $qry order by Abs(PackingStyle) asc"; 
$StmtPacking=$iconn->query($SqlPacking);

$packing =array();
while($RowPacking=$StmtPacking->fetch_array()){
   $packing[]=$RowPacking['PackingStyleID'];
   $AryPack[]=$RowPacking['PackingStyle'];
}
 
// print_r($packing);
 
$data.='<tr>
		<th style="font-size: 11px;color:red;">'.$FORM .'</th>
	    <th style="font-size: 11px" >Packing</th>';
	   
	 
	   foreach($AryGradenames as $Grades){ 
	  
			$data.='<th style="font-size: 11px" >'. $Grades . '</th>';
		   
		} 

$data.='
    <th style="font-size: 10px">Mcs</th>
    <th style="font-size: 10px">Kg</th>
	</tr>';


$KgsPerGrade=0;
$c=0;
foreach($packing as $PackStyleId){	
$SqlPackStyle="SELECT * FROM `AdminPackingStyles` WHERE PCKID='$PackStyleId'"; 
$StmtPackStyle=$iconn->query($SqlPackStyle);
$RowPackStyle=$StmtPackStyle->fetch_array();

$GradeRow='<tr><th></th>
<th style="font-size: 11px" >'.$RowPackStyle['PackingStyle'] .'</th>';
$c++;


$T_MCS=0;
$T_KGS=0;


/***************************************************Grade MCS/ kgs*********************************************/

//foreach($AryGrades as $GRADES){	
foreach($AryGradenames as $GRADES){	


	
 $SqlFormMCS="SELECT PackingStyle,PackingStyleID,
	 SUM(AvalMcs) as MCS ,
	 SUM(LooseBags) as LooseBags ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE CPID.UnitID ='$ColdstoreID' and VarietyID ='$VarietyID' and PackingStyleID='$PackStyleId' AND 
	Grade ='$GRADES' $qry"; 

	$SqlFormMCS= $SqlFormMCS  .$StatusQuery;
	$StmtFormMCS=$iconn->query($SqlFormMCS);
	$RowFormMCS=$StmtFormMCS->fetch_array();
	$MCS = $RowFormMCS['MCS'];  
	$LOOSE = $RowFormMCS['LOOSE'];  
	$KGS = $RowFormMCS['KGS']+$RowFormMCS['LKGS'];  
	
	$T_MCS=$T_MCS+$MCS;	
	$T_KGS=$T_KGS+$KGS;	

		
	$GradeRow.='<td style="font-size: 11px">'.round($MCS) .'/'.round($LOOSE).'</td>';
	//$GradeRow.='<td style="font-size: 11px">'.round($MCS).'</td>';

}
	 $Kgs=$RowFormMCS['KGS']+$RowFormMCS['LKGS']; 
	 $KgsPerGrade+=$Kgs;

if($T_KGS!=0){
$data.=	$GradeRow;
$data.='<td style="font-size: 11px">'.round($T_MCS) .'</td>';
$data.='<td style="font-size: 11px">'.round($T_KGS) .'</td></tr>';
}

$data.='</tr>';



	 }
	 
/***************************************************Grade MCS/ kgs*********************************************/	 
/***************************************************TOTAL MCS*********************************************/	 
	 
$data.=' <tr style="font-weight:bold;font-size:11px;color:blue" >
<th style="font-size: 11px">Total Mcs</th><th></th>';
	 
	 $T_MCS=0;
//foreach($AryGrades as $GRADES){
foreach($AryGradenames as $GRADES){	

	
	
$SqlFormMCS="SELECT PackingStyle,PackingStyleID,SUM(AvalMcs) as MCS ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE CPID.UnitID ='$ColdstoreID' and VarietyID ='$VarietyID' AND Grade ='$GRADES' $qry  $StatusQuery";
	
	$StmtFormMCS=$iconn->query($SqlFormMCS);
	$RowFormMCS=$StmtFormMCS->fetch_array();
	$MCS = $RowFormMCS['MCS']; 
	$KGS = $RowFormMCS['KGS']+$RowFormMCS['LKGS']; 
	$T_MCS=$T_MCS+$MCS;
	$OverAllMcs=$OverAllMcs+$T_MCS;
	$data.='<th style="
    font-size: 11px">'.round($MCS) .'</th>';
	
	
}	
$data.='<th>'.round($T_MCS) .'Mcs</th><th>NA</th></tr>';	



 
/***************************************************TOTAL MCS END*********************************************/	 
/***************************************************TOTAL KGS*********************************************/	 
	  
$data.=' <tr style="font-weight:bold;font-size:11px;color:#0bb50b" >
<th style="font-size: 11px">Total Kgs</th><th></th>';
	 
	 $T_MCS=0;
	 $T_KGS=0;
//foreach($AryGrades as $GRADES){	
foreach($AryGradenames as $GRADES){	



$SqlFormMCS="SELECT PackingStyle,PackingStyleID,SUM(AvalMcs) as MCS ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE CPID.UnitID ='$ColdstoreID' and VarietyID ='$VarietyID' AND Grade ='$GRADES' $qry  $StatusQuery";
	
	$StmtFormMCS=$iconn->query($SqlFormMCS);
	$RowFormMCS=$StmtFormMCS->fetch_array();
	$MCS = $RowFormMCS['MCS']; 
	 $KGS = $RowFormMCS['KGS']+$RowFormMCS['LKGS']; 
	$T_KGS=$T_KGS+$KGS;
	$OverAllMcs=$OverAllMcs+$T_MCS;
	$data.='<th style="
    font-size: 11px">'.round($KGS) .'</th>';
	
	
}	
$data.='<th>NA</th><th>'.round($T_KGS) .'Kg</th></tr>';	
}

$data.=' <tr style="font-weight:bold;font-size:11px;color:red" >
<th style="font-size: 11px">Over All Kgs</th>
<th></th>
';

	 $T_MCS=0;
	 $T_KGS=0;

foreach($AryGrades as $GRADES){	
		
 

  $SqlFormMCS="SELECT PackingStyle,PackingStyleID,SUM(AvalMcs) as MCS ,
	SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS,
	SUM(LooseBags*SlabWeight*MFactor) as LKGS
	FROM  `CsStockData` as CPID
	left join AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID
	$JoinQuery WHERE  CPID.UnitID ='$ColdstoreID' and VarietyID ='$VarietyID' $qry $VarietyIDQry $StatusQuery";
	
	$StmtFormMCS=$iconn->query($SqlFormMCS);
	$RowFormMCS=$StmtFormMCS->fetch_array();
	$MCS = $RowFormMCS['MCS'];
	$KGS = $RowFormMCS['KGS']+$RowFormMCS['LKGS']; 
	$T_KGS=$T_KGS+$KGS;

	
	if($MCS ==''){
	$MCS ='-';
	}else{
		$T_MCS=$T_MCS+$MCS;
		$OverAllMcs=$OverAllMcs+$T_MCS;
	}
	
	//print_r($T_MCS);
	
	$data.='<th style="
    font-size: 11px">'.round($MCS) .'</th>';
	
	
}


if($T_KGS!=0){
$data.='<td style="font-size: 11px">'.round($T_MCS) .'Mcs</td>';
$data.='<td style="font-size: 11px">'.round($T_KGS) .'Kgs</td></tr>';
}	

echo $data .='</tbody></table>   </div>
  
			
    </div></div>
 </div></div>
</div>';



?>
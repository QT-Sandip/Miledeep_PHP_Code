<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PoGradeId=$_POST['PoGradeId'];
$VarietyID=$_POST['VarietyID'];
$PackingStyleID=$_POST['PackingStyleID']; 
 $ColdstoreID=$_POST['ColdstoreID']; 
 $TenantID=$_POST['TenantID']; 




if($PoGradeId!=''){
$query=" and CPID.Grade='$PoGradeId'";
}
if($VarietyID!=''){
$query.=" and CPID.VarietyID='$VarietyID'";
}
if($PackingStyleID!=''){
$query.=" and CPID.PackingStyleID='$PackingStyleID'";
}

if($TenantID!=''){
$query .=" and CPID.StockHolderID='$TenantID'";
}

if($ColdstoreID!=''){
$query .=" and CPID.UnitID='$ColdstoreID'";
}
	

 $AccessType=$_SESSION["AccessType"];

	 $sql="
	
	SELECT cb.BarCodeID, PoNo,GROUP_CONCAT(ShortName) as ShortName, ProductionDate,PackingStyle, 
	StockID,sum(AvalMcs) as AvalMcs,sum(LooseBags) as LooseBags,PackingTypeID,Grade,FreezingType, 
	ProductionDate, CsPos.PoNo,`ProductionTypeID`, ProductCategoryID,CPID.Remarks as REMARKS, UnitID 
	FROM `CsBarCodes` as cb left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID 
	left join ScrmPIVarietyList as VL on VL.VarietyID=CPID.VarietyID 
	left join AdminPackingStyles as CSP on CSP.PckID=CPID.PackingStyleID 
	left join ScrmPoData as CsPos on CsPos.PoID=CPID.PoID where cb.BarCodeStatus='1' 
	$query
	and (AvalMcs!=0 or LooseBags!=0) and cb.OnFloor=0 and CPID.StockID IS NOT NULL GROUP by CPID.BarCodeID";  
	
	  $sql="
	
	SELECT cb.BarCodeID, PoNo,AbStatus,SockingType,ShortName, ProductionDate,CSP.PackingStyle, LotNo,ProductionID,
	StockID, AvalMcs, LooseBags,PackingTypeID,CPID.Grade,CPID.FreezingType, 
	ProductionDate, CsPos.PoNo,`ProductionTypeID`, ProductCategoryID,CPID.Remarks as REMARKS, UnitID 
	FROM `CsBarCodes` as cb left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID 
	left join ScrmPIVarietyList as VL on VL.VarietyID=CPID.VarietyID 
	left join AdminPackingStyles as CSP on CSP.PckID=CPID.PackingStyleID 
	left join ScrmPoData as CsPos on CsPos.PoID=CPID.PoID where cb.BarCodeStatus='1' 	
	$query
	and (AvalMcs!=0 or LooseBags!=0) and cb.OnFloor=0 and CPID.StockID IS NOT NULL order by cb.BarCodeID,
	ShortName,abs(CPID.Grade) asc ";  
//and (AvalMcs!=0 or LooseBags!=0)
$StmtFloorStock=$iconn->query($sql);

// echo $sql; exit;

$data='
<div class="card">
	<div class="card-body">
	<table  id="key-datatable" data-paging="false" 
class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
                  
				  
				  
				  <th >BarCode ID</th>
				  <th >Date</th>
				  <th >Variety</th>
				  <th >Grade</th>
				  <th >Soaking</th>
				  <th >Packing Type</th>
				  <th >Packing style</th>	
				  <th >Category</th>
				  <th >AB Status</th>
				   <th >Po Number</th>
				   <th >Lot Number</th>
				  <th >MCS</th>
				 
				  <th >Loose</th>
				  <th >Actions</th>				  
                </thead>
                <tbody>';

			   
	while($RowData=$StmtFloorStock->fetch_array()){	

$StockID=$RowData['StockID'];
$ProductionDate=date("d-m-Y", strtotime($RowData['ProductionDate']));	
$PackingType=$RowData['PackingTypeID'];	
$PoNumber=$RowData['PoNo'];	
$LotNo=$RowData['LotNo'];	
$VarietyName=$RowData['ShortName'];	
$GradeName=$RowData['Grade'];	
$SockingType=$RowData['SockingType'];	
$AvalMcs=$RowData['AvalMcs'];	
$PackingStyle=$RowData['PackingStyle'];	
$LooseBags=$RowData['LooseBags'];	
$BarcodeID=$RowData['BarCodeID'];
$AbStatus=$RowData['AbStatus'];
$ProductCategoryID=$RowData['ProductCategoryID'];

$VarietyName=explode(',',$VarietyName);	
$VarietyName=array_unique($VarietyName);
$VarietyName=implode(',',$VarietyName);



$data.='<tr>
				  <td >'. $BarcodeID .'</td>
				  <td >'. $ProductionDate .'</td>
				  <td >'. $VarietyName .'</td>
				  <td >'. $GradeName .'</td>
				  <td >'. $SockingType .'</td>
				  <td >'. $PackingType .'</td>
				  <td >'. $PackingStyle .'</td>	
				  <td >'. $ProductCategoryID .'</td>
				  <td >'. $AbStatus .'</td>
	  			  <td >'. $PoNumber .'</td>
	  			  <td >'. $LotNo .'</td>
				  <td >'. $AvalMcs .'</td>
				  <td >'. $LooseBags .'</td>
				  
				  
				  <td >
						  
				<a target="_blank" class="mr-3 text-primary" 
				href="inward?ProductionDataID='.$RowData['ProductionID'].'&BarCodeID='.$RowData['BarCodeID'].'"> 
				<i class="mdi mdi-pencil font-size-18"></i>  </a>
						  
				<a target="_blank" class="mr-3 text-primary" 
				href="view-store-layout?BarCodeId='.$RowData['BarCodeID'].'"> 
				<i class="mdi mdi-open-in-app font-size-18"></i>  </a> 
				
						<button class="btn btn-link text-danger AddToPallet" 
						data-id="'.$RowData['StockID'] .'" 
							data-toggle="tooltip" data-placement="top"
							title="Add To Pallet" value="'.$RowData['StockID'].'"
							data-original-title="Add To Pallet">
							<i class="mdi mdi-plus font-size-18">
							</i>
						</button> 
						
						<a target="_blank" class="mr-3 text-primary" 
				href="CsBarCodeLabelPrint.php?BID='.$RowData['BarCodeID'].'"> 
				<i class="mdi mdi-printer font-size-18"></i>  </a> 
						  
						 <!--<button class="btn btn-link text-danger Delete" 
						  data-id="'. $RowData['StockID'].'"
							data-toggle="tooltip" data-placement="top" 
							title="" value="'.$RowData['StockID'].'"
							data-original-title="Delete">
							<i class="mdi mdi-trash-can font-size-18">
							</i></button> 	-->					
		 
						 
                         
                       
				  </td>
</tr>';

	} 
	
echo	$data.='</tbody>
	</table></div></div>';


	
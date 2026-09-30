<?php 
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include($cp."Includes/IncludePage.php");
$ColdstoreID=$_GET['ColdstoreID']; 
 $TenantID=$_GET['TenantID'];
	if(isset($_GET['ToDate'])){
			
			$FromDate=$_GET['FromDate'];
			$ToDate=$_GET['ToDate'].' 23:59:59';
			
			if($_GET['OutType'] !="BOTH"){
				 $OutType=$_GET['OutType'];
			$query=" and OutType='$OutType'";	
			}
			if($_GET['PackData']!='BOTH'){
			$PackData=$_GET['PackData'];
			$query.=" and PackingTypeID='$PackData'";	
			}
		/*if($_GET['Station']!='BOTH'){
			$Station=$_GET['Station'];
			$query.=" and UnitID='$Station'";	
			}*/
		 if($ColdstoreID!='BOTH'){
			$query.=" and CS.UnitID ='$ColdstoreID'";	
			}
		
		if($TenantID!='BOTH'){
         $query .=" and CS.StockHolderID='$TenantID'";
         }
			
	 $SqlOutStock="SELECT *,AGOU.TenantName as Unit,CS.Grade,CSP.PackingStyle,CSD.PostedDate,COALESCE(PoNo,'NA')AS PoNo FROM `CsStockOutDetails` as CSD 
	 left join CsStockData as CS  on CSD.StockID=CS.StockID
     left join CsBarCodes as CBC on CS.BarCodeID=CBC.BarCodeID 
     left join AdminPackingStyles as CSP on CSP.PCKID = CS.PackingStyleID 
	 left join ScrmPIVarietyList as AGO on CS.VarietyID=AGO.VarietyID 
	left join CsTenant as AGOU on CS.UnitID=AGOU.TenantID 
	 left join ScrmPoData as CSPOS on CSPOS.PoID = CS.PoID 

	where CSD.PostedDate between '$FromDate' and '$ToDate' $query  ";	
	$StmtOutStock=$iconn->query($SqlOutStock);
	
	
		
	$header='<tr>  

           		<th  align="LEFT"  width="100%">
			<b>	From Date:'.getFormatedDate($FROM_DATE).
				'<b style="color:white">hgbhnjnjhuy</b>
				To Date:'.getFormatedDate($TO_DATE).'</b></th>

               </tr>';
		
	
	
		$w1="11";

	 $data .='<table id="example1" border="1" cellspacing="0" cellpadding="5"" > 
	 
	 
	 <thead>
		
		
		<tr>
		
		<th width="'.$w1.'%">Date</th>
		<th width="'.$w1.'%">PO No</th>
		<th width="'.$w1.'%">Bar Code</th>
		<th width="'.$w1.'%">Unit</th>
		<th width="'.$w1.'%">Variety</th>
		<th width="">Grade</th>
		
		<th width="'.$w1.'%">Packing Style</th>
		<th width="'.$w1.'%">Out Type</th>
		<th width="'.$w1.'%">Remarks</th>
		<th width="'.$w1.'%">MCS</th>
		<th width="'.$w1.'%">KGS</th>
		
		
		</tr>
		</thead>
		
		<tbody>';
		
	$C=0;
$TotalWeight=0;
$TotalMcs=0;
$WEIGHT=0;
		
		
	 while($RowOutStock=$StmtOutStock->fetch_array()){
	
		
	$TotalSlabs=$RowOutStock['TotalSlabs'];
	$SlabWeight=$RowOutStock['SlabWeight'];
	$MFactor=$RowOutStock['MFactor'];
	$MCS=$RowOutStock['OutMcs'];


$WEIGHT=$TotalSlabs*$SlabWeight*$MFactor*$MCS;

	$TotalWeight=$TotalWeight+$WEIGHT;
		$TotalMcs=$TotalMcs+$RowOutStock['OutMcs'];
			
			
				$data .='
				<tr>
		
		
		
			<td >'.getFormatedDate($RowOutStock['PostedDate']).'</td>
		<td >'.$RowOutStock['PoNo'].'</td>
	<td >'.$RowOutStock['BarCodeValue'].'</td>
		<td >'.$RowOutStock['Unit'].'</td>
		<td >'.$RowOutStock['ShortName'].'</td>
		<td >'.$RowOutStock['Grade'].'</td>
		<td >'.$RowOutStock['PackingStyle'].'</td>
		<td >'.$RowOutStock['OutType'].'</td>
		<td >'.$RowOutStock['Remarks'].'</td>
		<td >'.$RowOutStock['OutMcs'].'</td>
		<td >'.$WEIGHT.'</td>
		

		
			</tr>';
$C++;
					  
}

$data .= '


<tr>

				</tr>

				
		<tr> 
           		

                <th  align="center"width="" >Total </th>
                <th  align="center"width="13%" >'.$C.'</th>
<th  align="center" ></th>
<th  align="center" ></th>
<th  align="center" ></th>
<th  align="center" ></th>
<th  align="center" ></th>

				<th  align="center" ></th>

                <th  align="center" width="13%">Total Mcs</th>

				<th  align="center"width="13%" >'.$TotalMcs.'  Mcs</th>
				<th  align="center"width="13%" >'.$TotalWeight.'  Kgs</th>


   
               </tr>';




					
		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>
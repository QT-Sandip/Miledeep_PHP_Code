<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



			if($_POST['Type']=='List'){
				getList($_POST['Variety']);
			}else if($_POST['Type']=='Update'){
				
				updateList($_POST['Variety'],$_POST['Grades']);
			}
			
			
			
			
					
			
function updateList($Variety,$Grades){
	global $iconn;
	
	$date = date_default_timezone_set('Asia/Kolkata');
	$today =  date('Y-m-d h:i:s');
	
	for($i=0; $i<count($Grades); $i++){
		
		$AryGradeUsd=explode('&',$Grades[$i]);
		$ItemGrade=$AryGradeUsd[0];
		$ItemUsd=$AryGradeUsd[1];
		
		$SqlItemCount="SELECT count(*) as Total from CsSalePrices where Variety='$Variety' and Grade='$ItemGrade'";		
	    $StmtItemCount=$iconn->query($SqlItemCount);
		$RowCount=$StmtItemCount->fetch_array();
		$NoRows=$RowCount['Total'];
		
		if($NoRows > 0){
		$oldSalePriceData = legacyAuditFetchRowById('CsSalePrices', 'ID', $NoRows, 'ColdStore');
		$SqlRaw="UPDATE `CsSalePrices` SET `Price`='$ItemUsd',`LastUpdated`='$today' 
					where Variety='$Variety' and Grade='$ItemGrade'";	
		}else{
		$SqlRaw="INSERT INTO `CsSalePrices`(`Variety`, `Grade`, `Price`, `LastUpdated`) 
		VALUES ('$Variety','$ItemGrade','$ItemUsd','$today')";
		}
		$StmtRaw=$iconn->query($SqlRaw);
		if($StmtRaw){
			if($NoRows > 0){
				legacyAuditLogUpdateById('CsSalePrices', 'Variety', $Variety, $oldSalePriceData, $_POST, ['Remarks' => 'Sale price updated'], 'ColdStore');
			}else{
				legacyAuditLogInsertById('CsSalePrices', 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'New sale price added'], 'ColdStore');
			}
		}	
	}
	getList($Variety);
}
			
function getList($Variety){
	
	global $iconn;	
	include '../../includes/Globals.php';

/*	  
	  $sql="SELECT CARTONS_NUMBER,GRADES as VALUE,FORM,LastUpdated, 
(SELECT Price from CsSalePrices WHERE Grade=CsStockData.GRADES AND Variety=CsStockData.FORM ) as Price 
FROM CsStockData 
LEFT JOIN CsSalePrices ON CsStockData.GRADES=CsSalePrices.Grade
left join CS_BAR_CODE_DATA on CS_BAR_CODE_DATA.BAR_CODE_ID=CsStockData.BAR_CODE_ID

WHERE FORM='$Variety' and CARTONS_NUMBER !=0
and (CS_BAR_CODE_DATA.BAR_CODE_STATUS =1 or CS_BAR_CODE_DATA.BAR_CODE_STATUS=2) 
GROUP BY GRADES  ORDER BY ABS(GRADES)";
*/

 $Sql="SELECT AvalMcs,GradeID as VALUE,VarietyID,LastUpdated, 
(SELECT Price from CsSalePrices WHERE Grade=CsStockData.GradeID AND Variety=CsStockData.VarietyID ) as Price 
FROM CsStockData LEFT JOIN CsSalePrices ON CsStockData.GradeID=CsSalePrices.Grade
left join CsBarCodes on CsBarCodes.BarCodeID=CsStockData.BarCodeID
WHERE VarietyID='$Variety' and AvalMcs !=0
and (CsBarCodes.BarCodeStatus =1 or CsBarCodes.BarCodeStatus=2) 
GROUP BY GradeID  ORDER BY ABS(GradeID)";
	  
	$ExecSalesData=$iconn->query($sql);
	
	
		 $data = '<table id="scroll-vertical-datatable" class="table table-striped table-bordered dt-responsive wrap" > 
	
		
		<thead>
		<tr>
		<th>Variety</th>
		<th>Grade</th>
		<th>USD/Lb</th>		
		<th>Last Update</th>		
		</tr></thead><tbody>
		
		
		';
		$count=1;
	 while($RowSalesData=$ExecSalesData->fetch_array()){
		 
		$data .='
		<tr>
		<td  id="variety'.$count.'">'.$Variety.'</td>				
		<td id="grade'.$count.'">'.$RowSalesData['VALUE'].'</td>	
		
		<td ><input type="number"  id="usd'.$count.'" value="'.$RowSalesData['Price'].'"></input></td>
		<td >'.getFormatedDate($RowSalesData['LastUpdated']).'</td>	
		</tr>';
	$count++;				  
}


		 $data .='</tbody></table>';
		echo $data .='<div class="box-footer" >
					
             <div align="center" class="form-group col-sm-12" >
				
             <button id="update" value="'.$count.'" class="btn btn-info" >Update</button>
              
				</div>
				
				</div>';

		
	}			



?>


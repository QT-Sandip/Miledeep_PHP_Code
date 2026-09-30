<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PoID=$_GET['PoID'];
$TYPE=$_GET['TYPE'];
$UNIT=$_GET['UNIT'];



if($PoID!='' && $TYPE =='LIST' ){
	$SqlPo="SELECT PoNo FROM `ScrmPoData` WHERE PoID='$PoID'";		
     $StmtPo=$iconn->query($SqlPo);
	$RowPo= $StmtPo->fetch_array();
	$PoNo=$RowPo['PoNo'];
	
		 $sql="SELECT cb.BarCodeID,ShortName,BarCodeValue,coalesce(PoNo,'NA') as PoNo,RowID,cr.BlockID,RackName,
			StockID,AvalMcs,BlockName,PackingTypeID,PackingStyle,Grade,
			ProductionDate, `ProductionTypeID`, ProductCategoryID,
			UnitID FROM `CsBarCodes` as cb 
			left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID 
			left join CsStoresBlockData as CBD on CBD.BlockID=cr.BlockID

			left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID
	        left join ScrmPIVarietyList as AGO on CPID.VarietyID=AGO.VarietyID 

			left join ScrmPoData as CPOS on CPOS.PoID  = CPID.PoID 
			left join AdminPackingStyles as CSP on CSP.PCKID = CPID.PackingStyleID			
			 WHERE CPID.PoID='$PoID' and UnitID='$UNIT'";


	
	 $stmt=$iconn->query($sql);
	

$w1="10";


$data=' <table id="example1"  border="1" cellspacing="0" cellpadding="3" >
				
				<thead>
				<tr border="1"><B> PO Number:'.$PoNo.'</B> 

           		</tr>
                 <tr>	  
        <th width="'.$w1.'%">ID</th>
		<!--<th width="'.$w1.'%">Bar Code</th>-->
		<th width="'.$w1.'%">Variety</th>
        <th width="'.$w1.'%">Grade</th>	
		<th width="'.$w1.'%">Mcs</th>
		<th width="'.$w1.'%">Packing Style</th>
		<!--<th width="'.$w1.'%">Packing Type</th>-->
		<th width="'.$w1.'%">Store</th>
		<th width="'.$w1.'%">Rack</th>
      </tr>               
                </thead>
                ';
	$C=0;
	$WEIGHT_TOTAL=0;
		

				
  while($RowData = $stmt->fetch_array()){
	

	
	//$BLOCK_NAME =explode(' ',$RowData['BLOCK_NAME']);
	
	$BID=$RowData['BarCodeID'];
	$BarcodeValue=$RowData['BarCodeValue'];
	$AvalMcs = $RowData['AvalMcs'];
	$VARITY = $RowData['ShortName'];
	$GRADE =$RowData['Grade'];
	$PACKING = $RowData['PackingStyle'];
	$PoNo = $RowData['PoNo'];
	$BlockName = $RowData['BlockName'];
	$RackName = $RowData['RackName'];
	$PackingType = $RowData['PackingTypeID'];
	
	$WEIGHT_TOTAL+=$CARTONS_NUMBER;


	

		$data .='<td width="'.$w1.'%">'.$BID .'</td>
		
		<!--<td width="'.$w1.'%">'. $BarcodeValue .'</td>-->
		<td width="'.$w1.'%">' .$VARITY. '</td>
		<td width="'.$w1.'%">'.$GRADE .'</td>
		<td width="'.$w1.'%">'.$CARTONS_NUMBER .'</td>
		<td width="'.$w1.'%">'.$PACKING. '</td>
		<!--<td width="'.$w1.'%">'.$PackingType. '</td>-->
		<td width="'.$w1.'%">'.$BLOCK_NAME[2]. '</td>
		<td width="'.$w1.'%">'.$RACK_NAME. '</td>

			</tr>';
	$C++;
				  
  			  
}


$data .= '<tr>

				</tr>

				
		<tr> 
           		

                <th  align="center"width="" >Total </th>
                <th  align="center"width="13%" >'.$C.'</th>
<th  align="center" ></th>

                <th  align="center" width="13%">Total Mcs</th>

				<th  align="center"width="13%" >'.$WEIGHT_TOTAL.'  Mcs</th>
				<th  align="center" ></th>
<th  align="center" ></th>
<!--<th  align="center" ></th>
<th  align="center" ></th>-->


   
               </tr>';


					
					echo $data .='</table>';


	
}
?>	
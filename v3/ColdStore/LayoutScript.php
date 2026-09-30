<?php 
$PageTitle="Store";
$PageTitleFolder="ColdStore";



include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$RackDataTable="CsStoresRackDataV1";
/*
$StoreID=1;
$FloorID=1;
$StoreName='S1';
$NoOfFloors=6;
$TotalPallets=120;
$EachRowPallets=12;
$EachColumnPallets=10;
*/
//step >1

	$sql = "SELECT * FROM `CsStoresBlockData`";
	$query = $iconn->query($sql);
	while($Row=$query-> fetch_assoc()){
		
	$StoreID=$Row['BlockID'];
	$StoreName=$Row['BlockID'];
	$NoOfFloors=$Row['Floors'];
	$TotalPallets=$Row['Rows']*$Row['Columns'];
	$EachRowPallets=$Row['Rows'];

	//createRacks($StoreID,$StoreName,$NoOfFloors,$TotalPallets,$EachRowPallets);

 

	}



$sql = "SELECT * FROM `CsStoresFloorDesignData`";
	$query = $iconn->query($sql);
	while($Row=$query-> fetch_assoc()){
   $StoreID=$Row['BlockID'];
	$StoreName=$Row['BlockID'];
	$RowPath=$Row['RowPath'];
	$ColumnPath=$Row['ColumnPath'];
	$FloorID=$Row['FloorID'];
	
	updatePathPosition($StoreID,$FloorID,$RowPath,$ColumnPath);
	
	}

	//UpdateRackNames($StoreID,$StoreName,$EachRowPallets);
	UpdateRackNames($StoreID,$FloorID,$EachRowPallets);

function createRacks($StoreID,$StoreName,$NoOfFloors,$TotalPallets,$EachRowPallets){
	
	global $RackDataTable,$iconn;
	
	
for($x=1; $x <= $NoOfFloors; $x++){
$j=1;
$c_no=1;	
for($i=0; $i<$TotalPallets; $i++){
	$rack_id=$i+1;
	if($j>$EachRowPallets){
		$j=1;
		$c_no++;
		
	}
	
	$r_name=$StoreName .' -'.$rack_id;
	
	
	$sql = "INSERT INTO `CsStoresRackDataV1` (`BlockID`, `FloorID`, 
	`RowNumber`, `ColumnNumber`, `RackID`, `Position`, 
	`RackName`, `RackBarcode`, `RowCapacityInTonnes`,
	`RowPresentStock`, `BarCodeID`, `Status`) VALUES 
	
	('$StoreID','$x','$j','$c_no','$rack_id',
	'1','$r_name','1','0','0','0','0')";
	echo '<br>';
	
	$qry = $iconn->query($sql);
	
	
	$j++;
	
}	
}

}


function updatePathPosition($BlockID,$FloorID,$RowPath,$ColumnPath){
	
	//$ColumnPath="3,8,13";
	//$RowPath="4";
	echo "Updatting Path....";
	
	global $RackDataTable,$iconn;
	
			$sqlUp = "update $RackDataTable set Position='1',Path='0' 
			where BlockID='$BlockID' and FloorID='$FloorID' ";
			$sqlUp = $iconn->query($sqlUp);
			
			$sqlUp = "update $RackDataTable set Position='0',Path='0' 
			where BlockID='$BlockID' and FloorID='$FloorID' and  RowNumber in ($RowPath)";
			$sqlUp = $iconn->query($sqlUp);
			
			 $sqlUp = "update $RackDataTable set Position='0',Path='1' 
			where BlockID='$BlockID' and FloorID='$FloorID' and  ColumnNumber in ($ColumnPath)"; 
			$sqlUp = $iconn->query($sqlUp);
}		 



//function UpdateRackNames($StoreID,$StoreName,$EachRowPallets){
function UpdateRackNames($StoreID,$FloorID,$EachRowPallets){

echo "UpdateRackNames ..";
global $RackDataTable,$iconn;





$firstrow=1;
$OrderSeries=6;
$OrderIndex=0;
for($x=1; $x<$EachRowPallets; $x++){
	
	$Section=1; 
	//$Store=$StoreName;
	$Store=$StoreID;
	//
	//S1,GROUND,1,1,A22-A,A22-B,A22-C,P,P,P,A13-A,A13-B,A13-C,A7-A,A7-B,A7-C,A1-A,A1-B
	if($firstrow == 1){
	$Series=["A","B","C"];
	}else if($firstrow > 1 && $firstrow < 4){
	$Series=["A","B","C"];	
	}else if($firstrow > 3 && $firstrow < 6){
	$Series=["A","B"];	
	}
	
	$SeriesIndex=0;
	 
	
	    
	$SectionIndex=1; 
	$Block=1;
	
	 echo $sql = "SELECT * FROM $RackDataTable WHERE BlockID=$StoreID and ColumnNumber='$x' 
	 AND FloorID='$SectionIndex' order by RowNumber,ColumnNumber asc"; 
	$query = $iconn->query($sql);
	
	while($row_coloumn = $query->fetch_assoc()){
		
			
	$ROW_ID=$row_coloumn['RowID']; 
	
	
	if($SeriesIndex > 2){
		$SeriesIndex=0;
		IF($row_coloumn['ColumnNumber'] > 2){
			
			if($row_coloumn['RowNumber'] > 7  && $row_coloumn['RowNumber'] < 11){
			echo $SectionIndex;
			$row_coloumn['RowNumber'];	 $SectionIndex=$SectionIndex+3;
			}else if($row_coloumn['RowNumber'] < 7){
			 $SectionIndex=$SectionIndex+6;
			} else {
				$SectionIndex=$SectionIndex+6;
			}
			 
		}ELSE { 
			$SectionIndex=$SectionIndex+6;
		}
		}
	
	
	$RACK_NAME=$Store.$OrderSeries.'-'.$SectionIndex.$Series[$SeriesIndex];
	//$RACK_NAME='test';

	  echo  $sqlUp = "update $RackDataTable set RackName='$RACK_NAME'  where RowID='$ROW_ID' ";
		$sqlUp = $iconn->query($sqlUp);
		
		$SeriesIndex++;
		

		
		
		
		if($SeriesIndex > 2){
		$SeriesIndex=0;
		IF($row_coloumn['ColumnNumber'] > 2){
			
			IF($row_coloumn['RowNumber'] > 5 && $row_coloumn['RowNumber'] < 8 ){
				$SectionIndex=$SectionIndex+3; 
			}ELSE IF($row_coloumn['RowNumber'] > 8){
				$SectionIndex=$SectionIndex+3;
			}ELSE{
				$SectionIndex=$SectionIndex+6;
			}
			
		}ELSE {
			$SectionIndex=$SectionIndex+6;
		}
		}
		
		
		
				
	}
	$OrderIndex++; 
	if($OrderIndex > 1){
		$OrderIndex=0;
		$OrderSeries--;
	}
	
	$firstrow++;
	if($firstrow==6){
		$firstrow=2;
	}
}
}

?>



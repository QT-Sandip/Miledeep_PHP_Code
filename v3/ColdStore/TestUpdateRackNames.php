<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


/*
$BlockID=1;
$MaxRowNumber=16;
$MaxColumnNumber=10;
$MaxPalletCount=110;
$MaxRacks=4;
$PalletCounteachrack=11;
$i=1;



	$ch = 'A';
	for($FloorID=1;$FloorID<=3;$FloorID++){
	$RackID=1;
	for($ColumnNumber=1;$ColumnNumber<=$MaxColumnNumber;$ColumnNumber++){
		$RackName='';
	

	for($RowNumber=1;$RowNumber<=$MaxRowNumber;$RowNumber++){
		$Name='B22';

		$Position=array(1,1,1,0,0,0,1,1,1,0,1,1,1,0,1,1);

		if($next_ch>1) { 
		
 $next_ch = chr(ord($ch) + 1);
}
 		 $next_ch = $ch++;
		
		
		$RackName=$ColumnNumber.'-'.$FloorID.$next_ch;
		
		
		IF($ColumnNumber==3 || $ColumnNumber==7){
			$Path=1;
		}
		else{
			$Path=0;
		}
		
		
	 $Position=$Position[$RowNumber-1]; 
	
	 $SqlInsert="INSERT INTO `CsStoresRackDataV1`(`BlockID`, `FloorID`, `RowNumber`, `ColumnNumber`, `RackID`, `Position`, `Path`, `RackName`, `RackBarcode`, `RowCapacityInTonnes`, `RowPresentStock`, `BarCodeID`, `Status`) VALUES ('$BlockID','$FloorID','$RowNumber','$ColumnNumber','$RackID','$Position','$Path','$RackName','1','0','0','0','0')";


$StmtInsert=$iconn->query($SqlInsert);

  $RackID++;
 
	}
	

if($StmtInsert){
echo 'Success';
}
}

}*/


/**Insert racknames**/
$StoreID=1;

$EachRowPallets=14+4;//store1
//$EachRowPallets=27+4;//store2
//$EachRowPallets=21+4;//store3
//$EachRowPallets=15+4;//store4

$RackDataTable='CsStoresRackData';


	
	$Section=1; 
	
	$Store=$StoreID;
	
	$SqlRackNames="SELECT * FROM `s1` ";//store1
	//$SqlRackNames="SELECT * FROM `s2` ";//store2
	//$SqlRackNames="SELECT * FROM `s3` ";//store3
	//$SqlRackNames="SELECT * FROM `s4` ";//store4
	$StmtRackNmes=$iconn->query($SqlRackNames);
	
	while($RowRackNames=$StmtRackNmes->fetch_array()){
		$array=explode(',',$RowRackNames['Column1']);
		$RowNumber= 1;
		for($i=4;$i<$EachRowPallets;$i++){
			
	 $RackName= $array[$i];
	 $ColumnNumber= $array[3];
	 $FloorID= $array[2];
	 
	 	 echo   $sqlUp = "update $RackDataTable set RackName='$RackName'  where BlockID=$StoreID 
		  and ColumnNumber='$ColumnNumber' 
	 AND FloorID='$FloorID' and RowNumber='$RowNumber' ";
		$StmtUp = $iconn->query($sqlUp);
		if($StmtUp){
		echo "success";
		}
		
		 $RowNumber++;
		
	}
	}
	 
	
	

/**Insert racknames**/


?>
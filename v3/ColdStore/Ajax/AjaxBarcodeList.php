
<?php
//ini_set( 'display_errors', 1 );
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$TYPE=$_POST['type'];
$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];
$TenantID=$_POST['TenantID'];

	if($TenantID ==''){
      $query='';
	}else{
	$query="and StockHolderID=$TenantID";	
		
	}
	  
	 $sql="SELECT ProductionDate,GROUP_CONCAT(BarCodeID) AS BarCodeIDS 
	 FROM `CsStockData` where  
	 Year(ProductionDate)='$YEAR' and Month(ProductionDate)='$MONTH' $query 
	 GROUP BY ProductionDate"; 
			 
			 
//echo  $sql; exit;
	 
	 $stmt=$iconn->query($sql);
	

$data='<div id="table-scroll"> 
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
                 <tr>
				 
        
        <th>Production Date</th>
		<th>Printed Barcodes </th>
		<th>UnUsed Barcodes</th>
        <th>Inward Barcodes</th>	
		<th>OutWard Barcodes</th>
		<th>Actions</th>
		</tr>               
        </thead>
        <tbody id >';
				

				
  while($row1 = $stmt->fetch_array()){
	
	$ProductionDate =$row1['ProductionDate'];	
	$BarCodeIDS =$row1['BarCodeIDS'];	
	
     $SqlStock="SELECT COUNT(*)  AS TOTAL,
(SELECT COUNT(*) FROM CsBarCodeList WHERE StockID IN($BarCodeIDS) AND 
Status='0') AS UNUSED,
(SELECT COUNT(*) FROM CsBarCodeList WHERE StockID IN($BarCodeIDS) AND 
Status='1') AS INWARDSTOCK,
(SELECT COUNT(*) FROM CsBarCodeList WHERE StockID IN($BarCodeIDS) AND 
Status='2') AS OUTSTOCK
FROM `CsBarCodeList` WHERE StockID IN($BarCodeIDS)";
//echo $SqlStock;
//exit;
$stmtStock=$iconn->query($SqlStock);
$RowStock= $stmtStock->fetch_array();

$TotalPrinted=$RowStock['TOTAL'];
$UnusedCodes=$RowStock['UNUSED'];
$InwardStock=$RowStock['INWARDSTOCK'];
$OutWardStock=$RowStock['OUTSTOCK'];
$ProductionDatee = date("d-m-Y", strtotime($ProductionDate));
		$data .='
		
		<tr >
		
		<td >'.$ProductionDatee.'</td>
		<td >'.$TotalPrinted.'</td> 
		<td >'.$UnusedCodes.'</td>
		<td >'.$InwardStock.'</td>	
		<td >'.$OutWardStock.'</td>
		
		
	
		<td> 
		
		
		<a target="_blank" href="ViewBarcodeList.php?id='.$ProductionDate.'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit Stock" data-original-title="Edit"
						 ><i class="mdi mdi-eye font-size-18"></i></a>
			
				
				</td>
			</tr>';
					  			  
	$RACK_NAME='';				  
}
					
					echo $data .='</tbody></table></div>';


	


?>	
<?php 
	
	
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

$QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
  $PsID=$_GET['PsID']; 
   $Type=$_GET['Type'];

  $MonthName = date("F", mktime(0, 0, 0, $Month, 10));
 
    if($PsID!=''){
	  $PsID=$_GET['PsID'];	  
	  $Query="and PurchaseStationID='$PsID'";
  }else{
	   $Query="";
  }
 if($Type==1){
			$Query= "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= "PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }
			
	 $SqlBillNames="SELECT RmLotID,PurchaseDate,LotNumber,PanNumber,FinancierName FROM 
	 RmLotDetatils AS RLD 
	 LEFT JOIN RmBillDetails as RBD ON RBD.LotID=RLD.RmLotID 
	 LEFT JOIN RmFinanciers as RMF ON RMF.ID=RBD.FinancierID 
	 where  $Query
	 order by RLD.PurchaseDate Asc,RLD.LotNumber ASC  ";	
	 $StmtBillNames=$iconn->query($SqlBillNames); 
	
	
	//echo $SqlBillNames;	 exit;
	
	$header='<tr>  

           		<th  align="LEFT"  width="100%">
				<B>MONTH:'.$MonthName.'</B></th>

               </tr>';
	$w1="8";

	 $data ='<table id="example1"  border="1" cellspacing="0" cellpadding="5" > 
		
		<thead>
		<tr>
		
		<th width="'.$w1.'%">Date</th>
		<th width="'.$w1.'%">Lot Number</th>
		<th width="'.$w1.'%">Bill Name</th>
		<th width="'.$w1.'%">PAN Number</th>
		<th width="'.$w1.'%">TDS %</th>
		<th width="'.$w1.'%">Bill Amount</th>
		<th width="'.$w1.'%">TDS Amount</th>
		<th width="'.$w1.'%">Sub Total</th>
		
		</tr></thead><tbody>
		
		
		';
		
		$C=0;
$WEIGHT_TOTAL=0;

	 while($RowBillNames=$StmtBillNames->fetch_array()){
	
	$LotID=$RowBillNames['RmLotID'];
	
	$BillDetails=getBillAmount($LotID);
	
	$BillAmountTotal+=$BillDetails['BillAmount'];
	$TdsAmountTotal+=$BillDetails['TdsAmount'];
	$SubTotalTotal+=$BillDetails['SubTotal'];
	
	
	
	//print_r($BillDetails);
	$data .='
				<tr>
		
		
		
		<td >'.getFormatedDate($RowBillNames['PurchaseDate']).'</td>
		<td >'.$RowBillNames['LotNumber'].'</td>
		<td >'.$RowBillNames['FinancierName'].'</td>
		<td >'.$RowBillNames['PanNumber'].'</td>
		<td >'.$BillDetails['TdsPerc'].'</td>
		<td >'.$BillDetails['BillAmount'].'</td>
		<td >'.$BillDetails['TdsAmount'].'</td>
		<td >'.$BillDetails['SubTotal'].'</td>
		
	
			</tr>';
$C++;
					  
}

$data .= '


<tr>

				</tr>

				
		<tr> 
           		

                <th  align="center"width="" >Total </th>
                <th  align="center"width="13%" >'.$C.'Bills</th>
				<th  align="center" ></th>
				<th  align="center" ></th>
				<th  align="center" ></th>

				<th  align="center"width="13%" >'.$BillAmountTotal.' </th>
				<th  align="center"width="13%" >'.$TdsAmountTotal.'  </th>
				<th  align="center"width="13%" >'.round($SubTotalTotal,2).'  </th>


   
               </tr>';







		echo $data .='</tbody></table>';
		

	
function getBillAmount($LotID){
global $iconn;		
		
			
 $HLGQuery = "SELECT 
 RLD.PurchaseDate,
 RLD.LotNumber,
 RLD.PurchaseStationID,
 RLD.FarmAddress,
 RLD.PurchaseWeight AS F_WEIGHT,
 RLD.PurchaseCount AS F_COUNT,
 RUL.UserName AS AgentName,
 RGL.UserName AS GraderName,
 RFL.FinancierName,
 RFL.PanNumber,
 RBD.Deductions ,
 RBD.Remarks ,
 RBBP.Weight ,
 RBBP.Rate ,
 RBBP.Count AS CompanyCount 
  
 FROM `RmLotDetatils` AS RLD
 LEFT JOIN RmUsersList as RUL ON RUL.ID=RLD.AgentID
 LEFT JOIN RmUsersList as RGL ON RGL.ID=RLD.GraderID
 LEFT JOIN RmBillDetails as RBD on RLD.RmLotID = RBD.LotID 
 LEFT JOIN RmBillBreakup as RBBP on RLD.RmLotID = RBBP.LotID 
 LEFT JOIN RmFinanciers as RFL on RFL.ID = RBD.FinancierID 
 WHERE 
 RLD.RmLotID = '$LotID'";
 
 //echo $HLGQuery;

$HLGexec=$iconn->query($HLGQuery);
		
			$SqlDefectsDetails="SELECT * FROM `RmDefectsDetails` where LotID='$LotID'";
			$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);


	        $SqlQcRmDetails="SELECT * from QcRmOrgnDetails where LotID='$LotID'";
			$StmtQcRmDetails=$iconn->query($SqlQcRmDetails);
			$RowQcRmDetails = $StmtQcRmDetails->fetch_assoc();
			
			$SqlHonGrading="SELECT * FROM `PpcHonGradingBreakup` WHERE LotID='$LotID'";
			$StmtHonGrading=$iconn->query($SqlHonGrading);
			$RowHonGrading=$StmtHonGrading->fetch_array();
	
	$HLGRep = $HLGexec->fetch_assoc();
	
	$AccDeductionsKg= $HLGRep['Deductions'];
	$CompanyWeight = $HLGRep['Weight'];
	$RATE =$HLGRep['Rate'];
	$BillPanNumber =$HLGRep['PanNumber'];
	
	$PurchaseAmount=($CompanyWeight * $RATE);
	
	
	while($RowDefectsDetails=$StmtDefectsDetails->fetch_array()){
						$value[]=$RowDefectsDetails;
				 $DefectsAmount=$DefectsAmount+($value['DefectWeight'] * $value['RATE']);
	            
				}
	
	
			if($BillPanNumber !='' && strlen($BillPanNumber) ==10){
					  $TDSperc='0.1%'; 
					 
				   }else{
					   $TDSperc='5%';
					   $BillPanNumber='NA';
					 
				   }
				   
				    
			$HonUsed=$RowHonGrading['HonWeight'];
			$HonUsedRate=$RowHonGrading['HON_PACKED_RATE'];
			$HonUsedCount=$RowHonGrading['HonCount'];
			
			$HonUsedAmount=$HonUsedRate*$HonUsed;
			
			$TotalWeight=$CompanyWeight+$HonUsed;
			
			$LabDeduction= $RowQcRmDetails['DEDUCTIONS']*$TotalWeight;
			$AccDeductions= $AccDeductionsKg*$TotalWeight;
	
	$BillTotal=$PurchaseAmount + $DefectsAmount +$HonUsedAmount + $AccDeductions -$LabDeduction;
	$TdsAmount=($BillTotal*$TDSperc)/100;
	
	$BillData['TdsPerc']=$TDSperc;
	$BillData['BillAmount']=$BillTotal;
	$BillData['TdsAmount']=round($TdsAmount,2);
	$BillData['SubTotal']=round($BillTotal-$TdsAmount,2);
	
	return $BillData;
		
	}
	
?>
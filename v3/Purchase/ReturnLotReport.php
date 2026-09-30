<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php"); 
 
 
   $QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
 
  $Type=$_GET['Type'];
 $Month=$_GET['Month'];
 $Year=$_GET['Year'];
 $PsID=$_GET['PsID'];
 
	if($Month!=''){
		

			
			
			$Query='';
			
			if($PsID!=''){
			$Query.=" and PurchaseStationID='$PsID'";	
			}
			else{
			$Query.='';	
			}
			
			if($Type==1){
			$Query= "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= "PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }
			
			
			
	 $SqlReturnLots="	SELECT * FROM `RmLotDetatils` as RLD
      LEFT JOIN RmUsersList as RMUL on RMUL.ID=RLD.AgentID	 
	 LEFT JOIN RmPurchaseCategory RPC on RPC.CatID=RLD.PurchaseCategoryID LEFT JOIN
	 QcRmAbReport as QRAR on QRAR.LotID=RLD.RmLotID 
	 LEFT JOIN QcABOptions as QAO on QAO.OptID=QRAR.AbStatus 

	 where $Query and LotStatus='2'   order by PurchaseDate asc";	
	$StmtReturnLots=$iconn->query($SqlReturnLots);
	

	//echo $SqlReturnLots;	 exit;
	
	$header='<tr>  

           		<th  align="LEFT"  width="100%">
				<B>From Date:'.getFormatedDate($StartDate).
				'<b style="color:white">hgbhnjnjhuy</b>
				To Date:'.getFormatedDate($EndDate).'</B></th>

               </tr>';
	$w1="9";

	 $data ='<table id="example1"  border="1" cellspacing="0" cellpadding="5" > 
	
		
		<thead>
		<tr>
		
		<th width="'.$w1.'%">S.No</th>
		<th width="'.$w1.'%">Purchase Date</th>
		<th width="'.$w1.'%">Lot No</th>
		<th width="'.$w1.'%">Purchase Type</th>
		<th width="'.$w1.'%">Count</th>
		<th width="">Weight</th>	
		<th width="'.$w1.'%">Address</th>
		<th width="">Ab Status</th>
		<th width="'.$w1.'%">Agent</th>	
		</tr></thead><tbody>
		
		
		';
		
		
		$C=0;
$WEIGHT_TOTAL=0;

	 while($RowReturnLots=$StmtReturnLots->fetch_array()){
		 $AbStatus=$RowReturnLots['Name'];
		 $AgentName=$RowReturnLots['UserName'];
		 /*if($RowReturnLots['ANTI_BIOTIC_STATUS']==1){
			$AbStatus="Reject";
		}else if($RowReturnLots['ANTI_BIOTIC_STATUS']==2){
			$AbStatus="Accepted";
		}*/
				$data .='
				<tr>
		
		
		<td >'.++$C.'</td>
		<td >'.getFormatedDate($RowReturnLots['PurchaseDate']).'</td>
		<td >'.$RowReturnLots['LotNumber'].'</td>
		<td >'.$RowReturnLots['PurchaseType'].'</td>
		<td >'.$RowReturnLots['PurchaseCount'].'</td>
		<td >'.$RowReturnLots['PurchaseWeight'].'</td>
		<td >'.$RowReturnLots['FarmAddress'].'</td>
		<td >'.$AbStatus.'</td>
		
		<td >'.$AgentName.'</td>
		
	
			</tr>';
//$C++;
					  
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
<th  align="center" ></th>

                


   
               </tr>';







		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>
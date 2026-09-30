<?php 
 //ini_set('display_errors', 1);
	include '../includes/config.php';
	include '../includes/Globals.php';

	if(isset($_GET['to_date'])){
		
			$FROM_DATE=$_GET['from_date'];
			$TO_DATE=$_GET['to_date'].' 23:59:59';
			
			$query='';
			
			if($_GET['UNIT']!='BOTH'){
			$UNIT=$_GET['UNIT'];
			$query.=" and JME_MOURYA='$UNIT'";	
			}
			if($_GET['PACKING_TYPE']!='BOTH'){
			$PACKING_TYPE=$_GET['PACKING_TYPE'];
			$query.=" and DUMMY_MASTER='$PACKING_TYPE'";	
			}
			if($_GET['F_TYPE']!='BOTH'){
				 $F_TYPE=$_GET['F_TYPE'];
			$query.=" and TYPE='$F_TYPE'";	
			}
	 $sql="SELECT *,IM.COUNT AS COUNT FROM `inputmaterial_details` 
	 AS IM left join RM_BILL_DETAILS AS RBD ON RBD.LOT_ID=IM.id
	 where PURCHASE_DATE BETWEEN '$FROM_DATE' and '$TO_DATE' and WEIGHT IS NULL and IM.STATUS!=1 order by PURCHASE_DATE asc";	
	$stmt_pid=$iconn->query($sql);
	
	
	//echo $sql;	 exit;
	
	$header='<tr>  

           		<th  align="LEFT"  width="100%">
				<B>From Date:'.getFormatedDate($FROM_DATE).
				'<b style="color:white">hgbhnjnjhuy</b>
				To Date:'.getFormatedDate($TO_DATE).'</B></th>

               </tr>';
	$w1="9";

	 $data ='<table id="example1"  border="1" cellspacing="0" cellpadding="5" > 
	
		
		<thead>
		<tr>
		
		<th width="'.$w1.'%">Sno</th>
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

	 while($row_csrd=$stmt_pid->fetch_array()){
		 if($row_csrd['ANTI_BIOTIC_STATUS']==1){
			$AbStatus="Reject";
		}else if($row_csrd['ANTI_BIOTIC_STATUS']==2){
			$AbStatus="Accepted";
		}
				$data .='
				<tr>
		
		
		<td >'.++$C.'</td>
		<td >'.getFormatedDate($row_csrd['PURCHASE_DATE']).'</td>
		<td >'.$row_csrd['LOT_NO'].'</td>
		<td >'.$row_csrd['PURCHASE_TYPE'].'</td>
		<td >'.$row_csrd['COUNT'].'</td>
		<td >'.$row_csrd['WEIGHT_IN_KGS'].'</td>
		<td >'.$row_csrd['ADDRESS'].'</td>
		<td >'.$AbStatus.'</td>
		
		<td >'.$row_csrd['AGENT_NAME'].'</td>
		
	
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
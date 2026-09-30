<?php
session_start();
include "../includes/config.php";

$M=$_GET['M'];
$Y=$_GET['Y'];

$FROM_DATE=$Y.'-'.$M.'-01';
$TO_DATE=$Y.'-'.$M.'-30 23:59:59';

$UNIT=$_GET['U'];



//UMA


		
 $aval_hl_reports_sql = "select * from PPC_HOSO_W_DATA as HW left join inputmaterial_details AS 
	IM ON HW.LOT_ID=IM.id WHERE HW.DATA_TYPE='Headless Grading' AND IM.SAMPLE_TYPE='' 
	AND IM.PURCHASE_DATE BETWEEN '$FROM_DATE' and '$TO_DATE' and IM.UNIT='$UNIT' order by 
	IM.PURCHASE_DATE,IM.LOT_NO ASC";
	$stmt_hl=$iconn->query($aval_hl_reports_sql);
	
	 $BILLED_LOTS=$stmt_hl->num_rows;	
	


	
	 $table=getCostingTable($iconn,$EXCHANGE_RATE,$stmt_hl,$BILLED_LOTS,$Expanse,$DEPB);
	
	
	echo $table;
	
	

	function getCostingTable($iconn,$EXCHANGE_RATE,$hl_stmt,$BILLED_LOTS,$Expanse,$DEPB){
		
		$sql_po_date="SELECT * FROM `INVOICE_DATA`WHERE PO_DATE='2019-07-09'";
		$sql_po_date="SELECT * FROM `INVOICE_DATA` WHERE YEAR='2019'";
$stmt_po_date=$iconn->query($sql_po_date);
	
	
	//j
$c=1+$start_from;
				$cell_size=100-30;
				$cell_size=$cell_size/count($data);
			
			
			 
			 $count=1;
			 
	$text=4;
	$text1=2;
	
	
	
	$content .= '<table border="1" cellspacing="0" cellpadding="1"> 
				  <colgroup span="3"></colgroup>
				  <rowgroup span="8"></colgroup>

			  <tr> 
                <th  align="CENTER" width="5%" ><font size="'.$text.'"> <B>LOT NO</B></font></th>
							   

				
                <th  align="CENTER" width="5%"  ><font size="'.$text.'"> <B>BUYER NAME </B></font></th>



              <th  align="CENTER" width="5%" ><font size="'.$text.'"> <B>P.O NO</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>DESTINATION</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>CONTAINER NO</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>SEAL NO</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>INVOICE NO. &DATE</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>TRACEBIITY</B></font></th>
           		<th  align="center" width="5%"><font size="'.$text.'">DAY CODE</font></th>
				 <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>PRODUCT NAME</B></font></th>

				
				<th  align="CENTER" width="5%"><font size="'.$text.'"> <B>GRADE </B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>MCS</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>NO OF MASTER CARTONS PACKED</B></font></th>
				
				
</tr>';

while( $row_po_date=$stmt_po_date->fetch_array()){
		$id=$row_po_date['ID'];
		$PO_NO=$row_po_date['PO_NUM'];		
		$INVOICE_NUMBER	=$row_po_date['INVOICE_NUMBER'];		
		$INTERNAL_LOT_NUM=$row_po_date['INTERNAL_LOT_NUM'];		
		$DESTINATION_COUNTRY=$row_po_date['DESTINATION_COUNTRY'];		
		$PRODUCT_NAME=$row_po_date['SCIENTIFIC_NAME'];		
		$BUYER_NAME=$row_po_date['NAME_ADD_OF_CONSIGNEE'];		
		$CONSIGNMENT_EXPORTED_TO=$row_po_date['CONSIGNMENT_EXPORTED_TO'];	
$CONSIGNMENT_EXPORTED_TO=explode(',',$CONSIGNMENT_EXPORTED_TO);
$CONTAINER_NUMBER=$CONSIGNMENT_EXPORTED_TO[1];
$SEAL_NUMBER=$CONSIGNMENT_EXPORTED_TO[3];
	
 $sql_pl="SELECT PO_VARIETY_LIST.SHORT_NAME FROM `PO_VARIETY_LIST` JOIN  CODE_LIST_CREATE ON 
 PO_VARIETY_LIST.ID= CODE_LIST_CREATE.FULL_NAME WHERE CODE_LIST_CREATE.INV_ID='$id'"; 


$stmt_pl=$iconn->query($sql_pl);
	$data1=array();

while($row_pl=$stmt_pl->fetch_array()){
		$data1[]=$row_pl;

}

	
$sql_cl="select * from CODE_LIST_CREATE where INV_ID='$id'";
$stmt_cl=$iconn->query($sql_cl);



	$data=array();
while( $row_cl=$stmt_cl->fetch_array()){
	$data[]=$row_cl;
}	
	

				$content .= '<tr>
				<td  align="CENTER" width="5%"   ><font size="'.$text.'">'.$INTERNAL_LOT_NUM.'</font></td>
				<td  align="CENTER" width="5%" ><font size="'.$text.'">'.$BUYER_NAME.'</font></td>
				<td  align="CENTER" width="5%" ><font size="'.$text.'">'.$PO_NO.'</font></td>
                <td  align="CENTER" width="5%" ><font size="'.$text.'">'.$DESTINATION_COUNTRY.'</font></td>
                <td  align="CENTER" width="5%" ><font size="'.$text.'">'.$CONTAINER_NUMBER.'</font></td>
                <td  align="CENTER" width="5%" ><font size="'.$text.'">'.$SEAL_NUMBER.'</B></font></td>
                <td  align="CENTER" width="5%" ><font size="'.$text.'">'.$INVOICE_NUMBER.'</font></td>
				<td  align="CENTER" width="5%" >
				<table border="0" cellspacing="0" cellpadding="0">';

				foreach($data as $value){
				$content .='<tr>
				<td  align="CENTER"  width="5%" colspan=4  ><font size="'.$text.'">'.$value['FARM_REG_ID'].'</font></td>
			
				</tr>';
		}
				$content .='</table></td>
				
				<td  align="CENTER" width="5%" >
				<table border="0" cellspacing="0" cellpadding="0">';

				foreach($data as $value){
				$content .='<tr>
				<td  align="CENTER" width="5%" colspan=4  ><font size="'.$text.'">'.$value['DAY_CODE'].'</font></td>
			
				</tr>';
		}
		
		$content .='</table></td>
				
				<td  align="CENTER" width="5%" >
				<table border="0" cellspacing="0" cellpadding="0">';

				foreach($data1 as $value1){
				$content .='<tr>
				<td  align="CENTER" width="5%" colspan=4  ><font size="'.$text.'">'.$value1['SHORT_NAME'].'</font></td>
			
				</tr>';
		}
		
		
		
		
		
				$content .='</table></td>
				
				
				<td  align="CENTER" width="5%" >
				<table border="0" cellspacing="0" cellpadding="0">';

				foreach($data as $value){
				$content .='<tr>
				<td  align="CENTER" width="5%" colspan=4 ><font size="'.$text.'">'.$value['GRADES'].'</font></td>
			
				</tr>';
		}
				$content .='</table></td>
				
				
				<td  align="CENTER" width="5%" >
				<table border="0" cellspacing="0" cellpadding="0">';

				foreach($data as $value){
				$content .='<tr>
				<td  align="CENTER"  width="5%" colspan=4  ><font size="'.$text.'">'.$value['MCS'].'</font></td>
			
				</tr>';
		}
				$content .='</table></td>
				
				<td  align="CENTER" width="5%" >
				<table border="0" cellspacing="0" cellpadding="0">';

				foreach($data as $value){
				$content .='<tr>
				<td  align="CENTER" width="5%" colspan=4   ><font size="'.$text.'">'.$value['T_O_MCS'].'</font></td>
			
				</tr>';
		}
				$content .='</table></td>
				
				
				
				
				
				
				</tr>';
				
	}

														$content .='</table>'; 		

	
	return $content;

	
	}
	
	 
?>



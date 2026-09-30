<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";
 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

  //$date=$_GET['date']; 
$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
   

 //$month_name= date("F", strtotime($date)); 
 $month_name= date("F", strtotime($from_date));
$content1 .= '
			  			  <h3 align="center">IQF Packing Report - '.$month_name.'</h3>';
echo $content1; 


	 $table_data=getP_TABLE($iconn,$B_TYPE,$from_date,$to_date);
echo $table_data;

	//$pdf->writeHTML($table_data);
  
 
$file_name='IQF PACKING -'.$from_date.'-'.$to_date.'.pdf';

//$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$B_TYPE,$from_date,$to_date){

	/* $date_end=$date.'-31 23:59:59';  
	 $date=$date.'-01'; */
	
	//$date_end=$date.' 23:59:59';  
	$date_end=$to_date.' 23:59:59';  
	
	
	if($B_TYPE == 1){ 
	//$B_TYPE == 1;
	//$query.="and imd.id='0'";
}
 	$query.="and imd.id='0'";
	 $sql_lots="SELECT * from inputmaterial_details as imd left join
	CS_PALLET_INPUT_DATA as SCK on imd.id=SCK.PURCH_LOT_NUM where SCK.DATE_TIME 
	BETWEEN '$from_date' AND '$date_end' $query  order by PURCHASE_DATE,LOT_NO asc"; 
			  
			   $sql_lots;
			  
			  $stmt_lots=$iconn->query($sql_lots);
			 				  $Count=mysqli_num_rows($stmt_lots);
  
 
	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=120/15;
			  $width_s=$width-5;
			  $width_w=$width+5;
              
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  

           		

                <th  align="center"  width="'.$width_s.'%"><b>S No.</b></th>              
				 <th  align="center" width="'.$width.'%" ><b> Tub No</b></th>
				 <th  align="center" width="'.$width.'%" > <b>ID No</b></th>
				   <th  align="center"  width="'.$width.'%"><b>Rm Date</b></th>			
				   <th  align="center"  width="'.$width.'%"><b>IQF Date</b></th>			
				 <th  align="center" width="'.$width.'%" ><b>Item</b></th>
                <th  align="center"  width="'.$width.'%" ><b>Count</b> </th>
                <th  align="center"  width="'.$width.'%" ><b>Grade</b></th>
                <th  align="center"  width="'.$width.'%" ><b>Brand</b> </th>
                <th  align="center"  width="'.$width.'%" ><b>Category</b> </th>
                <th  align="center"  width="'.$width.'%" ><b>Cartons</b> </th>
                <th  align="center"  width="'.$width.'%" ><b>Packing</b></th>
               
                <th  align="center"  width="'.$width.'%" ><b>Net Qty </b></th>
                <th  align="center"  width="'.$width.'%" ><b>Remarks </b></th>
				
    
               </tr> 
			   
			  '; 
			  
			$TotalWeight=0;

		//	 
		while($r_lots = $stmt_lots->fetch_array()){
			$W=$r_lots['CARTONS_NUMBER']*$r_lots['TOTAL_SLABS'] * $r_lots['SLAB_WEIGHT'];
		
		if($r_lots['UOM']=='Lbs'){
		$N_W_KG=round(( $W * 454.4)/1000,2);
		}else if($r_lots['UOM']=='Grams'){
		$N_W_KG=round(($W )/1000,2);
		}else if($r_lots['UOM']=='Kgs'){
		$N_W_KG=round(($W ),2);
		}
		
		$PURCHZ_DATE=$r_lots['PURCHASE_DATE'];
		$PURCHASE_DT = date("d-m-Y", strtotime($PURCHZ_DATE));
		$IQF_DT = date("d-m-Y", strtotime($r_lots['DATE_TIME']));
		$IqfDate=explode('-',$IQF_DT);
		$ID=$r_lots['LOT_NO'];
		IF($ID =='-'){
			
		$ID=$IqfDate[1].$IqfDate[0];
		}
 
				    $content .= 
					
					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['TUB_NO'].'</th>
				 <th  align="center" width="'.$width.'%">'.$ID.'</th>
				 <th  align="center" width="'.$width.'%">'.$PURCHASE_DT.'</th>
				 <th  align="center" width="'.$width.'%">'.$IQF_DT.'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['FORM'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['COUNT'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['GRADES'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['BRAND'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['CATEGORY'].'</th>
				 
				 <th  align="center" width="'.$width.'%">'.$r_lots['CARTONS_NUMBER'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['PACKING_STYLE'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['NET_WT'].' Kg</th>
				 
				 <th  align="center" width="'.$width.'%">'.$r_lots['REMARKS'].' </th>
				 
				 </tr>';

 $S_NO++;	
$TotalWeight +=$r_lots['NET_WT'];
 
}
		  
   $content .= 
					
		 '<tr>
		 			   <th  align="center" width=""><b></b> </th>

               <th  align="center" width=""><b>Total Lots</b></th>				 
			   <th  align="center" width=""><b>'.$Count.'</b> </th>
			   <th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				<th  align="center" width=""><b></b> </th>
				 <th  align="center" width=""><b>Total Weight</b></th>				 
			   <th  align="left" width=""><b>'.$TotalWeight .' Kgs</b> </th>
							   <th  align="center" width=""><b></b> </th>
 
		 </tr>';	  
      

		$content .= '</table>';


	return $content;	

}

?>
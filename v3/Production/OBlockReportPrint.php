<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";
 $B_TYPE=$_GET['TYPE'];
 
 $LOT_ID=$_GET['ID'];

  //$date=$_GET['date']; 
$from_date=$_GET['from_dates']; 
  $to_date=$_GET['to_date']; 
   

 //$month_name= date("F", strtotime($date)); 
 $month_name= date("F", strtotime($from_date)); 
 $content1 .= '
			  			  <h3 align="center">Block Packing Report - '.$month_name.'</h3>';
echo $content1;

/*require_once('../tcpdf/tcpdf.php'); 
	
    $pdf->SetFont('helvetica', '', 11);  */

	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$B_TYPE,$from_date,$to_date);

echo $table_data;
	//$pdf->writeHTML($table_data);
  
 
$file_name='Block Packing -'.$from_date.'-'.$to_date.'.pdf';

//$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$B_TYPE,$from_date,$to_date){
	global $B_TYPE,$from_date;
	/* $date_end=$date.'-31 23:59:59';  
	 $date=$date.'-01'; */
	
	//$date_end=$date.' 23:59:59';  
	$date_end=$to_date.' 23:59:59';  
	
	
	if($B_TYPE == 1){ 
	//$B_TYPE == 1;
	$query.="and imd.id='0'";
}
 	
$SqlBlockData="SELECT  RLD.LotNumber,RLD.PurchaseDate,PPD.TubNo,PPD.ProductionDate,PPD.Variety,PPD.Count,
PPD.Grade,PPD.Brand,PPD.SLAB_WEIGHT,PPD.NetWeight,PackingStyle,PPD.Remarks from RmLotDetatils as RLD 
left join ProdProductionData as PPD on RLD.RmLotID=PPD.LotID 
where PPD.ProductionDate BETWEEN'$from_date' AND 
'$date_end' and PPD.FreezingType='BLOCK' order by RLD.PurchaseDate,RLD.LotNumber asc";  
			  
			
			$SqlBlockData;
			  
			  $stmt_lots=$iconn->query($SqlBlockData);
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
				 <th  align="center" width="'.$width.'%" ><b>Tub No</b></th>
				 <th  align="center" width="'.$width.'%" ><b> Id No</b></th>
				   <th  align="center"  width="'.$width.'%"><b>RM Date</b></th>			
				   <th  align="center"  width="'.$width.'%"><b> Date</b></th>			
				 <th  align="center" width="'.$width.'%" ><b>Item</b></th>
                <th  align="center"  width="'.$width.'%" ><b>Count</b></th>
                <th  align="center"  width="'.$width.'%" ><b>Grade </b></th>
                <th  align="center"  width="'.$width.'%" ><b>Brand </b></th>
                <th  align="center"  width="'.$width.'%" ><b>Slabs </b></th>
                <th  align="center"  width="'.$width.'%" ><b>Packing</b></th>
               
                <th  align="center"  width="'.$width.'%" ><b>Net Qty</b></th>
                <th  align="center"  width="'.$width.'%" ><b>Remarks</b> </th>
				
    
               </tr> 
			   
			  '; 
			  
	$TotalWeight=0;

		while($r_lots = $stmt_lots->fetch_array()){
			
		$W=$r_lots['Mcs']*$r_lots['TOTAL_SLABS'] * $r_lots['SLAB_WEIGHT'];
		
		if($r_lots['UOM']=='Lbs'){
		$N_W_KG=round(( $W * 454.4)/1000,2);
		}else if($r_lots['UOM']=='Grams'){
		$N_W_KG=round(($W )/1000,2);
		}else if($r_lots['UOM']=='Kgs'){
		$N_W_KG=round(($W ),2);
		}
		
	
		$PurchaseDate = date("d-m-Y", strtotime($r_lots['PurchaseDate']));
		$ProductionDate = date("d-m-Y", strtotime($r_lots['ProductionDate']));
		
				    $content .= 
					
					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['TubNo'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['LotNumber'].'</th>
				 <th  align="center" width="'.$width.'%">'.$PurchaseDate.'</th>
				 <th  align="center" width="'.$width.'%">'.$ProductionDate.'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Variety'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Count'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Grade'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Brand'].'</th>				 
				 <th  align="center" width="'.$width.'%">'.$r_lots['Mcs'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['PackingStyle'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['NetWeight'].' Kg</th>				 
				 <th  align="center" width="'.$width.'%">'.$r_lots['Remarks'].' </th>
				 
				 </tr>';

 $S_NO++;
$TotalWeight+= $r_lots['NetWeight'];
}
		  
    $content .= 
					
		 '<tr>			   <th  align="center" width=""><b></b> </th>

               <th  align="center" width=""><b>Total Lots</b></th>				 
			   <th  align="center" width=""><b>'.$Count.'</b> </th>
			   <th  align="center" width=""><b></b> </th>
			   <th  align="center" width=""><b></b> </th>
			   <th  align="center" width=""><b></b> </th>
			   <th  align="center" width=""><b></b> </th>
			   <th  align="center" width=""><b></b> </th>
			   <th  align="center" width=""><b></b> </th>
			   <th  align="center" width=""><b></b> </th>
				 <th  align="center" width=""><b>Total Weight</b></th>				 
			   <th  align="left" width=""><b>'.$TotalWeight .' Kgs</b></th>
							   <th  align="center" width=""><b></b> </th>
 
		 </tr>';

		$content .= '</table>';


	return $content;	

}

?>
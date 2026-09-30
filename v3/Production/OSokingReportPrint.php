<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";

  //$date=$_GET['date'];
$from_date=$_GET['from_dates'];
  $to_date=$_GET['to_date'];


 //$month_name= date("F", strtotime($date));
 $month_name= date("F", strtotime($from_date));
$content1 .= '
			  			  <h3 align="center"> Soaking Report-('.getFormatedDate($from_date).' To '.getFormatedDate($to_date).')</h3>';
echo $content1;

	 $table_data=getP_TABLE($iconn,$from_date,$to_date);

echo $table_data;
	//$pdf->writeHTML($table_data);


$file_name='SOACKING Report -'.$from_date.'-'.$to_date.'.pdf';

//$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$from_date,$to_date){
	
	$date_end=$to_date.' 23:59:59';



$sql_lots="SELECT DISTINCT imd.RmReferenceID, imd.LotNumber, imd.PurchaseDate, imd.PurchaseCount, 
SCK.TubNumber, SCK.SoakingDate,SCK.LotID, SCK.Variety, SCK.SoakInQty, SCK.SoakInCount,
SCK.SoakOutCount, SCK.SoakOutQty, SCK.Destination, SCK.TimePeriod, SCK.PostedTimeDate
from RmLotDetatils as imd 
left join ProdSoakingDetails as SCK on imd.RmLotID=SCK.LotID
where `SoakingDate` BETWEEN  '$from_date' AND '$date_end'
order by SoakingDate asc,LotNumber asc,SCK.Variety asc"; 



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



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
				 <th  align="center" width="'.$width.'%" ><B> Tub No </B></th>
				 <th  align="center" width="'.$width.'%" ><B> Id No </B></th>
				   <th  align="center"  width="'.$width.'%"><B>Rm Date</B></th>
				   <th  align="center"  width="'.$width.'%"><B>Soaking Date </B></th>
				 <th  align="center" width="'.$width.'%" ><B>Variety</B></th>
                <th  align="center"  width="'.$width.'%" ><B>Grade </B></th>

                <th  align="center"  width="'.$width.'%" ><B>In Quantity </B></th>
                <th  align="center" width="'.$width.'%"><B>In Count </B></th>
				 <th  align="center" width="'.$width.'%" ><B> Time Period</B></th>
				<th  align="center" width="'.$width.'%" ><B>Out Count</B></th>
				<th  align="center" width="'.$width.'%" ><B>Dest</B></th>
				<th  align="center" width="'.$width.'%" ><B>Out Quantity </B></th>


               </tr>

			  ';


		//
		while($r_lots = $stmt_lots->fetch_array()){


		 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PurchaseDate']));
	 $CreatedDateTime=date("d-m-Y", strtotime($r_lots['SoakingDate']));
if($CreatedDateTime=='01-01-1970'){
	
	$CreatedDateTime='Not Updated';
	
}


				    $content .=

					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['TubNumber'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['LotNumber'].'</th>
				 <th  align="center" width="'.$width.'%">'.$PURCHASE_DATE.'</th>
				 <th  align="center" width="'.$width.'%">'.$CreatedDateTime.'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Variety'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['PurchaseCount'].'</th>

				 <th  align="center" width="'.$width.'%">'.$r_lots['SoakInQty'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['SoakInCount'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['TimePeriod'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['SoakOutCount'].' </th>

				 <th  align="center" width="'.$width.'%">'.$r_lots['Destination'].' </th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['SoakOutQty'].' </th>

				 </tr>';

 $S_NO++;
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
			   <th  align="center" width=""><b></b> </th>

			   
		 </tr>';	

		$content .= '</table>';


	return $content;

}

?>

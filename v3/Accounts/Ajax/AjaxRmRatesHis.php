<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
$Month=$_POST['Month'];
//$Month=sprintf("%02d", $Month);
$YEAR=$_POST['Year'];
$SType=$_POST['Type'];
$UNIT=$_POST['PsId'];
$date_start=$YEAR.'-'.$Month.'-01';
$date_end=$YEAR.'-'.$Month.'-31  23:59:59';


 $accessType=$_SESSION['AccessType'];
   

/*
 
 $sql="SELECT * from RmPurchaseRatesHistory where Month(RmPriceDate)='$Month' and Year(RmPriceDate)='$YEAR'
	 order by ID DESC"; 
	 */
  $sql="SELECT * from RmPurchaseRatesHistory where RmPriceDate 
 BETWEEN '$date_start' AND '$date_end' and SType='$SType' order by ID DESC"; 


$stmt=$iconn->query($sql);





$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
                  
                  <th  >RM Price Date</th>
				 
				  <th  >Tools</th>
                </thead>
                <tbody>  
				
			   ';
	
	$PURCHASE_AMOUNT=0;
	
	while($row=$stmt->fetch_array()){
		
		
		
					 
$RM_PRICE_DATE = getFormatedDate($row['RmPriceDate']);


			
				$content .= '<tr>  
           		
               <td  ><font size="'.$text.'">Rates From -  '.$RM_PRICE_DATE.'</font></td>
                <td   style="width:40%;"> 
				 <a target="_blank" href="EditPurchaseRates.php?id='.urlEncode64($row['ID']).'" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="Edit Purchase Rates" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

                         
              <a  target="_blank"  href="RM_RatesPrnt.php?id='.urlEncode64($row['ID']).'" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
						 title="View Rates List" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>
		  
				
				
				
				
				<!--<a  target="_blank" 
				href="AddPurchaseRates.php?id='.urlEncode64($row['ID']).'"
				class="btn btn-danger">Edit</a>
				<a  target="_blank" href="RM_RatesPrnt.php?id='.urlEncode64($row['ID']).'"class="btn btn-success">Print</a>
				--></td>
		
		
		</tr>';
			
	 
	}		
	$content.='</tbody></table>'	;
				
echo $content;
	?>

	
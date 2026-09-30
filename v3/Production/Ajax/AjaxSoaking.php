<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Type=$_POST['Type'];
$PsID=$_POST['PsId'];



$COUNT=$_POST['COUNT'] ;

 $showQuantity=false;
 
 if($PsID!=''){
	  $Query="and Source='$PsID'";
  }else{
	   $Query="";
  }


 $accessType=$_SESSION['ACCESS_TYPE'];
 
	


  $sql="SELECT SoakingDate,SUM(SoakInQty) AS SoakInQty,
  SUM(SoakOutQty) AS SoakOutQty FROM ProdSoakingDetails  WHERE  month(SoakingDate)='$Month' 
and Year(SoakingDate)='$Year' $Query AND CreatedFrom=0 GROUP BY SoakingDate ORDER BY SoakingDate DESC";


$stmt=$iconn->query($sql);


$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

                  <th >Id</th>
                  <th >Soaking Date</th>
				  <th >In Quantity(Kgs)</th>
				   <th >Out Quantity(Kgs)</th>
                  <th >Pick Up</th>
				  <th >Actions</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($row=$stmt->fetch_array()){


$SoakingDate=$row['SoakingDate'];
$SoakInQty=$row['SoakInQty'];
$SoakOutQty=$row['SoakOutQty'];

	IF($SoakOutQty==0){
		
	$Yield='NA';	
	}ELSE{
	 
	 
	 $Yield=round(($SoakOutQty/$SoakInQty)*100,2);
	}
		
		//uma
		$SoakingDate=date("d-m-Y",strtotime($row['SoakingDate']));
		
		$QueryParms='Month='.$row['SoakingDate'].'&Year='.$row['SoakingDate'].'&PsID='.$PsID.'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);

			/* */
				$content .= '<tr>

				<td   >'.++$sno.'</td>
                <td  ><B>'.$SoakingDate.'</B></td>
                <td   >'.round($SoakInQty,2).'</td>
				<td   >'.round($SoakOutQty,2).'</td>
				<td   >'.round($Yield,2).'</td>
				<td  width="10%" >  
	
<!--<a  target="_blank" class="btn btn-primary" 
href="Reports/SokingReportPrint.php?Q='.$QueryParms.'" >View</a>-->

 <a target="_blank" href="Reports/SokingReportPrint.php?Q='.$QueryParms.'"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
		

</td>
';



			   

	  $content.=' 
		</tr>';


	}
	$content.='</tbody></table>'	;

/*	<a   href="#"class="btn btn-info">Lot Details</a>
		<a   href="AddRMDefects.php?ID='.$row['id'].'"class="btn btn-info">RM DEFECTS</a>
	*/

echo $content;

	?>
	
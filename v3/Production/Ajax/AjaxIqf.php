<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Type=$_POST['Type'];
$PsID=$_POST['PsId'];

/*$btn_id=$_POST['ID'];
$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31 23:59:59';*/

//$TYPE=$_POST['TO_DATE'];

/*$date_end=$_POST['TO_DATE'].' 23:59:59';
$date_start=$_POST['FROM_DATE'] ;
$COUNT=$_POST['COUNT'] ;*/

 $showQuantity=false;
 
 if($PsID!=''){
	  $Query="and Unit='$PsID'";
  }else{
	   $Query="";
  }



 $accessType=$_SESSION['ACCESS_TYPE'];
 



 $sql="SELECT COALESCE(SUM(Mcs),0) AS MASTERS, ProductionDate,
(SELECT COALESCE(SUM(Mcs),0) FROM
 `ProdProductionData` WHERE PackingType ='DUMMY'  AND ProductionDate=csi.ProductionDate AND LotID != '') AS DUMMY,

(SELECT round(COALESCE(SUM(NetWeight),0),2) FROM `ProdProductionData` 
where ProductionDate=csi.ProductionDate and FreezingType='BLOCK' AND LotID != '') AS BlockNetWeight,
(SELECT round(COALESCE(SUM(NetWeight),0),2) FROM `ProdProductionData` 
where ProductionDate=csi.ProductionDate and FreezingType='IQF' AND LotID != '') AS IQFNetWeight,
(SELECT round(COALESCE(SUM(NetWeight),0),2) FROM `ProdProductionData` 
where ProductionDate=csi.ProductionDate  AND LotID != '') AS TotalNetWeight

 FROM
 `ProdProductionData` AS csi WHERE 
 csi.PackingType='MASTER' AND month(ProductionDate)='$Month' 
and Year(ProductionDate)='$Year' $Query
 GROUP BY csi.ProductionDate ORDER BY ProductionDate DESC;";


$stmt=$iconn->query($sql);


$content .= ' <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

                  <th >Id</th>
                  <th >Iqf Date</th>
				  <th >Master Cartons</th>
				  <th >Dummy Cartons</th>
                  <th >Block</th>
                  <th >Iqf</th>
                  <th >Total Weight</th>
				  <th >Actions</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($RIqfData=$stmt->fetch_array()){

	 $IqfDate= getFormatedDate($RIqfData['ProductionDate']);

 
	$MasterCartons= $RIqfData['MASTERS'];
	 $DummyCartons=$RIqfData['DUMMY'];
	 
	 $BlockNetWeight=$RIqfData['BlockNetWeight'];
	 $IQFNetWeight=$RIqfData['IQFNetWeight'];
	 $TotalNetWeight=$RIqfData['TotalNetWeight'];

		$QueryParms='Month='.$RIqfData['ProductionDate'].'&Year='.$RIqfData['ProductionDate'].'&PsID='.$PsID.'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);

			/* */
				$content .= '<tr>

				<td   >'.++$sno.'</td>
                <td  ><B>'.$IqfDate.'</B></td>
                <td  ><B>'.$MasterCartons.'</B></td>
                <td  ><B>'.$DummyCartons.'</B></td>
                <td   >'.round($BlockNetWeight,2).'</td>
				<td   >'.round($IQFNetWeight,2).'</td>
				<td   >'.round($TotalNetWeight,2).'</td>
				<td  width="10%" >  
	
<!-- <a  target="_blank" class="btn btn-primary" href="Reports/IqfReportPrint.php?Q='.$QueryParms.'" >View</a>-->


<a target="_blank" href="Reports/IqfReportPrint.php?Q='.$QueryParms.'"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a></td>


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
	
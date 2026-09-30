<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

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
 `ProdProductionData` WHERE PackingType ='DUMMY'  AND ProductionDate=csi.ProductionDate) AS DUMMY,

(SELECT round(COALESCE(SUM(NetWeight),0),2) FROM `ProdProductionData` 
where ProductionDate=csi.ProductionDate and FreezingType='BLOCK') AS BlockNetWeight,
(SELECT round(COALESCE(SUM(NetWeight),0),2) FROM `ProdProductionData` 
where ProductionDate=csi.ProductionDate and FreezingType='IQF') AS IQFNetWeight,
(SELECT round(COALESCE(SUM(NetWeight),0),2) FROM `ProdProductionData` 
where ProductionDate=csi.ProductionDate ) AS TotalNetWeight

 FROM
 `ProdProductionData` AS csi WHERE 
 csi.PackingType='MASTER' AND month(ProductionDate)='$Month' 
and Year(ProductionDate)='$Year' $Query
 GROUP BY csi.ProductionDate ORDER BY ProductionDate DESC;";


$stmt=$iconn->query($sql);


$content .= '<table id="key-datatable" 
										class="table dt-responsive nowrap w-100">
		

			<thead>

                  <th align="CENTER">Id</th>
                  <th align="CENTER">Iqf Date</th>
				  <th align="CENTER">Master Cartons</th>
				  <th align="CENTER">Dummy Cartons</th>
                  <th align="CENTER">Block</th>
                  <th align="CENTER">Iqf</th>
                  <th align="CENTER">Total Weight</th>
				  <th align="CENTER">Actions</th>
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

				<td  align="CENTER" width="2%"><font size="'.$text.'">'.++$sno.'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$IqfDate.'</B></font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$MasterCartons.'</B></font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$DummyCartons.'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.round($BlockNetWeight,2).'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.round($IQFNetWeight,2).'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.round($TotalNetWeight,2).'</font></td>
				<td align="CENTER" width="10%" >  
	<!--  <a  target="_blank" class="btn btn-primary" 
href="Reports/BlockReportPrint.php?from_dates='.$RIqfData['ProductionDate'] .'&to_date='.$RIqfData['ProductionDate'].'" >View</a></td>
 -->
 <a  target="_blank" class="btn btn-primary" href="Reports/BlockReportPrint.php?Q='.$QueryParms.'" >View</a></td>
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
	
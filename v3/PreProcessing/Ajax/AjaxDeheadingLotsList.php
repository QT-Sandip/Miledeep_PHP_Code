<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

/*$MONTH=$_POST['Month'];
$YEAR=$_POST['Year'];*/
$TYPE=$_POST['Type'];
$PsId=$_POST['PsId'];
$ToDate=$_POST['ToDate'];
$FromDate=$_POST['FromDate'];


$TYPE=$_POST['TO_DATE'];


//echo $COUNT=$_POST['COUNT'] ;exit;

 //$_SESSION['DeptID']=4;

 $showQuantity=true;

 if($_SESSION['DeptID'] == 3  && $_SESSION['AccessType'] <= 3){
		 $showQuantity=true;
	 }


 if($_SESSION['DeptID']== 1){
	 $showOptions=true;
 }else{
	 $showOptions=true;
 }

 $accessType=$_SESSION['AccessType'];


   $query="";
   /*if($COUNT !=''){
	   $COUNT=explode('&',$COUNT);
	   $LowerCount=$COUNT[0];
	   $UpperCount=$COUNT[1];
	   
	   $query="and PurchaseCount > $LowerCount and PurchaseCount < $UpperCount";
   }else{
	$query="";   
   }*/
if($PsId!=''){
	   $query= " and PurchaseStationID=".$PsId;
	   
   }
  /*$SqlLotDetails="SELECT * FROM `RmLotDetatils` AS RLD
  LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
  WHERE Month(PurchaseDate)='$MONTH' and year(PurchaseDate)='$YEAR' $query";*/
  
  
  /*$SqlLotDetails="SELECT *,sum(WtAfterDH) as WtAfterDH FROM `RmLotDetatils` AS RLD
  LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
  LEFT JOIN PpcDeheading AS PDH ON RLD.RmLotID=PDH.LotID
  WHERE Month(PurchaseDate)='$MONTH' and year(PurchaseDate)='$YEAR' and LotStatus!=2 $query  group by RLD.RmLotID order by RmLotID DESC"; */
  
  $SqlLotDetails="SELECT *,sum(WtAfterDH) as WtAfterDH FROM `RmLotDetatils` AS RLD
  LEFT JOIN RmArrivalBreakUpDetails AS RLBD ON RLD.BreakUpID=RLBD.ArrivalBrkUpID
  LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
  LEFT JOIN PpcDeheading AS PDH ON RLD.RmLotID=PDH.LotID
  WHERE PurchaseDate between '$FromDate' and '$ToDate' AND RLBD.Variety='Head On' and LotStatus!=2 $query  group by RLD.RmLotID order by RmLotID DESC";
  $StmtLotDetails=$iconn->query($SqlLotDetails);

// echo $SqlLotDetails; exit;
$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
 
                  <!--<th >ID</th>
                  <th >LOT</th>
				  <th >Purchase Date</th>
				   <th >Type</th>';
				  if($showQuantity){
				  $content .= '<th >Grader</th>
				  <th >Agent</th>
				  <th >Count</th>
				  <th >Weight(Kgs)</th>';
				  }
				  $content .= '
                  <th >AB Status</th>
				  <th >Actions</th>-->
				  
                  <th >Lot ID</th>
                  <th >Lot No</th>
				  <th >Rm Date</th>
				
				  <th >Count</th>
				  
				  <th>DeHeading Date</th>
				  <th>DeHeading Total(Kgs)</th>
                  <th>Ab Status</th>
				  <th>Actions</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($RowLotDetails=$StmtLotDetails->fetch_array()){


		
		$PurchaseDate=date("d-m-Y",strtotime($RowLotDetails['PurchaseDate']));
		$ProcessingDate=getFormatedDatelines($RowLotDetails['ProcessingDate']);
		$PurchaseCount=$RowLotDetails['PurchaseCount'];
		$LotID=$RowLotDetails['RmLotID'];
		$PurchaseWeight=$RowLotDetails['PurchaseWeight'];
		$PurchaseType=$RowLotDetails['PurchaseType'];
		
		$SqlABDetails="SELECT * FROM `QcRmAbReport` AS QRAR
		LEFT JOIN QcABOptions AS QAO ON QAO.OptID=QRAR.AbStatus WHERE LotID ='$LotID'";
        $StmtABDetails=$iconn->query($SqlABDetails);
        $RowABDetails=$StmtABDetails->fetch_array();
		$ABStatus=$RowABDetails['Name'];
		if($ABStatus==''){
			$ABStatus='Ab Report Not Aval';
		}
		$BgColour=$RowABDetails['Colour'];
		$WtAfterDH=$RowLotDetails['WtAfterDH'];
			
				$content .= '<tr>

               
              <td >'.$RowLotDetails['RmLotID'].'</td>

                <td><B>'.$RowLotDetails['LotNumber'].'</B></td>
                <td>'.$PurchaseDate.'</td>
                <td>'.$RowLotDetails['Count'].'</td>
              
                 <td >'.$ProcessingDate.'</td>
                <td>'.round($WtAfterDH,2).'</td>
				<td>

		
		
		<div  class=" badge badge-warning font-size-10 "
	style="width:100px;background:'.$BgColour.';color:white"
					 >
							 '.$ABStatus.'
							 </div>	
		</td>

			  <td  width="10%">
			   <a target="_blank" href="AddDHReport.php?LotID='.$RowLotDetails['RmLotID'].'"
						 class="mr-3 text-primary Add" data-toggle="tooltip" data-placement="top"
						 title="Add" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
		</td>
		</tr>';


	}
	$content.='</tbody></table>'	;

/*	<a   href="#"class="btn btn-info">Lot Details</a>
		<a   href="AddRMDefects.php?ID='.$RowLotDetails['id'].'"class="btn btn-info">RM DEFECTS</a>
	*/

echo $content;

	?>
	
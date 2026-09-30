<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
$MONTH=$_POST['Month'];
$YEAR=$_POST['Year'];
$TYPE=$_POST['Type'];
$PsId=$_POST['PsId'];

$TYPE=$_POST['TO_DATE'];


//echo $COUNT=$_POST['COUNT'] ; exit;

 //$_SESSION['DeptID']=4;

 $showQuantity=false;



 if($_SESSION['DeptID']== 1){
	 $showOptions=true;
 }else{
	 $showOptions=false;
 }

 $accessType=$_SESSION['AccessType'];

$query="";
if($PsId!=''){
	   $query= " and PurchaseStationID=".$PsId;
	   
   }
   
   /*if($COUNT !=''){
	   $COUNT=explode('&',$COUNT);
	   $LowerCount=$COUNT[0];
	   $UpperCount=$COUNT[1];
	   
	   $query="and PurchaseCount > $LowerCount and PurchaseCount < $UpperCount";
   }else{
	$query="";   
   }*/

 /* $SqlLotDetails="SELECT *,GD.LotID as GdAval FROM `RmLotDetatils` AS RLD
	LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
	LEFT join PpcHLGradingDetails as GD on RLD.RmLotID=GD.LotID 
	LEFT join RmBillDetails as RMBD on RLD.RmLotID=RMBD.LotID
	WHERE Month(PurchaseDate)='$MONTH' and year(PurchaseDate)='$YEAR' $query order by RmLotID desc";*/
	
	$SqlLotDetails="SELECT *,GD.LotID as GdAval,
(select sum(WtAfterDH) from PpcDeheading as PDH where RLD.RmLotID=PDH.LotID AND CreatedFrom=0) as WtAfterDH,
(select sum(HLWeight) from PpcHLGradingBreakup as GDB where RLD.RmLotID=GDB.LotID AND CreatedFrom=0) as GradingWeight	FROM `RmLotDetatils` AS RLD
LEFT JOIN RmArrivalBreakUpDetails AS RLBD ON RLD.BreakUpID=RLBD.ArrivalBrkUpID
	LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
	LEFT join PpcHLGradingDetails as GD on RLD.RmLotID=GD.LotID AND GD.CreatedFrom=0
	LEFT join RmBillDetails as RMBD on RLD.RmLotID=RMBD.ArrivalID
	WHERE Month(PurchaseDate)='$MONTH' and year(PurchaseDate)='$YEAR' AND RLBD.Variety='Head On' $query and LotStatus!=2 order by RmLotID desc";
  $StmtLotDetails=$iconn->query($SqlLotDetails);

//echo $SqlLotDetails; //exit;
$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">

                  
				  
				  <th >Lot ID</th>
                  <th >Lot No</th>
                  <th >Rm Date</th>
				 <!-- <th >Rm Weight</th>
				 <th >Grading Date</th>
				   <th >Type</th>-->
				  <th >Count</th>
				  <th >DeHeading Total(Kgs)</th>
				  <th >Grading Total(Kgs)</th>
				  <th >Ab Status</th>
				  <th >Actions</th>
				  
				  
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($RowLotDetails=$StmtLotDetails->fetch_array()){


		
		$PurchaseDate=getFormatedDate($RowLotDetails['PurchaseDate']);
		$GradingDate=getFormatedDate($RowLotDetails['GradingDate']);
		$PurchaseCount=$RowLotDetails['PurchaseCount'];
		$LotID=$RowLotDetails['RmLotID'];
		$PurchaseWeight=$RowLotDetails['PurchaseWeight'];
		$PurchaseType=$RowLotDetails['PurchaseType'];
		$IsMix=$RowLotDetails['IsMix'];
		$WtAfterDH=$RowLotDetails['WtAfterDH'];
		$GradingWeight=$RowLotDetails['GradingWeight'];
		
		$SqlABDetails="SELECT * FROM `QcRmAbReport` AS QRAR
		LEFT JOIN QcABOptions AS QAO ON QAO.OptID=QRAR.AbStatus WHERE LotID ='$LotID'";
        $StmtABDetails=$iconn->query($SqlABDetails);
        $RowABDetails=$StmtABDetails->fetch_array();
		$ABStatus=$RowABDetails['Name'];
		$BgColour=$RowABDetails['Colour']?$RowABDetails['Colour']:'#ffc300';
		if($ABStatus==''){
			$ABStatus='Ab Report Not Aval';
		}
			
				$content .= '<tr>

                <td>'.$RowLotDetails['RmLotID'].'</td>

                <td><B>'.$RowLotDetails['LotNumber'].'</B></td>
                <td>'.$PurchaseDate.'</td>
				<td>'.$RowLotDetails['Count'].'</td>
				<td  >'.round($WtAfterDH,2).'</td>
             
			   <td  >'.round($GradingWeight,2).'</td> 
                <!--<td>'.$GradingDate.'</td>
				
                <td>'.$PurchaseType.'</td>-->
                <td><div  class=" badge  font-size-10 "
	style="background:'.$BgColour.';color:white"
					 >
							 '.$ABStatus.'
							 </div></td>';
	


	if($RowLotDetails['GdAval']== '' ){  //GRADING REPORT NOT SUBMITTED //AddHLGrdng.php
		$content.='<td   >


		<div>
		<!--<input type="checkbox" id="chbk" value="'.$LotID.'" ><span style="color: white";>= =</span>-->
		<input type="checkbox" id="chbk" value="'.$LotID.'" >

		<!--<a 
		class="btn btn-info" id='.$LotID.'
		href="AddHLGradingRprt.php?ID='.$LotID.'">Add </a>-->
		
		 <a  target="_blank" id='.$LotID.' href="AddHLGradingRprt.php?ID='.$LotID.'"
						 class="mr-3 text-primary Add" data-toggle="tooltip" data-placement="top"
						 title="Add" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>

		</div></td>';



	 }else{
		 //if($IsMix==0){
		$content.= '<td   >


		<div> 
		
		 <a  target="_blank" href="AddHLGradingRprt.php?ID='.$LotID.'"
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="Edit" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

		<!--<a  target="_blank" href="AddHLGradingRprt.php?ID='.$LotID.'"class="btn btn-info">Edit Grading</a>-->
	</td>';
	// }
	 
	 
	 
	 }



	  $content.='
		</tr>';


	}
	$content.='</tbody></table>'	;

/*	<a   href="#"class="btn btn-info">Lot Details</a>
		<a   href="AddRMDefects.php?ID='.$RowLotDetails['id'].'"class="btn btn-info">RM DEFECTS</a>
	*/

echo $content;

	?>
	

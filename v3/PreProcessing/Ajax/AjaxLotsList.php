<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$MONTH=$_POST['Month'];
$YEAR=$_POST['Year'];
$TYPE=$_POST['Type'];
$btn_id=$_POST['PsId'];


$TYPE=$_POST['TO_DATE'];


$COUNT=$_POST['COUNT'] ;

 //$_SESSION['DeptID']=4;

 $showQuantity=false;

 if($_SESSION['DeptID'] == 3  && $_SESSION['AccessType'] <= 3){
		 $showQuantity=true;
	 }


 if($_SESSION['DeptID']== 1){
	 $showOptions=true;
 }else{
	 $showOptions=false;
 }

 $accessType=$_SESSION['AccessType'];


   
   if($COUNT !=''){
	   $COUNT=explode('&',$COUNT);
	   $LowerCount=$COUNT[0];
	   $UpperCount=$COUNT[1];
	   
	   $query="and PurchaseCount > $LowerCount and PurchaseCount < $UpperCount";
   }else{
	$query="";   
   }

  $SqlLotDetails="SELECT * FROM `RmLotDetatils` AS RLD
  LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
  WHERE Month(PurchaseDate)='$MONTH' and year(PurchaseDate)='$YEAR' $query";
  $StmtLotDetails=$iconn->query($SqlLotDetails);

// echo $SqlLotDetails; exit;
$content .= '<table id="key-datatable" 
										class="table dt-responsive nowrap w-100">
		


			<thead>

                  <th align="CENTER">ID</th>
                  <th align="CENTER">LOT</th>
				  <th align="CENTER">Purchase Date</th>
				   <th align="CENTER">Type</th>';
				  if($showQuantity){
				  $content .= '<th align="CENTER">Grader</th>
				  <th align="CENTER">Agent</th>
				  <th align="CENTER">Count</th>
				  <th align="CENTER">Weight</th>';
				  }
				  $content .= '
                 <!-- <th align="CENTER">AB Status</th>-->
				  <th align="CENTER">Actions</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($RowLotDetails=$StmtLotDetails->fetch_array()){


		
		$PurchaseDate=date("d-m-Y",strtotime($RowLotDetails['PurchaseDate']));
		$PurchaseCount=$RowLotDetails['PurchaseCount'];
		$LotID=$RowLotDetails['RmLotID'];
		$PurchaseWeight=$RowLotDetails['PurchaseWeight'];
		$PurchaseType=$RowLotDetails['PurchaseType'];
		
		$SqlABDetails="SELECT * FROM `QcRmAbReport` AS QRAR
		LEFT JOIN QcABOptions AS QAO ON QAO.OptID=QRAR.AbStatus WHERE LotID ='$LotID'";
        $StmtABDetails=$iconn->query($SqlABDetails);
        $RowABDetails=$StmtABDetails->fetch_array();
		$ABStatus=$RowABDetails['Name'];
		$BgColour=$RowABDetails['Colour'];
			
				$content .= '<tr>

                <td  align="CENTER" width="5%"><font size="'.$text.'">'.$RowLotDetails['RmLotID'].'</font></td>

                <td  align="CENTER" width="10%"><font size="'.$text.'"><B>'.$RowLotDetails['LotNumber'].'</B></font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$PurchaseDate.'</font></td>
				
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$PurchaseType.'</font></td>';
				 if($showQuantity){
				  $content .= '
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$RowLotDetails['SUPERVISOR'].'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$RowLotDetails['AGENT_NAME'].'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$PurchaseCount.'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$PurchaseWeight.'</font></td>';
				 }



			   $content .= '<td align="CENTER" width="10%">

		<!--<div class="bg-active color-palette" align="center" >
			<span class="label" align="center" style="background:'.$BgColour.'">'. $ABStatus.'</span>
		</div>
		</td>

		 <td style="width:15%;">-->

		';





	 if($_SESSION['DeptID']== 4){

	 $content.=  '

		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>

				 <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
					<a  target="_blank" href="AddDHReport.php?LotID='.$RowLotDetails['RmLotID'].'"
					>    Add Deheading </a>
					</li>
				   	<li>
					
					<a  target="_blank" href="AddVAReport.php?LotID='.$RowLotDetails['RmLotID'].'"
					>   Add VA </a>
					
					
					</li>


				 </ul>

                </div>

	 </tr>';

	   }elseif($_SESSION['DeptID']== 18){
		$content.='

		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>

				 <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
					<a class="btn btn-warning target="_blank" href="AddSockngRprt.php?ID='.$RowLotDetails['RmLotID'].'"
					>  Add Soaking Report </a>
					</li>
				   	<li>
		<a  class="btn btn-primary target="_blank" href="AdIQFRprt.php?ID='.$RowLotDetails['RmLotID'].'"
					>  Add Iqf Report </a>
					</li>


				 </ul>

                </div>

		';

	   }elseif($_SESSION['DeptID']== 3){


		$content.='

		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>

				 <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
					<a target="_blank" href="AdMorCnts.php?ID='.$RowLotDetails['RmLotID'].'"
					>Add More Counts</a>
					</li>
				   	<li>
					<a target="_blank" href="AddRMDefects.php?ID='.$RowLotDetails['RmLotID'].'"
					>Add Defects</a>
					</li>
					<li>
					<a target="_blank" href="EdtLotRprt.php?ID='.$RowLotDetails['RmLotID'].'"
					>Edit Lot</a>
					</li>

				<!--		<li>
					<a target="_blank" href="#">Lot Details</a>
					</li> -->

				 </ul>

                </div>

		';


	   } else{

$content.='

		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>

				 <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
				<!--	<a target="_blank" href="AddSockngRprt.php?ID='.$RowLotDetails['RmLotID'].'"
					class="btn btn-warning btn-sm  btn-flat glyphicon
						glyphicon">  LOT DETAILS  </a> -->
					</li>

				 </ul>

                </div>

		';


	   }

	  $content.=' </td>
		</tr>';


	}
	$content.='</tbody></table>'	;

/*	<a   href="#"class="btn btn-info">Lot Details</a>
		<a   href="AddRMDefects.php?ID='.$RowLotDetails['id'].'"class="btn btn-info">RM DEFECTS</a>
	*/

echo $content;

	?>
	
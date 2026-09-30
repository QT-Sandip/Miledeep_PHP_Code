<?php
$PageTitleFolder="PreProcessing";
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($cp."Includes/IncludePage.php");

$AryCurrentDate=getCurrentDateTime();
$PostedDateTime=$AryCurrentDate['TimeStamp'];
global $PostedDateTime;			

$LotID=$_GET['ID'];
$MixedIds=$_GET['ID'];

extract($_POST);
extract($_GET);


$SqlGradingHl="SELECT * FROM `PpcHLGradingDetails` where LotID='$LotID' AND CreatedFrom=0";
$StmtGradingHl=$iconn->query($SqlGradingHl);
$RowGradingHl=$StmtGradingHl->fetch_array();

if($RowGradingHl['HLID']==''){
	$url='AddHLGradingRprt.php?ID='.$LotID;
	 header('Location: '.$url);
}

if($RowGradingHl['IsMix']!='' && $RowGradingHl['MixIDs']!='NA'){
  $Mix=$RowGradingHl['IsMix'];
  $MixedIds=$RowGradingHl['MixIDs'];
  $MixedIds=str_replace('_' , ',' ,$MixedIds);
}else{
	$MixedIds=$LotID;
}

$SqlLots="SELECT 
GROUP_CONCAT(LotNumber) as LotNumber,
GROUP_CONCAT(PurchaseCount) as PurchaseCount,
sum(PurchaseWeight) as PurchaseWeight FROM `RmLotDetatils` where RmLotID in ($MixedIds)";
$StmtLots=$iconn->query($SqlLots);
$RowLots=$StmtLots->fetch_array();

$LotNumer=$RowLots['LotNumber'];
$Count=$RowLots['PurchaseCount'];
$Weight=$RowLots['PurchaseWeight'];

$PageTitle="Add Grading Report  - ".$LotNumer.'('.$Count.'C/'.$Weight.'Kgs)';



//ROBO
$SqlGradMachines="SELECT * FROM `AdminGradingMachines`";
$StmtGradMachines=$iconn->query($SqlGradMachines);


$GradingBreakUpSql="SELECT * FROM `PpcHLGradingBreakup` where LotID in ($MixedIds)  AND CreatedFrom=0 order by HlCount asc";
$GradingBreakUpStmt=$iconn->query($GradingBreakUpSql);


 ?>



<!DOCTYPE html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					
					

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
                     <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

    		
			  
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enter Defect</label>
                 <input required type="text" class="form-control" name='Defect' id="Defect"
				  value="" placeholder="Enter Defect">
				</select>
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enter Count</label>
                 <input required type="text" class="form-control" name='Count' id="Count"
				  value="" placeholder="Enter Count">
				</select>
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enter Weight</label>
                  <input required type="text" class="form-control" name='Weight' id="Weight"
				  value="" placeholder="Enter Weight">
                </div>

                  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enter No Of Crates </label>
                  <input required type="text" class="form-control" name='NoOfTubs' id="NoOfTubs"
				  value="" placeholder="Enter Net Weight">
                </div>
		
				</div>
				 
            </form>
			 <div class="col-xs-12 box-footer"  align="right">
			   <a class="btn btn-danger"  href="LotsListGrading">Close</a>
                <button id="add"  name='add' class="btn btn-success">Add</button>
              </div>
       <!-- end col -->
		</div> <!-- end row -->
		</div> <!-- end row -->
		</div> <!-- end row -->


	
<div id="tdetails" class="table-responsive col-sm-12" >  
   <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    
                    

  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
      <tr>
	  
	  <th>S.No</th>
	  <th>Count</th>
	  <th>Total Weight</th>	  
	  <th>Tubs</th>
	  <th>Actions</th>

      </tr>
    </thead>
    <tbody>

	<?php
	
	while($GradingBreakUpRow=$GradingBreakUpStmt->fetch_array()){
		 $HLWeight+=$GradingBreakUpRow['HLWeight'];


$S_NO=1;
	//echo $sql;
   
   	?>
	<tr >
			
	<td  id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo ++$Sno; ?>    </td>
	<td id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo $GradingBreakUpRow['HLCount']; ?> </td>
	<td id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo $GradingBreakUpRow['HLWeight']; ?> </td>	
	<td id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo $GradingBreakUpRow['NoCrates']; ?> </td>


	<TD>
	<!--<button data-id="<?php echo $GradingBreakUpRow['DataID']; ?>" 
	class="glyphicon-remove btn btn-danger DeleteGrade"> Delete</button>-->
	
	<button class="btn btn-link text-danger DeleteGrade" 
		data-id="<?php echo $GradingBreakUpRow['DataID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>
	</TD> 
	
	
	
    </tr>
	<?php } ?>
   <tr >

   
	 <th>Total HL</th>
	 <th></th>
    <td><?php echo $HLWeight ?> Kgs</td>
    <th>Yield</th>
    <td> <?php // echo round(($HlWeight/$ROW_LOTS['WEIGHT_IN_KGS'])*100,2) ?>-- % </td>
     </tr>

     </tbody>

  </table>
</div>
</div>

</div>

    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>
  
       
    </body>
	
	
<script>
$('document').ready(function(){

    	$(document).on('click','#add',function(e){
		e.preventDefault();

			//alert('Hi');

			var LotID='<?php echo $LotID ?>';
			var UserID='<?php echo $UserID ?>';
			var Mix='<?php echo $Mix ?>';
			
			var Defect= $('#Defect').val();
			var Count= $('#Count').val();
			var Weight= $('#Weight').val();
			var NoOfTubs= $('#NoOfTubs').val();
			
			if(NoOfTubs==''){
				NoOfTubs=1;
			}else{
				Weight=Weight*NoOfTubs;
			}


			var validation=false;

			if(Count==""){
			alert("count is empty");
			$('#COUNT').focus();
			}else if(Weight==""){
			alert("Weight is empty");
			$('#WEIGHT').focus();
			}else{
				validation=true;
			}


			if(validation==false){
			return;
			}



			var finalData = {
				'LotID':LotID,
				'UserID':UserID,				
				'Defect':Defect,
				'Count':Count,
				'Weight':Weight,
				'NoCrates':NoOfTubs,
				'Mix': Mix,				
				'From':'Web'
				};


		$.ajax({
		 url:'Ajax/AjaxGrading',
		  type:'POST',
		  dataType: 'json',
		  data:{'Data':JSON.stringify(finalData),'Type':'BREAK_UP'},
		  success:function(data){
			   var obj = JSON.parse(JSON.stringify(data));
			 
			 if(obj.Error==null){

				//alert(obj.Msg);
				location.reload();
				}else{
				alert(obj.Error);
				//location.reload();
				}
			}
			});
  });

});

</script><script>

  $(document).on('click', '.DeleteGrade', function(e){
		  var GradeID =$(this).data('id');


   		var r = confirm("Are you sure do you want To delete "+GradeID+"?");
   if (r == true) {

		 //alert(LotID);		  
		$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
          data:{'TYPE':'GRDNGID_DELETE','GradeID':GradeID},
		  success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
location.reload();
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							location.reload();
				
			}
	 }
			});	
}
  else{}			
  }); 

</script>


</html>				

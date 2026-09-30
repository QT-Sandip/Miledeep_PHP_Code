<?php

//$Status=$_SESSION['Status'];


$PageTitleFolder="Purchase";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 
extract($_POST);
extract($_GET); 
 $LotID=$_GET['LotID'];

$SqlDefectsDetails="Select * from RmDefectsDetails where LotID='$LotID'";
$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);
//$RowDefectsDetails=$StmtDefectsDetails->fetch_array();

$SqlLotNumber="Select * from RmLotDetatils where RmLotID='$LotID'";
$StmtLotNumber=$iconn->query($SqlLotNumber);
$RowLotNumber=$StmtLotNumber->fetch_array();

$LotNumber=$RowLotNumber['LotNumber'];

$PageTitle="RM Defects - "  . $LotNumber  ;

 $SqlRmDefectsList="Select * from RmDefectsList "; 
 $StmtRmDefectsList=$iconn->query($SqlRmDefectsList);
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s");
	 

if(isset($_POST['submit'])){
	
	Global $LotID;
	Global $CurrentDateTime;
 	
   $SqlDefectDetails="INSERT INTO `RmDefectsDetails`( `LotID`, `UserID`, `CreatedDateTime`, `DefectID`, `DefectWeight`, `DefectCount`) VALUES
 ('$LotID','$UserID','$CurrentDateTime','$DefectName','$DefectWeight','$DefectCount')"; 

  $StmtDefectDetails=$iconn->query($SqlDefectDetails);
	
	if($StmtDefectDetails){ 
		$insertedDefectId = $iconn->insert_id;
		legacyAuditLogInsertById('RmDefectsDetails', 'ID', $insertedDefectId, array(
			'LotID' => $LotID,
			'UserID' => $UserID,
			'DefectID' => $DefectName,
			'DefectWeight' => $DefectWeight,
			'DefectCount' => $DefectCount,
		), array(
			'ReferenceNo' => $LotID,
			'Remarks' => 'Lot defect created from legacy purchase screen',
		), 'Purchase');
		
		
		header('Location: AddLotRmDefects.php?LotID='.$LotID);
		
	}
	
	
		}		

	  
	  


?>




<!DOCTYPE html>


<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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
                  <label for="exampleInputEmail1">Select Defect</label>
                   <select required type="text" class="form-control select2" name='DefectName' id="DefectName">

				    <?php while ($RowDefectsList=$StmtRmDefectsList->fetch_array()){ ?>
				     <option 
					            
					 value="<?php echo $RowDefectsList['DefectID'] ?>">
					 <?php echo $RowDefectsList['DefectName'] ?></option>
				   
				 <?php  }?>
                 </select>
               
                </div>				


              <div class="form-group  col-lg-3">
				<label for="exampleInputEmail1"> Weight</label>
                    <input class="form-control" type="text"
					id="DefectWeight" name="DefectWeight"
					placeholder="Enter Weight" value="" required>                 
                 </div>
				 
				 <div class="form-group  col-lg-3">
				<label for="exampleInputEmail1"> Rate/Kg</label>
                    <input class="form-control" type="text"
					id="Rate" name="Rate"
					placeholder="Enter Rate" value="" required>                 
                 </div>
				
				
				
			  
        <!-- end row -->
					</div>
			<div class="col-xs-12 box-footer"  align="right">
                <button  type="submit"  name='submit'  class="btn btn-success waves-effect waves-light">
				
			   Submit</button> 
              </div>
			  
			  </form>
			  
									</div>
									</div>
        
                                         <!-- end col -->
                        
	 <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="EmpList">
				
			  
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      

      <tr>
	  
	  <th>S.No</th>
	  <th>Defect Name</th>
	  <th>Weight</th>
	  <th>Remove</th>
	 
      </tr>

    </thead>

    <tbody>
	<?php
$SNo=1;
   while($RowDefectsDetails=$StmtDefectsDetails->fetch_array()){
	   
	   $DefectID=$RowDefectsDetails['DefectID'];
	  
	  $SqlDefectName="SELECT * FROM `RmDefectsList` WHERE DefectID='$DefectID'";
      $StmtDefectName=$iconn->query($SqlDefectName);
      $RowDefectName=$StmtDefectName->fetch_array();

	?>
	
	<tr> 
			
	<td id="<?php  echo $RowDefectsDetails['ID'];?>" > <?php echo $SNo; ?></td>
	<td id="<?php  echo $RowDefectsDetails['ID'];?>" > <?php echo $RowDefectName['DefectName']; ?></td>
	<td id="<?php  echo $RowDefectsDetails['ID'];?>" > <?php echo $RowDefectsDetails['DefectWeight']; ?></td>

	<td>
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowDefectsDetails['ID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
	</td>		
        </tr>

	  <?php 
$SNo++;
   
   }
	  ?>

     </tbody>

  </table>

			  
			  
									</div>
									</div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->
    
 



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
      
  
       
    </body>
</html>	
<script>
 $('document').ready(function(){
 showData(data);
 });
		
  $(document).on('click', '.Delete', function(e){
e.preventDefault();
	  var ODId =$(this).data('id');

	
	
	 /*var DeptID=$('#DeptID').val();
	 var SubDeptID=$('#SubDeptID').val();*/
	 //var EmpStatus = $(this).val();
	  var Type = 'DeleteOD';
   
     var r = confirm("Are You Sure You Want To Delete?");
  if (r == true) {
   
	$.ajax({
	
	type: 'POST',
 //   url: 'Ajax/AjDeptDetails.php',
    data: {'ODId': ODId,'Type': Type},
    
    success: function(response){
		var obj = JSON.parse(JSON.stringify(response));

		//showData(data);
		 if(obj.error == null){
			 alert("Deleted  Successfully");
			//alert(obj.Msg);
         location.reload();
		showData(data);
		 }else{
			 			 alert(obj.error);

		 }

	 
     }
  });
  }
else{}
    
  });
 

</script>

<script> 

function showData(data){
			
					$("#EmpList").html(data);
 
 		var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true,
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );
		}

</script>

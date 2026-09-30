<?php


session_start();
 $AbRepID=$_GET['AbRepID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

extract($_POST);
extract($_GET);




$PageTitle="Edit Daily Lab AB Reports" ;

$PageTitleFolder="Quality Control";
$DtofAnalysis = date("Y-m-d", strtotime($DOA)); 
  
  
  
				 
  $SqlDropDownOptions="SELECT * FROM `QcABOptions`  ORDER BY SortOrder ASC   ";
$StmtDropDownOptions=$iconn->query($SqlDropDownOptions);
$ArrayDropDownOptions=array();
WHILE($RowDropDownOptions=$StmtDropDownOptions->FETCH_ARRAY()){
	$ArrayDropDownOptions[]=$RowDropDownOptions;
}

 $SqlAbReport="SELECT * FROM `QcRmAbReport`   where  AbRepID='$AbRepID'"; 
$StmtAbReport=$iconn->query($SqlAbReport);
$RowAbReport=$StmtAbReport->fetch_array();

$SqlRmAbReport="SELECT * FROM `QcRmAbReport` as QRAR 
LEFT JOIN RmLotDetatils as RLD on RLD.RmLotID=QRAR.LotID 
LEFT JOIN QcABOptions as QAO on QRAR.AbStatus=QAO.Value
 LEFT JOIN QcABUsers as QAU on QAU.UserID=QRAR.TestedBy where AbRepID='$AbRepID'
 order by AbRepID desc";
$StmtRmAbReport=$iconn->query($SqlRmAbReport);


 $SqlABUsers="SELECT * FROM `QcABUsers`"; 
$StmtABUsers=$iconn->query($SqlABUsers);
	

if(isset($_POST['submit'])){
	$LoginId=$_SESSION["UserID"];

	$DtofAnalysis = date("d-m-Y", strtotime($AnalysisDate));
	$DtofCompletion = date("d-m-Y", strtotime($ReportDate));
	$PostedDate = date("Y-m-d");
	
	
	
  $SqlAbReportInsert="UPDATE `QcRmAbReport` SET `AnalysisDate`='$AnalysisDate',`ReportDate`='$ReportDate',
`Parameter`='$Parameter',`Sulphite`='$Sulphite',`Remarks`='$Remarks',`Shift`='$Shift',
`TestedBy`='$TestedBy',`AbStatus`='$AbStatus' WHERE AbRepID='$AbRepID'";
			
	


	$StmtAbReportInsert=$iconn->query($SqlAbReportInsert);

	$Insertid = $iconn->insert_id;


		echo "<script>alert('Success')</script>";

		header('Location:DailyLabReports.php?id=0');

	}





 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
 

            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

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
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>
<div class="modal fade" id="myModal" role="dialog">

    <div class="modal-dialog">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <h4 class="modal-title modheading"></h4>

            </div>

            <div class="modal-body">


			<div class="card-body" id="ATT_DATA">
              
            </div>




            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				
				

            </div>

        </div>

    </div>

</div>




    <!-- Main content -->



  <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
						
				
				  <div class="form-group col-lg-3" >

                  <label for="exampleInputEmail1">Analysis Date</label>

                  <input required type="date" class="form-control" name='AnalysisDate' id="AnalysisDate" value="<?php echo $RowAbReport['AnalysisDate'];?>">

                </div>
				


                <div class="form-group col-lg-3">

                  <label for="exampleInputPassword1">Report Date</label>

                  <input required type="date" class="form-control" name='ReportDate' id="ReportDate" value="<?php echo $RowAbReport['ReportDate']?>">

                </div>
				
				

				
				

				

                <div class="form-group col-lg-3" >

                  <label for="exampleInputEmail1">Parameter</label>
                  <select class="form-control select2" name="Parameter" id="Parameter">
				  		         <option Value=""> Select </option>

					 <?php foreach($ArrayDropDownOptions as $DropDownOptions){
					if($DropDownOptions['Category']=='Parameter'){
						$Options= $DropDownOptions['Name'];
						$Value= $DropDownOptions['OptID']; ?>
			 
					<option <?php if($RowAbReport['Parameter']==$Value){ ?>  selected="selected"<?php } ?>
					
					value="<?php echo $Value ?>"><?php echo $Options ?></option>
					<?php  }}?>
					                  </select>
				
                </div>

				
                <div class="form-group col-lg-3">

                  <label for="exampleInputPassword1"> Loading Status</label>

                  <select class="form-control select2" name="AbStatus" id="AbStatus">
				  		         <option Value=""> Select </option>

					 <?php foreach($ArrayDropDownOptions as $DropDownOptions){
					if($DropDownOptions['Category']=='TestStatus'){
						$Options= $DropDownOptions['Name'];
						$Value= $DropDownOptions['OptID']; ?>
			 
					<option <?php if($RowAbReport['AbStatus']==$Value){ ?>  selected="selected"<?php } ?>
					
					value="<?php echo $Value ?>"><?php echo $Options ?></option>
					<?php  }}?>
					                  </select>
				

                 
                </div>


				

				 <div class="form-group col-lg-3">
                  <label for="exampleInputEmail1">Shift</label>
				 <select class="form-control select2" name="Shift" id="Shift">
				 		         <option Value=""> Select </option>

					 <?php foreach($ArrayDropDownOptions as $DropDownOptions){
					if($DropDownOptions['Category']=='Shift'){
						$Options= $DropDownOptions['Name'];
						$Value= $DropDownOptions['OptID']; ?>
			 
					<option <?php if($RowAbReport['Shift']==$Value){ ?>  selected="selected"<?php } ?>
					value="<?php echo $Value ?>"><?php echo $Options ?></option>
					<?php  }}?>
				<select>
                </div>
				
				
				<div class="form-group col-lg-3">

                  <label for="exampleInputPassword1">Tested By</label>

                 
					 <select class="form-control select2" name="TestedBy" id="TestedBy">
				   		         <option Value=""> Select </option>

				   <?php while ($RowABUsers=$StmtABUsers->fetch_array()){ ?>
				     <option <?php if($RowAbReport['TestedBy']==$RowABUsers['UserID']){ ?>  selected="selected"<?php } ?>
					 value="<?php echo $RowABUsers['UserID'] ?>"><?php echo $RowABUsers['UserName'] ?></option>
				   
				 <?php  }?>
				
						  
				  </select>
               
                </div>                       
				
				<div class="form-group col-lg-3" >

                  <label for="exampleInputEmail1">Sulphite</label>

				  
				   <input required type="text" class="form-control" name='Sulphite'  value="<?php echo $RowAbReport['Sulphite'];?>"
				   id="Sulphite" placeholder="Enter Sulphite">

				

                </div>
				

				
				
                <div class="form-group col-lg-3" >

                  <label for="exampleInputEmail1">Remarks</label>

				  
				   <input required type="text" class="form-control" name='Remarks' id="Remarks" placeholder="Enter Remarks" value="<?php echo $RowAbReport['Remarks'];?>">

				

                </div>
                </div>
				

				

				

               
                
  <div class="col-xs-12 box-footer"  align="right">
                
				<!--<a href="DailyLabReports.php?id=0" class="btn btn-primary">Submit</a>-->
				<a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a>

				<button type="submit"  name='submit' class="btn btn-success">Submit</button>
</div>
     </form>

   </div> <!-- end row -->
    
 </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                    </div> <!-- container-fluid -->
                



            <!-- end main content-->

                          


 
        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>				






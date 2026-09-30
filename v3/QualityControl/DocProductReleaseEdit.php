<?php
session_start();

$QueryParms=$_GET['Q']; 
 $QueryParm=$_GET['Q'];
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$PoID=$_GET['PoID'];

$SqlLabUsers="SELECT * FROM $TblQcLabTechnologist WHERE LbuStatus=1";
$StmtLabUsers=$iconn->query($SqlLabUsers);


$SqlCodeList="SELECT * FROM $TblQcCodeList as QCL
left join ScrmPoData as SPD on SPD.PoID=QCL.InvID
left join QcLabTechnologist as QLT on QLT.LbuName=QCL.VerifiedBy
where InvID='$PoID' ORDER BY CLID ASC";
$StmtCodeList=$iconn->query($SqlCodeList);
$RowCodeList=$StmtCodeList->fetch_array();


$PageTitle="Product Release Edit - ".$RowCodeList['PoNo'];
$PageTitleFolder="Quality Control";



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$SqlUpdate="UPDATE `$TblQcCodeList` SET
	 `ProductReleaseDate`='$ProductReleaseDate',
	 `VerifiedBy`='$VerifiedBy',
	 `ProductReleaseRemarks`='$ProductReleaseRemarks'
	 
	 WHERE InvID='$PoID'";
	$StmtUpdate=$iconn->query($SqlUpdate);
				header('Location: DocAddReports.php?Q='.$QueryParm);

		
	
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

                        <?php include ($IncludesPath."page-title.php");?>
			

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
								 <form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			 <div class="form-group col-sm-3" >
                  
				  <label for="exampleInputEmail1">Product Release Date</label>
                  <input   type="date" class="form-control" name="ProductReleaseDate" id="ProductReleaseDate" 
				   placeholder="" value="<?php echo $RowCodeList['ProductReleaseDate']?>" max="<?php  echo date("Y-m-d");?>">
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>
                   <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
				 
					<?php while ($RowLabUsers=$StmtLabUsers->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUsers['LbuName'] ?>"
					 <?php if($RowCodeList['VerifiedBy']==$RowLabUsers['LbuName']){?>  selected="selected"<?PHP } ?>><?php echo $RowLabUsers['LbuName'] ?></option>
				   
					<?php  }?>				  
				  </select>
   		 
                </div>
				<div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1">Remarks</label>
				  
				  <textarea name="ProductReleaseRemarks" class="form-control" rows="1" cols="50"
                    id="ProductReleaseRemarks"   
					placeholder="Enter Remarks" ><?php echo $RowCodeList['ProductReleaseRemarks'] ?></textarea>
				 		
		
                </div>

				</div>
              
					

			  <div class="col-xs-12 box-footer"  align="right">
			  			   <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name="submit"   class="btn btn-success waves-effect waves-light">
				
				Submit</button>
              </div>
			  </form>

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


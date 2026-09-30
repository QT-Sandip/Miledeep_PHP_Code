<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
$PageTitle="Banks List";
$PageTitleFolder="Sales";
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlUserRoles="SELECT * FROM `AdminClientRoles`  as AUR left join AdminDepartments  
AS AD on AUR.DeptID =AD.DeptID GROUP by AUR.ID;";
$StmtUserRoles=$iconn->query($SqlUserRoles);
$AryRoles=array();
while($Row=$StmtUserRoles->fetch_assoc()){

	$SideBarHeaderTbl=rtrim($Row['SideBarHeaderTbl'], ", \t\n\r\0\x0B");
	$SideBarOptionTbl=rtrim($Row['SideBarOptionTbl'], ", \t\n\r\0\x0B");
	 $HeaderIds=rtrim($Row['Header'], ", \t\n\r\0\x0B");
	$OptionsIds=rtrim($Row['Options'], ", \t\n\r\0\x0B");
	
	$SqlHeaders="SELECT GROUP_CONCAT(Header) as HeaderNames FROM ClientSideBarHeaders  where HeaderID in ($HeaderIds)";
	$StmtHeaders=$iconn->query($SqlHeaders);
	$RowHeaders=$StmtHeaders->fetch_array();
	
	 $SqlOptions="SELECT GROUP_CONCAT(Name) as OptionsNames FROM `ClientSideBarOptions`  where ID in ($OptionsIds)";
	$StmtOptions=$iconn->query($SqlOptions);
	$RowOptions=$StmtOptions->fetch_assoc();

	
	$Row['UserHeader']=$RowHeaders['HeaderNames'];
	$Row['UserOptions']=$RowOptions['OptionsNames'];
	
	if($Row['EditAccess']==1){
		$Row['EditAccess']='Yes';
	}else{
		$Row['EditAccess']='No';	
	}
	
	if($Row['DeleteAccess']==1){
		$Row['DeleteAccess']='Yes';
	}else{
		$Row['DeleteAccess']='No';	
	}
	
$AryRoles[]=$Row;	



}



?>
<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
 <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php include ($IncludesPath."DbLinks.php");?>
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
                                    <div class="card-body" id="ShowDataTable">
									
								<div class="col-lg-12 box-footer"  align="center">
			  	
							 <a href="EditRoles" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Role</a> 
				
					</div>			
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive  w-100" >	
 <thead class="thead-light">
			  <tr>
                
                 
      <th  style="width: 30px;">ID</th>
      <th  style="width: 120px;">Role Name</th>
      <th  style="width: 150px;">Department Name</th>
      <th>Menu</th>
      <th>Sub Menu</th>
      <th style="width: 90px;">Edit Access</th>
      <th style="width: 90px;">Delete Access</th>
      <th style="width: 70px;">Actions</th>
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    foreach($AryRoles as $Item){?>
                       
					   <tr>
                       
<td> <?php echo $SNo ?></td>
		<td> <?php echo $Item['RoleName']?></td>  
		<td> <?php echo $Item['DepartmentName']?></td>  
		<td> <?php echo $Item['UserHeader']?></td>  
		<td> <?php echo $Item['UserOptions']?></td>  
		<td> <?php echo $Item['EditAccess']?></td>  
		<td> <?php echo $Item['DeleteAccess']?></td>  
	
						  <td>
						  
						  
                         
						 <a target="_blank" href="EditRoles.php?id=<?php  echo $Item['ID'];?>" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

                      </td>
                        </tr>
                      <?php
                $SNo++;    }
                  ?>
                </tbody>
              </table>
            	
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
      <?php include ($IncludesPath."ShowDataTable.php");?>

       
    </body>
</html>				

<script>
$(document).ready(function(){
	  showData();

	
    
	});




</script>

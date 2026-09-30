<?php 
// https://serp.silverlights.in/Api/ApiV1.php?f=getUserList

?>

<?php

$PageTitle="Users List";
$PageTitleFolder="Users";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

function GetUsersListAPI(){
    $curl = curl_init();
    
    $headers = array("Content-Type: application/json");

    $data = (object)array(
    'ClientID' => $_SESSION["ClientID"],
    'Token' => "Token");

    $json = json_encode($data);           
    $json = ltrim(rtrim($json, ']'),'[');
                    
    curl_setopt($curl,CURLOPT_URL, 'https://serp.silverlights.in/Api/ApiV1.php?f=getUserList'); 
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl,CURLOPT_POSTFIELDS, $json);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);        

    $MainData = curl_exec($curl);
    $result = json_decode($MainData);
    curl_close($curl);
    
    return $result;
}    
    
    $resUsersList = GetUsersListAPI();
    // var_dump($resUsersList);

// echo $_SESSION["ClientID"];
// echo $_SESSION["SideBarOptionTbl"]."\n";
// echo  $_SESSION["SideBarHeaderTbl"];

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

                        <?php include ($IncludesPath."page-title.php"); ?>			
 
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="ShowDataTable">
								<!-- <div class="col-lg-12 box-footer text-center">	  	
                                    <a href="AddExchangeRate.php" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> Add User 
                                    </a>
					            </div>			 -->
                                <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
                                    <thead class="thead-light">
                                        <tr>
                                            <th>S.No</th>
                                            <th>UserId</th>
                                            <th>Department Name</th>
                                            <th>Login ID</th>
                                            <th>Username</th>
                                            <th>Designation</th>
                                            <th>IP Access</th>
                                            <th>Access Type</th>
                                            <th>Last Login </th>
                                            
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if(!empty($resUsersList->Data)) {
                                            $SNo=1;
                                            foreach ($resUsersList->Data as $items) { 
                                        ?>
                                        <tr>
                                            <td> <?php echo $SNo; ?> </td>
                                            <td> <?php echo $items->UserID; ?></td>
                                            <td> <?php echo $items->DepartmentName; ?></td>
                                            <td> <?php echo $items->UserLoginID; ?></td>
                                            <td> <?php echo $items->UserName; ?></td>
                                            <td> <?php echo $items->DesignationID; ?></td>
                                            <td> <?php echo $items->AccessIPs; ?></td>
                                            <td> <?php 
                                                if($items->AccessType == "1") {
                                                    echo "Super Admin";
                                                }else if($items->AccessType == "2") {
                                                    echo "Admin";
                                                }else{
                                                    echo "User";
                                                }
                                                ?></td>
                                            <td> <?php echo $items->LastLogin; ?></td>
                                            
                                            <td>                    
                                                <a target="" href="EditUser.php?id=<?php echo $items->UserID; ?>" 
                                                    class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
                                                    title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            $SNo ++;
                                            }
                                        } 
                                        ?>
                                    </tbody>
                                </table>            	
                            </div> <!-- end col -->
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

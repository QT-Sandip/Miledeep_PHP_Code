<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
//nclude_once $_SERVER['DOCUMENT_ROOT'] . '/config/api-call.php';
include ($cp."Includes/IncludePage.php");
require_once "./RestAPI.php";

$PageTitle="Store Delivery Details List";
$PageTitleFolder="Masters";

$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 

$api = GetStoreDeliveryDetailsAPI;

$bodyContent = "";

$resGetStoreDeliveryDetailsAPI = CallAPI($api, "POST", "", $bodyContent);

// echo json_encode($resGetStoreDeliveryDetailsAPI, JSON_PRETTY_PRINT);
// exit;

$resList = $resGetStoreDeliveryDetailsAPI->Data->StoreDeliveryDetails; 


// $SqlBrand="SELECT BrandID,ConsigneeName,BrandName,Remarks FROM `ScrmBrandsMaster`as SBM 
// left join ScrmConsigneeDetails as SCD on SBM.BuyerID=SCD.ConID order by BrandID desc";

/*$SqlBrand="SELECT BrandID,ConsigneeName,BrandName,Remarks FROM `ScrmBrandsMaster` as SBM 
left join ScrmConsigneeDetails as SCD on SBM.BuyerID=SCD.ConID ORDER BY BrandID DESC";*/
	
//  $StmtBrand=$iconn->query($SqlBrand);



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
                                            <a href="AddStoreDeliveryDetails" class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i> Add Store Delivery Details</a> 
                                        </div>		

                                        <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
                                            <thead class="thead-light">
                                                <tr>                            
                                                    <th>S.No</th>                
                                                    <th>Store Name</th>
                                                    <th>Store Address</th>
                                                    <th>Buyer</th>
                                                    <!-- <th>Country</th> -->
                                                    <th>Port of Discharge</th>
                                                    <th>Final Destination</th>
                                                    <th>Actions</th>                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php			  
                                                    // 		  $SNo=1;
                                                    // while($RowBrand=$StmtBrand->fetch_array()){
                                                    // $BuyerName=	$RowBrand['ConsigneeName'];	
                                                    // $BrandName= $RowBrand['BrandName'];
                                                    // $Remarks= $RowBrand['Remarks'];
                                                ?>
                                                <?php $i = 1; foreach($resList as $item) { ?>
                                                <tr>                       
                                                    <td><?php echo $i; ?></td>                    
                                                    
                                                    <td><?php echo $item->StoreName ?></td>  
                                                    <td><?php echo $item->StoreAddress ?></td>  
                                                    <td><?php echo $item->Buyer ?></td>  
                                                    <td><?php echo $item->PortofDischarge ?></td>  
                                                    <td><?php echo $item->FinalDestination ?></td>  	
                                                    <td>
                                                        <a href="AddStoreDeliveryDetails?id=<?php echo $item->StoreID ?>" class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit">
                                                        <i class="mdi mdi-pencil font-size-18"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $i++ ; } ?>
                                                    <?php // $SNo++; } ?>
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
      <?php //include ($IncludesPath."ShowDataTable.php");?>

       
    </body>
</html>				

<script>
$(document).ready(function(){
	//    var IsSort=true;
	// 		if(sort==false){
	// 			IsSort=false;
	// 		}else{
	// 			IsSort=true;
	// 		}
		// $("#ShowDataTable").html(data);	

        var table = $('#key-datatable').DataTable( {
			 
            scrollY: "600px",
            scrollX:true,
            sScrollXInner: "150%", 
            paging:true,
            destroy: true,
            fixedColumns: true,
            scrollCollapse:true,
            bSort : true,
            "bStateSave": true,
            dom: 'Blfrtip',
            buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'Store Delivery Details List'}], 
            "fnStateSave": function (oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function (oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
 
	});



</script>

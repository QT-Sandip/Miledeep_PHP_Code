<?php

$PageTitle="Barcode List";
$PageTitleFolder="Cold Store";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$ClientID=$_SESSION["ClientID"];
$BarcodeDate=$_GET["id"];

$sql = "SELECT StoreRackID,GROUP_CONCAT(StockID) as StockID FROM `CsStockData` WHERE StockStatus=1 and AvalMcs!=0 or LooseBags !=0 GROUP by StoreRackID;";  
$stmt = $iconn->query($sql); // Execute the first query
while ($row1 = $stmt->fetch_array()) {


    $PlacedStockID=$RacBarcodeID=$row1['StockID'];
    $StoreRackID=$RacBarcodeID=$row1['StoreRackID'];

$update_sql = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '$PlacedStockID',Status = 1 WHERE `RowID` = '$StoreRackID'";
$stmt3 = $iconn->query($update_sql); 

}


exit;
$sql = "SELECT * FROM `CsStoresRackData` WHERE PlacedStockID != ''";  
$stmt = $iconn->query($sql); // Execute the first query
while ($row1 = $stmt->fetch_array()) {
    $PlacedStockIds = $row1['PlacedStockID'];
    $stockIdsArray = explode(",", $PlacedStockIds);
    // Filter out any empty elements
    $stockIdsArray = array_filter($stockIdsArray);
    $newPlacedStockIds = implode(",", $stockIdsArray);
    $totalItems = count($stockIdsArray);
    $currentIndex = 0;
    $RacBarcodeID=$row1['BarCodeID'];
    foreach ($stockIdsArray as $Item) {
    $currentIndex++;
    //Code to remove non existing stockID's for placedStockID
    $sql2 = "SELECT StockID,AvalMcs,LooseBags,BarCodeID FROM `CsStockData` WHERE StockID = '$Item'"; 
    
    // Execute the second query
    $stmt2 = $iconn->query($sql2);
    $row2 = $stmt2->fetch_array();
    
    // Check if num_rows > 0
    if ($stmt2->num_rows > 0) {
        $StockBarcodeID=$row2['BarCodeID'];
        echo "OK";
        echo "<hr/>";
        //Code to remove stockid for empty AvalMcs OR LooseBags zero
        if($row2['AvalMcs'] == 0 || $row2['LooseBags'] == 0){
            $stockIdsArray = array_diff($stockIdsArray, [$Item]);
            $newPlacedStockIds = implode(",", $stockIdsArray);
        }
        if( $RacBarcodeID!=0 ){
            if($RacBarcodeID != $StockBarcodeID){
                echo "<h1>Barcode: ".$RacBarcodeID."-".$StockBarcodeID."</h1>";
                if ($iconn->query("UPDATE `CsStockData` SET BarCodeID='$RacBarcodeID' WHERE StockID='$Item'") === TRUE) {
                    echo "Barcode for StockID " . $Item . " updated successfully.<br>";
                } else {
                    echo "Error updating record: " . $iconn->error . "<br>";
                }            
            }
        }

    } else {

        echo "No :".$Item." ";
        var_dump($stockIdsArray);
        $stockIdsArray = array_diff($stockIdsArray, [$Item]);
        $newPlacedStockIds = implode(",", $stockIdsArray);
        echo "<hr/>";

    }
    //Code to remove stockout ID's for PlacedStockID
    $sql3 = "SELECT StockID,StockOutID FROM `CsStockOutDetails` WHERE StockID = '$Item' AND OutType='Shipment Out'";
    $stmt3 = $iconn->query($sql3); 
    if ($stmt3->num_rows > 0) {
        echo "No :".$Item." ";
        var_dump($stockIdsArray);
        $stockIdsArray = array_diff($stockIdsArray, [$Item]);
        $newPlacedStockIds = implode(",", $stockIdsArray);
        echo "<hr/>";
    } 
    echo "<hr/>PlacedStockID: ";
    echo $newPlacedStockIds.":".$PlacedStockIds;
    echo "<hr/>Barcode: ";
    echo $StockBarcodeID.":".$RacBarcodeID;
    $RowID=$row1['RowID'];

    if ($currentIndex === $totalItems) {
        echo "This is the last iteration";
        echo "<hr/>";
        echo $newPlacedStockIds;
        $RowID=$row1['RowID'];
        if($newPlacedStockIds == ""){
            $update_sql = "UPDATE `CsStoresRackData` SET `PlacedStockID` = '',Status = 0 ,BarCodeID = 0 WHERE `RowID` = '$RowID'";
        }else{
            $update_sql = "UPDATE `CsStoresRackData` 
                        SET `PlacedStockID` = '$newPlacedStockIds' WHERE `RowID` = '$RowID'";
        }                
        // Execute the update query
        if ($iconn->query($update_sql) === TRUE) {
            echo "Row with RackID " . $RackID . " updated successfully.<br>";
        } else {
            echo "Error updating record: " . $iconn->error . "<br>";
        }
        
    } else {
        echo "Current item: $currentIndex\n"." ".$totalItems;
    }
    echo "<hr/>";

    }

    if($RacBarcodeID!=''){
        $sql4 = "SELECT * FROM `CsStockData` WHERE BarCodeID = '$RacBarcodeID'";  
        $stmt4 = $iconn->query($sql4);
        while ($row4 = $stmt4->fetch_array()) {
            // var_dump($row4['StockID']);
            // var_dump($stockIdsArray);
            // echo $PlacedStockIds;
            if (!in_array($row4['StockID'], $stockIdsArray)) {
                echo "<h1>Value not found.</h1>";
                $row4StockID=$row4['StockID'];
                $iconn->query("UPDATE `CsStockData` SET BarCodeID=0 WHERE StockID='$row4StockID'");
            }
        }    
    }
 
    // // Prepare the second query to check if BarCodeID exists in CsBarCodes
    // $barcode = $row1['BarCodeID'];
    // $RackID = $row1['RowID'];
    // $sql2 = "SELECT BarCodeID FROM `CsBarCodes` WHERE BarCodeID = '$barcode'"; 
    
    // // Execute the second query
    // $stmt2 = $iconn->query($sql2);
    
    // // Check if num_rows > 0
    // if ($stmt2->num_rows > 0) {
    //     // Barcode exists in CsBarCodes
    //     echo "Barcode " . $barcode . " exists in CsBarCodes.<br>";
    // } else {
    //     // Barcode does not exist in CsBarCodes, perform the UPDATE
    //      // $update_sql = "UPDATE `CsStoresRackData` 
    //     //                SET `BarCodeID` = '0', `PlacedStockID` = '', `Status` = 0 
    //     //                WHERE `RowID` = '$RackID'";
        
    //     // // Execute the update query
    //     // if ($iconn->query($update_sql) === TRUE) {
    //     //     echo "Row with RackID " . $RackID . " updated successfully.<br>";
    //     // } else {
    //     //     echo "Error updating record: " . $iconn->error . "<br>";
    //     // }
        
    //     echo "Barcode " . $barcode . " does not exist in CsBarCodes.<br>";
    // }



}






 exit;
 
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
                                    <div class="card-body" id="TraceabilityList">
                                            <div class="card-body" id="ShowDataTable">
                                            
                            <div class="col-lg-12 box-footer"  align="center">

                        
                                </div>      
            
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >                                       
        
<thead class="thead-light">
              <tr>
                
                 
                  <th>S.No</th>                      
                  <th>Batch codes</th>
                  <th>Printed Barcodes</th>
                  <th>USED Barcodes</th>
                  <th>UNUSED Barcodes</th>
                   <th>Actions</th>
                  
               
                </tr>
                </thead>
                <tbody>
                  <?php
                  
                  $SNo=1;
                   
                    while($row1 = $stmt->fetch_array()){
                        
                 $BarCodeValue =$row1['BarCodeValue'];  
                 $BarCodeID =$row1['BarCodeID'];    
                // $BarCodeIDS =$row1['BarCodeIDS'];    
                 
           
 
  $SqlTrace = "
SELECT COUNT(*) AS PRINTED ,
(SELECT COUNT(*) FROM `CsBarCodeList` WHERE Status!='0' AND BarCodeID='$BarCodeID') as USED,
(SELECT COUNT(*) FROM `CsBarCodeList` WHERE Status='0' AND BarCodeID='$BarCodeID') as UNUSED
FROM `CsBarCodeList` WHERE BarCodeID='$BarCodeID'  
 ORDER BY `BarCodeSeriesID` DESC";
 
  $StmtTrace = $iconn->query($SqlTrace);
  $RowTrace = $StmtTrace->fetch_array();
//ECHO $SqlTrace; EXIT;
                        
                        ?>
                       
                       <tr>
                       
                          <td><?php echo $SNo;?></td>
                          <td><?php echo  $BarCodeValue;?></td>
                          <td><?php echo  $RowTrace['PRINTED'];?></td>
                          <td><?php echo  $RowTrace['USED'];?></td>
                          <td><?php echo  $RowTrace['UNUSED'];?></td>
                           
                           
                         
                          <td>                        
                         
                         <a target="_blank" href="ViewBCLogList.php?ID=<?php  echo $RowTrace['TraceID'];?>" 
                         class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
                         title="" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
                              
        
                       </td>
                        </tr>
                      <?php
                $SNo++;    
                }
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

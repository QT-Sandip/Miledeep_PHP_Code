<?php

// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);



include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';



$ClientConfigData = (object)$_SESSION['ClientConfigData'];

$IsStoreProductionLinked = $ClientConfigData->IsStoreProductionLinked;  //sriram

$IsDeductGlazeFW = $ClientConfigData->IsDeductGlazeFW;  //sriram

$TillDateStockOut = $ClientConfigData->TillDateStockOut;  //sriram

$ShowBatchCode = $ClientConfigData->ShowBatchCode;  //sriram



$api = API_STOCK_OUT_DETAILS;



$Date = trim($_POST["date"]);

$ColdStores = trim($_POST["coldStore"]);

$StockBlnTo = trim($_POST["stockBlnTO"]);

$ProductionPlant = trim($_POST["productionPlant"]);

$PackingType = trim($_POST["packingType"]);

$Group = trim($_POST["group"]);

$PageNo = trim($_POST["PageNo"]);

$SearchKey = trim($_POST["SearchKey"]);

$LoadAll = trim($_POST["LoadAll"]);



$bodyContent = array(

    'StockOutDate' => $Date,

    'ColdStoreID' => $ColdStores,

    'ProductionPlant' => $ProductionPlant,

    'StockHolderID' => $StockBlnTo,

    'PackingType' => $PackingType,

    'Group' => $Group,

    'SearchKey' => $SearchKey,

    'PageNo' => $PageNo,

    'LoadAll' => $LoadAll,

    'IsStoreProductionLinked' => $IsStoreProductionLinked,

    'TillDateStockOut' => $TillDateStockOut,

    'IsDeductGlazeFW' => $IsDeductGlazeFW

);



$resList = CallAPI($api, "GET", "", $bodyContent);



/*var_dump($resList);

echo json_encode($resList, JSON_PRETTY_PRINT);

exit;*/

if (isset($resList) && !empty($resList)) {



    $error = $resList->Error;



    if (!isset($error) || empty($error)) {

        // $PageCount = $resList->PageCount;

        // $resTotal = $resList->TotalCount;

        $List = $resList->DailyStockOutDataList;

        $Count = count($List);

?>

<div class="card">

    <div class="card-body">



        <table id="tblDataTable" class="table table-hover table-bordered">

            <thead class="thead-light">

                <tr>

                    <th>S.NO</th>

                    <?php if($TillDateStockOut == 1){ ?>

                        <th>Stock Out Date</th>

                    <?php } ?>

                    <th>Type</th>

                    <?php if($ShowBatchCode == 1){?>

                                 <th style="min-width:80px;">Arrival Date</th>

                                <th style="min-width:80px;">Batch Code</th>

                           <?php }?>

                    <th>Supc</th>

                    <th>Product</th>

                    <th>Grade</th>

                    <th>Packing Type</th>

                    <th>Packing Style</th>

                    <th>Mcs</th>

                    <!-- <th>Balance Mcs</th> -->

                    <th>Bags</th>

                    <!-- <th>Balance Bags</th> -->

                    <th>Kgs</th>

                    <th>Balance Kgs</th>

                    <th>Status</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($List as $item) { ?>

                    <tr>

                        <td><?php echo $item->SNo ?></td>

                        <?php if($TillDateStockOut == 1){ ?>

                            <th><?php echo $item->StockOutDate ?></th>

                        <?php } ?>

                        <td><?php echo $item->Type ?></td>

                         <?php if($ShowBatchCode == 1){?>

                                  <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                    <td><?php echo $item->LotNumber ?></td>

                                <?php }?>

                        <td><?php echo $item->OurSupc ?></td>

                        <td><?php echo $item->ProductDescription ?></td>

                        <td><?php echo $item->Grade ?></td>

                        <td><?php echo $item->PackingType ?></td>

                        <td><?php echo $item->PackingStyle ?></td>

                        <td><?php echo $item->Mcs ?></td>

                        <!-- <td><?php // echo $item->BalanceMcs ?></td> -->

                        <td><?php echo $item->LooseBags ?></td>

                        <!-- <td><?php // echo $item->BalanceBags ?> </td> -->

                        <td><?php echo $item->Kgs ?></td>

                        <td><?php echo $item->BalanceKgs ?></td>

                        <td>

                            <?php if ($item->Status == "1") { //  0 - pending ,  1- comleted 

                            ?>

                                <button class="btn btn-link badge badge-success font-size-10" style="width:70px;background:#025009;color:white">

                                    Completed

                                </button>

                            <?php } else { ?>

                                <button class="btn btn-link badge badge-danger font-size-10" style="width:70px;background:#e20000;color:white">

                                    Pending

                                </button>

                            <?php } ?>

                        </td>

                        <td>

                            <!-- <a href="./daily-production-data.php?ProductionDataID=<?php // echo  $item->ProductionID; 

                                                                                        ?>" id="btnEdit" name="btnEdit"  data-stockid="<?php // echo $item->StockID 

                                                                                                                                        ?>" class="btn btn-link p-0 deleteBrkUp btnEdit">

                                <i class="mdi mdi-eye font-size-16 text-primary"></i>

                            </a> -->

                            <!-- <a href="./daily-production-data.php?ProductionDataID=<?php // echo  $item->ProductionID; 

                                                                                        ?>" id="btnEdit" name="btnEdit" data-stockid="<?php echo $item->StockID ?>" class="btn btn-link p-0 deleteBrkUp btnEdit">

                                <i class="mdi mdi-pencil font-size-16 text-primary"></i>

                            </a> -->

                            <?php if ($resList->AddAccess && $item->Status != "1" && ($IsStoreProductionLinked == 0 || $item->Type != "Melting")) { 

                                 $Type = ($item->Type == 'Conversion Out') ? 'Conversion In' : $item->Type;?>

                                <a href='<?php echo $resList->AddLink . $item->StockOutID .'&type='.$Type .'&kgs='.$item->BalanceKgs.'&glaze='.$item->GlazeType?>' class="mr-3 text-primary  btnActions" data-placement="top" title="ADD Stock" data-original-title="Edit">

                                    <i class="mdi mdi-plus font-size-18"></i>

                                </a>

                            <?php  } ?>

                            <!-- <a href="daily-production-data.php?StockOutID=<?php echo $item->StockOutID; ?>" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="Add Stock" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a> -->

                        </td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>

        <div class="mt-3 mb-3">Showing 1 to

            <?php echo count($List) ?> out of

            <?php echo $Count ?> entries

        </div>



        <?php // $activepagestyle = "border:2px solid #74788d" 

        ?>

        <div>

            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' /> -->

            <ul class="pagination pagination-rounded justify-content-center">

                <?php

                // for ($i = 1; $i <= $PageCount; $i++) {

                //     $pageActive = ($PageNo == $i ? "active" : "");

                //     echo '<li class="page-item"><button class="page-link ' . $pageActive . 'btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';

                // }

                ?>

            </ul>

        </div>

<?php } else {

        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";

    }

} ?>



    </div>  

</div>

<?php

// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);



include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ShowBrandInCSReport = $ClientConfigData->ShowBrandInCSReport;
$ShowBatchCode = $ClientConfigData->ShowBatchCode;
$ProductBatchCode = $ClientConfigData->ProductBatchCode;


$api = API_OUTWARD_REPORT;





$ColdStores = trim($_POST["coldStore"]);

$StockBlnTo = trim($_POST["stockBlnTO"]);

$PackingType = trim($_POST["packingType"]);

$packingStyle = trim($_POST["packingStyle"]);

$poNumber = trim($_POST["poNumber"]);

$variety = trim($_POST["variety"]);

$Group = trim($_POST["group"]);

$Grade = trim($_POST["grade"]);

$FromDate = trim($_POST["fromDate"]);

$ToDate = trim($_POST["toDate"]);

$Blocks = trim($_POST["blocks"]);

$PageNo = trim($_POST["pageNo"]);

$SearchKey = trim($_POST["searchKey"]);

$LoadAll = trim($_POST["loadAll"]);



$bodyContent = array(

    'ColdstoreID' => $ColdStores,

    'Grade' => $Grade,

    'TenantID' => $StockBlnTo,

    'PackingType' => $PackingType,

    'PackingStyle' => $packingStyle,

    'PoID' => $poNumber,

    'VarietyID' => $variety,

    'Group' => $Group,

    'FromDate' => $FromDate,

    'ToDate' => $ToDate,

    'Blocks' => $Blocks,

    'SearchKey' => $SearchKey,

    'PageNo' => $PageNo,

    'LoadAll' => $LoadAll,

    'TypeProduct' => trim($_POST["typeProduct"]),

    'Soaking' => trim($_POST["soaking"]),

    'PDCategory' => trim($_POST["pDCategory"]),

    'FreezingType' => trim($_POST["freezingType"]),

    'AbStatus' => trim($_POST["abstatus"]),
    "ShowBrandInCSReport" => $ShowBrandInCSReport,
    "ProductBatchCode" => $ProductBatchCode

);

//  echo print_r($bodyContent);

//     exit;

$resList = CallAPI($api, "GET", "", $bodyContent);



// $resList = CallAPI($api, "GET", "", $bodyContent);

//         header('Content-Type: application/json');

//         echo json_encode($resList);



// echo json_encode($resList, JSON_PRETTY_PRINT);

// exit;

// var_dump($resList);

if (isset($resList) && !empty($resList)) {



    $error = $resList->Error;



    if (!isset($error) || empty($error)) {
        //echo '<pre>';print_r( $resList);die;
        $PageCount = $resList->PageCount;

        $resTotal = $resList->TotalCount;

        $List = $resList->Data;

        $Totals = $resList->Totals;

        $count = count($List);

        ?>

        <div class="card">

            <div class="card-body">

                <table id="tblDataTable" class="table table-hover table-bordered">

                    <thead class="thead-light">

                        <tr>

                            <th>S.NO</th>

                            <th>StockID</th>

                            <th style="min-width:80px;">Arrival Date</th>

                            <th style="min-width:80px;">Track Code</th>

                             <?php if($ShowBatchCode == 1){?>
                                <th style="min-width:80px;">Batch Code</th>
                            <?php }?>
                             
                            <?php if($ProductBatchCode == 1){?>
                                <th>Arrival Code</th>
                            <?php }?>

                            <th style="min-width:100px;">Production Date</th>

                            <th style="min-width:100px;">StockOut Date</th>

                            <th style="min-width:100px;">Station</th>

                            <th style="min-width:100px;">Po No</th>

                            <!-- <th style="min-width:100px;">BarCode</th> -->

                            <th>Variety</th>

                            <th>Product Type </th>
                            <?php 
                            if($ShowBrandInCSReport == 1){?>
                                <th>Brand</th>
                            <?php }?>
                             
                            <th>Freezing </th>

                            <th>Soaking </th>

                            <th>Group</th>

                            <th>Out Stage</th>

                            <th>Grade</th>

                            <th>Packing Style</th>

                            <th>Category</th>

                            <th>Store </th>

                            <th>Ab Status </th>

                            <th>Location</th>

                            <th>Mcs</th>

                            <th>LooseBags</th>

                            <th>Slabs</th>
                            <th>Kgs</th>

                            <th>Remarks</th>



                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $TotalOutMcs = 0;

                        $TotalOutLooseBags = 0;

                        $TotalKGs = 0;
                        $TotalSlabs = 0;

                        foreach ($List as $item) {

                            $TotalOutMcs += $item->OutMcs;

                            $TotalOutLooseBags += $item->OutLooseBags;

                            $TotalKGs += $item->KGs;
                            $TotalSlabs += $item->McsSlabs;

                            ?>

                            <tr>

                                <td><?php echo $item->SNo ?></td>

                                <td><?php echo $item->StockID ?></td>

                                <td style="min-width:80px;"><?php echo $item->ArrivalDate ?></td>

                                <td style="min-width:80px;"><?php echo $item->TrackCode ?></td>

                                 <?php 
                                if($ShowBatchCode == 1){?>
                                    <td><?php echo $item->LotNumber ?></td>
                                <?php }?>
                                <?php 
                                if($ProductBatchCode == 1){?>
                                    <td><?php echo $item->ProdBatchCode ?></td>
                                <?php }?>

                                <td style="min-width:100px;"><?php echo $item->ProductionDate ?></td>

                                <td style="min-width:100px;"><?php echo $item->OutDate ?></td>

                                <td style="min-width:100px;"><?php echo $item->Unit ?></td>

                                <td style="min-width:100px;"><?php echo $item->PoNo ?></td>

                                <!-- <td  style="min-width:100px;"><?php echo $item->BarCodeValue ?></td> -->

                                <td><?php echo $item->ShortName ?></td>

                                <td><?php echo $item->TypeProduct ?></td>
                                <?php 
                                if($ShowBrandInCSReport == 1){?>
                                    <td><?php echo $item->BrandName ?></td>
                                <?php }?>
                                <td><?php echo $item->FreezingType ?></td>

                                <td><?php echo $item->Soaking ?></td>

                                <td><?php echo $item->OutType ?></td>

                                <td><?php echo $item->OutStage ?></td>

                                <td><?php echo $item->Grade ?></td>

                                <td><?php echo $item->PackingStyle ?></td>

                                <td><?php echo $item->ProductCategoryID ?></td>

                                <td><?php echo $item->Store ?></td>

                                <td><?php echo $item->AbStatus ?></td>

                                <td><?php echo $item->Location ?></td>

                                <!-- <td><?php echo $item->OutMcs ?></td>

                                <td><?php echo $item->OutLooseBags ?></td>

                                <td><?php echo $item->KGs ?></td> -->

                                <td class="out-mcs"><?php echo (int) $item->OutMcs; ?></td>

                                <td class="out-loose-bags"><?php echo $item->OutLooseBags; ?></td>

                                <td class="kgs"><?php echo $item->McsSlabs; ?></td>
                                <td class="kgs"><?php echo $item->KGs; ?></td>

                                <td><?php echo $item->Remarks ?></td>

                            </tr>

                        <?php } ?>

                    </tbody>

                    <tfoot>

                        <tr>

                            <th style="min-width:100px;">Totals</th>

                            <th></th>

                            <th style="min-width:80px;"></th>
                            <?php 
                            if($ShowBatchCode == 1){?>
                                <th style="min-width:80px;"></th>
                            <?php }?>
                              <?php 
                            if($ProductBatchCode == 1){?>
                                <th></th>
                            <?php }?>
                            <th style="min-width:80px;"></th>

                            <th style="min-width:100px;"></th>

                            <th style="min-width:100px;"></th>

                            <th style="min-width:100px;"></th>

                            <th></th>

                            <th></th>

                            <th></th>
                            <?php 
                            if($ShowBrandInCSReport == 1){?>
                                <th></th>
                            <?php }?>

                            <th></th>

                            <th></th>

                            <th></th>

                            <th></th>

                            <th></th>

                            <th></th>

                            <th></th>

                            <th></th>

                            <th></th>

                            <th></th>

                            <td id="TotalOutMcs" style="min-width:100px "><?php echo $TotalOutMcs ?></td>

                            <td id="TotalOutLooseBags"><?php echo $TotalOutLooseBags ?></td>
                            <td id="TotalSlabs"><?php echo $TotalSlabs ?></td>

                            <td id="TotalKGs"><?php echo $TotalKGs ?></td>

                            <th></th>





                        </tr>

                    </tfoot>

                </table>

                <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>



                <?php //$activepagestyle = "border:2px solid #74788d" 

                        ?>

                <div>

                    <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' />

                    <ul class="pagination pagination-rounded justify-content-center">
        <?php
        $start = max(1, $PageNo - 2);
        $end   = min($PageCount, $PageNo + 2);

        // PREVIOUS arrow
        if ($PageNo > 1) {
            echo '<li class="page-item">
                    <button class="page-link btnPageno" data-pageno="'.($PageNo - 1).'">&laquo;</button>
                  </li>';
        }

        // Show first 2 pages
        for ($i = 1; $i <= min(2, $PageCount); $i++) {
            $active = ($PageNo == $i) ? 'active' : '';
            echo '<li class="page-item '.$active.'">
                    <button class="page-link btnPageno" data-pageno="'.$i.'">'.$i.'</button>
                  </li>';
        }

        // Dots before middle
        if ($start > 3) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Middle pages (current ±2)
        for ($i = $start; $i <= $end; $i++) {
            if ($i > 2 && $i < ($PageCount - 1)) {
                $active = ($PageNo == $i) ? 'active' : '';
                echo '<li class="page-item '.$active.'">
                        <button class="page-link btnPageno" data-pageno="'.$i.'">'.$i.'</button>
                      </li>';
            }
        }

        // Dots after middle
        if ($end < $PageCount - 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Last 2 pages
        for ($i = max($PageCount - 1, 3); $i <= $PageCount; $i++) {
            $active = ($PageNo == $i) ? 'active' : '';
            echo '<li class="page-item '.$active.'">
                    <button class="page-link btnPageno" data-pageno="'.$i.'">'.$i.'</button>
                  </li>';
        }

        // NEXT arrow
        if ($PageNo < $PageCount) {
            echo '<li class="page-item">
                    <button class="page-link btnPageno" data-pageno="'.($PageNo + 1).'">&raquo;</button>
                  </li>';
        }
        ?>
    </ul>

                </div>

            <?php } else {

        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";

    }

} ?>

    </div>

</div>

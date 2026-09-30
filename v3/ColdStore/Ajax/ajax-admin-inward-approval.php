<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$api = API_ADMIN_INWARD_APPROVAL;

if(isset($_POST["methodtype"]) && !empty($_POST["methodtype"])) {

    if($_POST["methodtype"] == "inwardApproval") {     
        
        $PageNo = trim($_POST["pageNo"]);
        $SearchKey = trim($_POST["searchKey"]);
        $LoadAll = trim($_POST["loadAll"]);
        $StockStatus = trim($_POST["stockStatus"]);
        $ProductionDate = trim($_POST["productionDate"]);

        $bodyContent = array(
            'StockID' => trim($_POST["stockIdList"]),        
            'PageNo' => $PageNo,
            'SearchKey' => $SearchKey,
            'StockStatus' => $StockStatus,
            'ProductionDate' => $ProductionDate,
            'LoadAll' => $LoadAll
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;

        $resList = CallAPI($api, "POST", "", $bodyContent);

        // echo json_encode($resList, JSON_PRETTY_PRINT);
        // exit;
    } 

} else {

    // $api = API_ADMIN_INWARD_APPROVAL;

    $ColdstoreID = trim($_POST["coldStore"]);
    $StockBlgsTo = trim($_POST["stockBlgsTo"]);
    $VarietyID = trim($_POST["variety"]);
    $Grade = trim($_POST["grade"]);
    $PackingStyle = trim($_POST["pckgStyle"]);
    $StockType = trim($_POST["stockType"]);
    $ShortName = trim($_POST["fShortName"]);
    $PoNumber = trim($_POST["PoNumber"]);
    $Species = trim($_POST["species"]);
    $TypeProduct = trim($_POST["typeProduct"]);
    $freezingType = trim($_POST["freezingType"]);
    $StockStatus = trim($_POST["stockStatus"]);

    $PageNo = trim($_POST["pageNo"]);
    $SearchKey = trim($_POST["searchKey"]);
    $LoadAll = trim($_POST["loadAll"]);

    $bodyContent = array(
        'ColdstoreID' => $ColdstoreID,
        'TenantID' => $StockBlgsTo,
        'VarietyID' => $VarietyID,
        'PoGradeId' => $Grade,
        'PackingStyleID' => $PackingStyle,
        'StockType' => $StockType,
        'Species' => $Species,
        'ShortName' => $ShortName,
        'TypeProduct' => $TypeProduct,
        'PoNumber' => $PoNumber,
        'FreezingType' => $freezingType,
        'StockStatus' => $StockStatus,

        'PageNo' => $PageNo,
        'SearchKey' => $SearchKey,
        'LoadAll' => $LoadAll
    );


    $resList = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;

}

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $Status = $resList->StockStatus;
        $List = $resList->Data;

?>
        <?php if (!isset($error) || empty($error)) { ?>

            <input type="hidden" id="hdnSuccess" value="<?php echo $resList->Msg ?>" />
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th class="cellCheckbox text-center" style="min-width: 80px;">
                            <?php if ($Status == 0) { ?>
                                <input type="checkbox" class="big-checkbox" id="checkall" />
                            <?php } ?>
                        </th>
                        <th style="width: 40px">S.No</th>
                        <th style="width: 40px">BID</th>
                        <th style="min-width: 100px">Date</th>
                        <th>Variety</th>
                        <th>Grade</th>
                        <th>Soaking</th>
                        <th>Packing Type</th>
                        <th>Packing style</th>
                        <th>Category</th>
                        <th>AB Status</th>
                        <th>Po Number</th>
                        <th>Lot Number</th>
                        <th>MCS</th>
                        <th>Loose</th>
                        <th style="min-width: 100px">Store</th>

                        <?php if ($Status != 0) { ?>
                            <th style="min-width: 100px">Location</th>
                        <?php } ?>
                        
                        <th style="min-width: 110px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td class="tdCheckList text-center">
                                <?php if ($item->StockStatus == 0) { ?>
                                    <input type="checkbox" class="rowCheck big-checkbox" data-stockid="<?php echo $item->StockID ?>" value="<?php echo $item->StockID ?>" />
                                    <input type="hidden" class="hdnStockId" value="<?php echo $item->StockID ?>" />
                                <?php } ?>
                            </td>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->BarCodeID ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item->ProductionDate)) ?></td>
                            <td><?php echo $item->VarietyName ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->SockingType ?></td>
                            <td><?php echo $item->PackingTypeID ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->ProductCategoryID ?></td>
                            <td><?php echo $item->AbStatus ?></td>
                            <td><?php echo $item->PoNo ?></td>
                            <td><?php echo $item->LotNo ?></td>
                            <td><?php echo $item->AvalMcs ?></td>
                            <td><?php echo $item->LooseBags ?></td>
                            <td style="width: 150px"><?php echo $item->StoreName ?></td>

                            <?php if ($Status != 0) { ?>
                                <td><?php echo $item->Location ?></td>
                            <?php } ?>

                            <td>
                                <?php if ($item->StockStatus == 0) {
                                    $color = '#FA0F04';
                                } else {
                                    $color = '#025009';
                                } ?>
                                <div class="bg-active color-palette text-center">
                                    <span class="btn btn-link badge badge-warning font-size-10 btnPIStatus" style="width:70px;background:<?php echo $color ?>;color:white" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
                                        <?php echo ($item->StockStatus == 0) ? "Pending" : "Approved"; ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>

        <!-- <input type="hdnSuccess" value="<?php echo $resList->Msg; ?>" /> -->
        <div style="display: flex;">
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
            <ul class="pagination pagination-rounded " style="overflow-x: auto;margin: 0px auto;"> <!-- justify-content-center -->
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}

 ?>
<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$IsDeductGlaze = $ClientConfigData->IsDeductGlaze;  //sriram
$IsDeductGlazeFW = $ClientConfigData->IsDeductGlazeFW;  //sriram
$convertLooseToMcs = $ClientConfigData->convertLooseToMcs;  //sriram


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_STOCK_PREVIEW;

    if ($methodType == "get") {

        $IBTID = trim($_POST['IBTID']);
        $Type = trim($_POST['type']);

        if ($Type == 'IBT IN') {
            $deleteAccess = 'disabled';
        } else {
            $deleteAccess = '';
        }
        $bodyContentGet = array(
            "IBTRequestID" => $IBTID
        );

        $result = CallAPI($api, "GET", "", $bodyContentGet);
        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit;
    } else if ($methodType == "delete") {

        $IBTBreakUpID = trim($_POST["breakUpID"]);
        $IBTRequestID = trim($_POST["ibtID"]);

        $bodyContentPost = array(
            "IBTBreakupID" => $IBTBreakUpID,
            "IBTRequestID" => $IBTRequestID
        );
        $result = callAPI($api, "DELETE", "", $bodyContentPost);

        // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
        // exit;
    }
    // echo json_encode($result);
    if (isset($result) && !empty($result)) {

        $Msg = $result->Msg;
        $Error = $result->Error;

        if (!isset($Error) || empty($Error)) {
            $List = $result->Data;
            $Totals = $result->Total;
            $RequestStatus = $result->RequestStatus;

            $stockOutDate = $List[0]->StockOutDate;
            $stockOutReason = $List[0]->StockOutReason;
            $ToStore = $List[0]->ToStore;
            $TotalMcs = 0;
            $TotalLooseBags = 0;
            // echo json_encode($ToStore, JSON_PRETTY_PRINT);
            // exit;
?>
            <div id="stockDetails"
                data-tostore="<?php echo $ToStore ?>"
                data-stockoutdate="<?php echo $stockOutDate ?>"
                data-stockoutreason="<?php echo $stockOutReason ?>"
                data-Msg="<?php echo $Msg ?>"
                data-requestStatus="<?php echo $RequestStatus ?>">
            </div>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 25px;">S.NO</th>
                        <th>PoNo</th>
                        <th>Transfer Date </th>
                        <th>Variety</th>
                        <th>Grades</th>
                        <th>Mcs</th>
                        <th>Loose Bags</th>
                        <th>Packing Style</th>
                        <th>Packing Type</th>
                        <th>Category</th>
                        <th>Store Name</th>
                        <th>Rack Name</th>
                        <th>Kgs</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { $TotalMcs += $item->NoOfMcs; $TotalLooseBags += $item->NoOfLooseBags?>
                        <tr>
                            <td><?php echo $item->SNO ?>
                                <input type="hidden" class="hdnStockId" value="<?php echo $item->StockID ?>" />
                                <input type="hidden" class="hdnBarCodeID" value="<?php echo $item->BarCodeID ?>" />
                                <input type="hidden" class="hdnStoreID" value="<?php echo $item->StoreID ?>" />
                                <input type="hidden" class="hdnRackID" value="<?php echo $item->RackID ?>" />
                                <input type="hidden" class="hdnBlockID" value="<?php echo $item->BlockID ?>" />
                                <input type="hidden" class="hdnProductionID" value="<?php echo $item->ProductionID ?>" />
                                <input type="hidden" class="hdnStockOutDate" value="<?php echo $item->StockOutDate ?>" />
                                <input type="hidden" class="hdnStockOutReason" value="<?php echo $item->StockOutReason ?>" />
                                <input type="hidden" class="hdnMcs" value="<?php echo $item->NoOfMcs ?>" />
                                <input type="hidden" class="hdnLooseBags" value="<?php echo $item->NoOfLooseBags ?>" />
                                <input type="hidden" class="hdnIBTOutReason" value="<?php echo $item->Reason ?>" />
                            </td>
                            <td><?php echo $item->PoNumber ?></td>
                            <td><?php echo $item->TransferDate ?></td>
                            <td><?php echo $item->ShortName ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <td class="tdMcs"><?php echo $item->NoOfMcs ?></td>
                            <td class="tdLooseBags"><?php echo $item->NoOfLooseBags ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->PackingTypeID ?></td>
                            <td><?php echo $item->ProductCategoryID ?></td>
                            <td><?php echo $item->StoreName ?></td>
                            <td><?php echo $item->RackName ?></td>
                            <td><?php echo $item->Kgs ?></td>
                            <td><?php echo $item->Reason ?></td>
                            <td class="text-center">
                                <?php if ($result->DeleteAccess && $RequestStatus != 3) { ?>
                                    <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->IBTBreakupID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete" <?php echo $deleteAccess ?>>
                                        <i class="mdi mdi-trash-can font-size-16"></i>
                                    </button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Totals</td>
                    <td><?=$TotalMcs?></td>
                    <td><?=$TotalLooseBags?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tfoot>
            </table>
<?php
        } else {
            // echo "<script>alert('$msg');</script>";
        }
    }
    if ($methodType == "ibtOut") {

        $api = API_IBT_OUT;

        $bodyContent = array(
            "ProductionID" => $_POST["productionID"],
            "ProductionDate" => $_POST["productionDate"],
            "IBTRequestID" => $_POST["ibtRequestID"],
            "StockOutDate" => $_POST["stockOutDate"],
            "ToStoreID" => $_POST["toStore"],
            "StockOutReason" => $_POST["stockOutReason"],
            "ReStockinStage" => $_POST["reStockinstage"],
            // "Remarks" => $_POST["remarks"],
            "TotalMsc" => $_POST["totalMsc"],
            "TotalLooseBags" => $_POST["totalLooseBags"],
            "StockItems" => $_POST["stockItems"],
            "TransTypeId" => $_POST["selectedTransTypeId"],
            "PlantID" => $_POST["productionPlant"],
            "convertLooseToMcs" => $convertLooseToMcs

        );


        $res = CallAPI($api, "POST", "", $bodyContent);

        // echo json_encode($res, JSON_PRETTY_PRINT);
        // exit;
        header('Content-Type: application/json');
        echo json_encode($res);

        //  exit;
    }
}

?>
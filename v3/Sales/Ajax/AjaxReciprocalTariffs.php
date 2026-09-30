<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$PoDate = isset($_POST["PoDate"]) ? $_POST["PoDate"] : null;
$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : null;
$OnWater = isset($_POST['onWater']) ? $_POST['onWater'] : null;

$PoIDs = isset($_POST['PoIDs']) ? $_POST['PoIDs'] : [];
$ReciprocalTariff = isset($_POST['ReciprocalTariff']) ? $_POST['ReciprocalTariff'] : [];
$RevisedPo = isset($_POST['RevisedPo']) ? $_POST['RevisedPo'] : [];


$api = API_RECIPROCAL_TARIFF_DETAILS;
if ($methodType == "getPoNumbers") {
    $bodyContent = array(
        'PoDate' => $PoDate,
        'methodType' => $methodType,
        'OnWater' => $OnWater
    );
    $resCustom = CallAPI($api, "POST", "", $bodyContent);
    echo json_encode($resCustom, JSON_PRETTY_PRINT);
    exit;
} else if ($methodType == "getReciprocalTariffs") {

    $bodyContent = array(
        'PoDate' => $PoDate,
        'methodType' => $methodType,
    );
    $resCustom = CallAPI($api, "POST", "", $bodyContent);
    echo json_encode($resCustom, JSON_PRETTY_PRINT);
    exit;
} else if ($methodType == "postReciprocalTariff") {

    $bodyContent = array(
        'methodType' => $methodType,
        'PoIDs' => $PoIDs,
        'ReciprocalTariff' => $ReciprocalTariff,
        'RevisedPo' => $RevisedPo,
        'PoDate' => $PoDate,
    );
    $resCustom = CallAPI($api, "POST", "", $bodyContent);
    echo json_encode($resCustom, JSON_PRETTY_PRINT);
    exit;
} else if ($methodType == "updateReciprocalTariff") {

    $bodyContent = array(
        'methodType' => $methodType,
        'PoIDs' => $PoIDs,
        'ReciprocalTariff' => $ReciprocalTariff,
        'RevisedPo' => $RevisedPo,
        'PoDate' => $PoDate,
    );
    $resCustom = CallAPI($api, "POST", "", $bodyContent);
    echo json_encode($resCustom, JSON_PRETTY_PRINT);
    exit;
} else if ($methodType == "deleteReciprocalTariff") {

    $bodyContent = array(
        'methodType' => $methodType,
        'PoIDs' => $PoIDs,
        'PoDate' => $PoDate,
    );
    $resCustom = CallAPI($api, "POST", "", $bodyContent);
    echo json_encode($resCustom, JSON_PRETTY_PRINT);
    exit;
} else if ($methodType == "getReciprocalTariff") {
    $bodyContent = array(
        'methodType' => $methodType,
        'PoDate' => $PoDate,
    );

    $resCustom = CallAPI($api, "POST", "", $bodyContent);

    // echo json_encode($resCustom, JSON_PRETTY_PRINT);
    // exit;

    if (isset($resCustom) && !empty($resCustom)) {

        $error = $resCustom->Error;


        // if (isset($error) &&$error != true) {
        // $PageCount = $resCustom->PageCount;
        $resList = $resCustom->Data;


        // var_dump($resCustom);

        // exit;


        ?>
                            <table id="tblDataTable" class="table table-hover table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="min-width:50px;">SNO</th>
                                        <th style="min-width:100px;">Po Date</th>
                                        <th style="min-width:50px;">Po ID</th>
                                        <th style="min-width:120px;">Po Number</th>
                                        <th style="min-width:140px;">Reciprocal Tariff</th>
                                        <th style="min-width:350px;">Revised Po</th>
                                        <th style="min-width:100px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    <?php
                    $SNO = 1;
                    foreach ($resList as $item) {

                        ?>
                                        <!-- var_dump($item->CustomPoUrl); -->
                                        <tr>
                                            <td>
                            <?php echo $SNO ?>
                                            </td>
                                            <td>
                            <?php echo $item->PoDate ?>
                                            </td>
                                            <td>
                            <?php echo $item->PoID ?>
                                            </td>
                                            <td>
                            <?php echo $item->PoNo ?>
                                            </td>
                                            <td>
                            <?php echo $item->ReciprocalTariff ? "YES" : "NO"; ?>
                                            </td>
                                            <td>
                            <?php echo $item->RevisedPo ? "YES" : "NO"; ?>
                                            </td>

                                            <td class="text-center">
                                                <button class="btn btn-link text-primary btnEditReciprocalTariff" data-poid="<?php echo $item->PoID ?>"
                                                    data-podate="<?php echo $item->PoDate ?>"
                                                    data-reciprocaltariff="<?php echo $item->ReciprocalTariff ?>"
                                                    data-revisedpo="<?php echo $item->RevisedPo ?>"><i class="mdi mdi-pencil font-size-16"></i></button>
                                                <button class="btn btn-link text-danger btnDeleteReciprocalTariff"
                                                    data-poid="<?php echo $item->PoID ?>"><i class="mdi mdi-trash-can font-size-16"></i></button>
                                            </td>
                                        </tr>
                        <?php
                        $SNO++;
                    } ?>

                                </tbody>
                            </table>
        <?php
        // } else {
        //     echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
        // }
    }
}

?>
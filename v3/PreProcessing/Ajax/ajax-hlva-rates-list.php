<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HLVA_RATES_LIST;
// $Species = trim($_POST["Species"]);
$Month = trim($_POST["Month"]);
$Year = trim($_POST["Year"]);
$StationID = trim($_POST["StationID"]);
$ContractorType = trim($_POST["ContractorType"]);


$bodyContent = array(
    // 'Species' => $Species,
    'Month' => $Month,
    'Year' => $Year,
    'StationID' => $StationID,
    'ContractorType' => $ContractorType,
    'Shift' => $_POST['Shift']
);

$resList = callAPI($api, "GET", "", $bodyContent);

// var_dump($resList);

if (isset($resList) || !empty($resList)) {

    $error = $resList->Error;
    $msg = $resList->Msg;
    // var_dump($resList);

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;

        ?>

        <div class="card">
            <div class="card-body">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.NO</th>
                            <th>Date</th>
                            <th>Variety</th>
                            <th>Tools</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $item) { ?>
                            <tr>
                                <td><?php echo $item->SNO ?></td>
                                <td><?php echo $item->EffectiveDate ?></td>
                                <td><?php echo $item->ShortName ?></td>
                                <td><a target="_blank" href="Reports/hlva-rates-print.php?RateID=<?php echo $item->ID ?>"
                                        class="mr-3 text-success" data-toggle="tooltip" data-placement="top" title=""
                                        data-original-title="View"><i class="dripicons-preview font-size-18"></i></a>
                                    <?php if ($_SESSION["AccessType"] == '1') { ?>
                                        <a href='<?php echo $resList->EditLink . $item->ID
                                            ?>' class="mr-3 text-primary btnActions" data-placement="top" title="Edit"
                                            data-original-title="Edit">
                                            <i class="mdi mdi-pencil font-size-16"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
} ?>
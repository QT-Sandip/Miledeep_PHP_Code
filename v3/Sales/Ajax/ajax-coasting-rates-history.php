<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COASTING_RATES;

$Species = trim($_POST["Species"]);
$Month = trim($_POST["Month"]);
$Year = trim($_POST["Year"]);


$bodyContent = array(
    'Species' => $Species,
    'Month' => $Month,
    'Year' => $Year
);

$result = CallAPI($api, "POST", "", $bodyContent);

$error = $result->Error;
$List = $result->Data;
$LastUpdatedDate = $result->LastUpdatedDate;
// var_dump($List);


?>

<?php if (!isset($error) || empty($error)) { ?>
    <div class="card">
        <div class="card-body">
            <?php if ($LastUpdatedDate != null) { ?>
                <div align="center">
                    <span id="LastUpdatedDate"><?php echo "Last Updated Date is : " . date('d-m-Y', strtotime($LastUpdatedDate)) ?></span>
                </div>
            <?php } ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.NO</th>
                        <th>Date</th>
                        <th>Tools</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->FromDate ?></td>
                            <td><a target="_blank" href="Reports/coasting-rates-print.php?id=<?php echo $item->ID ?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i></a>
                                <a href="AddCostingRates?SType=<?php echo $item->SType?>&id=<?php echo $item->ID?>" class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>
    </div>
<?php } ?>
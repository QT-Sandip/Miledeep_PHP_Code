<?php
//  session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$api = API_LINER_CONTRACT;

$MethodType = trim($_POST["MethodType"]);


if ($MethodType == 'POST') {


    $LinerContractID = trim($_POST["LinerContractID"]);
    $Liner = trim($_POST["liner"]);
    $ContractNumber = trim($_POST["contractNumber"]);
    $Value = trim($_POST["value"]);
    $Period = trim($_POST["period"]);
    $Month = trim($_POST["month"]);
    $StartDate = trim($_POST["startDate"]);
    $EndDate = trim($_POST["endDate"]);
    $PageNo = trim($_POST["pageNo"]);
    $CountryId = trim($_POST["CountryId"]);
    $bodyContent = array(
        'LinerContractID' => $LinerContractID,
        'Liner' => $Liner,
        'ContractNumber' => $ContractNumber,
        'Value' => $Value,
        'Period' => $Period,
        'Month' => $Month,
        'StartDate' => $StartDate,
        'EndDate' => $EndDate,
        'PageNo' => $PageNo,
        'CountryId' => $CountryId,
    );


    $result = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($result);
}else if ($MethodType == 'getLinerContractNos') {
    $bodyContent = $_POST;
    $result = CallAPI($api, "POST", "", $bodyContent);
  
    header('Content-Type: application/json');
    echo json_encode($result);die;
} else {

    $resList = CallAPI($api, "GET", "", array());

    // echo json_encode($resList, JSON_PRETTY_PRINT);
    if (isset($resList) && !empty($resList)) {
        $error = $resList->Error;


        $List = $resList->LinerContractData;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.No</th>
                        <th>Liner</th>
                        <th>Country</th>
                        <th>Contract Number</th>
                        <th>Value</th>
                        <th>Period</th>
                        <th>Month</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>

                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($List as $item) {
                        // var_dump($List);
                        // exit;
        
                        ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td class="tdLiner" data-value="<?php echo $item->Liner; ?>"><?php echo $item->LinerName; ?></td>
                            <td class="tdCountry" data-value="<?php echo $item->CountryId; ?>"><?php echo $item->CountryName; ?></td>
                            <td class="tdContarctNumber" data-value="<?php echo $item->ContractNumber; ?>">
                                <?php echo $item->ContractNumber; ?>
                            </td>
                            <td class="tdValue" data-value="<?php echo $item->Value; ?>"><?php echo $item->Value; ?></td>
                            <td class="tdPeriod" data-value="<?php echo $item->Period; ?>"><?php echo $item->Period; ?></td>
                            <td class="tdMonth" data-value="<?php echo $item->Month; ?>"><?php echo $item->MonthName; ?></td>
                            <td class="tdStartDate" data-date="<?php echo $item->StartDate; ?>"><?php echo $item->StartDate; ?></td>
                            <td class="tdEndDate" data-date="<?php echo $item->EndDate; ?>"><?php echo $item->EndDate; ?></td>
                            <td>
                                <!-- <a class="text-primary btnEdit" data-id="<?php echo $item->LinerContractID; ?>" title="Edit">
                                <i class="mdi mdi-pencil font-size-16"></i> -->
                                <!-- </a> -->

                                <button class="btnEdit" data-id="<?php echo $item->LinerContractID; ?>" title="Edit"
                                    style="border: none; background: none; padding: 0; cursor: pointer;">
                                    <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                </button>
                            </td>

                        </tr>
                        </tfoot>
                    <?php } ?>
                    </tr>
                    <?php
        }
    }
}

?>
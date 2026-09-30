<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_FINANCIER_LIST;

$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);
$resList = callAPI($api, "GET", "", $bodyContent);
// var_dump($resList);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;


    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;

        ?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>SNO</th>
                    <th>Financier Type</th>
                    <th>Financier Name</th>
                    <th>PAN Number</th>
                    <th>GST No</th>
                    <th>Bank Name</th>
                    <th>Account No</th>
                    <th>IFSC</th>
                    <th>Address</th>
                    <th>EmailID</th>
                    <th>Phone Number</th>
                    <th>Company</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($List as $item) { ?>
                    <tr>
                        <td><?php echo $item->SNO ?></td>
                        <td><?php echo $item->SupplierType ?></td>
                        <td><?php echo $item->FinancierName ?></td>
                        <td><?php echo $item->PanNumber ?></td>
                        <td><?php echo $item->GstNo ?></td>
                        <td><?php echo $item->BankName ?></td>
                        <td><?php echo $item->BankAccNo ?></td>
                        <td><?php echo $item->IFSC ?></td>
                        <td><?php echo $item->Address ?></td>
                        <td><?php echo $item->EMailID ?></td>
                        <td><?php echo $item->PhoneNumber ?></td>
                        <td><?php echo ($item->IsCompany == 0) ? "Non-Company" : "Company"; ?></td>
                        <td class="text-center">
                            <?php if ($resList->EditAccess) { ?>
                                <a href='<?php echo $resList->EditLink . $item->ID ?>' class="mr-3 text-primary btnActions"
                                    data-placement="top" title="Edit Financier" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
        <?php $activepagestyle = "border:2px solid #74788d" ?>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
    <?php } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
} ?>
<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EXCESS_STOCK_REPORT_GRADEWISE;

$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(

    'ColdstoreID' => trim($_POST["coldStore"]),
    'TenantID' => trim($_POST["stockBlgsTo"]),
    'VarietyID' => trim($_POST["variety"]),
    'PackData' => trim($_POST["packingType"]),
    'Category' => trim($_POST["pdCategory"]),
    'Group' => trim($_POST["group"]),
    'Grade' => trim($_POST["grade"]),
    'PackingStyle' => trim($_POST["pckgStyle"]),
    'FreezingType' => trim($_POST["freezingType"]),
    'TypeProduct' => trim($_POST["productType"]),
    'Blocks' => trim($_POST["blocksTwo"]),
    'Soaking' => trim($_POST["soaking"]),
    'AntiBiotic' => trim($_POST["abStatus"]),
    'PONumber' => trim($_POST["poNumber"]),
    'Species' => trim($_POST["species"]),
    // 'ClosingDate' => trim($_POST["closingDate"]), // 
    'Brand' => trim($_POST["brand"]),
    'Type' => trim($_POST["Type"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"])

);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
        $GradesList = $resList->Grades;
        $StockTotal = $resList->StockTotal;
        $ProductTotal = $resList->ProductTotal;
?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50px;">S.No</th>
                        <th>Packing Type</th>
                        <th>Brand</th>
                        <th>Variety</th>
                        <th>Packing</th>
                        <th>Product Type</th>
                        <th>Freezing Type</th>
                        <th>Species</th>
                        <th>Product Category</th>
                        <th>Ab Status</th>
                        <th>Glaze</th>
                        <th>Glaze Type</th>
                        <th>Soaking </th>
                        <?php foreach ($GradesList as $list) { ?>
                            <th><?php echo $list ?></th>
                        <?php } ?>
                        <th>MCS</th>
                        <!-- <th>Loose</th> -->
                        <th>Slabs</th>
                        <th>KGs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $SNO = 0; ?>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo ++$SNO ?></td>
                            <td><?php echo $item->PackingType ?></td>
                            <td><?php echo $item->Brand ?></td>
                            <td style="/*font-weight:bold;font-size:15px;color:red;text-decoration:underline;*/"><?php echo $item->Species . '-' . $item->Variety ?></td>
                            <td><?php echo $item->PackingStyle ?></td>
                            <td><?php echo $item->TypeProduct ?></td>
                            <td><?php echo $item->FreezingType ?></td>
                            <td><?php echo $item->Species ?></td>
                            <td><?php echo $item->ProductCategory ?></td>
                            <td><?php echo $item->AbStatus ?></td>
                            <td><?php echo $item->GlazePerc ?></td>
                            <td><?php echo $item->Glaze ?> </td>
                            <td><?php echo $item->Soaking ?> </td>
                            <?php
                            foreach ($item->SubGrades as $SubGrades) {
                                foreach ($GradesList as $list) {
                                    if ($list == $SubGrades->GradeName) {
                                        echo '<td>' . $SubGrades->GradeValue . '</td>';
                                    }
                                }
                            }
                            ?>
                            <td><?php echo $item->ProductMcs ?></td>
                            <!-- <td><?php echo $item->ProductLooseBags ?></td> -->
                            <td><?php echo $item->ProductSlabs ?></td>
                            <td><?php echo $item->ProductTotalKgs ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Overall Totals</th>
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
                        <th></th>
                        <th></th>
                        <?php
                        foreach ($StockTotal as $total) {
                            foreach ($GradesList as $list) {
                                if ($list == $total->GradeName) {
                                    echo '<th>' . $total->GradeValue . '</th>';
                                }
                            }
                        }
                        ?>
                        <th><?php echo $ProductTotal->TotalMcs ?></th>
                        <!-- <th><?php echo $ProductTotal->TotalLooseBags ?></th> -->
                        <th><?php echo $ProductTotal->TotalLSlabs ?></th>
                        <th><?php echo $ProductTotal->TotalWeight ?></th>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $resTotal ?>' />
            <ul class="pagination pagination-rounded justify-content-center">
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
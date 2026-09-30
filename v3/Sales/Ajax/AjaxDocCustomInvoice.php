<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ID = trim($_POST["ID"]);
$HasSauce = trim($_POST["HasSauce"]);

$api = API_INVOICES_PRINT_PAGES_LIST_POPUP;

$bodyContent = array(
    "PoID" => $ID,
    "HasSauce" => $HasSauce,
    "DocType" => "1",
    "ReportCat" => "1"
);

$resGetInvoicesPrintPagesList = CallAPI($api, "GET", "", $bodyContent);
$resList = $resGetInvoicesPrintPagesList->Data;

?>
<table id="tblList" data-paging="false" class="table  wrap w-150">
    <thead class="thead-light">
        <tr>
            <th style="min-width: 60px;">Available Reports</th>
            <th style="min-width: 160px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resList as $item) { ?>
            <tr>
                <td><?php echo $item->ReportName ?></td>
                <td><a target="_blank" href="<?php echo $item->Url ?>" class="mr-3 text-primary"><i class="mdi mdi-printer font-size-16"></i></a></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
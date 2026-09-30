<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$MCSRestriction = $ClientConfigData->MCSRestriction;  //for displaying stock status for Master and Dummy in seperate Column
$HideViewOption = $ClientConfigData->HideViewOption; 

$api = API_RUNNING_ORDERS;

$bodyContent = array(

    'Exporter' => trim($_POST["exporter"]),
    'Processor' => trim($_POST["processor"]),
    'Consignee' => trim($_POST["consignee"]),
    'ColdStore' => trim($_POST["coldStore"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"]),
    'MCSRestriction' => $MCSRestriction
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;

        // echo print_r( $List );
        // exit;


?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>
                        <th>ID</th>
                        <th>P.Inv No </th>
                        <th>P.Inv Date</th>
                        <th>Po No </th>
                        <th>Po Date</th>
                        <th>Country</th>
                        <th>Buyer</th>
                        <?php 
                        if(!empty($MCSRestriction)){?>
                            <th>Master Status</th>
                            <th>Dummy Status</th>
                        <?php }else{?>
                            <th>Status</th>
                         <?php }
                        if($HideViewOption == 0){?>
                            <th>Actions</th>
                        <?php }?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->PoID ?></td>
                            <td><?php echo $item->InvNo ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item->InvDate)) ?></td>
                            <td><?php echo $item->PoNo ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item->PoDate)) ?></td>
                            <td><?php echo $item->CountryName ?></td>
                            <td><?php echo $item->ConsigneeName ?></td>
                            
                            <?php 
                            if(!empty($MCSRestriction)){?>
                                <td id="m<?php echo $item->PoID ?>">
                                    <!-- master -->
                                    <div class=" badge <?php echo $item->MasterColorclass ?> font-size-10 " style="width:70px;background:<?php echo $item->MasterColor ?>;color:white">
                                        <?php echo $item->MasterPercent ?>
                                    </div>
                                </td>
                                <td id="d<?php echo $item->PoID ?>">
                                    <!-- dummy -->
                                    <div class=" badge <?php echo $item->DummyColorclass ?> font-size-10 " style="width:70px;background:<?php echo $item->DummyColor ?>;color:white">
                                        <?php echo $item->DummyPercent ?>
                                    </div>
                                </td>
                            <?php }else{?>
                                    <td id="<?php echo $item->PoID ?>">
                                        <div class=" badge <?php echo $item->Colorclass ?> font-size-10 " style="width:70px;background:<?php echo $item->Color ?>;color:white">
                                            <?php echo $item->Percent ?>
                                        </div>
                                    </td>   
                            <?php }
                            if($HideViewOption == 0){?>                            
                                <td> <a target="_blank" href="CreateShipmentStatus.php?PoID=<?php echo $item->PoID ?>" class="mr-3 text-success" data-toggle="tooltip" data-placement="top" title="View Shipment Status" data-original-title="View">
                                        <i class="dripicons-preview font-size-18"></i> 
                                    </a>
                                </td>
                            <?php }?>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
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
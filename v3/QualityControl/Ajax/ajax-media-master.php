<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_MEDIA_MASTERS;

$actionName = (isset($_POST['action']) && $_POST['action']) ? $_POST['action'] : '';

if( $actionName == "InsertMasterMedia"){
    $bodyContent = array(
        'ParameterId' => $_POST['ParameterId'],
        'MediaId' => $_POST['MediaId'],        
        'MediaName' => $_POST['MediaName']        
    );

    $resList = CallAPI($api, "POST", "", $bodyContent);    
    header('Content-Type: application/json'); 
    echo json_encode($resList, JSON_PRETTY_PRINT);
    exit;
}

if( $actionName == "GetMediaData"){
    $bodyContent = array(
        'MediaId' => $_POST['MediaId'],   
        'SearchKey' => $SearchKey,
        'PageNo' => $PageNo,
        'LoadAll' => $LoadAll
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);   
    header('Content-Type: application/json'); 
    echo json_encode($resList, JSON_PRETTY_PRINT);
    exit;
}

$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resList = CallAPI($api, "GET", "", $bodyContent);

 //var_dump($resList);
 //echo json_encode($resList, JSON_PRETTY_PRINT);
 //exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;


?>
        <?php if (!isset($error) || empty($error)) { ?>

            <div class="card">
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25px">S.No</th>
                                <th>Parameter Name</th>
                                <th>Media Name</th>                                
                                <th style="width: 100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($List as $item) { ?>
                                <tr>
                                    <td><?php echo $item->SNO ?></td>
                                    <td><?php echo $item->ParameterName ?></td>
                                    <td><?php echo $item->MediaName ?></td>
                                    <td class="text-center">
                                        <?php if ($resList->EditAccess) { ?>
                                            <a href='javascript:void(0)' class="mr-3 text-primary btnActions" data-placement="top" title="Edit Media Names" data-original-title="Edit" onClick="EditMedia('<?php echo $item->MediaId; ?>')">
                                                <i class="mdi mdi-pencil font-size-16"></i>
                                            </a>
                                        <?php  } ?>
                                    </td>
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
                </div>
            </div>
    <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
    ?>
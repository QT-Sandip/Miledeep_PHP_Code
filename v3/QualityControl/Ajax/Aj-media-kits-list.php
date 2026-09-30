<?php 
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";


    include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
    include_once ROOT . 'config/api-call.php';

    function fetchLookupMap($iconn, $query, $keyField, $valueFields)
    {
        $result = $iconn->query($query);
        $map    = [];
        while ($row = $result->fetch_assoc()) {
            $key   = $row[$keyField];
            $value = [];
            foreach ($valueFields as $field) {
                $value[$field] = $row[$field];
            }
            $map[$key] = $value;
        }
        return $map;
    }

    $allParameters  = fetchLookupMap($iconn, "SELECT TestID, TestName FROM QcTestList", 'TestID', ['TestName']);
    $allMedia       = fetchLookupMap($iconn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);

    $api = API_MEDIA_KITS;
    $PageNo = trim($_POST["pageNo"]);
    $AccessType = trim($_POST["accessType"]);
    $Action = trim($_POST["Action"]);
    $DeleteAccess = trim($_POST["DeleteAccess"]);
    if ($Action == "Delete") {
        $KitID = trim($_POST["KitID"]);
        $bodyContent = array('KitID' => $KitID, 'PageNo' => $PageNo);
        $DeleteAccess = trim($_POST["DeleteAccess"]);

        $oldData = legacyAuditFetchRowById('mediakitsdetails', 'mKitID', $KitID, 'QualityControl');

        // Call the API to delete the record
        $resDelete = CallAPI($api, "DELETE", "", $bodyContent);

        if (empty($resDelete->Error)) {
            legacyAuditLogDeleteById('mediakitsdetails', 'mKitID', $KitID, $oldData, $_POST, array('remarks' => 'Deleted media kit ID: ' . $KitID), 'QualityControl');
        }

        // Return the response as JSON
        header('Content-Type: application/json');
        echo json_encode($resDelete);
    }else{
        $bodyContent = array(
            'FromDate' => trim($_POST["fromDate"]),
            'ToDate' => trim($_POST["toDate"]),
            'SearchKey' => trim($_POST["search"]),
            'PageNo' => trim($_POST["start"]),
            'LoadAll' => trim($_POST["loadAll"]),
            'DeleteAccess' => $DeleteAccess,
        );
        $resList = CallAPI($api, "GET", "", $bodyContent);
    }

    //echo "<pre>"; print_r($resList); exit;
    $finalArray = [];
    if (isset($resList) && !empty($resList)) {

        $error = $resList->Error;
        //$List = $resList->Data;

        if (!isset($error) || empty($error)) {
            $PageCount = $resList->PageCount;
            $resTotal = $resList->TotalCount;
            $List = $resList->Data;
            foreach($List as $item){                
                $ation ="";
                if ($item->DeleteAccess == 1) {
                    $ation ="<a href='".$item->EditURL."' class='mr-3 text-primary btnActions' data-placement='top' title='Edit' data-original-title='Edit'> <i class='mdi mdi-pencil font-size-16'></i> </a> <button type='button' name='btnDelete' data-kitid='".$item->mKitID."' class='btn btn-link p-0 btnDelete'> <i class='mdi mdi-delete font-size-16 text-danger'></i> </button>";
                }
                $inner_array = array(
                    "Sno" => $item->SNO,                    
                    "mMedia" => $allMedia[$item->mMedia]['MediaName'] ? $allMedia[$item->mMedia]['MediaName'] : '',
                    "mKitsOrderedDate" => $item->mKitsOrderedDate,
                    "mKitsReceivedDate" => $item->mKitsReceivedDate,
                    "mKitsLotNo" => $item->mKitsLotNo,
                    "mNoOfKitsOrdered" => $item->mNoOfKitsOrdered,
                    "mNoOfKitsReceived" => $item->mNoOfKitsReceived,
                    "mInvoiceNo" => $item->mInvoiceNo,
                    "mManufactureDate" => $item->mManufactureDate,
                    "mExpiryDate" => $item->mExpiryDate,
                  "mBottleWeight" => $item->mBottleWeight,

                    "Action" =>$ation
                );
                $finalArray[] = $inner_array;
            }
            
            if (!isset($error) || empty($error)) { 

            }     
        }
    }

    echo json_encode([
        "draw"            => intval($_POST['draw']),
        "recordsTotal"    => intval($resTotal),
        "recordsFiltered" => intval($PageCount),
        "data"            => $finalArray, // could be empty, and that's okay
    ]);
    exit;

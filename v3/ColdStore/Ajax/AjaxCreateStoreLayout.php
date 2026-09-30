<?php 
    include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
    include_once ROOT.'config/api-call.php';

    $methodType = trim($_POST["methodType"]);

    if(isset($methodType) && $methodType == "put") {
        $api = API_CREATE_STORE_LAYOUT;

        $jsonObj = trim($_POST["jsonObj"]);
        $storeID = trim($_POST["storeID"]);
        $blockID = trim($_POST["blockID"]);
        $floorID = trim($_POST["floorsID"]);

        // echo $jsonObj;
        // echo json_encode($jsonObj);
        $bodyContent = array(
            "StoreID" => $storeID,
            "BlockID" => $blockID,
            "FloorID" => $floorID,
            "ResObj" =>  json_decode($jsonObj)//$jsonObj
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;

        $result = CallAPI($api, "PUT", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode($result);

    }else { 

        $storeID = trim($_POST["storeID"]);
        $blockID = trim($_POST["blockID"]);
        $floorID = trim($_POST["floorsID"]);
        $rows = trim($_POST["rows"]);
        $cols = trim($_POST["cols"]);

        $api = API_CREATE_STORE_LAYOUT;

        $bodyContent = array(
            "StoreID" => $storeID,
            "BlockID" => $blockID,
            "FloorID" => $floorID,
            "Rows" => $rows,
            "Column" => $cols
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;

        $resList = CallAPI($api, "POST", "", $bodyContent);

        $error = $resList->Error;
        // $resList->Data;
        // echo json_encode($resList, JSON_PRETTY_PRINT);
        // exit;

        if(!isset($error) || empty($error)) {

            $List = $resList->Data;
            echo '
                <div class="card">
                    <div class="card-body">';
            echo '<div style="overflow-y: auto">';
            echo '<table>';            
                echo '<tr class="trHeaderCol">';
                    echo '<td class="text-center">#</td>';
                    for($a = 1; $a <= count($List[0]->RowData); $a++) {
                        echo '<td  style="min-width: 85px;">
                                <div class="form-check" name="rackID_'.$a.'">
                                    <input class="form-check-input chkAllCol" data-colno="'.$a.'" type="checkbox"  id="chkCol'.$a.'" checked />
                                    <label for="chkCol'.$a.'">Col-'.$a.'</label>
                                </div>
                            </td>';
                    }
                echo '</tr>';

                // for($r = 1; $r <= $rows; $r++) {
                    $r = 1;
                    foreach($List as $item) {
                    echo '<tr>';

                        echo '<td class="tdHeaderRow">
                                <div class="form-check">
                                    <input class="form-check-input chkAllRow" type="checkbox" data-rowno="'.$r.'" id="chkRow'.$r.'" checked />
                                    <label for="chkRow'.$r.'">Row-'.$r.' </label>
                                    &nbsp;&nbsp; <input type="text" class="txtRow" data-rid="'.$r.'"  />
                                </div>
                            </td>';

                        // for($c = 1; $c <= $cols; $c++){
                            $c = 1;
                            foreach($item->RowData as $subitem) {
                                if($subitem->RackType == "1") {
                                    // $divChk = "";
                                    // $divPath = "display:none;";
                                    $txtShow = "";
                                    $pathShow = "display:none;";
                                    $checked = "checked";
                                    $tdBgWhite = "";
                                }else {
                                    // $divChk = "display:none;";
                                    // $divPath = "";       
                                    $txtShow = "display:none;";
                                    $pathShow = "";            
                                    $checked = "";
                                    $tdBgWhite = "tdbgbg-white";
                                }
                            echo '<td class="tdRow'.$r.' tdCol'.$c.' tdCell'.$r.'-'.$c.' '.$tdBgWhite.'">';
                                echo '
                                <div class=" form-check dvRow'.$r.' dvCol'.$c.'" id="div'.$r.'-'.$c.'" >
                                    <input class="form-check-input chkRow'.$r.' chkCol'.$c.'" id="chk'.$r.'-'.$c.'" type="checkbox" data-cellid="'.$r.'-'.$c.'" 
                                        data-row="" data-col="" id="chk-" '.$checked.'>
                                    <input type="text" class="txtCellName txtRow'.$r.' txtCol'.$c.'"  style="'.$txtShow.'" name="cellname" id="txt'.$r.'-'.$c.'" value="'.$subitem->Name.'" />
                                    <input type="hidden" value="'.$subitem->RackID.'" id="hdn'.$r.'-'.$c.'" />

                                    <i class="fa-solid fa-person-walking iPathRow'.$r.' iPathCol'.$c.' iCell'.$r.'-'.$c.'" style="position: relative;top: 4px;margin-left: 10px;'.$pathShow.'"></i>
                                </div>
                                <!--
                                <div class="text-center dvPathRow'.$r.' dvPathCol'.$c.'" style="width: 69px;'.$divPath.'" id="path'.$r.'-'.$c.'">
                                    <i class="fa-solid fa-person-walking"></i>
                                </div>-->';

                            echo '</td>';
                            $c++;
                        }
                        $r++;
                    echo '</tr>';
                }
            echo '</table>';
            echo '</div>';

            echo '
            <div class="text-center" style="margin-top: 25px;">
                <input type="hidden" value="'.count($List[0]->RowData).'" id="hdnColCount" />
                <input type="hidden" value="'.count($List).'" id="hdnRowCount" />
                <button class="btn btn-primary" id="btnSave">Save</button>
            </div>';

            echo '
                </div>
            </div>';
        }

        // echo '<table>';
        //     for($r = 1; $r <= $rows; $r++) {
        //         echo '<tr>';
        //             for($c = 1; $c <= $cols; $c++){
        //                 echo '<td><input type="text" data-row="'.$r.'" data-col="'.$c.'" /></td>';
        //             }
        //         echo '</tr>';
        //     }
        // echo '</table>';
    
}

?>
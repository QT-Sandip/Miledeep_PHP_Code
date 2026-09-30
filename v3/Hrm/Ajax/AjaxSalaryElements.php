<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


if(isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

$api = API_HRM_SALARY_ELEMENTS_MASTER_LIST;

if($methodType == "get") {
    
    $resSalaryElements = CallAPI($api, "GET", "", $bodyContent);

} else if($methodType == "post") {

    $ElementName = trim($_POST["elementName"]);
    $ElementType = trim($_POST["elementType"]);
    $ElementsValues = trim($_POST["elementsValues"]);
    $BasedOn = trim($_POST["basedOn"]);
    $CalcType = trim($_POST["calculationMode"]);

     $bodyContent = array(
        "ElementName" => $ElementName,
        "ElementType" => $ElementType,
        "ElementsValues" => $ElementsValues,
        "BasedOn" => $BasedOn,
        "CalcType" => $CalcType
    );

    
    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;
    $resSalaryElements = CallAPI($api, "POST", "", $bodyContent);
}

// echo json_encode($resSalaryElements, JSON_PRETTY_PRINT);

if (isset($resSalaryElements) && !empty($resSalaryElements)) {

    $error = $resSalaryElements->Error;

    if (!isset($error) || empty($error)) {
        
        $resList = $resSalaryElements->Data->SalaryElementsData;

        echo '
        <div class="card">
            <div class="card-body">

                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width:50px;">SNo</th>
                            <th style="min-width:120px;">Element Name</th>
                            <th style="min-width:100px;">Element Type</th>
                            <th style="min-width:140px;">Elements Values</th>
                            <th style="min-width:350px;">Based On</th>
                            <th style="min-width:350px;">Calc Mode</th>
                            <th style="min-width:100px;" class="text-center">Actions </th>
                        </tr>
                    </thead>
                    <tbody>';
                        foreach ($resList as $item) {
                            echo '
                            <tr>
                                <td>'. $item->SNo .'</td>
                                <td>'. $item->ElementName .'</td>
                                <td>'. $item->ElementType .'</td>
                                <td>'. $item->ElementsValues .'</td>
                                <td>'. $item->BasedOn .'</td>
                                <td>'. $item->CalcType .'</td>
                                <td></td>
                            </tr>';
                        }
                    echo '
                    </tbody>
                </table>

            </div>
        </div>
        ';
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }

} else {
   // echo "jjo";
}

?>
<?php 
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$finalArray = [];    
$api = API_RETURN_CONTAINER_LATEST;
    
$bodyContent = array(
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    'SearchKey' => trim($_POST["search"]),
    'productionPlant' => trim( $_POST['productionPlant']),
    'species' => trim( $_POST['species']),
    // 'Soaking' => trim( $_POST['Soaking']),
    'Variety' => trim( $_POST['Variety']),
    'Grade' => trim( $_POST['Grade']),
    'PackingStyle' => trim( $_POST['PackingStyle']),
    // 'ProductCategory' => trim( $_POST['ProductCategory']),
    'PageNo' => trim($_POST["start"]),
    'LoadAll' => trim($_POST["LoadAll"]),
    'DeleteAccess' => $DeleteAccess,
);
$resList = CallAPI($api, "GET", "", $bodyContent);

//echo "<pre>"; print_r($resList); exit;
    
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;        
    
    if (!isset($error) || empty($error)) {

            $PageCount = $resList->PageCount;
            $resTotal = $resList->TotalCount;
            $List = $resList->Data;
            header('Content-Type: application/json');
        echo json_encode($List); 
        exit;
            $i=0;
            ?>
            <table  id="mytable-data" class="table dt-responsive nowrap w-100" >
                <thead>
                    <tr>
                        <th>Sno </th>
                        <th>Type </th>                        
                        <th>Po Number </th>
                        <th>Po Date </th>                       
                        <th>Return Container Date</th>
                        <th>Supc</th>
                        <th>Product</th>         
                        <th>Grade </th>
                        <th>Packing Type </th>
                        <th>Packing Style </th>
                        <th>Mcs </th>
                        <th>Loose Bags </th>
                        <th>KGs </th> 
                        <th>
                            <table>                                                
                                <tr>
                                    <th>Production Date</th>                        
                                    <th>Transaction Date</th>
                                    <th>Production Plant</th>                       
                                    <th>PoNo</th>
                                    <th>Packing Style</th>
                                    <th>Product</th>         
                                    <th>Grade</th>
                                    <th>Packing Type </th>
                                    <th>Group</th>
                                    <th>Mcs</th>                               
                                    <th>Loose Bags</th>                             
                                    <th>KGs</th>                             
                                    <th>Remarks</th>                             
                                </tr>
                            </table>
                        <th>
                    </tr>
                </thead>                                        
                <tbody>
                    <?php
                foreach($List as $item){    
                    $i++;            
                    ?>
                    <tr>
                        <td><?php echo $i; ?> </td>
                        <td><?php echo $item->ProductionTypeID?> </td>                        
                        <td><?php echo $item->PoID?> </td>
                        <td><?php echo $item->PoDate?> </td>                       
                        <td><?php echo $item->emptyReturn?> </td>
                        <td><?php echo $item->DeviSUPC?> </td>
                        <td><?php echo $item->Variety?> </td>
                        <td><?php echo $item->Grade?> </td>
                        <td><?php echo $item->PackingTypeID?> </td>
                        <td><?php echo $item->PackingStyle?> </td>
                        <td><?php echo $item->AvalMcs?> </td>
                        <td><?php echo $item->looseBags?> </td>
                        <td><?php echo $item->AvalKgs?> </td>
                        <td>
                            <table>     
                                <?php if( isset($item->Breackup) && is_array($item->Breackup) && count($item->Breackup)>0 ){
                                        foreach($item->Breackup as $listitem){ ?>
                                        <tr>
                                            <td><?php echo $listitem->ProductionDate ?></td>                        
                                            <td><?php echo $listitem->TransactionDate ?></td>
                                            <td><?php echo $listitem->ProductionPlantName ?></td>
                                            <td><?php echo $listitem->PoNo ?></td>
                                            <td><?php echo $listitem->PackingStyle ?></td>
                                            <td><?php echo $listitem->ProductDescription ?></td>
                                            <td><?php echo $listitem->Grade ?></td>
                                            <td><?php echo $listitem->Group ?></td>
                                            <td><?php echo $listitem->PackingType ?></td>
                                            <td><?php echo $listitem->Mcs ?></td>
                                            <td><?php echo $listitem->LooseBags ?></td>
                                            <td><?php echo $listitem->Kgs ?></td>
                                            <td><?php echo $listitem->remarks ?></td>
                                        </tr>
                            <?php } }else{ ?>
                                <tr>
                                    <td></td>                        
                                    <td></td>
                                    <td></td>                       
                                    <td></td>
                                    <td></td>
                                    <td></td>         
                                    <td></td>
                                    <td> </td>
                                    <td></td>
                                    <td></td>                               
                                    <td></td>                             
                                    <td></td>                             
                                    <td></td>                             
                                </tr>
                            <?php } ?> 
                            </table>
                        </td>
                    </tr>
                <?php } ?>
                
                    </tbody>
                                    </table>
                    <?php 


                if(0){


                $inner_array = array(
                    "Sno" => $i,
                    "Type" => $item->ProductionTypeID,
                    "Ponumber" => $item->PoID,
                    "PoDate" => $item->PoDate,
                    "ReturnContainerDate" => $item->emptyReturn,
                    "Supc" => $item->DeviSUPC,
                    "Product" => $item->Variety,
                    "Grade" => $item->Grade,
                    "PackingType" => $item->PackingTypeID,
                    "PackingStyle" => $item->PackingStyle,
                    "Mcs" => $item->AvalMcs,
                    "LooseBags" => $item->looseBags,
                    "Kgss" => $item->AvalKgs,

                    "cProductionDate" => $item->ProductionDate,
                    "cTransationDate" => $item->TransactionDate,
                    "cPlant" => $item->StationName,                    
                    "cPono" => $item->PoNo,                    
                    "cPackingStyle" => $item->PackingStyle,
                    "cProduct" => $item->Variety,
                    "cGrade" => $item->Grade,
                    "cPackingType" => $item->PackingTypeID,
                    "cGroup" => $item->Group,
                    "cMcs" => $item->AvalMcs,
                    "cLooseBags" => $item->looseBags,
                    "cKgss" => $item->AvalKgs,
                    "cRemarks" => $item->Remarks,
                );
                $finalArray[] = $inner_array;
            }
            }
            
            if (!isset($error) || empty($error)) { 

            }     
        }
    

    echo json_encode([
        "draw"            => intval($_POST['draw']),
        "recordsTotal"    => intval($resTotal),
        "recordsFiltered" => intval($PageCount),
        "data"            => $finalArray, // could be empty, and that's okay
    ]);
    exit;    
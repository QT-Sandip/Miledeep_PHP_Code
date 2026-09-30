<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$VarietyID = $_POST['VarietyID'];



 $sqlInvoicesList = "SELECT DISTINCT InvNumFull as ProformaInvoiceNo ,CustomsInvNo   FROM `ScrmPoGradeData` as SPGD 
 left join ScrmPIData as SPD on SPD.PoID=SPGD.InvId 
 left join ScrmCustomInvoices as SCI on SPD.PoID=SCI.PiID 
 WHERE VarietyID=$VarietyID order by InvNumFull asc"; 
$stmtInvoicesList = $iconn->query($sqlInvoicesList);
// $RowInvoicesList = $stmtInvoicesList->fetch_array();

$data .= '<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >
                    <thead class="thead-light">
                    <th>P.Invoice.No</th>
                    <th>C.Invoice.No</th>
                    
                    </thead>
                    <tbody>';
// echo json_encode($StmtLate->fetch_array(), JSON_PRETTY_PRINT);
while ($RowInvoicesList = $stmtInvoicesList->fetch_array()) {
    $ProformaInvoiceNo=$RowInvoicesList['ProformaInvoiceNo'];
    $CustomsInvNo=$RowInvoicesList['CustomsInvNo'];



	$data .= '<tr>
		 <td>' .  $ProformaInvoiceNo . '</td>
		 <td>' .  $CustomsInvNo . '</td> </tr>';
}



echo	 $data .= '</tbody>

		</table>';


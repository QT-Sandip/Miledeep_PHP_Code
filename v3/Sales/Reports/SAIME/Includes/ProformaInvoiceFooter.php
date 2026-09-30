<?php

/**
 * PROFORMA INVOICE - FOOTER SECTION
 *
 * This file prints the amount in words, processor declaration and signature.
 * It also closes the main invoice table opened by ProformaInvoiceHeader.php.
 */

// Invoice amount in words.
$html .= '<tr><td colspan="10"><b>' . strtoupper(saimeReportHtml($iconn, $ID, 'CurrencyName', 'US DOLLARS')) . ' ' . $e(strtoupper(numberTowords($totalAmount))) . ' ONLY.</b></td></tr>';

// Processor declaration and exporter signature.
$html .= '<tr nobr="true"><td colspan="6" style="height:58px;font-size:6pt"><b>PROCESSED AND PACKED BY</b><br><b>' . $e($processorName) . '</b><br>FDA REGISTRATION NO:' . $e($ProcessorData['ExpUsfdaNo']) . '<br>' . $nl($processorAddress) . '<br>' . saimeProcessorContactHtml($iconn, $ID) . '<br><br>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</td><td colspan="4" align="center" valign="bottom" style="font-size:6pt">Signature &amp; date<br><b>for ' . $e($exporterName) . '</b><br><br><br><br><br>CHAIRMAN</td></tr>';

$html .= '</table>';


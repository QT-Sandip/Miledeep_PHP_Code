<?php

/**
 * PROFORMA INVOICE - HEADER SECTION
 *
 * This file prints everything from the invoice title up to the line-item
 * column headings. The variables used here are prepared in
 * DocProformaInvoice.php before this file is included.
 *
 * Keep the colspan, width, height and border values unchanged unless the
 * printed invoice format itself must change.
 */

// Render the title separately so its top edge is drawn once, at normal thickness.
$html .= '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><th align="center" valign="middle" style="height:20px;font-size:12pt;line-height:1.1;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000"><b>PROFORMA INVOICE</b></th></tr></table>';
$html .= '<table border="1" cellpadding="2" cellspacing="0" width="100%">';

// Keep the exporter aligned with the three compact detail rows on the right.
$html .= '<tr><td width="55%" colspan="5" rowspan="3" valign="top" style="height:84px"><span class="label"><u>Exporter:</u></span><br><b>' . $e(strtoupper($exporterName)) . '</b><br><b>' . $nl(strtoupper($exporterAddress)) . '</b></td>';
$html .= '<td width="45%" colspan="5" valign="top" style="height:25px"><span class="label">INVOICE No. &amp; Date :</span><br><b>' . $e($invoiceNumber) . ', DT: ' . $e($invoiceDate) . '</b></td></tr>';
$html .= '<tr><td colspan="5" valign="top" style="height:29px"><span class="label">Buyer\'s order no &amp; date</span><br><b>' . $e($PoDataRow['PoNo']) . ($poDate ? ', ' . $e($poDate) : '') . '</b></td></tr>';
$html .= '<tr><td colspan="5" valign="top" style="height:30px"><b>PROCESSOR CODE:</b> ' . $e($ProcessorData['ExpApprovalNo']) . '<br><b>IE CODE:</b> ' . $e($ExporterData['ExpIeCode']) . '<br><b>USFDA BIOTERRORISM REGN NO:</b> ' . $e($ProcessorData['ExpUsfdaNo']) . '</td></tr>';

// Importer and consignee on the left; bank sections begin cleanly on the right.
$html .= '<tr><td width="55%" colspan="5" cellpadding="0" valign="top"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="65%"><b>IMPORTER OF RECORD</b></td><td width="35%" align="right"><b>' . ($importerCode !== '' ? 'IRS NO. ' . $e($importerCode) : '') . '</b></td></tr></table><b>' . $e(strtoupper($importerName)) . '</b><br><b>' . $nl(strtoupper($importerAddress)) . '</b><br>' . $notifyPartyHtml . '</td></tr><tr><td style="border-top:1px solid #000"><span class="label">Ultimate Consignee :</span><br><b>' . $e(strtoupper($ultimateName)) . '</b><br><b>' . $nl(strtoupper($ultimateAddress)) . '</b></td></tr></table></td>';
$html .= '<td width="45%" colspan="5" cellpadding="0" valign="top"><table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td>' . $bankHtml . '</td></tr><tr><td style="border-top:1px solid #000">' . $correspondingBankHtml . '</td></tr></table></td></tr>';

// Shipment, destination and payment details.
$html .= '<tr><td colspan="2"><span class="label">Pre-Carriage by:</span><br><b>CONTAINER</b></td><td colspan="3"><span class="label">Place of Receipt by:</span><br><b>' . $e($placeReceipt) . '</b></td><td colspan="2" align="center"><span class="label">Country of Origin of Goods :</span><br><b>' . saimeReportHtml($iconn, $ID, 'CountryOfOrigin', 'INDIA') . '</b></td><td colspan="3" align="center"><span class="label">Country of Final Destination :</span><br><b>' . $e(strtoupper($countryName)) . '</b></td></tr>';
$html .= '<tr><td colspan="2"><span class="label">Vessel/ Flight No.</span></td><td colspan="3"><span class="label">Port of Loading :</span><br><b>' . saimeReportHtml($iconn, $ID, 'LoadingPort', 'INDIAN PORTS') . '</b></td><td colspan="5" rowspan="2"><span class="label">Terms of Delivery &amp; Payment :</span><br><b>' . $nl($paymentTerms) . '</b><br>TRANSPORT TERMS: ' . $e($shippingLine) . $referenceNoHtml . '<br><b>SHIPMENT DATE: ' . $e(getFormatedDate($PoDataRow['ShipByDate'])) . '</b></td></tr>';
$html .= '<tr><td colspan="2"><span class="label">Port of Discharge:</span><br><b>' . $e(strtoupper($portDischarge)) . '</b></td><td colspan="3"><span class="label">Final Destination:</span><br><b>' . $e(strtoupper($finalDestination)) . '</b></td></tr>';

// Line-item column headings.
$html .= '<tr><th width="8%">Marks &amp; Nos.<br>Container No.</th><th width="9%">No of Kind<br>Packages</th><th width="30%" colspan="2">Description of Goods</th><th width="7%">ITEM<br>CODE</th><th width="8%">SIZE</th><th width="5%">No. Of<br>Cases</th><th width="9%">Total Wt.<br>(LBS)</th><th width="8%">Rate/LB<br>' . saimeReportHtml($iconn, $ID, 'UnitCurrency', 'USD') . '</th><th width="16%" align="center">AMOUNT<br>' . saimeReportHtml($iconn, $ID, 'UnitCurrency', 'USD') . '</th></tr>';


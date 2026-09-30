<?php

/**
 * PROFORMA INVOICE - LINE-ITEM BODY SECTION
 *
 * Every product is printed as one table row. When a long description wraps,
 * TCPDF increases the height of that whole row, so all values stay aligned.
 */

$productAreaHeight = $productSpecs !== '' ? 88 : 76;
$specRowHeight = $productSpecs !== '' ? 92 : 0;
$weightRowHeight = 58;

/* Visible widths match the heading: 8+9+30+7+8+5+9+8+16 = 100%. */
$html .= '<tr><td colspan="10" cellpadding="0">';
$html .= '<table border="0" cellpadding="2" cellspacing="0" width="100%">';

// Destination heading at the top of the Amount column.
$html .= '<tr>';
$html .= '<td width="47%" style="height:26px;border-right:1px solid #000"></td>';
$html .= '<td width="7%" style="height:26px;border-right:1px solid #000"></td>';
$html .= '<td width="8%" style="height:26px;border-right:1px solid #000"></td>';
$html .= '<td width="5%" style="height:26px;border-right:1px solid #000"></td>';
$html .= '<td width="9%" style="height:26px;border-right:1px solid #000"></td>';
$html .= '<td width="8%" style="height:26px;border-right:1px solid #000"></td>';
$html .= '<td width="16%" align="center" style="height:26px;border-bottom:1px solid #000"><b><u>' . $e(strtoupper($PoDataRow['ShippingTerms'])) . '</u></b><br><b>' . $e($amountDestination) . '</b></td>';
$html .= '</tr>';

// Prepare the proforma values in the common renderer's column order.
// Same product + packing style + brand is one group: the packages count and
// description print once. All grade values are listed inside the right-side
// cells of that same physical row; this avoids TCPDF rowspan/page-break overlap.
$breakupRows = array();
foreach ($groups as $group) {
    $meta = $group['meta'];
    $description = !empty($meta['Description']) ? $meta['Description'] : $meta['Variety'];
    $groupPackagesHtml = '<b>' . number_format($group['cases']) . '<br>M/CARTONS</b>';
    $groupDescriptionHtml = '<b>' . number_format($group['cases']) . ' M/CARTONS OF ' . $nl($description)
        . '<br>PACK: ' . $e($meta['PackingStyle']) . '<br>BRAND: ' . $e($meta['Brand'])
        . (trim((string) $PoDataRow['CerificationName']) !== '' ? ' (' . $e(formatSaimeCertificationName($PoDataRow['CerificationName'])) . ')' : '') . '</b>';

    $itemCodes = array();
    $grades = array();
    $cases = array();
    $weights = array();
    $rates = array();
    $amounts = array();
    foreach ($group['items'] as $item) {
        $itemCode = trim((string) $item['BuyerItemCode']);
        $rate = rtrim(rtrim(number_format($item['_rateLb'], 3), '0'), '.');
        $itemCodes[] = $e($itemCode);
        $grades[] = $e($item['Grade']);
        $cases[] = number_format($item['PIMcs']);
        $weights[] = number_format($item['_weightLb'], 3);
        $rates[] = $rate;
        $amounts[] = number_format($item['_amount'], 2);
    }

    $breakupRows[] = array(
        '',
        $groupPackagesHtml,
        $groupDescriptionHtml,
        '<b>' . implode('<br>', $itemCodes) . '</b>',
        '<b>' . implode('<br>', $grades) . '</b>',
        '<b>' . implode('<br>', $cases) . '</b>',
        '<b>' . implode('<br>', $weights) . '</b>',
        '<b>' . implode('<br>', $rates) . '</b>',
        '<b>' . implode('<br>', $amounts) . '</b>'
    );
}

$breakupColumns = array(
    array('width' => '8%', 'align' => 'center'),
    array('width' => '9%', 'align' => 'center'),
    array('width' => '30%', 'borderAfter' => true),
    array('width' => '7%', 'align' => 'center', 'borderAfter' => true),
    array('width' => '8%', 'align' => 'center', 'borderAfter' => true),
    array('width' => '5%', 'align' => 'center', 'borderAfter' => true),
    array('width' => '9%', 'align' => 'center', 'borderAfter' => true),
    array('width' => '8%', 'align' => 'center', 'borderAfter' => true),
    array('width' => '16%', 'align' => 'center')
);
$html .= renderSaimeBreakupRows($breakupRows, $breakupColumns, $productAreaHeight);
// Optional product specifications remain below all line items.
if ($productSpecs !== '') {
    $html .= '<tr>';
    $html .= '<td colspan="3" width="47%" valign="top" style="height:' . $specRowHeight . 'px;border-top:1px solid #000;border-right:1px solid #000"><b>PRODUCT SPECS :</b> ' . $nl($productSpecs) . '</td>';
    $html .= '<td width="7%" style="height:' . $specRowHeight . 'px;border-right:1px solid #000"></td>';
    $html .= '<td width="8%" style="height:' . $specRowHeight . 'px;border-right:1px solid #000"></td>';
    $html .= '<td width="5%" style="height:' . $specRowHeight . 'px;border-right:1px solid #000"></td>';
    $html .= '<td width="9%" style="height:' . $specRowHeight . 'px;border-right:1px solid #000"></td>';
    $html .= '<td width="8%" style="height:' . $specRowHeight . 'px;border-right:1px solid #000"></td>';
    $html .= '<td width="16%" style="height:' . $specRowHeight . 'px"></td>';
    $html .= '</tr>';
}

// Weight details stay above a separate final totals row.
$weightDetailHeight = $weightRowHeight - 20;
$html .= '<tr>';
$html .= '<td colspan="3" width="47%" cellpadding="0" style="height:' . $weightDetailHeight . 'px;border-top:1px solid #000;border-right:1px solid #000">';
$html .= '<table border="0" cellpadding="1" cellspacing="0" width="100%">';
$html .= '<tr><td width="40%"></td><td width="27%" align="right"><b>KGS</b></td><td width="33%" align="right"><b>LBS</b></td></tr>';
$html .= '<tr><td width="40%"><b>NET WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($totalKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($totalLb, 3) . '</b></td></tr>';
$html .= '<tr><td width="40%"><b>GROSS WEIGHT:</b></td><td width="27%" align="right"><b>' . number_format($grossKg, 3) . '</b></td><td width="33%" align="right"><b>' . number_format($grossLb, 3) . '</b></td></tr>';
$html .= '</table></td>';
$html .= '<td width="7%" style="height:' . $weightDetailHeight . 'px;border-right:1px solid #000"></td>';
$html .= '<td width="8%" style="height:' . $weightDetailHeight . 'px;border-right:1px solid #000"></td>';
$html .= '<td width="5%" style="height:' . $weightDetailHeight . 'px;border-right:1px solid #000"></td>';
$html .= '<td width="9%" style="height:' . $weightDetailHeight . 'px;border-right:1px solid #000"></td>';
$html .= '<td width="8%" style="height:' . $weightDetailHeight . 'px;border-right:1px solid #000"></td>';
$html .= '<td width="16%" style="height:' . $weightDetailHeight . 'px"></td></tr>';

// Actual final row: cases, weight and amount totals cannot float upward.
$html .= '<tr><td width="47%"></td><td width="7%" style="border-right:1px solid #000"></td><td width="8%" style="border-right:1px solid #000"></td>';
$html .= '<td width="5%" align="center" style="border-top:1px solid #000;border-right:1px solid #000"><b>' . number_format($totalCases) . '</b></td>';
$html .= '<td width="9%" align="center" style="border-top:1px solid #000;border-right:1px solid #000"><b>' . number_format($totalLb, 3) . '</b></td>';
$html .= '<td width="8%" style="border-right:1px solid #000"></td>';
$html .= '<td width="16%" align="center" style="border-top:1px solid #000"><b>' . number_format($totalAmount, 2) . '</b></td></tr>';

$html .= '</table></td></tr>';

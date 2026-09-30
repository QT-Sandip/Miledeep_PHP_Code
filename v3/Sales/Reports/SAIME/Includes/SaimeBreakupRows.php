<?php

/**
 * Render product breakup rows for SAIME PDF documents.
 *
 * Each product is one real table row. If Description wraps, TCPDF gives every
 * cell in that product the same height, keeping all values correctly aligned.
 *
 * $rows contains already escaped/approved HTML values in column order. A cell
 * can instead be array('html' => ..., 'rowspan' => N) to visually span N rows
 * (e.g. a grouped product description behind several grade rows) - rows below
 * it must simply omit that column index so no <td> is emitted for it there.
 *
 * $columns example:
 * array('width' => '8%', 'align' => 'center', 'borderAfter' => true)
 */
function renderSaimeBreakupRows($rows, $columns, $minimumAreaHeight)
{
    $rowCount = count($rows);
    $minimumRowHeight = $rowCount > 0
        ? (float) $minimumAreaHeight / $rowCount
        : (float) $minimumAreaHeight;

    $html = '';
    foreach ($rows as $row) {
        $html .= '<tr nobr="true">';

        foreach ($columns as $columnIndex => $column) {
            if (!array_key_exists($columnIndex, $row)) {
                // Covered by a rowspan cell from an earlier row in this group.
                continue;
            }

            $value = $row[$columnIndex];
            $rowspan = 1;
            if (is_array($value)) {
                $rowspan = !empty($value['rowspan']) ? (int) $value['rowspan'] : 1;
                $value = isset($value['html']) ? $value['html'] : '';
            }

            $align = !empty($column['align']) ? $column['align'] : 'left';
            $valign = !empty($column['valign']) ? $column['valign'] : 'top';
            $style = 'height:' . ($minimumRowHeight * $rowspan) . 'px';

            if (!empty($column['borderAfter'])) {
                $style .= ';border-right:1px solid #000';
            }

            // Only write rowspan when it actually spans more than one row - TCPDF's
            // page-break handling for tall cells changes once any rowspan attribute
            // is present, even rowspan="1", causing misplaced content near a page break.
            $rowspanAttr = $rowspan > 1 ? ' rowspan="' . $rowspan . '"' : '';

            $html .= '<td width="' . $column['width'] . '" align="' . $align
                . '" valign="' . $valign . '"' . $rowspanAttr . ' style="' . $style . '">'
                . $value . '</td>';
        }

        $html .= '</tr>';
    }

    return $html;
}
/**
 * Keep a column total at the physical bottom of a tall TCPDF table cell.
 * TCPDF does not consistently honour valign="bottom" in HTML tables.
 */
function renderSaimeBottomTotal($valueHtml, $cellHeight, $totalRowHeight = 20)
{
    $spacerHeight = max(0, (float) $cellHeight - (float) $totalRowHeight);

    $html = '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
    $html .= '<tr><td style="height:' . $spacerHeight . 'px"></td></tr>';
    $html .= '<tr><td align="center" style="height:' . $totalRowHeight
        . 'px;border-top:1px solid #000"><b>' . $valueHtml . '</b></td></tr>';
    $html .= '</table>';

    return $html;
}
/** Beaver Street Fisheries is consignee ID 2 in ScrmConsigneeDetails. */
function isSaimeBeaverStreetConsignee($consigneeId)
{
    return (int) $consigneeId === 2;
}

/**
 * Build Product Specs from breakup values while keeping saved compliance text
 * that follows "PER MASTER CARTON". HTS numbers are never printed.
 */
/**
 * Convert stored SAIME certification codes to printable labels.
 */
function formatSaimeCertificationName($certificationName)
{
    $certificationName = trim((string) $certificationName);
    $labels = array(
        'BAP1*' => 'BAP 1 STAR',
        'BAP2*' => 'BAP 2 STAR',
        'BAP 3*' => 'BAP 3 STAR',
        'BAP 4*' => 'BAP 4 STAR',
    );

    return isset($labels[$certificationName]) ? $labels[$certificationName] : $certificationName;
}
function buildSaimeProductSpecsFromBreakup($items, $defaultRemarks = '')
{
    $specifications = array();

    foreach ($items as $item) {
        $itemCode = trim((string) $item['itemCode']);
        $description = trim((string) $item['description']);
        $size = trim((string) $item['size']);
        $packing = trim((string) $item['packing']);
        $brand = trim((string) $item['brand']);
        $remarks = trim((string) $item['remarks']);
        $uom = trim((string) $item['uom']);
        $slabWeight = trim((string) $item['slabWeight']);
        $totalSlabs = trim((string) $item['totalSlabs']);
        $certificationName = trim((string) $item['certificationName']);
        if ($remarks === '') {
            $remarks = trim((string) $defaultRemarks);
        }

        $packing = preg_replace('/\s+[xX]\s+/', '/', $packing);

        $complianceText = '';
        if ($remarks !== '' && preg_match('/PER\s+MASTER\s+CARTON\s*,?\s*(.*)$/is', $remarks, $match)) {
            $complianceText = trim($match[1]);
        }

        if ($brand !== '' && $complianceText !== '') {
            $complianceText = preg_replace(
                '/\b[A-Z][A-Z0-9 &.\-]*\s+BRAND(?=\s*,\s*WITH\s+UPC)/i',
                strtoupper($brand) . ' BRAND',
                $complianceText
            );
        }


        $certificationNameText = '';
        if ($certificationName == 'BAP1*') {
            $certificationNameText = strtoupper("ONE STAR BAP");
        } elseif ($certificationName == 'BAP2*') {
            $certificationNameText = strtoupper("TWO STAR BAP");
        } else if ($certificationName == 'BAP 3*') {
            $certificationNameText = strtoupper("THREE STAR BAP");
        } else if ($certificationName == 'BAP 4*') {
            $certificationNameText = strtoupper("FOUR STAR BAP");
        }
        $parts = array();
        if ($itemCode !== '')
            $parts[] = 'ITEM CODE ' . $itemCode;
        if ($description !== '')
            $parts[] = strtoupper($description);
        if ($size !== '')
            $parts[] = '
        SIZE ' . strtoupper($size) . ' CT./LB. (FINISHED COUNT PER POUND)';
        if ($packing !== '')
            $parts[] = 'PACKED ' . strtoupper($packing) . ' PER MASTER CARTON,';

        $parts[] = 'EACH ' . strtoupper($slabWeight) . ' ' . strtoupper($uom) . ' . INNER IS RETAIL PHOTO PRINTED GUSSETED POLY BAG, ' . strtoupper($brand) . ' BRAND, WITH UPC CODE AND NUTRITIONAL INFORMATION, ' . $certificationNameText . ' LOGO AND OUR SEVEN DIGIT ORDER NUMBER IMPRINTED ON BACK OF BAG, ALL PACKAGING WITH ' . $certificationNameText . ' LOGO, 100 PCT. NET WEIGHT GUARANTEED BASED ON U.S. DEPARTMENT OF COMMERCE STANDARDS, PRODUCED ACCORDING TO WALMART SPECIFICATIONS';

        if ($brand !== '' && $complianceText === '')
            // $parts[] = 'BRAND: ' . strtoupper($brand);

            $specification = '' . implode(', ', $parts);
        if ($complianceText !== '') {
            $specification .= ', ' . $complianceText;
        }
        $specifications[] = $specification;
    }

    return implode("\n\n", array_unique(array_filter($specifications)));
}

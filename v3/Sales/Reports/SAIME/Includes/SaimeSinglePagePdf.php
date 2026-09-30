<?php

function writeSaimeSinglePage($pdf, $html)
{
    $html = preg_replace('/\s+nobr="true"/i', '', $html);
    $startPage = $pdf->getPage();
    $startPages = $pdf->getNumPages();
    $bottom = $pdf->getPageHeight() - $pdf->getBreakMargin();
    $baseFont = $pdf->getFontSizePt();

    for ($attempt = 0; $attempt < 45; ++$attempt) {
        $scale = pow(0.96, $attempt);
        $fittedHtml = preg_replace_callback(
            '/(font-size|height|line-height)\s*:\s*([0-9.]+)(pt|px)/i',
            function ($match) use ($scale) {
                return $match[1] . ':' . sprintf('%.3F', (float) $match[2] * $scale) . $match[3];
            },
            $html
        );
        $fittedHtml = preg_replace_callback(
            '/cellpadding="([0-9.]+)"/i',
            function ($match) use ($scale) {
                return 'cellpadding="' . sprintf('%.3F', (float) $match[1] * $scale) . '"';
            },
            $fittedHtml
        );

        $pdf->startTransaction();
        $pdf->SetFontSize($baseFont * $scale);
        $pdf->writeHTML($fittedHtml);
        if ($pdf->getNumPages() === $startPages && $pdf->getPage() === $startPage && $pdf->GetY() <= $bottom) {
            $pdf->commitTransaction();
            return;
        }
        $pdf->rollbackTransaction(true);
    }

    throw new RuntimeException('Report content could not fit on a single page.');
}

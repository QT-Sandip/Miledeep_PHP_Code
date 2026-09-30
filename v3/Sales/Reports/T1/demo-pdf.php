<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// define("PDF_PAGE_ORIENTATION", 'P');
// define("PDF_UNIT", 'mm');
// define("PDF_PAGE_FORMAT", 'A4');

include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");

class CustomPDF extends TCPDF
{
    // private $headerHeight = 0;
    // private $footerHeight = 0;

    private $headerHeight = 0; // Set your header height here (in points)
    private $footerHeight = 0; // Set your footer height here (in points)

    private $customMargins = array(); // Store custom margins

    public function __construct()
    {
        parent::__construct();
    }

    public function SetHeaderHeight($height)
    {
        $this->headerHeight = $height;
    }

    public function SetFooterHeight($height)
    {
        $this->footerHeight = $height;
    }

    // public function getHeaderHeight()
    // {
    //     return $this->headerHeight();
    // }

    // public function getFooterHeight()
    // {
    //     return $this->footerHeight();
    // }

    public function Header()
    {
        // HTML content for the header
        $header = '<table style="height: ' . $this->headerHeight . 'px;border-collapse: collapse;">
                    <tr>
                        <td style=" width: 49%;padding: 0px;">
                            <div style="line-height: 11px;">&nbsp;</div>
                            <div style="border: 1px solid #333333;text-align: center;line-height: 16px;">Original</div>
                            <div style="line-height: 2px;">&nbsp;</div>
                            <table style="width: 100%; border: 1px solid #333333; border-collapse: collapse;" >
                                <tbody>
                                    <tr>                                        
                                        <td><table style="border-collapse: collapse; padding: 1px 3px;">
                                                <tr>
                                                    <td colspan="2"> GST. 37AAACD7852Q1ZZ</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="padding: 0px 5px" >
                                                        <!-- <img src="https://camaroo.in/assets/images/logo-Devi.png" style="height:45px;" /> -->
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="text-align: center;vertical-align: center">
                                                        <span style="text-align:center; margin: 0px;padding: 0px;font-size: 22px;font-weight: bold;line-height: 14px;">
                                                            Devi Fisheries Limited
                                                        </span><br>
                                                        <span style="text-align:center;line-height:14px;font-size: 12px;font-weight: bold;">BOUGHT NOTE</span>
                                                    </td>
                                                </tr>
                                                <!-- <tr>
                                                    <td colspan="2">
                                                        <h1 style="display: flex;justify-content: center;align-items: center;"> 
                                                           <!-- <img src="https://camaroo.in/assets/images/logo-Devi.png" style="height:55px;" />  -->
                                                            Devi Fisheries Limited
                                                        </h1>
                                                        <p>BOUGHT NOTE</p>
                                                    </td>
                                                </tr> -->
                                                <tr>
                                                    <td style="padding: 0px 5px;">Bill No : AMP2265</td>
                                                    <td style="text-align:right;font-weight: bold;padding-right:5px;">Bill Date : 20/08/2023 </td>
                                                </tr>
                                                <tr><td colspan="2" style="text-align: left">Supplier : R.V.S.RAMA RAJU(AM)</td></tr>
                                                <tr><td colspan="2" style="text-align:left">Address : AMALAPURAM,ANDHRA PRADESH</td></tr>
                                                <tr>
                                                    <td style="text-align: left">PAN/GST : AMJPR4703J</td>
                                                    <td style="text-align: right">Phone : 8374378777 </td>
                                                </tr>                        
                                            </table>   
                                                          
                                        </td>
                                    </tr>  
                                    <!--
                                    <tr>
                                        <td style="text-align: left;padding:0px;">
                                            <table style="width: 100%;border-collapse: collapse;border-top:1px solid #333333; line-height: 18px;" >
                                                <tr>
                                                    <td style="width:12%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Sl No</td>
                                                    <td style="width:12%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Variety</td>
                                                    <td style="width:12%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Count</td>
                                                    <td style="width:24%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Quantity - In kgs</td>
                                                    <td style="width:16%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Rate In Rs </td>
                                                    <td style="width:24%; text-align: center;line-height: 16px;">Amount In Rs</td>
                                                </tr>  
                                            </table> 
                                        </td>
                                    </tr>
                                    -->
                                </tbody>
                            </table> 
                            
                            
                        </td>
                        <td style="width: 2%;padding: 0px;">&nbsp;</td>
                        <td style=" width: 49%;padding: 0px;">                            
                            <div style="line-height: 11px;">&nbsp;</div>
                            <div style="border: 1px solid #333333;text-align: center;line-height: 16px;">Duplicate</div>
                            <div style="line-height: 2px;">&nbsp;</div>
                            <table style="width: 100%; border: 1px solid #333333; border-collapse: collapse;" >
                                <tbody>
                                    <tr>                                        
                                        <td style="padding: 0px;"><table style="border-collapse: collapse; padding: 1px 3px;">
                                                <tr>
                                                    <td colspan="2"> GST. 37AAACD7852Q1ZZ</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="padding: 0px 5px" >
                                                        <!--<img src="https://camaroo.in/assets/images/logo-Devi.png" style="height:45px;" /> -->
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="text-align: center;vertical-align: center">
                                                        <span style="text-align:center; margin: 0px;padding: 0px;font-size: 22px;font-weight: bold;line-height: 14px;">
                                                            Devi Fisheries Limited
                                                        </span><br>
                                                        <span style="text-align:center;line-height:14px;font-size: 12px;font-weight: bold;">BOUGHT NOTE</span>
                                                    </td>
                                                </tr>
                                                <!-- <tr>
                                                    <td colspan="2">
                                                        <h1 style="display: flex;justify-content: center;align-items: center;"> 
                                                            <!--<img src="https://camaroo.in/assets/images/logo-Devi.png" style="height:55px;" /> -->
                                                            Devi Fisheries Limited
                                                        </h1>
                                                        <p>BOUGHT NOTE</p>
                                                    </td>
                                                </tr> -->
                                                <tr>
                                                    <td style="padding: 0px 5px;">Bill No : AMP2265</td>
                                                    <td style="text-align:right;font-weight: bold;padding-right:5px;">Bill Date : 20/08/2023 </td>
                                                </tr>
                                                <tr><td colspan="2" style="text-align: left">Supplier : R.V.S.RAMA RAJU(AM)</td></tr>
                                                <tr><td colspan="2" style="text-align:left">Address : AMALAPURAM,ANDHRA PRADESH</td></tr>
                                                <tr>
                                                    <td style="text-align: left">PAN/GST : AMJPR4703J</td>
                                                    <td style="text-align: right">Phone : 8374378777 </td>
                                                </tr>                        
                                            </table>   
                                                          
                                        </td>
                                    </tr>  
                                    <!--
                                    <tr>
                                        <td style="text-align: left;padding:0px;">
                                            <table style="width: 100%;border-collapse: collapse;border-top:1px solid #333333; line-height: 18px;" >
                                                <tr>
                                                    <td style="width:12%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Sl No</td>
                                                    <td style="width:12%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Variety</td>
                                                    <td style="width:12%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Count</td>
                                                    <td style="width:24%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Quantity - In kgs</td>
                                                    <td style="width:16%;border-right: 1px solid #333333;text-align: center;line-height: 16px;">Rate In Rs </td>
                                                    <td style="width:24%; text-align: center;line-height: 16px;">Amount In Rs</td>
                                                </tr>  
                                            </table> 
                                        </td>
                                    </tr>-->

                                </tbody>
                            </table> 

                        </td>
                      </tr>
                    </table>';
        $this->SetFont('times', 'B', 11); // Set font, style, and size
        $this->writeHTML($header, true, false, true, false, '');
    }
  
    // public function Footer()
    // {
    //     $this->SetY(-70); // Position at 15mm from bottom
    //     $footer = '
    //         <table style="width: 100%;border-collapse: collapse;">
    //             <tr>
    //                 <td style="width: 49%">
    //                     <table style="border-collapse: collapse;border: 1px solid #333333; text-align: center">                    
    //                         <tr>
    //                             <td  style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
    //                             <td  style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;">1374.400</td>
    //                             <td  style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td  style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;">2,94,318.00</td>
    //                         </tr>
    //                         <tr>
    //                             <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-bottom: 1px solid #333333;line-height: 22px;">295.00</td>
    //                         </tr>
    //                         <tr>
    //                             <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-bottom: 1px solid #333333;line-height: 22px;">2,94,023.00</td>
    //                          </tr>
    //                         <tr>
    //                             <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
    //                             <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
    //                         </tr>
    //                         <tr>
    //                             <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
    //                             <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
    //                             <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
    //                         </tr>                        
    //                         <tr>
    //                             <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
    //                             <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
    //                             <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
    //                         </tr>
    //                     </table>
    //                 </td>
    //                 <td style="width: 2%">
    //                 </td>
    //                 <td style="width: 49%">
    //                     <table style="border-collapse: collapse;border: 1px solid #333333; text-align: center">                    
    //                         <tr>
    //                             <td  style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
    //                             <td  style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;">1374.400</td>
    //                             <td  style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td  style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;">2,94,318.00</td>
    //                         </tr>
    //                         <tr>
    //                             <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-bottom: 1px solid #333333;line-height: 22px;">295.00</td>
    //                         </tr>
    //                         <tr>
    //                             <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
    //                             <td style="border-bottom: 1px solid #333333;line-height: 22px;">2,94,023.00</td>
    //                          </tr>
    //                         <tr>
    //                             <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
    //                             <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
    //                         </tr>
    //                         <tr>
    //                             <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
    //                             <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
    //                             <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
    //                         </tr>                        
    //                         <tr>
    //                             <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
    //                             <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
    //                             <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
    //                         </tr>
    //                     </table>
    //                 </td>
    //             </tr>
    //         </table>

             
    //     ';
    //     $this->SetFont('times', '', 11); // Set font, style, and size
    //     $this->writeHTML($footer, true, false, true, false, '');
    //     // $this->writeHTML('<div style="text-align: center;">This is the custom footer content</div>', true, false, false, false, 'C');
    // }

    
    public function SetCustomMargins($left, $top, $right, $bottom)
    {
        $this->customMargins = array(
            'left' => $left,
            'top' => $top + $this->headerHeight, // Include header height
            'right' => $right,
            'bottom' => $bottom + $this->footerHeight  // Include footer height
        );
    }
    public function SetMaxHeight($height)
    {
        $this->m_MaxHeight = $height;
    }
    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
    {
        // PDF_MARGIN_LEFT =15 
        // PDF_MARGIN_TOP = "27"; 
        // PDF_MARGIN_RIGHT =15 
        // PDF_MARGIN_BOTTOM =25 

        // $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + $this->headerHeight, PDF_MARGIN_RIGHT);
        // $this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM + $this->footerHeight);
        if (!empty($this->customMargins)) {
            $this->SetMargins($this->customMargins['left'], $this->customMargins['top'], $this->customMargins['right']);
            $this->SetAutoPageBreak(true, $this->customMargins['bottom']);
        }
        $this->SetMaxHeight(100);

        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
    }

}

$pdf = new CustomPDF();
// $pdf->SetPageSize('A4'); // Set page size to A4


$content = '<div style="height: 100%;"><table style="width: 100%;border-collapse: collapse;height: 100%">
        <tr>
            <td style="width: 49%;height: 100%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333;width: 100%; height: 100%;padding: 3px;">
                 <tr><td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Sl No</td>
                    <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Variety</td>
                    <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Count</td>
                    <td style="font-weight: bold;width:24%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Quantity - In kgs</td>
                    <td style="font-weight: bold;width:16%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Rate In Rs </td>
                    <td style="font-weight: bold;width:24%;border-bottom: 1px solid #333333; text-align: center;line-height: 16px;">Amount In Rs</td>
                </tr> '; 
                for ($i = 1; $i < 3; $i++) {
                    $content .= '
                    <tr>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">' . $i . '</td>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">HOV</td>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">100</td>
                        <td style="width:24%;border-right: 1px solid #33333; line-height: 18px;text-align:right">1138.800</td>
                        <td style="width:16%;border-right: 1px solid #33333; line-height: 18px;text-align:right">215.00</td>
                        <td style="width:24%; line-height: 18px;text-align:right">2,44,842.00</td>
                    </tr>';
                }
                    $content .= '
                    <tr>
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;"><br><br><br><br></td> 
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:24%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:16%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:24%;min-height: 300px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td style="width: 2%;height: 100%;padding: 0px;">&nbsp;</td>
            <td style="width: 49%;height: 100%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333;width: 100%; height: 100%;padding: 3px;">
                 <tr><td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Sl No</td>
                    <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Variety</td>
                    <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Count</td>
                    <td style="font-weight: bold;width:24%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Quantity - In kgs</td>
                    <td style="font-weight: bold;width:16%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Rate In Rs </td>
                    <td style="font-weight: bold;width:24%;border-bottom: 1px solid #333333; text-align: center;line-height: 16px;">Amount In Rs</td>
                </tr> ';
                    for ($i = 1; $i < 3; $i++) {
                        $content .= '
                    <tr>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">' . $i . '</td>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">HOV</td>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">100</td>
                        <td style="width:24%;border-right: 1px solid #33333; line-height: 18px;text-align:right">1138.800</td>
                        <td style="width:16%;border-right: 1px solid #33333; line-height: 18px;text-align:right">215.00</td>
                        <td style="width:24%; line-height: 18px;text-align:right">2,44,842.00</td>
                    </tr>';
                    }
                    $content .= '
                    <tr>
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;"><br><br><br><br></td> 
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:24%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:16%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:24%;min-height: 300px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 49%;height: 100%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333;width: 100%; height: 100%;padding: 3px;">
                    <tr>
                        <td style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
                        <td style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align:right">1374.400</td>
                        <td style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;text-align:right">2,94,318.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">295.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">2,94,023.00</td>
                        </tr>
                    <tr>
                        <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
                        <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                        <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                        <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
                    </tr>                        
                    <tr>
                        <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
                        <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
                        <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
                    </tr>
                </table>
            </td>
            <td style="width: 2%;height: 100%;padding: 0px;">
            </td>
            <td style="width: 49%;height: 100%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333;width: 100%; height: 100%;padding: 3px;">
                    <tr>
                        <td style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
                        <td style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align:right">1374.400</td>
                        <td style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;text-align:right">2,94,318.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">295.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                        <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">2,94,023.00</td>
                        </tr>
                    <tr>
                        <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
                        <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                        <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                        <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
                    </tr>                        
                    <tr>
                        <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
                        <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
                        <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table></div>
    <!--
    <table style="width: 100%;border-collapse: collapse;">
                <tr>
                    <td style="width: 49%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333; text-align: center">                    
                            <tr>
                                <td style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
                                <td style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align:right">1374.400</td>
                                <td style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;text-align:right">2,94,318.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">295.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">2,94,023.00</td>
                             </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
                                <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
                            </tr>                        
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
                                <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 2%;padding: 0px;">
                    </td>
                    <td style="width: 49%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333; text-align: center">                    
                            <tr>
                                <td style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
                                <td style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align:right">1374.400</td>
                                <td style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;text-align:right">2,94,318.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">295.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">2,94,023.00</td>
                             </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
                                <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
                            </tr>                        
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
                                <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>-->';

$contentSecond = '
    <table style="width: 100%;border-collapse: collapse;height: 100%;">
        <tr>
            <td style="width: 49%;height: 100%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333; height: 100%">
                    <tr>
                        <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Sl No</td>
                        <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Variety</td>
                        <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Count</td>
                        <td style="font-weight: bold;width:24%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Quantity - In kgs</td>
                        <td style="font-weight: bold;width:16%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Harvest </td>
                        <td style="font-weight: bold;width:24%; text-align: center;border-bottom: 1px solid #333333;line-height: 16px;">Amount In Rs</td>
                    </tr> ';
                for ($j = 1; $j < 4; $j++) {
                    $contentSecond .= '
                    <tr>
                        <td style="width:36%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">Harvest Expenses</td>
                        <!--<td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;"></td>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;"></td>-->
                        <td style="width:24%;border-right: 1px solid #33333; line-height: 18px;text-align:right">1138.800</td>
                        <td style="width:16%;border-right: 1px solid #33333; line-height: 18px;text-align:right">215.00</td>
                        <td style="width:24%; line-height: 18px;text-align:right">2,44,842.00</td>
                    </tr>';
                }
                    $contentSecond .= '
                    <tr>
                        <td style="width:36%;min-height: 300px;border-right: 1px solid #333333;"><br><br><br><br></td> 
                        <!--<td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>-->
                        <td style="width:24%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:16%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:24%;min-height: 300px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td style="width: 2%;height: 100%;padding: 0px;">&nbsp;</td>
            <td style="width: 49%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333; height: 100%">
                    <tr style="font-weight: bold;">
                        <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Sl No</td>
                        <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Variety</td>
                        <td style="font-weight: bold;width:12%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Count</td>
                        <td style="font-weight: bold;width:24%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Quantity - In kgs</td>
                        <td style="font-weight: bold;width:16%;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align: center;line-height: 16px;">Harvest </td>
                        <td style="font-weight: bold;width:24%; text-align: center;border-bottom: 1px solid #333333;line-height: 16px;">Amount In Rs</td>
                    </tr> ';
                    for ($j = 1; $j < 4; $j++) {
                        $contentSecond .= '
                    <tr>
                        <td style="width:36%; text-align: center; border-right: 1px solid #333333; line-height: 18px;">Harvest Expenses</td>
                        <!--<td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;"></td>
                        <td style="width:12%; text-align: center; border-right: 1px solid #333333; line-height: 18px;"></td>-->
                        <td style="width:24%;border-right: 1px solid #33333; line-height: 18px;text-align:right">1138.800</td>
                        <td style="width:16%;border-right: 1px solid #33333; line-height: 18px;text-align:right">215.00</td>
                        <td style="width:24%; line-height: 18px;text-align:right">2,44,842.00</td>
                    </tr>';
                    }
                    $contentSecond .= '
                    <tr>
                        <td style="width:36%;min-height: 300px;border-right: 1px solid #333333;"><br><br><br><br></td> 
                        <!--<td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:12%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>-->
                        <td style="width:24%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:16%;min-height: 300px;border-right: 1px solid #333333;">&nbsp;</td>
                        <td style="width:24%;min-height: 300px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="width: 100%;border-collapse: collapse;">
                <tr>
                    <td style="width: 49%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333; text-align: center">                    
                            <tr>
                                <td style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
                                <td style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align:right">1374.400</td>
                                <td style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;text-align:right">2,94,318.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">295.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">2,94,023.00</td>
                             </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
                                <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
                            </tr>                        
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
                                <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 2%;padding: 0px;">
                    </td>
                    <td style="width: 49%;padding: 0px;"><table style="border-collapse: collapse;border: 1px solid #333333; text-align: center">                    
                            <tr>
                                <td style="width: 36%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;" colspan="3">Total </td>
                                <td style="width: 24%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;text-align:right">1374.400</td>
                                <td style="width: 16%;line-height: 22px;border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="width: 24%;line-height: 22px;border-bottom: 1px solid #333333;text-align:right">2,94,318.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Less TDS @ 0.1 % </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">295.00</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount Payable After TDS </td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;"></td>
                                <td style="border-bottom: 1px solid #333333;line-height: 22px;text-align:right">2,94,023.00</td>
                             </tr>
                            <tr>
                                <td colspan="3" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 22px;">Amount in Words</td>                        
                                <td colspan="3" style="border-bottom: 1px solid #333333;line-height: 16px;">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-right: 1px solid #333333;border-bottom: 1px solid #333333;line-height: 35px;"><br></td>                        
                                <td colspan="2" style="border-bottom: 1px solid #333333;line-height: 35px;"><br></td>
                            </tr>                        
                            <tr>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Supplier <br> Signature</td>
                                <td colspan="2" style="border-right: 1px solid #333333;line-height: 16px;">Checked By</td>
                                <td colspan="2" style="line-height: 16px;">Authorized Sign</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>';

//$content .= PDF_MARGIN_LEFT ."--". PDF_MARGIN_TOP . "--". PDF_MARGIN_RIGHT . "--" . PDF_MARGIN_BOTTOM;

$pdf->SetHeaderHeight(51);
$pdf->SetFooterHeight(0); // 45
$pdf->SetCustomMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, PDF_MARGIN_BOTTOM); // Set initial custom margins

$halfA3Height = 841.89 / 2;
// $this->AddPage('P', array(PDF_PAGE_WIDTH_A3, $halfA3Height));

$pdf->AddPage('P', 'A3');
$pdf->SetFont('times', '', 11); // Set font, style, and size
$pdf->writeHTML($content, true, false, true, false, '');


$pdf->AddPage('P', 'A3');
$pdf->SetFont('times', '', 11); // Set font, style, and size
$pdf->writeHTML($contentSecond, true, false, true, false, '');

// $headerHeight = $pdf->getHeaderHeight();
// $footerHeight = $pdf->getFooterHeight();

// echo "Header Height: {$headerHeight} points";
// echo "Footer Height: {$footerHeight} points";


$pdf->Output('example.pdf', 'I');
?>

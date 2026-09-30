<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <style media="print">
        @page {
            size: auto;
            margin: 5;
        }
    </style>

    <style>
        @media print{
            body {
                width: 210mm; 
                height: 297mm; 
            } 
            .getPrintPDF{
                display: none;
            }
            /* thead {
                display: table-header-group;
            } */
        }
        body{
            margin: 0px;
            font-size: 15px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .wrapper{
            max-width: 950px;            
            margin: 0px auto;
        }
        h1{
            font-size: 22px;
        }
        h1, p{
            margin: 0px 0px 5px;
        }
        .bold{
            font-weight: bold;
        }
        table{
            border-collapse: collapse;
            width: 100%;
        }
        td, th{
            vertical-align: top;
        }
        .tblHeader th, .tblHeader td, .tblFooter td{
            padding:3px 5px;            
        }
        .tblContent{
            height: 100%;
        }
        .tblContent td{
            padding:5px 5px;
        }
        thead {display: table-header-group;}

        .p0{
            padding: 0px;
        }
        .bt{
            border-top: 1px solid #333333;
        }
        .br{
            border-right: 1px solid #333333;
        }
        .bb{
            border-bottom: 1px solid #333333;
        }
        .bl{
            border-left: 1px solid #333333;
        }
        .b2{
            border-width: 2px solid #333333;
        }
        .bt2{
            border-top: 2px solid #333333;
        }
        .br2{
            border-right: 2px solid #333333;
        }
        .bb2{
            border-bottom: 2px solid #333333;
        }
        .bl2{
            border-left: 2px solid #333333;
        }
        .text-center{
            text-align: center;
        }
        .text-left{
            text-align: left;
        }
        .text-right{
            text-align: right;
        }
        .getPrintPDF{
            text-align: center;
        }
        .getPrintPDF a{
            text-decoration: none;
            padding: 9px 10px;
            font-weight: 600;
            display: inline-block;
            background: #308f44;
            width: 210px;
            margin: 15px;
            color: #FFF;
            border-radius: 20px;
            cursor: pointer;
        }
        .getPrintPDF button{
            border: 0px;            
            padding: 9px 10px;
            font-weight: 600;
            display: inline-block;
            background: #308f44;
            width: 210px;
            margin: 15px;
            color: #FFF;
            border-radius: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body> 
    <div class="getPrintPDF">
        <!-- <a href="print-commission-bill" target="_blank">Get Print / PDF Download</a> -->
        <button >Get Print / PDF Download</button>
    </div>
    <table class="wrapper" style="height: 100vh;">
        <thead>
            <tr>
                <th class="p0 bl br ">
                    <table class="tblHeader bt bb">
                        <tr>
                            <td colspan="2" class="text-left">GST. 37AAACD7852Q1ZZ</td>
                        </tr>
                        <tr class="trimg">
                            <td colspan="2" class="text-left " style="padding: 0px 5px" >
                                <img src="https://camaroo.in/assets/images/logo-Devi.png" style="height:45px;" /> 
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center">
                                <h1> 
                                    Devi Fisheries Limited
                                </h1>
                                <p>BOUGHT NOTE</p>
                            </td>
                        </tr>
                        <!-- <tr>
                            <td colspan="2" class="text-center">
                                <h1 style="display: flex;justify-content: center;align-items: center;"> 
                                    <img src="https://camaroo.in/assets/images/logo-Devi.png" style="height:55px;" /> 
                                    Devi Fisheries Limited
                                </h1>
                                <p>BOUGHT NOTE</p>
                            </td>
                        </tr> -->
                        <tr>
                            <td class="text-left">Bill No : AMP2265</td>
                            <td class="text-right">Bill Date : 20/08/2023</td>
                        </tr>
                        <tr><td colspan="2" class="text-left">Supplier : R.V.S.RAMA RAJU(AM)</td></tr>
                        <tr><td colspan="2" class="text-left">Address : AMALAPURAM,ANDHRA PRADESH</td></tr>
                        <tr>
                            <td style="text-align: left">PAN/GST : AMJPR4703J</td>
                            <td class="text-right">Phone : 8374378777</td>
                        </tr>                        
                    </table>   
                    <table class="tblContent bt bb">
                        <tr class=" bb">
                            <td class="br" style="width:12%">Sl No</td>
                            <td class="br" style="width:12%">Variety</td>
                            <td class="br" style="width:12%">Count</td>
                            <td class="br" style="width:24%">Quantity - In kgs</td>
                            <td class="br" style="width:16%">Rate In Rs </td>
                            <td style="width:24%">Amount In Rs</td>
                        </tr>  
                    </table>               
                </th>
            </tr>
            
        </thead>
        <tbody>
            <tr>
                <td class="p0 br bl" style="height: 100%">
                    <table class="tblContent">
                         <?php for ($i = 1; $i <= 35; $i++) { ?>
                            <tr>
                                <td style="width:12%" class="bb br text-center"><?php echo $i ?></td>
                                <td style="width:12%" class="bb br text-center">HOV</td>
                                <td style="width:12%" class="bb br text-center">100</td>
                                <td style="width:24%" class="bb br text-right">1138.800</td>
                                <td style="width:16%" class="bb br text-right">215.00</td>
                                <td style="width:24%" class="bb text-right">2,44,842.00</td>
                            </tr>
                        <?php } ?>
                        <tr class="emptyTr" style="height: 100%">
                            <td style="width:12%" class="bb br text-center">&nbsp;</td> 
                            <td style="width:12%" class="bb br text-center">&nbsp;</td>
                            <td style="width:12%" class="bb br text-center">&nbsp;</td>
                            <td style="width:24%" class="bb br text-right">&nbsp;</td>
                            <td style="width:16%" class="bb br text-right">&nbsp;</td>
                            <td style="width:24%" class="bb text-right">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="p0 bl br">
                    <table class="tblFooter">
                        <tr>
                            <td class="bb br text-center" colspan="3" style="width: 36%;height: 45px;vertical-align: bottom">Total </td>
                            <td class="bb br text-right"  style="width: 24%;vertical-align: bottom">1374.400</td>
                            <td class="bb br"  style="width: 16%;vertical-align: bottom"></td>
                            <td class="bb  text-right" style="width: 24%;vertical-align: bottom">2,94,318.00</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="bb br text-center">Less TDS @ 0.1 % </td>
                            <td class="bb br"></td>
                            <td class="bb br"></td>
                            <td class="bb  text-right">295.00</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="bb br text-center">Amount Payable After TDS </td>
                            <td class="bb br"></td>
                            <td class="bb br"></td>
                            <td  class="bb  text-right">2,94,023.00</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="bb br text-center">Amount in Words</td>                        
                            <td colspan="3" class="bb text-center">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                        </tr>
                        <tr>
                            <td class="bb br" colspan="2"><br></td>                        
                            <td class="bb br" colspan="2"><br></td>                        
                            <td class="bb" colspan="2"><br></td>
                        </tr>                        
                        <tr>
                            <th  class="bb br text-center" colspan="2">Supplier Signature <?php echo $j ?></th>
                            <th  class="bb br text-center"  colspan="2">Checked By</th>
                            <th  class="bb text-center"  colspan="2">Authorized Sign</th>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </tfoot>
    </table>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <script>
        // show the Print Preview then Download PDF
        function printPage() {
            var fileName = "Commission.pdf";
            document.title = fileName;
                window.print(); 
        }

        $(document).on("click", ".getPrintPDF button", function(){
            printPage();
            setTimeout(function() {
                window.close();
            }, 2000); 
        }); 
    </script>
<!-- 
    <div class="wrapper b2">
        <header>
            <table class="bold">
                <thead>
                    <tr>
                        <td colspan="2">GST. 37AAACD7852Q1ZZ</td>
                    </tr>
                    <tr>
                        <td colspan="2">IMAGE</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">
                            <h1>Devi Fisheries Limited</h1>
                            <p>BOUGHT NOTE</p>
                        </td>
                    </tr>
                    <tr>
                        <td>Bill No : AMP2265</td>
                        <td class="text-right">Bill Date : 20/08/2023</td>
                    </tr>
                    <tr><td colspan="2">Supplier : R.V.S.RAMA RAJU(AM)</td></tr>
                    <tr><td colspan="2">Address : AMALAPURAM,ANDHRA PRADESH</td></tr>
                    <tr>
                        <td style="text-align: left">PAN/GST : AMJPR4703J</td>
                        <td class="text-right">Phone : 8374378777</td>
                    </tr>
                </thead>
            </table>
        </header>
        <main>
            <table class="">
                <thead>                    
                    <tr>
                        <th colspan="6"  class="text-left">GST. 37AAACD7852Q1ZZ</th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-left">IMAGE</th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-center">
                            <h1>Devi Fisheries Limited</h1>
                            <p>BOUGHT NOTE</p>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-left">Bill No : AMP2265</th>
                        <th colspan="3" class="text-right">Bill Date : 20/08/2023</th>
                    </tr>
                    <tr><th colspan="3" class="text-left">Supplier : R.V.S.RAMA RAJU(AM)</th></tr>
                    <tr><th colspan="3" class="text-left">Address : AMALAPURAM,ANDHRA PRADESH</th></tr>
                    <tr>
                        <th colspan="3" style="text-align: left">PAN/GST : AMJPR4703J</th>
                        <th colspan="3" class="text-right">Phone : 8374378777</th>
                    </tr>
                    <tr class="bt bb">
                        <th class="br bl">Sl No</th>
                        <th class="br">Variety</th>
                        <th class="br">Count</th>
                        <th class="br">Quantity - In kgs</th>
                        <th class="br">Rate In Rs </th>
                        <th class="br">Amount In Rs</th>
                    </tr>
                </thead>
                <tbody class="h100vh">
                    <tr>
                        <td class="bb bl br text-center">1</td>
                        <td class="bb br text-center">HOV</td>
                        <td class="bb br text-center">100</td>
                        <td class="bb br text-right">1138.800</td>
                        <td class="bb br text-right">215.00</td>
                        <td class="bb br text-right">2,44,842.00</td>
                    </tr>
                    <tr>
                        <td class="bb bl br  text-center">1</td>
                        <td class="bb br text-center">HOV</td>
                        <td class="bb br text-center">100</td>
                        <td class="bb br text-right">1138.800</td>
                        <td class="bb br text-right">215.00</td>
                        <td class="bb br text-right">2,44,842.00</td>
                    </tr>
                   
                </tbody>
                <tfoot>
                    <tr>
                        <td class="bb bl br text-center" colspan="3">Total </td>
                        <td class="bb br text-right">1374.400</td>
                        <td class="bb br"></td>
                        <td class="bb br text-right">2,94,318.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="bb bl br text-center">Less TDS @ 0.1 % </td>
                        <td class="bb br"></td>
                        <td class="bb br"></td>
                        <td class="bb br text-right">295.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="bb bl br text-center">Amount Payable After TDS </td>
                        <td class="bb br"></td>
                        <td class="bb br"></td>
                        <td  class="bb br text-right">2,94,023.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="bb bl br text-center">Amount in Words</td>                        
                        <td colspan="3" class="bb br text-center">Two Lakh Ninety Four Thousand Twenty Three rupees and zero paise</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bb bl br">&nbsp;</td>                        
                        <td colspan="2" class="bb br">&nbsp;</td>
                        <td colspan="2" class="bb br">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bb bl br text-center">Supplier Signature</td>
                        <td colspan="2" class="bb br text-center">Checked By</td>
                        <td colspan="2" class="bb br text-center">Autdorized Sign</td>
                    </tr>
                </tfoot>
            </table>
        </main>
        <footer>
            <table class="bold">
                <tbody>
                    <tr>
                        <td class="bb bl br">&nbsp;</td>                        
                        <td class="bb br">&nbsp;</td>
                        <td class="bb br">&nbsp;</td>
                    </tr>
                    <tr>
                        <th  class="bb bl br text-center">Supplier Signature</th>
                        <th  class="bb br text-center">Checked By</th>
                        <th  class="bb br text-center">Authorized Sign</th>
                    </tr>
                </tbody>
            </table>
        </footer>
    
    </div> -->
</body>
</html>
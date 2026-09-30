<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    
window.jsPDF = window.jspdf.jsPDF;
var pdf = new jsPDF({
    orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
    unit: 'px', // Measurement unit
    format: 'a4', // Page size format, 'a4' for A4 size
    
});

function downloadpdf(fileName) {
    var pWidth = pdf.internal.pageSize.getWidth(); // Page width
    var pHeight = pdf.internal.pageSize.getHeight(); // Page height

    // Define the HTML element you want to capture (e.g., a table with id "printTable")
    var element = document.getElementById("printTable");

    // Adjust the width of the HTML content to match the PDF page width
    // element.style.width = pWidth + "px"; // Set the width in points

    // Calculate the adjusted height to maintain the original aspect ratio
    var aspectRatio = element.offsetWidth / element.offsetHeight;
    var adjustedHeight = pWidth / aspectRatio;

    // Calculate the margins (adjust as needed)
    var marginLeft = 50;
    var marginTop = 30;
    var marginRight = 50;
    var marginBottom = 30;

    var contentWidth = element.offsetWidth - marginLeft - marginRight;
    console.log(contentWidth);
    var contentHeight = element.offsetHeight - marginTop - marginBottom;

    // Calculate the x and y position to center the content within the PDF page
    var x = 0; // Left-aligned content

    pdf.html(element, {
        html2canvas: {
            scale: 0.43,
            width: contentWidth,
            height: contentHeight,
            removeContainer: false // for analyzing iframe
        },
        x: x,
        margin: [15, 0, 30, 0], // Adds the margin to the PDF view 
        autoPaging: 'text',
        callback: function (pdf) {
            
            var footerHeight = 20; // Height of the footer
            var gap = 10; // Gap between content and footer

            for (var i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
                pdf.setPage(i);
                pdf.setFontSize(9);

                // Calculate the Y position for the footer
                var footerY = pHeight - marginBottom + gap;

                // Calculate the Y position for the page number text
                var pageNumberY = footerY + (footerHeight / 2);

                pdf.text(
                    pWidth / 2,
                    pageNumberY,
                   'Page ' + i + ' of ' + pdf.internal.getNumberOfPages() + " - " + invNo,
                    null,
                    null,
                    'center'
                );
            }

            var timestamp = Date.now();
            var pdfName = fileName;
            pdf.save(pdfName);
        }
    });
}
</script>

<script> 
/*
    window.jsPDF = window.jspdf.jsPDF;
    var pdf = new jsPDF({
        orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
        unit: 'pt', // Measurement unit
        format: 'a4' // Page size format, 'a4' for A4 size
    });

    function downloadpdf(fileName) {
        var pWidth = pdf.internal.pageSize.getWidth();
        var pHeight = pdf.internal.pageSize.getHeight();

        // Define the HTML element you want to capture (e.g., a table with id "printTable")
        var element = document.getElementById("printTable");

        // Calculate the scaling factors to fit content to the page
        var scaleX = (pWidth - 30 * 2) / element.offsetWidth; // Subtract 30px margin from both sides
        var scaleY = (pHeight - 15 * 2) / element.offsetHeight; // Subtract 30px margin from both top and bottom
        var scale = Math.min(scaleX, scaleY);

        // Calculate the x and y position to center the content within the margins
        var x = 30; // Left margin
        var y = 15; // Top margin

        pdf.html(element, {
            html2canvas: {
                scale: scale,
                width: element.offsetWidth,
                removeContainer: false // for analyzing iframe
            },
            x: x,
            y: y,
            callback: function (pdf) {
                var timestamp = Date.now();
                var pdfName = fileName;
                pdf.save(pdfName);
            }
        });
    } */
</script> 

<script> 
// window.jsPDF = window.jspdf.jsPDF;
//     var pdf = new jsPDF({
//         orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
//         unit: 'pt', // Measurement unit
//         format: 'a4' // Page size format, 'a4' for A4 size
//     });

//     function downloadpdf(fileName) {
//         var pWidth = pdf.internal.pageSize.getWidth();
//         var pHeight = pdf.internal.pageSize.getHeight();

//         // Define the HTML element you want to capture (e.g., a table with id "printTable")
//         var element = document.getElementById("printTable");

//         // Calculate the scaling factors to fit content to the page
//         var scaleX = (pWidth - 30 * 2) / element.offsetWidth; // Subtract 30px margin from both sides
//         var scaleY = (pHeight - 15 * 2) / element.offsetHeight; // Subtract 30px margin from both top and bottom
//         var scale = Math.min(scaleX, scaleY);

//         // Calculate the x and y position to center the content within the margins
//         var x = 30; // Left margin
//         var y = 15; // Top margin

//         pdf.html(element, {
//             html2canvas: {
//                 // scale: scale,
//                 width: element.offsetWidth,
//                 removeContainer: false // for analyzing iframe
//             },
//             x: x,
//             y: y,
//             callback: function (pdf) {
//                 var timestamp = Date.now();
//                 var pdfName = fileName;
//                 pdf.save(pdfName);
//             }
//         });
//     } 


</script> 

<script> 

// window.jsPDF = window.jspdf.jsPDF;
// var docPDF = new jsPDF();
// function downloadpdf(pdfName) {
//     var elementHTML = document.querySelector("#printTable");
//     docPDF.html(elementHTML, {
//         callback: function (docPDF) {
//             docPDF.save(pdfName);
//         },
//         x: 15,
//         y: 15,
//         width: 118,
//         windowWidth: 650
//     });
// }
</script>
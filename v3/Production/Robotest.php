
<html>
<!-- jQuery library -->
<button>print</button>

<script src="js/jquery.min.js"></script>

<!-- jsPDF library -->
<script src="js/jsPDF/dist/jspdf.min.js"></script>


<script>


function generatePDF(){
	
var doc = new jsPDF();

doc.text(20, 20, 'Hello world!');
doc.text(20, 30, 'This is client-side Javascript to generate a PDF.');

// Add new page
doc.addPage();
doc.text(20, 20, 'Visit ');


doc.save('document.pdf');
}
</script>


</html>
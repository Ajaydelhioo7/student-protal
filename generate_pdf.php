<?php
require 'vendor/autoload.php';

// Retrieve data from the form submission
$data = json_decode($_POST['data'], true);

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your School or Institution Name');
$pdf->SetTitle('Test Score Report');
$pdf->SetSubject('Detailed Test Score');

// Set default header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set some content to print
$html = <<<EOD
<h1>Test Score Details</h1>
<p><strong>Roll No:</strong> {$data['rollno']}</p>
<p><strong>Batch:</strong> {$data['batch']}</p>
<p><strong>Test Name:</strong> {$data['testname']}</p>
<p><strong>Right Questions:</strong> {$data['right_question']}</p>
<p><strong>Wrong Questions:</strong> {$data['wrong_question']}</p>
<p><strong>Not Attempted:</strong> {$data['not_attempted']}</p>
<p><strong>Max Marks:</strong> {$data['max_marks']}</p>
<p><strong>Marks Obtained:</strong> {$data['marks_obtained']}</p>
<p><strong>Percentage:</strong> {$data['percentage']}%</p>
<p><strong>Award for Wrong:</strong> {$data['award_for_wrong']}</p>
<p><strong>Award for Right:</strong> {$data['award_for_right']}</p>
EOD;

// Print text using writeHTML()
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('test_score_details.pdf', 'I'); // Sends the PDF inline to the browser

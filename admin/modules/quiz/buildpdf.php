<?php
require 'pdfcrowd.php';

try
{   
    // create an API client instance
    $client = new Pdfcrowd("thewiper", "42c81c40d32aa7b4bfb255893b7e44c9");

    // convert a web page and store the generated PDF into a $pdf variable
    $pdf = $client->convertURI('https://biologiawiki.it/testpdf.html');

    // set HTTP response headers
    header("Content-Type: application/pdf");
    header("Cache-Control: max-age=0");
    header("Accept-Ranges: none");
    header("Content-Disposition: attachment; filename=\"pdfhtml.pdf\"");

    // send the generated PDF 
    echo $pdf;
}
catch(PdfcrowdException $why)
{
    echo "Pdfcrowd Error: " . $why;
}

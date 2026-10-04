<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$pdf=new Mpdf\Mpdf(['tempDir'=>sys_get_temp_dir()]);
$pdf->WriteHTML('<h1>Supplier ACME</h1><p>Motor, 2 pcs, USD 100, freight USD 15, lead time 14 days.</p>');
$bytes=$pdf->Output('',Mpdf\Output\Destination::STRING_RETURN);
$text=(new Smalot\PdfParser\Parser())->parseContent($bytes)->getText();
if(!str_contains($text,'Supplier ACME')||!str_contains($text,'lead time 14 days')){fwrite(STDERR,"PDF extraction check failed\n");exit(1);}echo"AI PDF extraction check passed\n";

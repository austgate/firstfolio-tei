<?php
/**
*  Proposed search function for TEI
*/

include 'xml_transform.php';

$text = $_GET['t'];
$lineno=$_GET['no'];

$quotation = extract_quotation($text, $lineno, '');
header('Content-Type: application/json');
echo json_encode($quotation, JSON_PRETTY_PRINT);
?>

<?php
/**
 * This script creates an xml file for errors
 */
header('Content-Type: text/xml');

  $writer = new XMLWriter();
  $writer->openURI('php://output');
  $writer->startDocument('1.0','UTF-8');
  $writer->setIndent(2);
    $writer->startElement('errors');
    foreach ($errors as $error) {
      $writer->writeElement('error',$error->getMessage());
    }
    $writer->endElement();
  $writer->endDocument();
$writer->flush();
?>

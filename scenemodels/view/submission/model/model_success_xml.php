<?php
/**
 * This script creates an xml file containing the country code
 */

header('Content-Type: text/xml');

  $writer = new XMLWriter();
  $writer->openURI('php://output');
  $writer->startDocument('1.0','UTF-8');
  $writer->setIndent(2);
    $writer->startElement('success');
      $writer->writeElement('requestId',$updatedReq->getId());
    $writer->endElement();
  $writer->endDocument();
$writer->flush();
?>

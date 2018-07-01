<?php

header('Content-Type: text/xml');

  $writer = new XMLWriter();
  $writer->openURI('php://output');
  $writer->startDocument('1.0','UTF-8');
  $writer->setIndent(2);
    $writer->startElement('model');
      $writer->writeElement('name',htmlspecialchars($modelMD->getName()));
      $writer->writeElement('notes',htmlspecialchars($modelMD->getDescription()));
      $writer->writeElement('author',$modelMD->getAuthor()->getId());
    $writer->endElement();
  $writer->endDocument();
$writer->flush();
?>

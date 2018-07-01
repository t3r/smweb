<?php
header('Content-Type: text/xml');

  $writer = new XMLWriter();
  $writer->openURI('php://output');
  $writer->startDocument('1.0','UTF-8');
  $writer->setIndent(2);
    $writer->startElement('models');
    foreach($modelMDs as $modelMD) {
      $writer->startElement('model');
        $writer->writeElement('id',$modelMD->getId());
        $writer->writeElement('name',htmlspecialchars($modelMD->getFilename()));
      $writer->endElement();
    }
    $writer->endElement();
  $writer->endDocument();
$writer->flush();

?>

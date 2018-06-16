<?php

/* 
 * Copyright (C) 2015 FlightGear Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

header('Content-Type: text/xml');

  $writer = new XMLWriter();  
  $writer->openURI('php://output');  
  $writer->startDocument('1.0','UTF-8');  
  $writer->setIndent(2);   
    $writer->startElement('files');  
    foreach ($filesInfos as $fileInfo) {
      $writer->startElement('file');  
        $writer->writeElement('name',$fileInfo->getFilename());
        $writer->writeElement('size',\FormatUtils::formatBytes($fileInfo->getSize()));
      $writer->endElement();    
    }
    $writer->endElement();  
  $writer->endDocument();   
$writer->flush();
?>

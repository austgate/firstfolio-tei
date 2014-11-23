<?php
// extract data takes the short code and converts into a url
function extract_data ($short) {

  $xml_str = open_file($short);  

  $reader = new XMLReader();

  if (!$reader->open($xml_str)) {
    die("Failed to open First Folio");
  }
  $pid = $act = $scene = $line = 0;
  $play = [];
  $person = [];
  $speaker = $scen = '';
  $id = $name = '';
  while($reader->read()) {
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'person') {
        $id = $reader->getAttribute('xml:id');
     }

     if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'persName') {
        if ($reader->getAttribute('type') == 'standard') {
          $pid++;
          $name = $reader->readString();
        }
     }
    $person{$id}=array('id'=>($pid-1), 'name'=>$name);
    // parse the play sections
    /*if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'div') {
      $divtype = $reader->getAttribute('type');
      if ($divtype == 'act') {
        $act = $reader->getAttribute('n');
      }
      if ($divtype == 'scene') {
        $scene = $reader->getAttribute('n');
      }
    }*/
    // get the lines
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'l') {
       $line = $reader->getAttribute('n');
    }
    // get the speaker
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'sp') {
       $speaker = $reader->getAttribute('who');
    }
    // get the scene
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'stage') {
       $type = $readertype = $reader->getAttribute('type');
       $scen = $type.'::'.$reader->readString();
    }
    //$ycoord = $act . $scene . $line;
    $ycoord = $line;
    $play{$ycoord} = array('scene'=> $scen, 'speaker'=>substr($speaker, 1));
  }
  $reader->close();
  
  return array($person, $play);
}

// function to retrieve the x coordinates
function transform_labels ($people) {

   $labels = "[";
   foreach ($people as $p) {
      if ($p['name']){
        $n = explode(',', $p['name']);
        $labels .= "'".addslashes($n[0])."',";
      }
   }
   return substr($labels, 0, -1) . "]";
}
//create the y coordinates with collapsing the act, scene and line.
function transform_coords ($drama, $people) {
   $xcoords = '[';
   $ycoords = '[';
   foreach ($drama as $line => $value) {
      $xcoords .= "'".$people[$value['speaker']]['id']."',";
      $ycoords .= "'$line',";
   }
   return array(substr($xcoords, 0, -1)."]",substr($ycoords, 0, -1)."]" );
}
// convert the short code string into a valid URL
// @todo does this need to be in a setting in admin rather than hardcoded?
function open_file($code) {
   return "http://firstfolio.bodleian.ox.ac.uk/download/xml/F-$code.xml";
}

/*list($people, $play) = extract_data();
list($label, $xcoord) = mungex($people);
list ($x,$y) = mungey($play, $people);*/

?>
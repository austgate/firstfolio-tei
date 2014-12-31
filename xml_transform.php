<?php
// should this just be a public string?
function cite() {
 return "Digital facsimile of the Bodleian First Folio of Shakespeare's plays, Arch. G c.7";
}

// extract a line or block quotes
function extract_quotation ($short, $start, $end) {

  $xml_str = open_file($short);

  $reader = new XMLReader();

  if (!$reader->open($xml_str)) {
    die("Failed to open First Folio");
  }
  $act = $scene = $line = 0;
  $lines = array();
  $title = '';
  while($reader->read()) {
  
  if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'title') {
      if ($reader->getAttribute('type')=='statement' && !$title) {
          $title = $reader->readString();
      }
  } 

  if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'div') {
        $divtype = $reader->getAttribute('type');
        if ($divtype == 'act') {
          $act = $reader->getAttribute('n');
        }
        if ($divtype == 'scene') {
          $scene = $reader->getAttribute('n');
        }
      }

    // get the lines
    if ($reader->nodeType == XMLReader::ELEMENT && ($reader->name == 'l' || $reader->name == 'p')) {
       $line = $reader->getAttribute('n');
       $node = new SimpleXMLElement($reader->readOuterXML());
       if ($end && ($line >=  $start && $line <= $end)) {
         if ($node->choice) {
           $lines[] = array('type'=>$reader->name,'title'=>$title,'act'=>$act, 'scene'=> $scene, 'lineno'=> $line, 'text'=>$reader->readString(), 'orig'=>$node->choice->orig, 'corr'=>$node->choice->corr);
         } else {
           $lines[] = array('type'=>$reader->name,'title'=>$title,'act'=>$act, 'scene'=> $scene, 'lineno'=> $line, 'text'=>$reader->readString());
         }
       } else if ($start && !$end) {
           if ($line == $start) {
              if ($node->choice) {
                $lines[] = array('type'=>$reader->name,'title'=>$title,'act'=>$act, 'scene'=> $scene, 'lineno'=> $line, 'text'=>$reader->readString(), 'orig'=>$node->choice->orig, 'corr'=>$node->choice->corr);
              } else {
                $lines[] = array('type'=>$reader->name,'title'=>$title,'act'=>$act, 'scene'=> $scene, 'lineno'=> $line, 'text'=>$reader->readString());
              }
           }
       }
    }
  }
  $reader->close();

  return $lines;
}

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
  $speaker = $scen = $type= '';
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
    if ($reader->nodeType == XMLReader::ELEMENT && ($reader->name == 'l' || $reader->name=='p')) {
       $line = $reader->getAttribute('n');
       if ($reader->name == 'l') {
          $type = ($reader->getAttribute('rhyme')) ? 'rhyme' : 'blank' ;
       } else {
          $type = $reader->name;
       }
    }
    // get the speaker
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'sp') {
       $speaker = $reader->getAttribute('who');
    }
    
    $ycoord = $line;
    $play{$ycoord} = array('speaker'=>substr($speaker, 1),'type'=>$type);
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
   $types = '[';
   foreach ($drama as $line => $value) {
      $xcoords .= "'".$people[$value['speaker']]['id']."',";
      $ycoords .= "'$line',";
      $types .= "'". $value['type']."',";
   }
   return array(substr($xcoords, 0, -1)."]",substr($ycoords, 0, -1)."]", substr($types, 0,-1)."]" );
}
// convert the short code string into a valid URL
// @todo does this need to be in a setting in admin rather than hardcoded?
function open_file($code) {
   return "http://localhost/~iainemsley/text/F-$code.xml"; 
   #return "http://firstfolio.bodleian.ox.ac.uk/download/xml/F-$code.xml";
}

?>

<?php
/**
 * Plugin Name: TEI Visualisation
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Visualisation of TEI drama works
 * Version: 0.0.1
 * Author: Iain Emsley
 * Author URI: http://www.austgate.co.uk
 * License: GPL2
 */

/*  Copyright 2014  Iain Emsley  (email : iain_emsley@austgate.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include 'xml_transform.php';

//add quotation
add_shortcode( 'teiquote', 'teiquote_shortcode');

//add in the visualisation
add_shortcode( 'tei', 'tei_shortcode' );

// register and queue the relevant scripts. 
function add_tei_js() {
  //load the dependencies
  wp_register_script('D3', 'http://d3js.org/d3.v3.min.js');
  wp_enqueue_script('D3');
  //load module specific JS
  wp_register_script('dna', plugins_url('tei/graph.js'), __FILE__);
  wp_enqueue_script('dna');
}

add_action('init','add_tei_js');

// short code to put in the quote text
function teiquote_shortcode($atts) {
  extract(
    shortcode_atts(
      array(
        'id' => '',
        'start' => '',
        'end' => '',
      ),
      $atts)
  );
 $quote = extract_quotation ($atts['id'], $atts['start'], $atts['end']);
 return format_citation(sizeof($quote), $quote,$atts['id']);
}

// function to format the quotation
function format_citation($length, $quotation,$id) {
  if ($length == '1') {
    return '"'. format_text($quotation[0]) .'" ' . '[' .format_title($id). '] '. $quotation[0]['title'] .' ('.$quotation[0]['act'].'.' . $quotation[0]['scene'].'.' . $quotation[0]['lineno'].')';
  } else if ($length == '2') {
    return '"'. format_text($quotation[0]) . '/'. format_text($quotation[1]) .'" ' . '[' .format_title($id). '] '. $quotation[0]['title'] 
           .' ('.$quotation[0]['act'].'.' . $quotation[0]['scene'] .'.' . $quotation[0]['lineno'] .'-'.$quotation[1]['lineno']. ')'; 
  } else {
    $t = '';
    foreach ($quotation as $line=>$text) {
      $t .= format_text($text) ."<br />";
      $act = $text['act'];
      $scene = $text['scene'];
    }
    $name = format_title($id);
    $title = $quotation[0]['title'];
    $line =  $quotation[0]['lineno'] .'&ndash;'. $quotation[max(array_keys($quotation))]['lineno'];
    return "<blockquote> $t
      <br /><footer>
      [$name] $title ($act . $scene . $line) <br />
      <a href='http://firstfolio.bodleian.ox.ac.uk'>".cite()."</a>
      </footer>
      </blockquote>";
  }
}

//function to return the better known name
function format_title($id) {
   $title= array(
     '1ham' => 'Hamlet',
     'ham' => 'Hamlet',
   );
   return $title[$id];
}

//function to set the title tooltip
function format_text($textln) {
  if ($textln['orig']) {
    $text=str_replace($textln['orig'], '', $textln['text']);
    return str_replace($textln['corr'], '<span title="'.$textln['orig'].'" style="text-decoration:underline">'.$textln['corr'].'</span>', $text);
  } 
  return $textln['text'];
}
/**
* Shortcode creation function to get the correct text
* from the store
*
* @param array $atts
* @return string
* HTML to place the test with graph markup
*/
function tei_shortcode($atts) {
  extract(
    shortcode_atts(
      array(
        'id' => '',
      ),
      $atts)
  );


 list($people, $play) = extract_data($atts['id']);
 $label = transform_labels($people);
 list ($x,$y,$types) = transform_coords($play, $people);
 $id = $atts['id'];
 return "
<div id='ffvis'></div>
<script>
dna_graph($y, $x, $label,'$id', $types);
</script>
<div id='result'></div>
"; //return the javascript here from dnagraph
}

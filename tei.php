<?php
/**
 * Plugin Name: TEI Visualisation
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Visualisation of TEI drama works
 * Version: 0.0.1
 * Author: Iain Emsley
 * Author URI: http://www.austgate.co.uk
 * License: A short license name. Example: GPL2
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
 $t = '';
 foreach ($quote as $line=>$text) {
    $t .= $text['text'] ."<br />";
    $act = $text['act'];
    $scene = $text['scene'];
 }
 $title = $quote[0]['title'];
 $line = (sizeof($quote) > 1) ? $quote[0]['lineno'] .'&ndash;'. $quote[max(array_keys($quote))]['lineno'] : $quote[0]['lineno'];
 return "<blockquote>". $t ."
<footer>
$title ($act . $scene . $line) <br />
<a href='http://firstfolio.bodleian.ox.ac.uk'>".cite()."</a>
</footer>
</blockquote>";
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
 list ($x,$y) = transform_coords($play, $people);

 return "
<div id='ffvis'>
</div>
<script>
dna_graph($y, $x, $label);
</script>
"; //return the javascript here from dnagraph
}

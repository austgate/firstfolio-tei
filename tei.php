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

add_shortcode( 'tei', 'tei_shortcode' );

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
<script src='http://d3js.org/d3.v3.min.js'></script>
<script src='../dnagraph/graph.js'></script>
<script>
dna_graph($y, $x, $label);
</script>
"; //return the javascript here from dnagraph
}

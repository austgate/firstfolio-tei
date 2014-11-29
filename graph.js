/**
*  Functions to contain the graphs
*/
var xcoords, ycoords, labels;
/* 
 * Function to show a graph that looks like a DNA trace
 * Plots line numbers against the characters in a play
 *
 * Provides a very simple search API as well
 */
function dna_graph(xcoords, ycoords, labels, shortcode) {

	var xdata = xcoords,
	    ydata = ycoords,
	    labellist = labels;
	    
	    
	    var margin = {top: 20, right: 15, bottom: 60, left: 150}
	      , width = 2500 - margin.left - margin.right
	      , height = 960 - margin.top - margin.bottom;
	    
	    var x = d3.scale.linear()
	              .domain([0, 2500]) //xdata not 2500
	              .range([ 0, 2500 ]);
	    
	    var y = d3.scale.linear()
	    	      .domain([-1, 18])
	    	      .range([ height, 0 ]);
	 
	    var chart = d3.select('#ffvis')
		.append('svg:svg')
		.attr('width', width + margin.right + margin.left)
		.attr('height', height + margin.top + margin.bottom)
		.attr('class', 'chart')

	    var main = chart.append('g')
		.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')')
		.attr('width', width)
		.attr('height', height)
		.attr('class', 'main')   
	        
	    // draw the x axis
	    var xAxis = d3.svg.axis()
		.scale(x)
		.orient('bottom');

	    main.append('g')
		.attr('transform', 'translate(0,' + height + ')')
		.attr('class', 'main axis date')
		.call(xAxis);

	    // draw the y axis
	    var yAxis = d3.svg.axis()
		.scale(y)
		.orient('left')
		.ticks(35)
	        .tickFormat(function (d, i) {
	            return labellist[d];
	        });

	    main.append('g')
		.attr('transform', 'translate(0,0)')
		.attr('class', 'main axis date')
		.call(yAxis);

	    var g = main.append("svg:g"); 
	   //refactor me!! 
	    g.selectAll("scatter-dots")
	      .data(ydata)
	      .enter().append("svg:circle")
	          .attr("cy", function (d) { return y(d); } )
	          .attr("cx", function (d,i) { return x(xdata[i]); } )
	          .attr("r", 6)
	          .style("opacity", 0.6)
                  .on("mouseover", function(d,i){
d3.json("wp-content/plugins/tei/teisearch.php?t="+ shortcode +"&no="+i, 
  function (error, json) {
    if (error) d3.select("#result").text("error occured");
    d3.select("#result").text(json[0].text + " " + json[0].title + " " + json[0].act +"."+json[0].scene+"."+json[0].lineno);
  });

})
                  .on("mouseout", function(){d3.select("#result").text("");});
}

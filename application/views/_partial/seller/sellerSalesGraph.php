<div id="monthlysalesearnings--js" class="sales-graph"></div>

<!-- load Google AJAX API -->																	
<script type="text/javascript">


$salesKey = <?php echo json_encode( array_keys($dashboardInfo['sales_earnings_chart_data']));?>;
$salesVal = <?php  echo json_encode(  array_values($dashboardInfo['sales_earnings_chart_data']) );

?>;
		var chart = new Chartist.Line('#monthlysalesearnings--js', {
		  labels: $salesKey ,
		  
		  series: [
			$salesVal,
		   
		  ]
		}, {
		  fullWidth: true,
			showArea: true,
		  chartPadding: {
			right: 40
		  }
		});
			var seq = 0,
			  delays = 80,
			  durations = 500;

			// Once the chart is fully created we reset the sequence
			chart.on('created', function() {
			  seq = 0;
			});

			// On each drawn element by Chartist we use the Chartist.Svg API to trigger SMIL animations
			chart.on('draw', function(data) {
			  seq++;

			  if(data.type === 'line') {
				// If the drawn element is a line we do a simple opacity fade in. This could also be achieved using CSS3 animations.
				data.element.animate({
				  opacity: {
					// The delay when we like to start the animation
					begin: seq * delays + 1000,
					// Duration of the animation
					dur: durations,
					// The value where the animation should start
					from: 0,
					// The value where it should end
					to: 1
				  }
				});
			  } else if(data.type === 'label' && data.axis === 'x') {
				data.element.animate({
				  y: {
					begin: seq * delays,
					dur: durations,
					from: data.y + 100,
					to: data.y,
					// We can specify an easing function from Chartist.Svg.Easing
					easing: 'easeOutQuart'
				  }
				});
			  } else if(data.type === 'label' && data.axis === 'y') {
				data.element.animate({
				  x: {
					begin: seq * delays,
					dur: durations,
					from: data.x - 100,
					to: data.x,
					easing: 'easeOutQuart'
				  }
				});
			  } else if(data.type === 'point') {
				data.element.animate({
				  x1: {
					begin: seq * delays,
					dur: durations,
					from: data.x - 10,
					to: data.x,
					easing: 'easeOutQuart'
				  },
				  x2: {
					begin: seq * delays,
					dur: durations,
					from: data.x - 10,
					to: data.x,
					easing: 'easeOutQuart'
				  },
				  opacity: {
					begin: seq * delays,
					dur: durations,
					from: 0,
					to: 1,
					easing: 'easeOutQuart'
				  }
				});
			  } else if(data.type === 'grid') {
				// Using data.axis we get x or y which we can use to construct our animation definition objects
				var pos1Animation = {
				  begin: seq * delays,
				  dur: durations,
				  from: data[data.axis.units.pos + '1'] - 30,
				  to: data[data.axis.units.pos + '1'],
				  easing: 'easeOutQuart'
				};

				var pos2Animation = {
				  begin: seq * delays,
				  dur: durations,
				  from: data[data.axis.units.pos + '2'] - 100,
				  to: data[data.axis.units.pos + '2'],
				  easing: 'easeOutQuart'
				};

				var animations = {};
				animations[data.axis.units.pos + '1'] = pos1Animation;
				animations[data.axis.units.pos + '2'] = pos2Animation;
				animations['opacity'] = {
				  begin: seq * delays,
				  dur: durations,
				  from: 0,
				  to: 1,
				  easing: 'easeOutQuart'
				};

				data.element.animate(animations);
			  }
			});

			// For the sake of the example we update the chart every time it's created with a delay of 10 seconds
			chart.on('created', function() {
			  if(window.__exampleAnimateTimeout) {
				clearTimeout(window.__exampleAnimateTimeout);
				window.__exampleAnimateTimeout = null;
			  }
			  //window.__exampleAnimateTimeout = setTimeout(chart.update.bind(chart), 12000);
			});

	
</script>
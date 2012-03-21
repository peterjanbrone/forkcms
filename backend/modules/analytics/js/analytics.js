/**
 * Interaction for the analytics module
 *
 * @author	Annelies Vanextergem <annelies@netlash.com>
 * @author	Thomas Deceuninck <thomasdeceuninck@netlash.com>
 * @author  Peter-Jan Brone <peterjan.brone@wijs.be>
 */
jsBackend.analytics =
{
	init: function()
	{
		// variables
		$chartPieChart = $('#chartPieChart');
		$chartWidget = $('#chartWidget');
		$chartDoubleMetricPerDay = $('#chartDoubleMetricPerDay');
		$chartSingleMetricPerDay = $('#chartSingleMetricPerDay');
		$chartPageNotFoundStatistics = $('#chartPageNotFoundStatistics');
		$chartWidgetPageNotFoundStatistics = $('#chartWidgetPageNotFoundStatistics');

		// filter variables
		$filterExtension = $('#extension');
		$filterIsLoggedIn = $('#isLoggedIn');
		$filterCallerIsAction = $('#callerIsAction');
		$filterBrowser = $('#browser');
		$filterBrowserVersion = $('#browserVersion');

		jsBackend.analytics.charts.init();
		jsBackend.analytics.chartDoubleMetricPerDay.init();
		jsBackend.analytics.chartPieChart.init();
		jsBackend.analytics.chartSingleMetricPerDay.init();
		jsBackend.analytics.chartWidget.init();
		jsBackend.analytics.chartPageNotFoundStatistics.init();
		jsBackend.analytics.chartWidgetPageNotFoundStatistics.init();
		jsBackend.analytics.pageNotFoundStatistics.init();
		jsBackend.analytics.loading.init();
		jsBackend.analytics.resize.init();
	}
}

jsBackend.analytics.charts =
{
	init: function()
	{
		if($chartPieChart.length > 0 || $chartDoubleMetricPerDay.length > 0 || $chartSingleMetricPerDay.length > 0 || $chartWidget.length > 0 || $chartWidgetPageNotFoundStatistics.length > 0)
		{
			Highcharts.setOptions(
			{
				colors: ['#058DC7', '#50b432', '#ED561B', '#EDEF00', '#24CBE5', '#64E572', '#FF9655'],
				title: { text: '' },
				legend:
				{
					layout: 'vertical',
					backgroundColor: '#FFF',
					borderWidth: 0,
					shadow: false,
					symbolPadding: 12,
					symbolWidth: 10,
					itemStyle: { cursor: 'pointer', color: '#000', lineHeight: '18px' },
					itemHoverStyle: { color: '#666' },
					style: { right: '0', top: '0', bottom: 'auto', left: 'auto' }
				}
			});
		}
	}
}

jsBackend.analytics.chartPieChart =
{
	chart: '',

	init: function()
	{
		if($chartPieChart.length > 0) { jsBackend.analytics.chartPieChart.create(); }
	},

	// add new chart
	create: function()
	{
		// variables
		$pieChartValues = $('#dataChartPieChart ul.data li');
		var pieChartData = [];

		$pieChartValues.each(function()
		{
			// variables
			$this = $(this);

			pieChartData.push(
			{
				'name': $this.children('span.label').html(),
				'y': parseInt($this.children('span.value').html()),
				'percentage': parseInt($this.children('span.percentage').html())
			});
		});

		var containerWidth = $chartPieChart.width();

		jsBackend.analytics.chartPieChart.chart = new Highcharts.Chart(
		{
			chart: { renderTo: 'chartPieChart', height: 200, width: containerWidth, margin: [0, 160, 0, 0] },
			credits: { enabled: false },
			plotArea: { shadow: null, borderWidth: null, backgroundColor: null },
			tooltip:
			{
				formatter: function()
				{
					var percentage = String(this.point.percentage);
					return '<b>'+ this.point.name +'</b>: '+ this.y + ' (' + percentage.substring(0, $.inArray('.', percentage) + 3) + '%)';
				},
				borderWidth: 2, shadow: false
			},
			plotOptions:
			{
				pie:
				{
					allowPointSelect: true,
					dataLabels:
					{
						enabled: false
					},
					showInLegend: true
				}
			},
			legend: { style: { right: '10px' } },
			series: [ {type: 'pie', data: pieChartData } ]
		});
	},

	// destroy chart
	destroy: function()
	{
		jsBackend.analytics.chartPieChart.chart.destroy();
	}
}

jsBackend.analytics.chartDoubleMetricPerDay =
{
	chart: '',

	init: function()
	{
		if($chartDoubleMetricPerDay.length > 0) { jsBackend.analytics.chartDoubleMetricPerDay.create(); }
	},

	// add new chart
	create: function()
	{
		var xAxisItems = $('#dataChartDoubleMetricPerDay ul.series li.serie:first-child ul.data li');
		var xAxisValues = [];
		var xAxisCategories = [];
		var counter = 0;
		var interval = Math.ceil(xAxisItems.length / 10);

		xAxisItems.each(function()
		{
			xAxisValues.push($(this).children('span.fulldate').html());
			var text = $(this).children('span.date').html();
			if(xAxisItems.length > 10 && counter%interval > 0) text = ' ';
			xAxisCategories.push(text);
			counter++;
		});

		var metric1Name = $('#dataChartDoubleMetricPerDay ul.series li#metric1serie span.name').html();
		var metric1Values = $('#dataChartDoubleMetricPerDay ul.series li#metric1serie span.value');
		var metric1Data = [];

		metric1Values.each(function() { metric1Data.push(parseInt($(this).html())); });

		var metric2Name = $('#dataChartDoubleMetricPerDay ul.series li#metric2serie span.name').html();
		var metric2Values = $('#dataChartDoubleMetricPerDay ul.series li#metric2serie span.value');
		var metric2Data = [];

		metric2Values.each(function() { metric2Data.push(parseInt($(this).html())); });

		var containerWidth = $('#chartDoubleMetricPerDay').width();

		jsBackend.analytics.chartDoubleMetricPerDay.chart = new Highcharts.Chart(
		{
			chart: { renderTo: 'chartDoubleMetricPerDay', height: 200, width: containerWidth, margin: [60, 0, 30, 40], defaultSeriesType: 'line' },
			xAxis: { lineColor: '#CCC', lineWidth: 1, categories: xAxisCategories, color: '#000' },
			yAxis: { min: 0, max: $('#dataChartDoubleMetricPerDay #maxYAxis').html(), tickInterval: ($('#dataChartDoubleMetricPerDay #tickInterval').html() == '' ? null : $('#dataChartDoubleMetricPerDay #tickInterval').html()), title: { text: '' } },
			credits: { enabled: false },
			tooltip: { formatter: function() { return '<b>'+ this.series.name +'</b><br/>'+ xAxisValues[this.point.x] +': '+ this.y; } },
			plotOptions:
			{
				line: { marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 5, lineWidth: 1 } } } },
				area: {	marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 5, lineWidth: 1 } } } },
				column: { pointPadding: 0.2, borderWidth: 0 },
				series: { fillOpacity: 0.3 }
			},
			series: [{name: metric1Name, data: metric1Data, type: 'area' }, { name: metric2Name, data: metric2Data }]
		});
	},

	// destroy chart
	destroy: function()
	{
		jsBackend.analytics.chartDoubleMetricPerDay.chart.destroy();
	}
}

jsBackend.analytics.chartSingleMetricPerDay =
{
	chart: '',

	init: function()
	{
		if($chartSingleMetricPerDay.length > 0) { jsBackend.analytics.chartSingleMetricPerDay.create(); }
	},

	// add new chart
	create: function()
	{
		var xAxisItems = $('#dataChartSingleMetricPerDay ul.series li.serie:first-child ul.data li');
		var xAxisValues = [];
		var xAxisCategories = [];
		var counter = 0;
		var interval = Math.ceil(xAxisItems.length / 10);

		xAxisItems.each(function()
		{
			xAxisValues.push($(this).children('span.fulldate').html());
			var text = $(this).children('span.date').html();
			if(xAxisItems.length > 10 && counter%interval > 0) text = ' ';
			xAxisCategories.push(text);
			counter++;
		});

		var singleMetricName = $('#dataChartSingleMetricPerDay ul.series li#metricserie span.name').html();
		var singleMetricValues = $('#dataChartSingleMetricPerDay ul.series li#metricserie span.value');
		var singleMetricData = [];

		singleMetricValues.each(function() { singleMetricData.push(parseInt($(this).html())); });

		var containerWidth = $('#chartSingleMetricPerDay').width();

		jsBackend.analytics.chartSingleMetricPerDay.chart = new Highcharts.Chart(
		{
			chart: { renderTo: 'chartSingleMetricPerDay', height: 200, width: containerWidth, margin: [60, 0, 30, 40], defaultSeriesType: 'area' },
			xAxis: { lineColor: '#CCC', lineWidth: 1, categories: xAxisCategories, color: '#000' },
			yAxis: { min: 0, max: $('#dataChartSingleMetricPerDay #maxYAxis').html(), tickInterval: ($('#dataChartSingleMetricPerDay #tickInterval').html() == '' ? null : $('#dataChartSingleMetricPerDay #tickInterval').html()), title: { text: '' } },
			credits: { enabled: false },
			tooltip: { formatter: function() { return '<b>'+ this.series.name +'</b><br/>'+ xAxisValues[this.point.x] +': '+ this.y; } },
			plotOptions:
			{
				area: { marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 5, lineWidth: 1 } } } },
				column: { pointPadding: 0.2, borderWidth: 0 },
				series: { fillOpacity: 0.3 }
			},
			series: [{ name: singleMetricName, data: singleMetricData }]
		});
	},

	// destroy chart
	destroy: function()
	{
		jsBackend.analytics.chartSingleMetricPerDay.chart.destroy();
	}
}

jsBackend.analytics.chartWidget =
{
	chart: '',

	init: function()
	{
		if($chartWidget.length > 0) { jsBackend.analytics.chartWidget.create(); }
	},

	// add new chart
	create: function()
	{
		var xAxisItems = $('#dataChartWidget ul.series li.serie:first-child ul.data li');
		var xAxisValues = [];
		var xAxisCategories = [];
		var counter = 0;
		var interval = Math.ceil(xAxisItems.length / 10);

		xAxisItems.each(function()
		{
			xAxisValues.push($(this).children('span.fulldate').html());
			var text = $(this).children('span.date').html();
			if(xAxisItems.length > 10 && counter%interval > 0) text = ' ';
			xAxisCategories.push(text);
			counter++;
		});

		var metric1Name = $('#dataChartWidget ul.series li#metric1serie span.name').html();
		var metric1Values = $('#dataChartWidget ul.series li#metric1serie span.value');
		var metric1Data = [];

		metric1Values.each(function() { metric1Data.push(parseInt($(this).html())); });

		var metric2Name = $('#dataChartWidget ul.series li#metric2serie span.name').html();
		var metric2Values = $('#dataChartWidget ul.series li#metric2serie span.value');
		var metric2Data = [];

		metric2Values.each(function() { metric2Data.push(parseInt($(this).html())); });

		jsBackend.analytics.chartWidget.chart = new Highcharts.Chart(
		{
			chart: { renderTo: 'chartWidget', defaultSeriesType: 'line', margin: [30, 0, 30, 0], height: 200, width: 270, defaultSeriesType: 'line' },
			xAxis: { categories: xAxisCategories },
			yAxis: { min: 0, max: $('#dataChartWidget #maxYAxis').html(), tickInterval: ($('#dataChartWidget #tickInterval').html() == '' ? null : $('#dataChartWidget #tickInterval').html()), title: { enabled: false } },
			credits: { enabled: false },
			legend: { layout: 'horizontal', backgroundColor: 'transparent' },
			tooltip: { formatter: function() { return '<b>'+ this.series.name +'</b><br/>'+ xAxisValues[this.point.x] +': '+ this.y; } },
			plotOptions:
			{
				line: { marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 5, lineWidth: 1 } } } },
				area: { marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 5, lineWidth: 1 } } } },
				column: { pointPadding: 0.2, borderWidth: 0 },
				series: { fillOpacity: 0.3 }
			},
			series: [ { name: metric1Name, data: metric1Data, type: 'area' }, { name: metric2Name, data: metric2Data } ]
		});
	},

	// destroy chart
	destroy: function()
	{
		jsBackend.analytics.chartWidget.chart.destroy();
	}
}

jsBackend.analytics.chartPageNotFoundStatistics =
{
	chart: '',

	init: function()
	{
		if($chartPageNotFoundStatistics.length > 0) { jsBackend.analytics.chartPageNotFoundStatistics.create(); }

		// show day details when clicking on a chart node
		$('#chartPageNotFoundStatistics .highcharts-tracker').on('click', function(){jsBackend.analytics.pageNotFoundStatistics.toggleDays();});

		// show details, except when the row text is 'none...'
		$("#dataGridPageNotFoundStatistics td").not(":contains('none...')").on('click', function(e){jsBackend.analytics.pageNotFoundStatistics.toggleDetails(e);});
	},

	create: function()
	{
		var xAxisItems = $('#dataChartPageNotFoundStatistics ul.series li.serie:first-child ul.data li');
		var xAxisValues = [];
		var xAxisCategories = [];
		var counter = 0;
		var interval = Math.ceil(xAxisItems.length / 10);

		xAxisItems.each(function()
		{
			xAxisValues.push($(this).children('span.fulldate').html());
			var text = $(this).children('span.date').html();
			if(xAxisItems.length > 10 && counter%interval > 0) text = ' ';
			xAxisCategories.push(text);
			counter++;
		});

		var metric1Name = $('#dataChartPageNotFoundStatistics ul.series li#metric1serie span.name').html();
		var metric1Values = $('#dataChartPageNotFoundStatistics ul.series li#metric1serie span.value');
		var metric1Data = [];

		metric1Values.each(function() { metric1Data.push(parseInt($(this).html())); });

		var containerWidth = $('#chartPageNotFoundStatistics').width();

		jsBackend.analytics.chartPageNotFoundStatistics.chart = new Highcharts.Chart(
		{
			chart: { renderTo: 'chartPageNotFoundStatistics', height: 200, width: containerWidth, margin: [60, 0, 30, 40], defaultSeriesType: 'line' },
			xAxis: { lineColor: '#CCC', lineWidth: 1, categories: xAxisCategories, color: '#000' },
			yAxis: { min: 0, max: $('#dataChartPageNotFoundStatistics #chartPageNotFoundStatisticsMaxYAxis').html(), tickInterval: ($('#dataChartPageNotFoundStatistics #chartPageNotFoundStatisticsTickInterval').html() == '' ? null : $('#dataChartPageNotFoundStatistics #chartPageNotFoundStatisticsTickInterval').html()), title: { text: '' } },
			credits: { enabled: false },
			tooltip: { formatter: function() { return '<b>'+ this.series.name +'</b><br/>'+ xAxisValues[this.point.x] +': '+ this.y; } },
			plotOptions:
			{
				line: { marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 5, lineWidth: 1 } } } },
				area: {	marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 5, lineWidth: 1 } } } },
				column: { pointPadding: 0.2, borderWidth: 0 },
				series: { fillOpacity: 0.3 }
			},
			series: [{name: metric1Name, data: metric1Data, type: 'area' }]
		});
	},

	destroy: function()
	{
		jsBackend.analytics.chartPageNotFoundStatistics.chart.destroy();
	}
}

jsBackend.analytics.chartWidgetPageNotFoundStatistics =
{
	chart: '',

	init: function()
	{
		if($chartWidgetPageNotFoundStatistics.length > 0) { jsBackend.analytics.chartWidgetPageNotFoundStatistics.create(); }

		// show day details when clicking on a chart node
		$('#chartPageNotFoundStatistics .highcharts-tracker').on('click', function(){jsBackend.analytics.pageNotFoundStatistics.toggleDays();});

		// show details, except when the row text is 'none...'
		$("#pageNotFoundIndex td").not(":contains('none...')").on('click', function(e){jsBackend.analytics.pageNotFoundStatistics.toggleDetails(e);});
	},

	// add new chart
	create: function()
	{
		var xAxisItems = $('#datachartWidgetPageNotFoundStatistics ul.series li.serie:first-child ul.data li');
		var xAxisValues = [];
		var xAxisCategories = [];
		var counter = 0;
		var interval = Math.ceil(xAxisItems.length / 10);

		xAxisItems.each(function()
		{
			xAxisValues.push($(this).children('span.fulldate').html());
			var text = $(this).children('span.date').html();
			if(xAxisItems.length > 10 && counter%interval > 0) text = ' ';
			xAxisCategories.push(text);
			counter++;
		});

		var metric1Name = $('#datachartWidgetPageNotFoundStatistics ul.series li#metric1serie span.name').html();
		var metric1Values = $('#datachartWidgetPageNotFoundStatistics ul.series li#metric1serie span.value');
		var metric1Data = [];

		metric1Values.each(function() { metric1Data.push(parseInt($(this).html())); });

		jsBackend.analytics.chartWidgetPageNotFoundStatistics.chart = new Highcharts.Chart(
		{
			chart: { renderTo: 'chartWidgetPageNotFoundStatistics', defaultSeriesType: 'line', margin: [30, 0, 30, 0], height: 200, width: 270, defaultSeriesType: 'line' },
			xAxis: { categories: xAxisCategories },
			yAxis: { min: 0, max: $('#datachartWidgetPageNotFoundStatistics #maxYAxis').html(), tickInterval: ($('#datachartWidgetPageNotFoundStatistics #tickInterval').html() == '' ? null : $('#datachartWidgetPageNotFoundStatistics #tickInterval').html()), title: { enabled : false } },
			credits: { enabled: false },
			legend: { layout: 'horizontal', backgroundColor: 'transparent' },
			tooltip: { formatter: function() { return '<b>'+ this.series.name +'</b><br/>'+ xAxisValues[this.point.x	] +': '+ this.y; } },
			plotOptions:
			{
				line: { marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 6, lineWidth: 1 } } } },
				area: { marker: { enabled: false, states: { hover: { enabled: true, symbol: 'circle', radius: 6, lineWidth: 1 } } } },
				column: { pointPadding: 0.2, borderWidth: 0 },
				series: { fillOpacity: 0.2 }
			},
			series: [ { name: metric1Name, data: metric1Data, type: 'area' }]
		});
	},

	// destroy chart
	destroy: function()
	{
		jsBackend.analytics.chartWidgetPageNotFoundStatistics.chart.destroy();
	}
}

jsBackend.analytics.pageNotFoundStatistics =
{
	init: function()
	{
		if($filterExtension.length > 0) {
			
			// add event listeners
			$filterIsLoggedIn.on('click', jsBackend.analytics.pageNotFoundStatistics.filter);
			$filterCallerIsAction.on('click', jsBackend.analytics.pageNotFoundStatistics.filter);
			$filterExtension.on('change', jsBackend.analytics.pageNotFoundStatistics.filter);
			$filterBrowser.on('change', jsBackend.analytics.pageNotFoundStatistics.filter);
			$filterBrowserVersion.on('change', jsBackend.analytics.pageNotFoundStatistics.filter);

		}
	},

	filter: function()
	{
		// calculate the timestamp
		var headerDate = $('#pageNotFoundDate').text();
		var dateString = headerDate.replace('missing pages:', '');

		// append the year and get the unix timestamp
		var year = new Date().getFullYear(); // we can't use Date.Now() cause of IE8
		date = new Date(dateString + ' ' + year);
		var timestamp = Math.round(date.getTime() / 1000) + 46800; // add 46800 to equal google dates

		$.ajax(
		{
			data:
			{
				fork: { action: 'filter_statistics' , module: 'analytics'},
				timestamp: timestamp,
				isLoggedIn: $filterIsLoggedIn.attr('checked'),
				callerIsAction: $filterCallerIsAction.attr('checked'),
				extension: $filterExtension.val(),
				browser: $filterBrowser.val(),
				browserVersion: $filterBrowserVersion.val()
			},
			success: function(json, textStatus)
			{
				if(json.code != 200)
				{
					// show error if needed
					if(jsBackend.debug) alert(textStatus);
				}
				else
				{
					// remove all current data
					$('#dataChartPageNotFoundStatistics ul.data li').remove();

					// insert the new data,
					// meanwhile calc. maxYAxis
					var maxYAxis = 0;
					for(var i in json.data.data[0].data)
					{
						// get date for formatting
						var date = new Date(json.data.data[0].data[i].date * 1000);

						$('#dataChartPageNotFoundStatistics ul.data').append(
							'<li><span class="fulldate">' + date.format(" ddd d mmm ") +
							'</span><span class="date">' + date.format(" d mmm ") +
							'</span><span class="value">' + json.data.data[0].data[i].value +
							'</span></li>'
						);

						// update maxYAxis
						if(json.data.data[0].data[i].value  > maxYAxis) maxYAxis = json.data.data[0].data[i].value;
					}

					// set max Y and tick interval
					$('#chartPageNotFoundStatisticsMaxYAxis').html(maxYAxis);
					(maxYAxis == 2)
						? $('#chartPageNotFoundStatisticsTickInterval').html('1')
						: $('#chartPageNotFoundStatisticsTickInterval').html('');

					// init to re-bind all event listeners
					jsBackend.analytics.chartPageNotFoundStatistics.init();

					// make sure the datagrid refreshes
					jsBackend.analytics.pageNotFoundStatistics.toggleDays(json.data.data[0].data[i].date * 1000);
				}
			}
		});
	},

	toggleDays: function(timestamp)
	{
		// calculate the timestamp if it's undefined
		if(timestamp === undefined)
		{
			// extract the date from the tooltip
			var tooltipText = $('#chartPageNotFoundStatistics .highcharts-tooltip').text();
			var dateString = tooltipText.replace('Pageviews', '').split(':')[0];

			// append the year and get the unix timestamp
			var year = new Date().getFullYear(); // we can't use Date.Now() cause of IE8
			var date = new Date(dateString + ' ' + year);
			var timestamp = Math.round(date.getTime() / 1000) + 46800; // add 46800 to equal google dates
		}

		// make sure dateString isn't undefined when a timestamp is given
		else
		{
			var dateString = new Date(timestamp).format("ddd d mmm");
		}

		// check if we even need to refresh
		if($('#pageNotFoundDate').text() === dateString + ' missing pages:') return;

		// collapse the datagrid
		$('#pageNotFoundIndex').slideUp('medium', function() {});

		// reset the date
		$('#pageNotFoundDate').text(dateString + ' missing pages:');

		// move the spinner !
		var ajaxSpinner = $('#ajaxSpinner');
		var style = ajaxSpinner.attr('style');
		ajaxSpinner.remove();
		ajaxSpinner.insertAfter('#pageNotFoundDate');
		ajaxSpinner.attr('style', 'position:relative; left: 8px;');

		// call to refresh the grid
		$.ajax(
		{
			data:
			{
				fork: { action: 'reload_datagrid' , module: 'analytics'},
				timestamp: timestamp
			},
			success: function(json, textStatus)
			{
				if(json.code != 200)
				{
					// show error if needed
					if(jsBackend.debug) alert(textStatus);
				}
				else
				{
					// build the new table html
					var html = '';
					var counter = 0;

					for(var url in json.data.data)
					{
						(counter % 2 == 0)
							? html += '<tr class="even"><td data-index=' + counter + '>' + json.data.data[url] + '</td></tr>'
							: html += '<tr class="odd"><td data-index=' + counter + '>' + json.data.data[url] + '</td></tr>';
						counter++;
					}

					// insert a 'no-results-message'
					if(html === '') html += '<tr class="even"><td>none...</td></tr>';

					// switch the table data
					$('#pageNotFoundIndex tbody').empty().append(html);

					// expand the datagrid
					$('#pageNotFoundIndex').slideDown('slow', function() {});

					// show details on click
					$("#pageNotFoundIndex td").not(":contains('none...')").on('click', function(e){jsBackend.analytics.pageNotFoundStatistics.toggleDetails(e);});

					// move the spinner back to it's place
					ajaxSpinner.attr('style', style);
					ajaxSpinner.insertAfter('#messaging');
				}
			}
		});
	},

	toggleDetails: function(e)
	{
		// get the row index
		var index = e.currentTarget.attributes[0].nodeValue;

		// get the row
		var row = $('#pageNotFoundIndex tr:eq(' + index + ')');

		// got details already?
		if(row.next().hasClass('detailsPane'))
		{
			// are they hidden -> show
			(row.next().css('display') === 'none')
				? row.next().slideDown()
				: row.next().slideUp('fast');

			// we're done here
			return;
		}

		// move the spinner !
		var ajaxSpinner = $('#ajaxSpinner');
		var style = ajaxSpinner.attr('style');
		ajaxSpinner.remove();
		ajaxSpinner.insertAfter('#pageNotFoundDate');
		ajaxSpinner.attr('style', 'position:relative; left: 8px;');

		// get the timestamp
		var dateString = $('#pageNotFoundDate').text().replace('missing pages:', '');

		// append the year and get the unix timestamp
		var year = new Date().getFullYear(); // we can't use Date.Now() cause of IE8
		date = new Date(dateString + ' ' + year);
		var timestamp = Math.round(date.getTime() / 1000) + 46800; // add 46800 to equal google dates

		// fetch the data
		$.ajax(
		{
			data:
			{
				fork: { action: 'fetch_details_data' , module: 'analytics'},
				index: index,
				timestamp: timestamp
			},
			success: function(json, textStatus)
			{
				if(json.code != 200)
				{
					// show error if needed
					if(jsBackend.debug) alert(textStatus);
				}
				else
				{
					// build the html
					var html = '';
					html += '<div class="detailsPane">';
					html += '<h3>Page info:</h3>';
					html += '<p><span>full-url:</span> ' + json.data.data.full_url + '</p>';
					html += '<p><span>pageviews:</span>' + json.data.data.pageviews + '</p>';
					html += '<p><span>unique:</span>' + json.data.data.unique_pageviews + '</p>';
					html += '<p><span>extension:</span> ' + json.data.data.extension + '</p>';
					html += '<h3>Browser info:</h3>';
					html += '<p>' + json.data.data.browser + ' version ' + json.data.data.browser_version + '</p>';
					html += '<h3>Extra info:</h3>';
					html += '<p><span>logged in: </span>' + json.data.data.is_logged_in + '</p>';
					html += '<p><span>caller is action: </span>' + json.data.data.caller_is_action + '</p>';
					html += '</div>';

					// insert the details
					$(html).insertAfter(row).slideDown("slow");

					// move the spinner back to it's place
					ajaxSpinner.attr('style', style);
					ajaxSpinner.insertAfter('#messaging');
				}
			}
		});
	}
}

jsBackend.analytics.loading =
{
	page: 'index',
	identifier: '',
	interval: '',

	init: function()
	{
		// variables
		$longLoader = $('#longLoader');

		if($longLoader.length > 0)
		{
			// loading bar stuff
			$longLoader.show();

			// get the page to get data for
			var page = $('#page').html();
			var identifier = $('#identifier').html();

			// save data
			jsBackend.analytics.loading.page = page;
			jsBackend.analytics.loading.identifier = identifier;

			// check status every 5 seconds
			jsBackend.analytics.loading.interval = setInterval("jsBackend.analytics.loading.checkStatus()", 5000);
		}
	},

	checkStatus: function()
	{
		// get data
		var page = jsBackend.analytics.loading.page;
		var identifier = jsBackend.analytics.loading.identifier;
		$longLoader = $('#longLoader');
		$statusError = $('#statusError');
		$loading = $('#loading');

		// make the call to check the status
		$.ajax(
		{
			timeout: 5000,
			data:
			{
				fork: { action: 'check_status' },
				page: page,
				identifier: identifier
			},
			success: function(data, textStatus)
			{
				// redirect
				if(data.data.status == 'unauthorized') { window.location = $('#settingsUrl').html(); }

				if(data.code == 200)
				{
					// get redirect url
					var url = document.location.protocol +'//'+ document.location.host;
					url += $('#redirect').html();
					if($('#redirectGet').html() != '') url += '&' + $('#redirectGet').html();

					// redirect
					if(data.data.status == 'done') window.location = url;
				}
				else
				{
					// clear interval
					clearInterval(jsBackend.analytics.loading.interval);

					// loading bar stuff
					$longLoader.show();

					// show box
					$statusError.show();
					$loading.hide();

					// show message
					jsBackend.messages.add('error', textStatus);

					// alert the user
					if(jsBackend.debug) alert(textStatus);
				}

				// alert the user
				if(data.code != 200 && jsBackend.debug) { alert(data.message); }
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				// clear interval
				clearInterval(jsBackend.analytics.loading.interval);

				// show box and hide loading bar
				$statusError.show();
				$loading.hide();
				$longLoader.hide();

				// show message
				jsBackend.messages.add('error', textStatus);

				// alert the user
				if(jsBackend.debug) alert(textStatus);
			}
		});
	}
}

jsBackend.analytics.resize =
{
	interval: 1000,
	timeout: false,

	init: function()
	{
		$(window).on('resize', function()
		{
			resizeTime = new Date();
			if(jsBackend.analytics.resize.timeout === false)
			{
				timeout = true;
				setTimeout(jsBackend.analytics.resize.resizeEnd, jsBackend.analytics.resize.interval);
			}
		});
	},

	resizeEnd: function()
	{
		if(new Date() - resizeTime < jsBackend.analytics.resize.interval)
		{
			setTimeout(jsBackend.analytics.resize.resizeEnd, jsBackend.analytics.resize.interval);
		}
		else
		{
			timeout = false;
			if($chartPieChart.length > 0)
			{
				$chartPieChart.html('&nbsp;');
				jsBackend.analytics.chartPieChart.create();
			}
			if($chartDoubleMetricPerDay.length > 0)
			{
				$chartDoubleMetricPerDay.html('&nbsp;');
				jsBackend.analytics.chartDoubleMetricPerDay.create();
			}
			if($chartSingleMetricPerDay.length > 0)
			{
				$chartSingleMetricPerDay.html('&nbsp;');
				jsBackend.analytics.chartSingleMetricPerDay.create();
			}
			if($chartWidget.length > 0)
			{
				$chartWidget.html('&nbsp;');
				jsBackend.analytics.chartWidget.create();
			}
			if($chartPageNotFoundStatistics.length > 0)
			{
				$chartPageNotFoundStatistics.html('&nbsp;');
				jsBackend.analytics.chartPageNotFoundStatistics.create();
			}
			if($chartWidgetPageNotFoundStatistics.length > 0)
			{
				$chartWidgetPageNotFoundStatistics.html('&nbsp;');
				jsBackend.analytics.chartWidgetPageNotFoundStatistics.create();
			}
		}
	}
}

$(jsBackend.analytics.init);

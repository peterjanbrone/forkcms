<?php

/*
 * This file is part of Fork CMS.
*
* For the full copyright and license information, please view the license
* file that was distributed with this source code.
*/

/**
 * @author Peter-Jan Brone <peterjan.brone@wijs.be>
 */

class BackendAnalyticsWidgetPageNotFoundStats extends BackendBaseWidget
{

	/**
	 * Execute the widget
	 */
	public function execute()
	{
		// check analytics session token and analytics table id
		if(BackendModel::getModuleSetting('analytics', 'session_token', null) == '') return;
		if(BackendModel::getModuleSetting('analytics', 'table_id', null) == '') return;

		// settings are ok, set option
		$this->tpl->assign('analyticsValidSettings', true);

		$this->setColumn('middle');
		$this->setPosition(2);

		// add css
		$this->header->addCSS('widgets.css', 'analytics');

		// add highchart javascript
		$this->header->addJS('highcharts.js', 'core', false);
		$this->header->addJS('analytics.js', 'analytics');
		$this->header->addJS('dashboard.js', 'analytics');

		$this->parse();
		$this->display();
	}

	/**
	 * Parse into template
	 */
	private function parse()
	{
		$maxYAxis = 2;
		$metrics = array('pageviews');
		$graphData = array();

		// search for the analytics cache file
		$timestamps = array(0,0);
		foreach (glob(BACKEND_CACHE_PATH . '/analytics/*.xml') as $filename)
		{
			// get timestamps
			$path_parts = pathinfo($filename);
			$result = $path_parts['filename'];
			$fileTimestamps = explode('_', $result);

			// make sure the cache file holds at least 1 week of data
			if(((int) $fileTimestamps[1] - (int) $fileTimestamps[0]) > 6 * 24 * 60 * 60)
			{
				// make sure to take the most recent file
				if((int) $fileTimestamps[1] > (int) $timestamps[1])
				{
					$timestamps = $fileTimestamps;
				}
			}
		}

		// store timestamps
		$startTimestamp = $timestamps[0];
		$endTimestamp = $timestamps[1];

		// get the data from cache
		if($startTimestamp !== 0)
		{
			$dashboardData = BackendAnalyticsModel::getPageNotFoundStatistics($startTimestamp, $endTimestamp);
		}

		// there are some metrics
		if($dashboardData !== false && !empty($dashboardData))
		{
			// make the data highchart usable
			$dashboardData = BackendAnalyticsModel::convertForHighchart($dashboardData, strtotime('-1 week -1 days', mktime(0, 0, 0)), mktime(0, 0, 0));

			// loop metrics
			foreach($metrics as $i => $metric)
			{
				// build graph data array
				$graphData[$i] = array();
				$graphData[$i]['title'] = $metric;
				$graphData[$i]['label'] = SpoonFilter::ucfirst(BL::lbl(SpoonFilter::toCamelCase($metric)));
				$graphData[$i]['i'] = $i + 1;
				$graphData[$i]['data'] = array();

				// loop metrics per day
				foreach($dashboardData as $j => $data)
				{
					// cast SimpleXMLElement to array
					$data = (array) $data;

					// build array
					$graphData[$i]['data'][$j]['date'] = (int) $data['timestamp'];
					$graphData[$i]['data'][$j]['value'] = (int) count($data[$metric]);

					// perform an extra check to determine if we counted the 'none...' row
					if($data[$metric][0]['url'] === 'none...') $graphData[$i]['data'][$j]['value'] = 0;
				}
			}

			// get the maximum Y value
			foreach($graphData as $metric)
			{
				foreach($metric['data'] as $data)
				{
					if((int) $data['value'] > $maxYAxis) $maxYAxis = (int) $data['value'];
				}
			}

			$this->tpl->assign('analyticsPageNotFoundStatisticsMaxYAxis', $maxYAxis);
			$this->tpl->assign('analyticsPageNotFoundStatisticsTickInterval', ($maxYAxis == 2 ? '1' : ''));
			$this->tpl->assign('analyticsPageNotFoundStatisticsGraphData', $graphData);
			$this->tpl->assign('analyticsPageNotFoundStatisticsDate', date("D j M", (int) $dashboardData[0]['timestamp']));
			$this->tpl->assign('pageNotFoundStatisticsDataGrid', $dashboardData[0]);
		}

		$this->tpl->assign('analyticsPageNotFoundStatisticsStartDate', $startTimestamp);
		$this->tpl->assign('analyticsPageNotFoundStatisticsEndDate', $endTimestamp);
	}
}

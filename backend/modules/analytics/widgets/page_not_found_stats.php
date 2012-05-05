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
		$metric = 'pageviews';
		$graphData = array();
		$dashboardData = array();

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
					// make sure it contains the data we want
					$start = strtotime('-1 week -1 days', mktime(0, 0, 0));
					$end = mktime(0, 0, 0);
					if(((int) $fileTimestamps[0] <= (int) $start) && ((int) $fileTimestamps[1] >= (int) $end))
					{
						$timestamps = $fileTimestamps;
					}
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

			// perform an extra check because sometimes the cached xml has the perfect dates, but doesn't contain any page not found statistics
			if(!$dashboardData) $dashboardData = BackendAnalyticsHelper::getPageNotFoundStatistics(strtotime('-1 week -1 days', mktime(0, 0, 0)), mktime(0, 0, 0));
		}
		else
		{
			$dashboardData = BackendAnalyticsHelper::getPageNotFoundStatistics(strtotime('-1 week -1 days', mktime(0, 0, 0)), mktime(0, 0, 0));
		}

		// xml found
		if($dashboardData !== false)
		{
			// if completely empty add an array with timestamp -1, convertForHighchart will pick this up and act accordingly
			if(empty($dashboardData)) $dashboardData = array(array('timestamp' => -1));

			// make the data highchart usable
			$dashboardData = BackendAnalyticsModel::convertForHighchart($dashboardData, strtotime('-1 week -1 days', mktime(0, 0, 0)), mktime(0, 0, 0));

			// init graphdata array
			$graphData[] = array(
					'title' => $metric,
					'label' => SpoonFilter::ucfirst(BL::lbl($metric)),
					'i' => 1,
					'data' => array()
			);

			$maxY = 0;
			foreach($dashboardData as $i => $data)
			{
				$pageviews = ($data[$metric][0]['url'] !== 'none...')
					? count($data[$metric])
					: 0;

				$graphData[0]['data'][] = array(
					'date' => (int) $data['timestamp'],
					'value' => (string) $pageviews
				);

				// get max Y-axis
				$maxY = max($maxY, count($data[$metric]));
			}

			$this->tpl->assign('analyticsPageNotFoundStatisticsMaxYAxis', $maxY);
			$this->tpl->assign('analyticsPageNotFoundStatisticsTickInterval', ($maxY == 2 ? '1' : ''));
			$this->tpl->assign('analyticsPageNotFoundStatisticsGraphData', $graphData);
			$this->tpl->assign('analyticsPageNotFoundStatisticsDate', SpoonDate::getDate("D j M", (int) $dashboardData[0]['timestamp'], BL::getWorkingLanguage()));
			$this->tpl->assign('pageNotFoundStatisticsDataGrid', $dashboardData[0]);
		}

		$this->tpl->assign('analyticsPageNotFoundStatisticsStartDate', strtotime('-1 week -1 days', mktime(0, 0, 0)));
		$this->tpl->assign('analyticsPageNotFoundStatisticsEndDate', mktime(0, 0, 0));
	}
}

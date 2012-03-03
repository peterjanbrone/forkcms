<?php

/*
 * This file is part of Fork CMS.
*
* For the full copyright and license information, please view the license
* file that was distributed with this source code.
*/

/**
 * This widget will show the latest traffic sources
*
* @author Peter-Jan Brone <peterjan.brone@netlash.com>
*/
class BackendAnalyticsWidgetPageNotFoundStats extends BackendBaseWidget
{
	/**
	 * Holds all the 404 page stats
	 *
	 * @var	array
	 */
	private $stats = array();

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
		$this->setPosition(3);

		// add css
		$this->header->addCSS('widgets.css', 'analytics');

		// add highchart javascript
		$this->header->addJS('highcharts.js', 'core', false);
		$this->header->addJS('analytics.js', 'analytics');
		$this->header->addJS('dashboard.js', 'analytics');

		$this->parse();
		//$this->getData();
		$this->display();
	}

	/**
	 * Parse into template
	 */
	private function getData()
	{
		$URL = SITE_URL . '/backend/cronjob.php?module=analytics&action=get_error_page_statistics&id=3';

		// set options
		$options = array();
		$options[CURLOPT_URL] = $URL;
		if(ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) $options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = 1;

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		curl_exec($curl);
		curl_close($curl);
	}

	/**
	 * Parse into template
	 */
	private function parse()
	{
		$maxYAxis = 2;
		$metrics = array('pageviews');
		$graphData = array();
		$startTimestamp = strtotime('-1 week -1 days', mktime(0, 0, 0));
		$endTimestamp = mktime(0, 0, 0);

		// get dashboard data
		$dashboardData = BackendAnalyticsModel::getDashboardPageNotFoundData($startTimestamp, $endTimestamp);

		// there are some metrics
		if($dashboardData !== false)
		{
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
					$graphData[$i]['data'][$j]['value'] = (string) $data[$metric];
				}
			}
		}

		foreach($graphData as $metric)
		{
			foreach($metric['data'] as $data)
			{
				// get the maximum value
				if((int) $data['value'] > $maxYAxis) $maxYAxis = (int) $data['value'];
			}
		}

		$this->tpl->assign('analyticsPageNotFoundStatsStartDate', $startTimestamp);
		$this->tpl->assign('analyticsPageNotFoundStatsEndDate', $endTimestamp);
		$this->tpl->assign('analyticsPageNotFoundStatsMaxYAxis', $maxYAxis);
		$this->tpl->assign('analyticsPageNotFoundStatsTickInterval', ($maxYAxis == 2 ? '1' : ''));
		$this->tpl->assign('analyticsPageNotFoundStatsGraphData', $graphData);
	}
}

<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This edit action will filter the page not found statistics
 *
 * @author Peter-Jan Brone <peterjan.brone@wijs.be>
 */
class BackendAnalyticsAjaxFilterStatistics extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// get the data
		$startTimestamp = strtotime('-1 week -1 days', mktime(0, 0, 0));
		$endTimestamp = mktime(0, 0, 0);
		$dashboardData = BackendAnalyticsModel::getDashboardData(array('pages'), $startTimestamp, $endTimestamp, true);

		// make it highchart usable
		$dashboardData = BackendAnalyticsModel::convertForHighchart($dashboardData);

		// apply the filter
		$dashboardData = $this->filter($dashboardData);

		// loop metrics
		$metrics = array('pageviews');
		$graphData = array();
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
				$graphData[$i]['data'][$j]['value'] = (int) sizeof($data[$metric]);

				// perform an extra check to determine if we counted the 'none...' row
				foreach($data[$metric] as $pageview)
				{
					if($pageview['url'] === 'none...')
					{
						$graphData[$i]['data'][$j]['value'] = 0;
					}
				}
			}
		}


		// return status
		$this->output(
				self::OK,
				array(
						'status' => 'success',
						'data' => $graphData
				),
				'Data has been retrieved.'
		);
	}

	/**
	 * This function filters all page statistics
	 *
	 * @param array $data
	 * @return array
	 */
	public function filter($data)
	{
		// get parameters
		$callerIsAction = trim(SpoonFilter::getPostValue('callerIsAction', null, '', 'string'));
		$extension = trim(SpoonFilter::getPostValue('extension', null, '', 'string'));
		$browser = trim(SpoonFilter::getPostValue('browser', null, '', 'string'));
		$browserVersion = trim(SpoonFilter::getPostValue('browserVersion', null, '', 'string'));
		$isLoggedIn = trim(SpoonFilter::getPostValue('isLoggedIn', null, '', 'string'));

		// validate
		if($callerIsAction === '') $this->output(self::BAD_REQUEST, null, BL::err('No caller is action filter provided.'));
		if($extension === '') $this->output(self::BAD_REQUEST, null, BL::err('No extension filter provided.'));
		if($browser === '') $this->output(self::BAD_REQUEST, null, BL::err('No browser filter provided.'));
		if($browserVersion === '') $this->output(self::BAD_REQUEST, null, BL::err('No browser version provided.'));
		if($isLoggedIn === '') $this->output(self::BAD_REQUEST, null, BL::err('No is logged in filter provided.'));

		foreach($data as &$dataItem)
		{
			foreach($dataItem['pages_info'] as $i => $pageInfo)
			{
				// user logged in?
				if($isLoggedIn !== 'false')
				{
					if($pageInfo['is_logged_in'] !== 'yes')
					{
						unset($dataItem['pages_info'][$i]);
						unset($dataItem['pageviews'][$i]);
						continue;
					}
				}

				// caller is action?
				if($callerIsAction !== 'false')
				{
					if($pageInfo['caller_is_action'] !== 'yes')
					{
						unset($dataItem['pages_info'][$i]);
						unset($dataItem['pageviews'][$i]);
						continue;
					}
				}

				// extension?
				if($extension !== '-')
				{
					if($pageInfo['extension'] !== $extension)
					{
						unset($dataItem['pages_info'][$i]);
						unset($dataItem['pageviews'][$i]);
						continue;
					}
				}

				// browser?
				if($browser !== '-')
				{
					if($pageInfo['browser'] !== $browser)
					{
						unset($dataItem['pages_info'][$i]);
						unset($dataItem['pageviews'][$i]);
						continue;
					}
				}

				// browser version?
				if($browserVersion !== '-')
				{
					if($pageInfo['browser_version'] !== $browserVersion)
					{
						unset($dataItem['pages_info'][$i]);
						unset($dataItem['pageviews'][$i]);
						continue;
					}
				}
			}

			// make sure '...none' is re-applied
			if(sizeof($dataItem['pageviews']) < 1)
			{
				$dataItem['pageviews'] = array(array('index' => 0, 'url' => 'none...'));
			}
		}

		return $data;
	}
}

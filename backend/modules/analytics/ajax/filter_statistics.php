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

		$result = array();

		// get the data
		$startTimestamp = strtotime('-1 week -1 days', mktime(0, 0, 0));
		$endTimestamp = mktime(0, 0, 0);
		$dashboardData = BackendAnalyticsModel::getDashboardData(array('pages'), $startTimestamp, $endTimestamp, true);

		// make it highchart usable & filter
		$dashboardData = BackendAnalyticsModel::convertForHighchart($dashboardData);
		$dashboardData = $this->filter($dashboardData);

		// get parameters
		$date = trim(SpoonFilter::getPostValue('date', null, '', 'string'));
		$index = trim(SpoonFilter::getPostValue('index', null, '', 'string'));

		// if no date was given we want graph data
		if($date === '')
		{
			// build graph data array
			$result[0] = array();
			$result[0]['title'] = 'pageviews';
			$result[0]['label'] = SpoonFilter::ucfirst(BL::lbl(SpoonFilter::toCamelCase('pageviews')));
			$result[0]['i'] = 1;
			$result[0]['data'] = array();

			// loop metrics per day
			foreach($dashboardData as $i => $data)
			{
				// cast SimpleXMLElement to array
				$data = (array) $data;

				// build array
				$result[0]['data'][$i]['date'] = (int) $data['timestamp'];
				$result[0]['data'][$i]['value'] = (int) sizeof($data['pageviews']);

				// perform an extra check to determine if we counted the 'none...' row
				foreach($data['pageviews'] as $pageview)
				{
					if($pageview['url'] === 'none...')
					{
						$result[0]['data'][$i]['value'] = 0;
					}
				}
			}
		}

		// if we have a date we want datagrid data
		else
		{

			$timestamp = strtotime($date . '+ 13 hours'); // add 13h to match google's dates

			foreach($dashboardData as $dataItem)
			{
				if((int) $dataItem['timestamp'] === (int) $timestamp)
				{
					// if there's an index we need datagrid details
					if($index !== '')
					{
						$result = $dataItem['pages_info'][$index];
					}

					// if not we only need url's
					else
					{
						foreach($dataItem['pageviews'] as $page)
						{
							array_push($result, $page['url']);
						}
					}
				}
			}
		}

		// return status
		$this->output(
				self::OK,
				array(
						'status' => 'success',
						'data' => $result
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

		// dont filter if the extension is empty, this means the call originated from the dashboard
		if($extension === '') return $data;

		foreach($data as &$dataItem)
		{
			// filter
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

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

		// get variables
		$date = trim(SpoonFilter::getPostValue('date', null, '', 'string'));
		$index = trim(SpoonFilter::getPostValue('index', null, '', 'string'));
		$startTimestamp = trim(SpoonFilter::getPostValue('startTimestamp', null, '', 'int'));
		$endTimestamp = trim(SpoonFilter::getPostValue('endTimestamp', null, '', 'int'));

		// fetch statistics, make it highchart usable and filter
		$stats = BackendAnalyticsModel::getPageNotFoundStatistics($startTimestamp, $endTimestamp);
		$stats = BackendAnalyticsModel::convertForHighchart($stats, $startTimestamp, $endTimestamp);
		$stats = $this->filter($stats);

		// no date -> return graphdata
		if($date === '')
		{
			// prepare result array
			$result[0] = array();
			$result[0]['title'] = 'pageviews';
			$result[0]['label'] = SpoonFilter::ucfirst(BL::lbl(SpoonFilter::toCamelCase('pageviews')));
			$result[0]['i'] = 1;
			$result[0]['data'] = array();

			foreach($stats as $i => $stat)
			{
				$result[0]['data'][$i]['date'] = (int) $stat['timestamp'];
				$result[0]['data'][$i]['value'] = (int) sizeof($stat['pageviews']);

				// collect all urls, if 'none...' is amongst them, set #pageviews to 0
				$urls = array_map(function($page){ return $page['url']; }, $stat['pageviews']);
				if(in_array('none...', $urls)) $result[0]['data'][$i]['value'] = 0;
			}
		}

		// we have a date -> return datagrid data
		else
		{
			foreach($stats as $stat)
			{
				if((int) $stat['timestamp'] === (int) strtotime($date))
				{
					// index ? details : urls
					$result = ($index !== '')
						? $stat['pages_info'][$index]
						: array_map(function($page){ return $page['url']; }, $stat['pageviews']);
				}
			}
		}

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
		$version = trim(SpoonFilter::getPostValue('version', null, '', 'string'));
		$isLoggedIn = trim(SpoonFilter::getPostValue('isLoggedIn', null, '', 'string'));

		// dont filter if the extension is empty, this means the call originated from the dashboard
		if($extension === '') return $data;

		foreach($data as &$dataItem)
		{
			foreach($dataItem['pages_info'] as $i => $pageInfo)
			{
				// user logged in?
				if($isLoggedIn !== 'false' && $pageInfo['is_logged_in'] !== 'yes')
				{
					unset($dataItem['pages_info'][$i]);
					unset($dataItem['pageviews'][$i]);
					continue;
				}

				// caller is action?
				if($callerIsAction !== 'false' && $pageInfo['caller_is_action'] !== 'yes')
				{
					unset($dataItem['pages_info'][$i]);
					unset($dataItem['pageviews'][$i]);
					continue;
				}

				// extension?
				if($extension !== '-' && $pageInfo['extension'] !== $extension)
				{
					unset($dataItem['pages_info'][$i]);
					unset($dataItem['pageviews'][$i]);
					continue;
				}

				// browser?
				if($browser !== '-' && $pageInfo['browser'] !== $browser)
				{
					unset($dataItem['pages_info'][$i]);
					unset($dataItem['pageviews'][$i]);
					continue;
				}

				// browser version?
				if($version !== '-' && $pageInfo['browser_version'] !== $version)
				{
					unset($dataItem['pages_info'][$i]);
					unset($dataItem['pageviews'][$i]);
					continue;
				}
			}

			// make sure '...none' is re-applied
			if(sizeof($dataItem['pageviews']) < 1)
			{
				$dataItem['pageviews'] = array(array('index' => 0, 'url' => 'none...'));
			}

			// make sure to re-index!
			$dataItem['pageviews'] = array_values($dataItem['pageviews']);
			$dataItem['pages_info'] = array_values($dataItem['pages_info']);
		}

		return $data;
	}
}

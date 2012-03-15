<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This Ajax action will filter the page not found statistics
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

		// get parameters
		$timestamp = trim(SpoonFilter::getPostValue('timestamp', null, '', 'string'));
		$callerIsAction = trim(SpoonFilter::getPostValue('callerIsAction', null, '', 'string'));
		$extension = trim(SpoonFilter::getPostValue('extension', null, '', 'string'));
		$browser = trim(SpoonFilter::getPostValue('browser', null, '', 'string'));
		$browserVersion = trim(SpoonFilter::getPostValue('browserVersion', null, '', 'string'));
		$isLoggedIn = trim(SpoonFilter::getPostValue('isLoggedIn', null, '', 'string'));

		// get the data
		$startTimestamp = strtotime('-1 week -1 days', mktime(0, 0, 0));
		$endTimestamp = mktime(0, 0, 0);
		$data = BackendAnalyticsModel::getDashboardData(array('pages'), $startTimestamp, $endTimestamp, true);

		// make highchart usable
		$data = BackendAnalyticsModel::convertForHighchart($data);

		// start filtering
		$result = array();
		foreach($data as $dataItem)
		{
			// filter out the correct day
			if((int)$dataItem['timestamp'] === (int)$timestamp)
			{
				$result = $dataItem;

				// now start applying all the other filters
				foreach($result['pages_info'] as $i => $pageInfo)
				{
					// caller is action?
					if($callerIsAction === 'checked')
					{
						if($pageInfo['caller_is_action'] !== 'yes') {
							unset($result[$i]);
							continue;
						}
					}

					// user logged in?
					if($isLoggedIn === 'checked')
					{
						if($pageInfo['is_logged_in'] !== 'yes') {
							unset($result[$i]);
							continue;
						}
					}

					// extension?
					if($extension !== '-')
					{
						if($pageInfo['extension'] !== $extension) {
							unset($result[$i]);
							continue;
						}
					}

					// browser?
					if($browser !== '-')
					{
						if($pageInfo['browser'] !== $browser) {
							unset($result[$i]);
							continue;
						}
					}

					// browser version?
					if($browserVersion !== '-')
					{
						if($pageInfo['browser_version'] !== $browser) {
							unset($result[$i]);
							continue;
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
}

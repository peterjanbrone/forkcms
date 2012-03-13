<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This edit-action will check the status using Ajax
 *
 * @author Annelies Van Extergem <annelies.vanextergem@netlash.com>
 */
class BackendAnalyticsAjaxFetchDetailsData extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// get parameters
		$index = trim(SpoonFilter::getPostValue('index', null, '', 'int'));
		$timestamp = trim(SpoonFilter::getPostValue('timestamp', null, '', 'string'));

		// validate
		if($index === '') $this->output(self::BAD_REQUEST, null, BL::err('SomethingWentWrong'));
		if($timestamp === '') $this->output(self::BAD_REQUEST, null, BL::err('SomethingWentWrong'));

		// get the data
		$startTimestamp = strtotime('-1 week -1 days', mktime(0, 0, 0));
		$endTimestamp = mktime(0, 0, 0);
		$data = BackendAnalyticsModel::getDashboardData(array('pages'), $startTimestamp, $endTimestamp, true);

		// filter the data
		$data = BackendAnalyticsModel::filterData($data);

		// get the correct day
		$result = array();
		foreach($data as $dataItem)
		{
			if((int)$dataItem['timestamp'] === (int)$timestamp)
			{
				$result = $dataItem['pages_info'][$index];
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

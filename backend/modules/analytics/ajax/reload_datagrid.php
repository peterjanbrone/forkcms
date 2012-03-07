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
class BackendAnalyticsAjaxReloadDatagrid extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// get parameters
		$timestamp = trim(SpoonFilter::getPostValue('timestamp', null, '', 'string'));

		// validate
		if($timestamp === '') $this->output(self::BAD_REQUEST, null, BL::err('SomethingWentWrong'));

		// get the data
		$startTimestamp = strtotime('-1 week -1 days', mktime(0, 0, 0));
		$endTimestamp = mktime(0, 0, 0);
		$data = BackendAnalyticsModel::getDashboardPageNotFoundData($startTimestamp, $endTimestamp);

		// filter the data
		$result = array();
		foreach($data as $dataItem)
		{
			if($dataItem['timestamp'] === $timestamp)
			{
				foreach($dataItem['page'] as $page)
					array_push($result, $page['url']);
			}
		}

		// return status
		$this->output(
				self::OK,
				array(
						'date' => date("D j F", $timestamp) . ' missing pages:',
						'status' => 'success',
						'data' => $result
				),
				'Data has been retrieved.'
		);

	}
}

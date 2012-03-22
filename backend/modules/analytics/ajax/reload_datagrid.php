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
 * @author Peter-Jan Brone <peterjan.brone@wijs.be>
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
		if($timestamp === '') $this->output(self::BAD_REQUEST, null, BL::err('No timestamp provided.'));

		// get the data
		$startTimestamp = strtotime('-1 week -1 days', mktime(0, 0, 0));
		$endTimestamp = mktime(0, 0, 0);
		$data = BackendAnalyticsModel::getDashboardData(array('pageviews'), $startTimestamp, $endTimestamp, true);

		// filter the data
		$data = BackendAnalyticsModel::convertForHighchart($data);

		// extract all url's
		$result = array();
		foreach($data as $dataItem)
		{
			if((int) $dataItem['timestamp'] === (int) $timestamp)
			{
				foreach($dataItem['pageviews'] as $page)
				{
					array_push($result, $page['url']);
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

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
class BackendAnalyticsWidget404Statistics extends BackendBaseWidget
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
		$this->header->addJS('dashboard.js', 'analytics');
		$this->parse();
		$this->getData();
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
		// check if this action is allowed
		if(BackendAuthentication::isAllowedAction('settings', 'analytics'))
		{
			// parse redirect link
			$this->tpl->assign('settingsUrl', BackendModel::createURLForAction('settings', 'analytics'));
		}

		// get the stats from the db
		$this->stats = BackendAnalyticsModel::getErrorPageStatistics();

		// parse it
		$this->parseErrorPageStatistics();
		$this->parseVisitorInfo();
	}

	/**
	 * Parse the error page statistics datagrid
	 */
	private function parseErrorPageStatistics()
	{
		$results = $this->stats['pageStats'];

		if(!empty($results))
		{
			$dataGrid = new BackendDataGridArray($results);
			$dataGrid->setPaging(false);
			$dataGrid->setColumnsHidden('id', 'date');

			// parse the datagrid
			$this->tpl->assign('dgAnalyticsErrorPageStatistics', $dataGrid->getContent());
		}

		// get date
		$date = (isset($results[0]['date']) ? substr($results[0]['date'], 0, 10) : date('Y-m-d'));
		$timestamp = mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));

		// assign date label
		$this->tpl->assign('errorPageStatisticsDate', ($date != date('Y-m-d') ? BackendModel::getUTCDate('d-m', $timestamp) : BL::lbl('Today')));
	}

	/**
	 * Parse the visitor info datagrid
	 */
	private function parseVisitorInfo()
	{
		$results = $this->stats['visitorInfo'];

		if(!empty($results))
		{
			$dataGrid = new BackendDataGridArray($results);
			$dataGrid->setPaging(false);
			$dataGrid->setColumnsHidden('id', 'page', 'extension', 'date');

			// parse the datagrid
			$this->tpl->assign('dgAnalyticsVisitorInfo', $dataGrid->getContent());
		}
	}
}

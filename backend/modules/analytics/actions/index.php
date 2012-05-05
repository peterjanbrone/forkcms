<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action (default), it will display the overview of analytics posts
 *
 * @author Annelies Van Extergem <annelies.vanextergem@netlash.com>
 * @author Dieter Vanden Eynde <dieter.vandeneynde@netlash.com>
 * @author Peter-Jan Brone <peterjan.brone@wijs.be>
 */
class BackendAnalyticsIndex extends BackendAnalyticsBase
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->parse();
		$this->display();
	}

	/**
	 * Parse this page
	 */
	protected function parse()
	{
		parent::parse();

		$warnings = BackendAnalyticsModel::checkSettings();
		$this->tpl->assign('warnings', $warnings);

		if(empty($warnings))
		{
			$this->parseOverviewData();
			$this->parseLineChartData();
			$this->parsePieChartData();
			$this->parsePageNotFoundStatistics();
			$this->parseImportantReferrals();
			$this->parseImportantKeywords();

			$googleURL = BackendAnalyticsModel::GOOGLE_ANALYTICS_URL . '/%1$s?id=%2$s&amp;pdr=%3$s';
			$googleTableId = str_replace('ga:', '', BackendAnalyticsModel::getTableId());
			$googleDate = date('Ymd', $this->startTimestamp) . '-' . date('Ymd', $this->endTimestamp);

			$this->tpl->assign('googleTopReferrersURL', sprintf($googleURL, 'referring_sources', $googleTableId, $googleDate));
			$this->tpl->assign('googleTopKeywordsURL', sprintf($googleURL, 'keywords', $googleTableId, $googleDate));
			$this->tpl->assign('googleTopContentURL', sprintf($googleURL, 'top_content', $googleTableId, $googleDate));
			$this->tpl->assign('googleTrafficSourcesURL', sprintf($googleURL, 'sources', $googleTableId, $googleDate));
			$this->tpl->assign('googleVisitorsURL', sprintf($googleURL, 'visitors', $googleTableId, $googleDate));
			$this->tpl->assign('googlePageviewsURL', sprintf($googleURL, 'pageviews', $googleTableId, $googleDate));
			$this->tpl->assign('googleTimeOnSiteURL', sprintf($googleURL, 'time_on_site', $googleTableId, $googleDate));
			$this->tpl->assign('googleVisitorTypesURL', sprintf($googleURL, 'visitor_types', $googleTableId, $googleDate));
			$this->tpl->assign('googleBouncesURL', sprintf($googleURL, 'bounce_rate', $googleTableId, $googleDate));
			$this->tpl->assign('googleAveragePageviewsURL', sprintf($googleURL, 'average_pageviews', $googleTableId, $googleDate));
		}
	}

	/**
	 * Parses the most important keywords
	 */
	private function parseImportantKeywords()
	{
		$results = BackendAnalyticsModel::getTopKeywords($this->startTimestamp, $this->endTimestamp, 25);
		if(!empty($results))
		{
			$dataGrid = new BackendDataGridArray($results);

			// set headers
			$dataGrid->setHeaderLabels(
				array(
					'pageviews' => SpoonFilter::ucfirst(BL::lbl('Views')),
					'pageviews_percentage' => '% ' . SpoonFilter::ucfirst(BL::lbl('Views'))
				)
			);

			// parse the datagrid
			$this->tpl->assign('dgKeywords', $dataGrid->getContent());
		}
	}

	/**
	 * Parses the most important referrals
	 */
	private function parseImportantReferrals()
	{
		$results = BackendAnalyticsModel::getTopReferrals($this->startTimestamp, $this->endTimestamp, 25);
		if(!empty($results))
		{
			$dataGrid = new BackendDataGridArray($results);
			$dataGrid->setColumnsHidden(array('referral_long'));
			$dataGrid->setColumnURL('referral', 'http://[referral_long]', '[referral_long]');

			// set headers
			$dataGrid->setHeaderLabels(
				array(
					'pageviews' => SpoonFilter::ucfirst(BL::lbl('Views')),
					'pageviews_percentage' => '% ' . SpoonFilter::ucfirst(BL::lbl('Views'))
				)
			);

			// parse the datagrid
			$this->tpl->assign('dgReferrers', $dataGrid->getContent());
		}
	}

	/**
	 * Parses the data to make the line-chart
	 */
	private function parseLineChartData()
	{
		$maxYAxis = 2;
		$metrics = array('visitors', 'pageviews');
		$graphData = array();

		$metricsPerDay = BackendAnalyticsModel::getMetricsPerDay($metrics, $this->startTimestamp, $this->endTimestamp);

		foreach($metrics as $i => $metric)
		{
			// build graph data array
			$graphData[$i] = array();
			$graphData[$i]['title'] = $metric;
			$graphData[$i]['label'] = SpoonFilter::ucfirst(BL::lbl(SpoonFilter::toCamelCase($metric)));
			$graphData[$i]['i'] = $i + 1;
			$graphData[$i]['data'] = array();

			foreach($metricsPerDay as $j => $data)
			{
				// cast SimpleXMLElement to array
				$data = (array) $data;

				// build array
				$graphData[$i]['data'][$j]['date'] = (int) $data['timestamp'];
				$graphData[$i]['data'][$j]['value'] = (string) $data[$metric];
			}
		}

		foreach($graphData as $metric)
		{
			// loop the data
			foreach($metric['data'] as $data)
			{
				// get the maximum value
				if((int) $data['value'] > $maxYAxis) $maxYAxis = (int) $data['value'];
			}
		}

		$this->tpl->assign('maxYAxis', $maxYAxis);
		$this->tpl->assign('tickInterval', ($maxYAxis == 2 ? '1' : ''));
		$this->tpl->assign('graphData', $graphData);
	}

	/**
	 * Parses the overview data
	 */
	private function parseOverviewData()
	{
		// get aggregates
		$results = BackendAnalyticsModel::getAggregates($this->startTimestamp, $this->endTimestamp);
		$resultsTotal = BackendAnalyticsModel::getAggregatesTotal($this->startTimestamp, $this->endTimestamp);

		// are there some values?
		$dataAvailable = false;
		foreach($resultsTotal as $data) if($data != 0) $dataAvailable = true;

		// show message if there is no data
		$this->tpl->assign('dataAvailable', $dataAvailable);

		if(!empty($results))
		{
			// time on site values
			$timeOnSite = ($results['entrances'] == 0) ? 0 : ($results['timeOnSite'] / $results['entrances']);
			$timeOnSiteTotal = ($resultsTotal['entrances'] == 0) ? 0 : ($resultsTotal['timeOnSite'] / $resultsTotal['entrances']);
			$timeOnSiteDifference = ($timeOnSiteTotal == 0) ? 0 : number_format((($timeOnSite - $timeOnSiteTotal) / $timeOnSiteTotal) * 100, 0);
			if($timeOnSiteDifference > 0) $timeOnSiteDifference = '+' . $timeOnSiteDifference;

			// pages / visit
			$pagesPerVisit = ($results['visits'] == 0) ? 0 : number_format(($results['pageviews'] / $results['visits']), 2);
			$pagesPerVisitTotal = ($resultsTotal['visits'] == 0) ? 0 : number_format(($resultsTotal['pageviews'] / $resultsTotal['visits']), 2);
			$pagesPerVisitDifference = ($pagesPerVisitTotal == 0) ? 0 : number_format((($pagesPerVisit - $pagesPerVisitTotal) / $pagesPerVisitTotal) * 100, 0);
			if($pagesPerVisitDifference > 0) $pagesPerVisitDifference = '+' . $pagesPerVisitDifference;

			// new visits
			$newVisits = ($results['entrances'] == 0) ? 0 : number_format(($results['newVisits'] / $results['entrances']) * 100, 0);
			$newVisitsTotal = ($resultsTotal['entrances'] == 0) ? 0 : number_format(($resultsTotal['newVisits'] / $resultsTotal['entrances']) * 100, 0);
			$newVisitsDifference = ($newVisitsTotal == 0) ? 0 : number_format((($newVisits - $newVisitsTotal) / $newVisitsTotal) * 100, 0);
			if($newVisitsDifference > 0) $newVisitsDifference = '+' . $newVisitsDifference;

			// bounces
			$bounces = ($results['entrances'] == 0) ? 0 : number_format(($results['bounces'] / $results['entrances']) * 100, 0);
			$bouncesTotal = ($resultsTotal['entrances'] == 0) ? 0 : number_format(($resultsTotal['bounces'] / $resultsTotal['entrances']) * 100, 0);
			$bouncesDifference = ($bouncesTotal == 0) ? 0 : number_format((($bounces - $bouncesTotal) / $bouncesTotal) * 100, 0);
			if($bouncesDifference > 0) $bouncesDifference = '+' . $bouncesDifference;

			$this->tpl->assign('pageviews', $results['pageviews']);
			$this->tpl->assign('visitors', $results['visitors']);
			$this->tpl->assign('pageviews', $results['pageviews']);
			$this->tpl->assign('pageviewsTotal', $resultsTotal['pageviews']);
			$this->tpl->assign('pagesPerVisit', $pagesPerVisit);
			$this->tpl->assign('pagesPerVisitTotal', $pagesPerVisitTotal);
			$this->tpl->assign('pagesPerVisitDifference', $pagesPerVisitDifference);
			$this->tpl->assign('timeOnSite', BackendAnalyticsModel::getTimeFromSeconds($timeOnSite));
			$this->tpl->assign('timeOnSiteTotal', BackendAnalyticsModel::getTimeFromSeconds($timeOnSiteTotal));
			$this->tpl->assign('timeOnSiteDifference', $timeOnSiteDifference);
			$this->tpl->assign('newVisits', $newVisits);
			$this->tpl->assign('newVisitsTotal', $newVisitsTotal);
			$this->tpl->assign('newVisitsDifference', $newVisitsDifference);
			$this->tpl->assign('bounces', $bounces);
			$this->tpl->assign('bouncesTotal', $bouncesTotal);
			$this->tpl->assign('bouncesDifference', $bouncesDifference);
		}
	}

	/**
	 * Parses the page not found statistics data
	 */
	private function parsePageNotFoundStatistics()
	{
		$statistics = BackendAnalyticsModel::getPageNotFoundStatistics($this->startTimestamp, $this->endTimestamp);

		// make sure statistics is never false or empty, else the highchart won't be rendered
		// assign a timestamp value of -1 so convertForHighchart knows what's going on
		if($statistics === false || empty($statistics)) $statistics = array(array('timestamp' => -1));

		// make the data highchart usable
		$statistics = BackendAnalyticsModel::convertForHighchart($statistics, $this->startTimestamp, $this->endTimestamp);

		// init graphdata array
		$graphData[] = array(
			'title' => 'pageviews',
			'label' => SpoonFilter::ucfirst(BL::lbl('pageviews')),
			'i' => 1,
			'data' => array()
		);

		// loop all statistics and filter out wanted information
		$maxY = 0;
		$browsers = array();
		$ddmExtensions = array();
		foreach($statistics as $i => $stat)
		{
			$pageviews = ($stat['pageviews'][0]['url'] !== 'none...')
				? count($stat['pageviews'])
				: 0;

			$graphData[0]['data'][] = array(
				'date' => (int) $stat['timestamp'],
				'value' => (string) $pageviews
			);

			$maxY = max($maxY, count($stat['pageviews']));

			// collect filter data
			foreach($stat['pages_info'] as $page)
			{
				$browser = $page['browser'];
				if(!in_array($browser, $browsers)) $browsers[$browser] = array('name' => $browser, 'versions' => array());

				$version = $page['browser_version'];
				if(!in_array($version, $browsers[$browser]['versions'])) $browsers[$browser]['versions'][] = $browser . '||' . $version;

				$extension = $page['extension'];
				if(!in_array($extension, $ddmExtensions)) $ddmExtensions[$extension] = array('name' => $extension);
			}
		}

		// make browsers and versions suited for a dropdown
		$ddmBrowsers = array();
		$ddmVersions = array();
		foreach($browsers as $browser)
		{
			$ddmBrowsers[] = array('name' => $browser['name']);
			foreach($browser['versions'] as $version) $ddmVersions[] = array('version' => $version);
		}
		array_unshift($ddmExtensions, array('name' => '-'));
		array_unshift($ddmBrowsers, array('name' => '-'));
		array_unshift($ddmVersions, array('version' => '-'));

		$this->tpl->assign('chartPageNotFoundStatisticsMaxYAxis', $maxY);
		$this->tpl->assign('chartPageNotFoundStatisticsTickInterval', ($maxY == 2 ? '1' : ''));
		$this->tpl->assign('pageNotFoundStatisticsGraphData', $graphData);
		$this->tpl->assign('pageNotFoundStatisticsDate', date("D j M", (int) $statistics[0]['timestamp']));
		$this->tpl->assign('pageNotFoundStatisticsDataGrid', $statistics[0]);
		$this->tpl->assign('ddmBrowsers', $ddmBrowsers);
		$this->tpl->assign('ddmVersions', $ddmVersions);
		$this->tpl->assign('ddmExtensions', $ddmExtensions);
		$this->tpl->assign('chartPageNotFoundStatisticsStartDate', $this->startTimestamp);
		$this->tpl->assign('chartPageNotFoundStatisticsEndDate', $this->endTimestamp);
	}

	/**
	 * Parses the data to make the pie-chart
	 */
	private function parsePieChartData()
	{
		$graphData = array();
		$sources = BackendAnalyticsModel::getTrafficSourcesGrouped($this->startTimestamp, $this->endTimestamp);

		foreach($sources as $i => $source)
		{
			// get label
			$label = BL::lbl(SpoonFilter::toCamelCase($source['label']), 'analytics');
			if($label == '{$lblAnalytics' . SpoonFilter::toCamelCase($source['label']) . '}') $label = $source['label'];

			// build array
			$graphData[$i]['label'] = SpoonFilter::ucfirst($label);
			$graphData[$i]['value'] = (string) $source['value'];
			$graphData[$i]['percentage'] = (string) $source['percentage'];
		}

		$this->tpl->assign('pieGraphData', $graphData);
	}
}

{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblAnalytics|ucfirst}</h2>
</div>

{option:warnings}
	<div class="generalMessage infoMessage content">
		<p><strong>{$msgConfigurationError}</strong></p>
		<ul class="pb0">
			{iteration:warnings}
				<li>{$warnings.message}</li>
			{/iteration:warnings}
		</ul>
	</div>
{/option:warnings}

{option:!warnings}
	{option:!dataAvailable}
		<div class="generalMessage infoMessage content singleMessage">
			<p><strong>{$msgNoData}</strong></p>
		</div>
	{/option:!dataAvailable}

	<div class="box">
		{include:{$BACKEND_MODULE_PATH}/layout/templates/period.tpl}

		<div class="options content">
			<div class="analyticsColWrapper clearfix">
				<div class="analyticsCol">
					<p><strong>{$pageviews} </strong><a href="{$googlePageviewsURL}">{$lblPageviews|ucfirst}</a></p>
					<p><strong>{$visitors} </strong><a href="{$googleVisitorsURL}">{$lblVisitors|ucfirst}</a></p>
				</div>
				<div class="analyticsCol">
					<p><strong>{$pagesPerVisit} </strong><a href="{$googleAveragePageviewsURL}">{$lblPagesPerVisit|ucfirst}</a> <small>({$pagesPerVisitDifference}%)</small></p>
					<p><strong>{$timeOnSite} </strong><a href="{$googleTimeOnSiteURL}">{$lblAverageTimeOnSite|ucfirst}</a> <small>({$timeOnSiteDifference}%)</small></p>
				</div>
				<div class="analyticsCol">
					<p><strong>{$newVisits}% </strong><a href="{$googleVisitorTypesURL}">{$lblNewVisitsPercentage|ucfirst}</a> <small>({$newVisitsDifference}%)</small></p>
					<p><strong>{$bounces}% </strong><a href="{$googleBouncesURL}">{$lblBounceRate|ucfirst}</a> <small>({$bouncesDifference}%)</small></p>
				</div>
			</div>
		</div>

		<div class="options content">
			<div class="analyticsGraphWrapper">
				<div class="analyticsLeftCol">
					<div class="box boxLevel2">
						<div class="heading">
							<h3><a href="{$googleVisitorsURL}">{$lblRecentVisits|ucfirst}</a></h3>
							<div class="buttonHolderRight">
								<a class="button icon iconGoto linkButton" href="{$googleVisitorsURL}"><span>{$lblViewReport|ucfirst}</span></a>
							</div>
						</div>
						<div class="options">
							{option:graphData}
								<div id="dataChartDoubleMetricPerDay" class="hidden">
									<span id="maxYAxis">{$maxYAxis}</span>
									<span id="tickInterval">{$tickInterval}</span>
									<span id="yAxisTitle">{$lblVisits|ucfirst}</span>
									<ul class="series">
										{iteration:graphData}
											<li class="serie" id="metric{$graphData.i}serie">
												<span class="name">{$graphData.label}</span>
												<ul class="data">
													{iteration:graphData.data}
														<li>
															<span class="fulldate">{$graphData.data.date|date:'D d M':{$INTERFACE_LANGUAGE}|ucwords}</span>
															<span class="date">{$graphData.data.date|date:'d M':{$INTERFACE_LANGUAGE}|ucwords}</span>
															<span class="value">{$graphData.data.value}</span>
														</li>
													{/iteration:graphData.data}
												</ul>
											</li>
										{/iteration:graphData}
									</ul>
								</div>
								<div id="chartDoubleMetricPerDay">&nbsp;</div>
							{/option:graphData}
							<div class="buttonHolderRight">
								<a href="http://highcharts.com/" class="analyticsBacklink">Highcharts</a>
							</div>
						</div>
					</div>
				</div>

				<div class="analyticsRightCol">
					<div class="box boxLevel2">
						<div class="heading">
							<h3><a href="{$googleTrafficSourcesURL}">{$lblPageviewsByTrafficSources|ucfirst}</a></h3>
							<div class="buttonHolderRight">
								<a class="button icon iconGoto linkButton" href="{$googleTrafficSourcesURL}"><span>{$lblViewReport|ucfirst}</span></a>
							</div>
						</div>
						<div class="options">
							{option:pieGraphData}
								<div id="dataChartPieChart" class="hidden">
									<ul class="data">
										{iteration:pieGraphData}
											<li><span class="label">{$pieGraphData.label}</span><span class="value">{$pieGraphData.value}</span><span class="percentage">{$pieGraphData.percentage}</span></li>
										{/iteration:pieGraphData}
									</ul>
								</div>
								<div id="chartPieChart">&nbsp;</div>
							{/option:pieGraphData}
							<div class="buttonHolderRight">
								<a href="http://highcharts.com/" class="analyticsBacklink">Highcharts</a>
							</div>
						</div>
					</div>
				</div>

				<div class="analyticsFullWidth">
					<div class="box boxLevel2">
						<div class="heading">
							<h3><a href="{$googleVisitorsURL}">{$lblPageNotFoundStatistics|ucfirst}</a></h3>
							<div class="buttonHolderRight">
								<a class="button icon iconGoto linkButton" href="{$googleVisitorsURL}"><span>{$lblViewReport|ucfirst}</span></a>
							</div>
						</div>
						<div class="options">
							{option:pageNotFoundStatisticsGraphData}
								<div id="dataChartPageNotFoundStatistics" class="hidden">
									<span id="chartPageNotFoundStatisticsMaxYAxis">{$chartPageNotFoundStatisticsMaxYAxis}</span>
									<span id="chartPageNotFoundStatisticsTickInterval">{$chartPageNotFoundStatisticsTickInterval}</span>
									<span id="chartPageNotFoundStatisticsYAxisTitle">{$lblPageviews|ucfirst}</span>
									<ul class="series">
										{iteration:pageNotFoundStatisticsGraphData}
											<li class="serie" id="metric{$pageNotFoundStatisticsGraphData.i}serie">
												<span class="name">{$pageNotFoundStatisticsGraphData.label}</span>
												<ul class="data">
													{iteration:pageNotFoundStatisticsGraphData.data}
														<li>
															<span class="fulldate">{$pageNotFoundStatisticsGraphData.data.date|date:'D d M':{$INTERFACE_LANGUAGE}|ucwords}</span>
															<span class="date">{$pageNotFoundStatisticsGraphData.data.date|date:'d M':{$INTERFACE_LANGUAGE}|ucwords}</span>
															<span class="value">{$pageNotFoundStatisticsGraphData.data.value}</span>
														</li>
													{/iteration:pageNotFoundStatisticsGraphData.data}
												</ul>
											</li>
										{/iteration:pageNotFoundStatisticsGraphData}
									</ul>
								</div>

								<div id="chartPageNotFoundStatistics">&nbsp;</div>

								<div id="pageNotFoundStatisticsFilter">
									<h4>Filter:</h4>
									<p>user logged in:</p>
									<input type=checkbox name="user_logged_in" id="isLoggedIn"/>

									<p>caller is module action:</p>
									<input type=checkbox name="caller_is_action" id="callerIsAction"/>

									{option:filterExtension}
									<p>extension</p>
									<select id="extension">
										{iteration:filterExtension}
										<option>{$filterExtension.name}</option>
										{/iteration:filterExtension}
									</select>
									{/option:filterExtension}

									{option:filterBrowser}
									<p>browser</p>
									<select id="browser">
										{iteration:filterBrowser}
										<option>{$filterBrowser.name}</option>
										{/iteration:filterBrowser}
									</select>
									{/option:filterBrowser}

									{option:filterBrowserVersion}
									<p>version</p>
									<select id="browserVersion">
										{iteration:filterBrowserVersion}
										<option>{$filterBrowserVersion.versionId}</option>
										{/iteration:filterBrowserVersion}
									</select>
									{/option:filterBrowserVersion}
								</div>

								<div id="dataGridPageNotFoundStatistics" class="boxLevel2">
									<div class=" heading">
										<h3 id="pageNotFoundDate">{$pageNotFoundDate}</h3>
									</div>
									{option:missingPages}
									<div id="pageNotFoundIndex" class="dataGridHolder">
										<table class="dataGrid">
											<tbody>
												{iteration:missingPages.pageviews}
												<tr class="{cycle:'even':'odd'}">
													<td data-index="{$missingPages.pageviews.index}">{$missingPages.pageviews.url}</td>
												</tr>
												{/iteration:missingPages.pageviews}
											</tbody>
										</table>
									</div>
									{/option:missingPages}
								</div>
							{/option:pageNotFoundStatisticsGraphData}
							<div id="highChartsLink" class="buttonHolderRight">
								<a href="http://highcharts.com/" class="analyticsBacklink">Highcharts</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="dataGridHolder" id="analyticsDataGridLeftCol">
		<div class="tableHeading">
			<h3><a href="{$googleTopReferrersURL}">{$lblTopReferrers|ucfirst}</a></h3>
			<div class="buttonHolderRight">
				<a class="button icon iconGoto linkButton" href="{$googleTopReferrersURL}"><span>{$lblViewReport|ucfirst}</span></a>
			</div>
		</div>

		{* Top referrers *}
		{option:dgReferrers}
			{$dgReferrers}
		{/option:dgReferrers}
		{option:!dgReferrers}
			<table class="dataGrid">
				<tr>
					<td>{$msgNoReferrers}</td>
				</tr>
			</table>
		{/option:!dgReferrers}
	</div>

	<div class="dataGridHolder" id="analyticsDataGridRightCol">
		<div class="tableHeading">
			<h3><a href="{$googleTopKeywordsURL}">{$lblTopKeywords|ucfirst}</a></h3>
			<div class="buttonHolderRight">
				<a class="button icon iconGoto linkButton" href="{$googleTopKeywordsURL}"><span>{$lblViewReport|ucfirst}</span></a>
			</div>
		</div>

		{* Top keywords *}
		{option:dgKeywords}
			{$dgKeywords}
		{/option:dgKeywords}
		{option:!dgKeywords}
			<table class="dataGrid">
				<tr>
					<td>{$msgNoKeywords}</td>
				</tr>
			</table>
		{/option:!dgKeywords}
	</div>
{/option:!warnings}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}
{option:analyticsValidSettings}
<div class="box" id="widgetAnalyticsPageNotFoundStatistics">
	<div class="heading">
		<h3>
			{$lblPageNotFoundStatistics|ucfirst} {$lblFrom}
			{$analyticsPageNotFoundStatisticsStartDate|date:'j-m':{$INTERFACE_LANGUAGE}} {$lblTill}
			{$analyticsPageNotFoundStatisticsEndDate|date:'j-m':{$INTERFACE_LANGUAGE}}
		</h3>
	</div>

	<div class="options content">
		{option:analyticsPageNotFoundStatisticsGraphData}
			<div id="dataChartPageNotFoundStatistics" class="hidden">
				<span id="maxYAxis">{$analyticsPageNotFoundStatisticsMaxYAxis}</span>
				<span id="tickInterval">{$analyticsPageNotFoundStatisticsTickInterval}</span>
				<span id="yAxisTitle">{$lblPageviews|ucfirst}</span>
				<ul class="series">
					{iteration:analyticsPageNotFoundStatisticsGraphData}
						<li class="serie" id="metric{$analyticsPageNotFoundStatisticsGraphData.i}serie">
							<span class="name">{$analyticsPageNotFoundStatisticsGraphData.label}</span>
							<ul class="data">
								{iteration:analyticsPageNotFoundStatisticsGraphData.data}
									<li>
										<span class="fulldate">{$analyticsPageNotFoundStatisticsGraphData.data.date|date:'D d M':{$INTERFACE_LANGUAGE}|ucwords}</span>
										<span class="date">{$analyticsPageNotFoundStatisticsGraphData.data.date|date:'D':{$INTERFACE_LANGUAGE}|ucfirst}</span>
										<span class="value">{$analyticsPageNotFoundStatisticsGraphData.data.value}</span>
									</li>
								{/iteration:analyticsPageNotFoundStatisticsGraphData.data}
							</ul>
						</li>
					{/iteration:analyticsPageNotFoundStatisticsGraphData}
				</ul>
			</div>
			<div id="chartPageNotFoundStatistics">&nbsp;</div>

			<div id="dataGridPageNotFoundStatistics" class="boxLevel2">
				<div class=" heading">
					<h3 id="pageNotFoundStatisticsDate">{$analyticsPageNotFoundStatisticsDate}</h3>
				</div>
				{option:pageNotFoundStatisticsDataGrid}
				<div id="pageNotFoundIndex" class="dataGridHolder">
					<table class="dataGrid">
						<tbody>
							{iteration:pageNotFoundStatisticsDataGrid.pageviews}
							<tr class="{cycle:'even':'odd'}">
								<td data-index="{$pageNotFoundStatisticsDataGrid.pageviews.index}">{$pageNotFoundStatisticsDataGrid.pageviews.url}</td>
							</tr>
							{/iteration:pageNotFoundStatisticsDataGrid.pageviews}
						</tbody>
					</table>
				</div>
				{/option:pageNotFoundStatisticsDataGrid}
			</div>
		{/option:analyticsPageNotFoundStatisticsGraphData}

		{option:!analyticsPageNotFoundStatisticsGraphData}
			<p class="analyticsFallback">
				<a href="{$var|geturl:'index':'analytics'}" class="linkedImage">
					<img src="{$SITE_URL}/backend/modules/analytics/layout/images/analytics_widget_{$INTERFACE_LANGUAGE}.jpg" alt="" />
				</a>
			</p>
		{/option:!analyticsPageNotFoundStatisticsGraphData}
	</div>
	<div class="footer">
		<div class="buttonHolderRight">
			<a href="{$var|geturl:'index':'analytics'}" class="button"><span>{$lblAllStatistics|ucfirst}</span></a>
		</div>
	</div>
</div>
{/option:analyticsValidSettings}
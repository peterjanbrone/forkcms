{option:analyticsValidSettings}
<div class="box" id="widgetAnalyticsPageNotFoundStats">
	<div class="heading">
		<h3>
			{$lblPageNotFoundStats|ucfirst} {$lblFrom}
			{$analyticsPageNotFoundStatsStartDate|date:'j-m':{$INTERFACE_LANGUAGE}} {$lblTill}
			{$analyticsPageNotFoundStatsEndDate|date:'j-m':{$INTERFACE_LANGUAGE}}
		</h3>
	</div>

	<div class="options content">
		{option:analyticsPageNotFoundStatsGraphData}
			<div id="dataPageNotFoundStatsWidget" class="hidden">
				<span id="maxYAxis">{$analyticsPageNotFoundStatsMaxYAxis}</span>
				<span id="tickInterval">{$analyticsPageNotFoundStatsTickInterval}</span>
				<span id="yAxisTitle">{$lblPageviews|ucfirst}</span>
				<ul class="series">
					{iteration:analyticsPageNotFoundStatsGraphData}
						<li class="serie" id="metric{$analyticsPageNotFoundStatsGraphData.i}serie">
							<span class="name">{$analyticsPageNotFoundStatsGraphData.label}</span>
							<ul class="data">
								{iteration:analyticsPageNotFoundStatsGraphData.data}
									<li>
										<span class="fulldate">{$analyticsPageNotFoundStatsGraphData.data.date|date:'D d M':{$INTERFACE_LANGUAGE}|ucwords}</span>
										<span class="date">{$analyticsPageNotFoundStatsGraphData.data.date|date:'D':{$INTERFACE_LANGUAGE}|ucfirst}</span>
										<span class="value">{$analyticsPageNotFoundStatsGraphData.data.value}</span>
									</li>
								{/iteration:analyticsPageNotFoundStatsGraphData.data}
							</ul>
						</li>
					{/iteration:analyticsPageNotFoundStatsGraphData}
				</ul>
			</div>
			<div id="pageNotFoundStatsWidget">&nbsp;</div>
		{/option:analyticsPageNotFoundStatsGraphData}

		{option:!analyticsPageNotFoundStatsGraphData}
			<p class="analyticsFallback">
				<a href="{$var|geturl:'index':'analytics'}" class="linkedImage">
					<img src="{$SITE_URL}/backend/modules/analytics/layout/images/analytics_widget_{$INTERFACE_LANGUAGE}.jpg" alt="" />
				</a>
			</p>
		{/option:!analyticsPageNotFoundStatsGraphData}

		<div class="boxLevel2">
			<div class=" heading">
				<h3 id="pageNotFoundDate">{$pageNotFoundDate}</h3>
			</div>
			{option:missingPages}
			<div id="pageNotFoundDetails" class="dataGridHolder">
				<table class="dataGrid">
					<tbody>
						{iteration:missingPages.page}
						<tr class="{cycle:'even':'odd'}">
							<td class="name">{$missingPages.page.url}</td>
						</tr>
						{/iteration:missingPages.page}
					</tbody>
				</table>
			</div>
			{/option:missingPages}
		</div>
	</div>
</div>
{/option:analyticsValidSettings}
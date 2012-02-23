{option:analyticsValidSettings}
<div class="box" id="widgetAnalytics404Statistics">
	<div class="heading">
		<h3>
			<a href="{$var|geturl:'index':'analytics'}">
				{$lblErrorPageStatistics|ucfirst}
			</a>
		</h3>
	</div>

	<div class="options">
		<div id="tabs" class="tabs">
			<ul>
				<li><a href="#tabAnalyticsErrorPageStats">{$lblTopErrorPageStats|ucfirst}</a></li>
				<li><a href="#tabAnalyticsVisitorInfo">{$lblTopVisitorInfo|ucfirst}</a></li>
			</ul>

			<div id="tabAnalyticsErrorPageStats">
				{* Top error stats *}
				<div class="dataGridHolder" id="dataGridErrorPageStats">
					{option:dgAnalyticsErrorPageStatistics}
						{$dgAnalyticsErrorPageStatistics}
					{/option:dgAnalyticsErrorPageStatistics}

					{option:!dgAnalyticsErrorPageStatistics}
						<table class="dataGrid">
							<tr>
								<td>{$msgNoErrorPageStats}</td>
							</tr>
						</table>
					{/option:!dgAnalyticsErrorPageStatistics}
				</div>
			</div>

			<div id="tabAnalyticsVisitorInfo">
				{* Top visitor info *}
				<div class="dataGridHolder" id="dgAnalyticsVisitorInfo">
					{option:dgAnalyticsVisitorInfo}
						{$dgAnalyticsVisitorInfo}
					{/option:dgAnalyticsVisitorInfo}

					{option:!dgAnalyticsVisitorInfo}
						<table class="dataGrid">
							<tr>
								<td>{$msgNoVisitorInfo}</td>
							</tr>
						</table>
					{/option:!dgAnalyticsVisitorInfo}
				</div>
			</div>
		</div>
	</div>
	
	<div class="footer">
		<div class="buttonHolderRight">
			<a href="{$var|geturl:'index':'analytics'}" class="button"><span>{$lblAllStatistics|ucfirst}</span></a>
			{option:settingsUrl}<div id="settingsUrl" class="hidden">{$settingsUrl}</div>{/option:settingsUrl}
		</div>
	</div>
</div>
{/option:analyticsValidSettings}
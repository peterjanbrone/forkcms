<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a widget which inserts the 404 tracker code and saves
 * some simple statistics data
 *
 * @author Peter-Jan Brone <peterjan.brone@kahosl.be>
 */
class FrontendPagesWidgetTracker extends FrontendBaseWidget
{
	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();
		$this->loadTemplate();
		$this->parse();
	}

	private function parse()
	{
		// get the google analytics ic
		$webPropertyId = FrontendModel::getModuleSetting('analytics', 'web_property_id', null);

		// set up the 404 tracking script
		$script = '<script>
						try{
							var hndl = window.setTimeout("track404()", 100);

							function track404(){
								if (typeof(_gat) == \'object\'){
									window.clearTimeout(hndl);
									var pageTracker =_gat._getTracker(\'' . $webPropertyId . '\');
									pageTracker._trackPageview(\'/404?page=\' + encodeURIComponent(document.location.pathname)
									+ encodeURIComponent(document.location.search) + \'&from=\' + encodeURIComponent(document.referrer));
								} else {hndl = window.setTimeout("track404()", 1000);}
							}

							_gaq.push([\'_setAllowLinker\', true]);
							_gaq.push([\'_trackEvent\', \'404\', encodeURIComponent(document.location.pathname)
							+ encodeURIComponent(document.location.search), encodeURIComponent(document.referrer)]);
						}catch(err) {console.log(err);}
					</script>';

		// insert the script
		$this->tpl->assign('script', $script);

		// get 404 page stats
		$stats = array();

		// get page & referrer
		$stats['page'] = '/' . Spoon::get('url')->getQueryString();
		$stats['referrer'] = (isset($_SERVER['HTTP_REFERER']))? $_SERVER['HTTP_REFERER'] : null;

		// get the extension
		$queryStringChunks = explode(".", $stats['page']);
		$stats['extension'] = (count($queryStringChunks) > 1)? end($queryStringChunks) : null;

		// get the browser and remote ip
		$stats['browser'] = $_SERVER['HTTP_USER_AGENT'];
		$stats['remote_ip'] = $_SERVER['REMOTE_ADDR'];

		// find out if the call was made from a module
		$trace = debug_backtrace();
		$stats['caller_is_module'] = false;
		for($i = 0; $i < count($trace); $i++)
		{
			// look for dieWith404 the next
			// one's our caller
			$searchValue = "dieWith404";
			$dieWith404Index = array_keys($trace[$i], $searchValue);

			if(count($dieWith404Index) == 1)
				$stats['caller_is_module'] = ($trace[++$i]['object']->getModule() !== null && $trace[++$i]['object']->getModule() !== '')? true : false;
		}

		// find out if the user was logged in or not
		$stats['is_logged_in'] = FrontendProfilesAuthentication::isLoggedIn();

		$stats['date'] = FrontendModel::getUTCDate('Y-m-d H:i:s');

		// insert the stats and trigger an event
		$stats['id'] = (int) FrontendPagesModel::insertErrorPageStatistics($stats);
	}
}

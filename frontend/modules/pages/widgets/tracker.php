<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a widget wherin the sitemap lives
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

		// run the script
		$this->tpl->assign('script', $script);
	}
}

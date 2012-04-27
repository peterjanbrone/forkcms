<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action will update a translation using AJAX
 *
 * @author Lowie Benoot <lowie.benoot@netlash.com>
 * @author Matthias Mullie <matthias@mullie.eu>
 */
class BackendSettingsAjaxSaveSettings extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// get parameters
		$column = SpoonFilter::getPostValue('column', null, null, 'string');
		$language = SpoonFilter::getPostValue('language', null, null, 'string');
		$setting = SpoonFilter::getPostValue('setting', null, null, 'string');
		$value = SpoonFilter::getPostValue('value', null, null, 'string');

		// validate
		if($column == '' || $language == '' || $setting == '' || trim($value) == '') $error = BL::err('InvalidValue');

		if(!isset($error))
		{
			// get current settings
			$settings = BackendModel::getModuleSetting('core', $setting);

			// insert where languages match
			foreach($settings as &$item)
			{
				if($item['language'] === $language){$item[$column] = $value;}
			}

			// save settings
		 	BackendModel::setModuleSetting('core', $setting, $settings);

			// output OK
			$this->output(self::OK);
		}

		// output the error
		else $this->output(self::ERROR, null, $error);
	}
}

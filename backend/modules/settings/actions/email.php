<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the email-action, it will display a form to set email settings
 *
 * @author Tijs Verkoyen <tijs@sumocoders.be>
 */
class BackendSettingsEmail extends BackendBaseActionIndex
{
	/**
	 * Is the user a god user?
	 *
	 * @var bool
	 */
	protected $isGod = false;

	/**
	 * The form instance
	 *
	 * @var	BackendForm
	 */
	private $frm;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->isGod = BackendAuthentication::getUser()->isGod();
		$this->wLang = BL::getWorkingLanguage();

		// email settings
		$this->langDependency = BackendModel::getModuleSetting('core', 'language_dependency');
		$this->mailerFrom = BackendModel::getModuleSetting('core', 'mailer_from');
		$this->mailerTo = BackendModel::getModuleSetting('core', 'mailer_to');
		$this->mailerReplyTo = BackendModel::getModuleSetting('core', 'mailer_reply_to');

		// make sure to insert recently 'installed' languages
		$languages = array_keys($this->mailerFrom);
		foreach(array_diff(BL::getActiveLanguages(), $languages) as $language)
		{
			// copy values from working language
			$default = array($language => array(
				'name' => $this->mailerFrom[$this->wLang]['name'],
				'email' => $this->mailerFrom[$this->wLang]['email']
			));

			// insert default values for the new language
			array_push($this->mailerFrom, $default);
			array_push($this->mailerTo, $default);
			array_push($this->mailerReplyTo, $default);

			// save in module settings
			BackendModel::setModuleSetting('core', 'mailer_from', $this->mailerFrom);
			BackendModel::setModuleSetting('core', 'mailer_to', $this->mailerTo);
			BackendModel::setModuleSetting('core', 'mailer_reply_to', $this->mailerReplyTo);
		}

		// init datagrid array, 'setting_name' => ['setting_data', 'setting_datagrid']
		$this->grids = array(
			'mailer_from' => array($this->mailerFrom, $this->dgEmailFrom = null),
			'mailer_to' => array($this->mailerTo, $this->dgEmailTo = null),
			'mailer_reply_to' => array($this->mailerReplyTo, $this->dgReplyTo = null),
		);

		// loop each grid and insert a language value
		foreach($this->grids as $i => &$grid)
		{
			foreach($grid[0] as $key => &$value) $value = array('language' => $key) + $value;
			$grid[1] = new BackendDataGridArray($grid[0]);
		}

		// set datagrid attributes
		foreach($this->grids as $setting => &$grid)
		{
			foreach(array('name', 'email') as $column)
			{
				$grid[1]->setColumnAttributes($column, array('data-id' => '{value: \'[value]\', column: \'' . $column . '\', setting: \'' . $setting . '\'}'));
				$grid[1]->setColumnAttributes($column, array('class' => 'translationValue'));
				$grid[1]->setColumnAttributes($column, array('style' => 'width: 48%'));
			}
			$grid[1]->setRowAttributes(array('style' => 'height: 36px'));
		}

		// setup form
		$this->frm = new BackendForm('settingsEmail');
		$this->frm->addDropdown('language_dependency', array('0' => 'Language independent', '1' => 'Language dependent'),$this->langDependency);
		$this->frm->addText('mailer_from_name', (isset($this->mailerFrom[$this->wLang]['name']))? $this->mailerFrom[$this->wLang]['name'] : '');
		$this->frm->addText('mailer_from_email', (isset($this->mailerFrom[$this->wLang]['email'])) ? $this->mailerFrom[$this->wLang]['email'] : '');
		$this->frm->addText('mailer_to_name', (isset($this->mailerTo[$this->wLang]['name'])) ? $this->mailerTo[$this->wLang]['name'] : '');
		$this->frm->addText('mailer_to_email', (isset($this->mailerTo[$this->wLang]['email'])) ? $this->mailerTo[$this->wLang]['email'] : '');
		$this->frm->addText('mailer_reply_to_name', (isset($this->mailerReplyTo[$this->wLang]['name'])) ? $this->mailerReplyTo[$this->wLang]['name'] : '');
		$this->frm->addText('mailer_reply_to_email', (isset($this->mailerReplyTo[$this->wLang]['email'])) ? $this->mailerReplyTo[$this->wLang]['email'] : '');

		if($this->isGod)
		{
			$mailerType = BackendModel::getModuleSetting('core', 'mailer_type', 'mail');
			$this->frm->addDropdown('mailer_type', array('mail' => 'PHP\'s mail', 'smtp' => 'SMTP'), $mailerType);

			// smtp settings
			$this->frm->addText('smtp_server', BackendModel::getModuleSetting('core', 'smtp_server', ''));
			$this->frm->addText('smtp_port', BackendModel::getModuleSetting('core', 'smtp_port', 25));
			$this->frm->addText('smtp_username', BackendModel::getModuleSetting('core', 'smtp_username', ''));
			$this->frm->addPassword('smtp_password', BackendModel::getModuleSetting('core', 'smtp_password', ''));
		}

		$this->tpl->assign('isGod', $this->isGod);
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		// parse datagrids
		$this->tpl->assign('dgEmailFrom', $this->grids['mailer_from'][1]->getContent());
		$this->tpl->assign('dgEmailTo', $this->grids['mailer_to'][1]->getContent());
		$this->tpl->assign('dgReplyTo', $this->grids['mailer_reply_to'][1]->getContent());

		// parse the form
		$this->frm->parse($this->tpl);
	}

	/**
	 * Validates the form
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// validate required fields
			$this->frm->getField('mailer_from_name')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('mailer_from_email')->isEmail(BL::err('EmailIsInvalid'));
			$this->frm->getField('mailer_to_name')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('mailer_to_email')->isEmail(BL::err('EmailIsInvalid'));
			$this->frm->getField('mailer_reply_to_name')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('mailer_reply_to_email')->isEmail(BL::err('EmailIsInvalid'));

			if($this->isGod)
			{
				// SMTP type was chosen
				if($this->frm->getField('mailer_type')->getValue() == 'smtp')
				{
					// server & port are required
					$this->frm->getField('smtp_server')->isFilled(BL::err('FieldIsRequired'));
					$this->frm->getField('smtp_port')->isFilled(BL::err('FieldIsRequired'));
				}
			}

			// no errors ?
			if($this->frm->isCorrect())
			{
				// store settings
				$this->mailerFrom[$this->wLang]['name'] = $this->frm->getField('mailer_from_name')->getValue();
				$this->mailerFrom[$this->wLang]['email'] = $this->frm->getField('mailer_from_email')->getValue();
				$this->mailerTo[$this->wLang]['name'] = $this->frm->getField('mailer_to_name')->getValue();
				$this->mailerTo[$this->wLang]['email'] = $this->frm->getField('mailer_to_email')->getValue();
				$this->mailerReplyTo[$this->wLang]['name'] = $this->frm->getField('mailer_reply_to_name')->getValue();
				$this->mailerReplyTo[$this->wLang]['email'] = $this->frm->getField('mailer_reply_to_email')->getValue();

				// save settings
				BackendModel::setModuleSetting('core', 'language_dependency', $this->frm->getField('language_dependency')->getValue());
				BackendModel::setModuleSetting('core', 'mailer_from', $this->mailerFrom);
				BackendModel::setModuleSetting('core', 'mailer_to', $this->mailerTo);
				BackendModel::setModuleSetting('core', 'mailer_reply_to', $this->mailerReplyTo);

				if($this->isGod)
				{
					BackendModel::setModuleSetting('core', 'mailer_type', $this->frm->getField('mailer_type')->getValue());

					// smtp settings
					BackendModel::setModuleSetting('core', 'smtp_server', $this->frm->getField('smtp_server')->getValue());
					BackendModel::setModuleSetting('core', 'smtp_port', $this->frm->getField('smtp_port')->getValue());
					BackendModel::setModuleSetting('core', 'smtp_username', $this->frm->getField('smtp_username')->getValue());
					BackendModel::setModuleSetting('core', 'smtp_password', $this->frm->getField('smtp_password')->getValue());
				}

				// assign report
				$this->tpl->assign('report', true);
				$this->tpl->assign('reportMessage', BL::msg('Saved'));
				$this->tpl->assign('isGod', $this->isGod);
			}
		}
	}
}

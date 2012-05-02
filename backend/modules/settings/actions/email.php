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
		$this->emailSettings = BackendModel::getModuleSetting('core', 'mailer_settings');

		// check for recently added languages
		$this->languages = array_keys($this->emailSettings);
		foreach(array_diff(BL::getActiveLanguages(), $this->languages) as $language)
		{
			// ignore default
			if($language === 'default') continue;

			// copy default values into the new language array
			$this->emailSettings[$language] = $this->emailSettings['default'];

			BackendModel::setModuleSetting('core', 'mailer_settings', $this->emailSettings);
		}


		// create our grid array
		$source = array();
		foreach($this->emailSettings as $key => $langSetting)
		{
			// make language pretty
			$language = ($key === 'default')
				? ucfirst($key)
				: ucfirst(SpoonLocale::getLanguage($key));

			array_push($source, array(
				'language' => $language,
				'to' => implode('||', $langSetting['to']),
				'from' => implode('||', $langSetting['from']),
				'reply' => implode('||', $langSetting['reply'])
			));
		}

		// set datagrid options
		$this->dgEmailSettings = new BackendDataGridArray($source);
		$this->dgEmailSettings->setColumnAttributes('to', array('class' => 'transform', 'data-column' => 'to'));
		$this->dgEmailSettings->setColumnAttributes('from', array('class' => 'transform', 'data-column' => 'from'));
		$this->dgEmailSettings->setColumnAttributes('reply', array('class' => 'transform', 'data-column' => 'reply'));
		$this->dgEmailSettings->setColumnAttributes('language', array('style' => 'vertical-align: top; width: 30px;'));
		$this->dgEmailSettings->setRowAttributes(array('data-language' => '[language]'));

		// setup form
		$this->frm = new BackendForm('settingsEmail');
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

		// parse datagrid
		$this->tpl->assign('dgEmailSettings', $this->dgEmailSettings->getContent());

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
			// collect email settings
			foreach($this->languages as $language)
			{
				foreach(array('from', 'to', 'reply') as $col)
				{
					// make sure to change language from 'en' to 'english' f.e.
					$name = ($language === 'default')
						? SpoonFilter::getPostValue($language . '-' . $col . '-name', null, '')
						: SpoonFilter::getPostValue(SpoonLocale::getLanguage($language) . '-' . $col . '-name', null, '');
					$email = ($language === 'default')
						? SpoonFilter::getPostValue($language . '-' . $col . '-email', null, '')
						: SpoonFilter::getPostValue(SpoonLocale::getLanguage($language) . '-' . $col . '-email', null, '');

					// if null, use defaults
					if($name === null || $name === '') $name = $this->emailSettings['default'][$col]['name'];
					if($email === null || $email === '') $email = $this->emailSettings['default'][$col]['email'];

					$fields[$language][$col]['name'] = $name;
					$fields[$language][$col]['email'] = $email;
				}
			}

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
				// save email settings
				BackendModel::setModuleSetting('core', 'mailer_settings', $fields);

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

				$this->redirect(BackendModel::createURLForAction('email') . '&report=saved');
			}
		}
	}
}

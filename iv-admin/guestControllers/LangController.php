<?php

class LangController extends ivController
{

	/**
	 * Pre-dispatching
	 *
	 */
	public function _preDispatch()
	{
		if (!ivAcl::isAdmin()) {
			$this->_forward('login', 'cred');
			if (ivAuth::getCurrentUserLogin()) {
				ivMessenger::add(ivMessenger::ERROR, "You don't have access to this page");
			}
			return;
		}
	}

	/**
	 * Default action (edit language)
	 *
	 */
	public function indexAction()
	{
		$crumbs = ivPool::get('breadCrumbs');
		$crumbs->push('Languages', 'index.php?c=lang');
		$lang = mb_strtolower($this->_getParam('name', 'english'), 'UTF-8');
		if (!preg_match('/^[\w\d\_]+$/', $lang)) {
			ivMessenger::add(ivMessenger::ERROR, 'Use only alphanumeric symbols and "_" symbol in language name');
			$this->_redirect('index.php?c=lang');
		}
		$this->view->assign('lang', $lang);
		$crumbs->push($lang, 'index.php?c=lang&amp;name=' . $lang);

		$xml = ivLanguage::getLanguage($lang);

		$this->view->assign('flatConfig', $xml->toFlatTree());
		$this->view->assign('languages', ivLanguage::getAllLanguageNames());
	}

	/**
	 * Set default language
	 *
	 */
	public function useAction()
	{
		$this->_redirect('index.php?c=lang');
	}

}

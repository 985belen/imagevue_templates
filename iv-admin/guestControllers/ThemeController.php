<?php

class ThemeController extends ivController
{

	/**
	 * Default action
	 *
	 */
	public function indexAction()
	{
		$crumbs = ivPool::get('breadCrumbs');
		$crumbs->push('Themes', 'index.php?c=theme');

		$selectedTheme = $this->conf->get('/config/imagevue/settings/theme');
		$defaultThemes = ivThemeMapper::getInstance()->getDefaultThemes();
		$themes = array_diff(ivThemeMapper::getInstance()->getAllThemes(), array($selectedTheme), $defaultThemes);
		sort($themes);
		array_unshift($themes, $selectedTheme);
		foreach (array_diff($defaultThemes, array($selectedTheme)) as $name) {
			$themes[] = $name;
		}

		$this->view->assign('theme', $selectedTheme);
		$this->view->assign('themes', $themes);
		$this->view->assign('defaultThemes', $defaultThemes);
	}

	/**
	 * Edit theme cascade stylesheet
	 *
	 */
	public function editcssAction()
	{
		$themeName = $this->_getParam('name', 'default', 'alnum');
		if (in_array($themeName, ivThemeMapper::getInstance()->getDefaultThemes())) {
			$this->_redirect('?c=theme');
		}

		$theme = ivThemeMapper::getInstance()->find($themeName);
		if (!$theme) {
			ivMessenger::add(ivMessenger::ERROR, "Theme named '$themeName' not found");
			$this->_redirect('?c=theme');
		}

		if (isset($_POST['css'])) {
			ivMessenger::add(ivMessenger::NOTICE, 'CSS file succesfully saved');
			$this->_redirect($_SERVER['REQUEST_URI']);
		}

		$crumbs = ivPool::get('breadCrumbs');
		$crumbs->push('Themes', 'index.php?c=theme');
		$crumbs->push(ucfirst($themeName), 'index.php?c=theme&amp;a=editconfig&amp;name=' . $themeName);
		$crumbs->push('Stylesheet', 'index.php?c=theme&amp;a=editcss&amp;name=' . $themeName);

		$this->view->assign('theme', $theme);
		$this->view->assign('themes', ivThemeMapper::getInstance()->getAllThemes());
	}

	/**
	 * Edit theme configuration
	 *
	 */
	public function editconfigAction()
	{
		$crumbs = ivPool::get('breadCrumbs');
		$crumbs->push('themes', 'index.php?c=theme');

		$themeName = $this->_getParam('name', 'default', 'alnum');
		if (in_array($themeName, ivThemeMapper::getInstance()->getDefaultThemes())) {
			$this->_redirect('?c=theme');
		}

		$crumbs->push(ucfirst($themeName), 'index.php?c=theme&amp;a=editconfig&amp;name=' . $themeName);

		$theme = ivThemeMapper::getInstance()->find($themeName);
		if (!$theme) {
			ivMessenger::add(ivMessenger::ERROR, "Theme named '$themeName' not found");
			$this->_redirect('?c=theme');
		}
		$xml = $theme->getConfig();

		$sections = array();
		$rootNode = $xml->findByXPath('/config/imagevue');
		if ($rootNode) {
			foreach ($rootNode->getChildren() as $k => $child) {
				$sections[$child->getName()] = $child->toFlatTree();
			}
		}

		$this->view->assign('sections', $sections);

		$this->view->assign('themes', ivThemeMapper::getInstance()->getAllThemes());
		$this->view->assign('themeName', $themeName);

		if (isset($_COOKIE['ivconf'])) {
			$openedPanels = array_unique(array_explode_trim(',', $_COOKIE['ivconf']));
		}
		$this->view->assign('openedPanels', $openedPanels);
	}

	/**
	 * Set default theme
	 *
	 */
	public function useAction()
	{
		$this->_redirect('index.php?c=theme');
	}

	/**
	 * Copy theme
	 *
	 */
	public function copyAction()
	{
		$newTheme = (string) $this->_getParam('new');
		if (!ctype_alnum($newTheme)) {
			ivMessenger::add(ivMessenger::ERROR, 'Use only alphanumeric symbols in theme name');
			$this->_redirect('index.php?c=theme');
		}
		ivMessenger::add(ivMessenger::NOTICE, "Theme $newTheme succesfully created");
		$this->_redirect('index.php?c=theme');
	}

	/**
	 * Delete theme
	 *
	 */
	public function deleteAction()
	{
		$themeName = $this->_getParam('name', null, 'alnum');
		if (in_array($themeName, ivThemeMapper::getInstance()->getDefaultThemes())) {
			$this->_redirect('?c=theme');
		}

		ivMessenger::add(ivMessenger::NOTICE, "Theme $themeName succesfully deleted");
		$this->_redirect('index.php?c=theme');
	}

	/**
	 * Upload background file
	 *
	 */
	public function uploadAction()
	{
		$themeName = $this->_getParam('name', null, 'alnum');
		if (in_array($themeName, ivThemeMapper::getInstance()->getDefaultThemes())) {
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}

		$this->_setNoRender();
		if (!isset($_FILES['Filedata'])) {
			ivMessenger::add(ivMessenger::ERROR, 'File not found');
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
		$imageData = $_FILES['Filedata'];
		if (!@getimagesize($imageData['tmp_name'])) {
			ivMessenger::add(ivMessenger::ERROR, 'Incompatible file');
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
		ivMessenger::add(ivMessenger::NOTICE, "File {$imageData['name']} succesfully uploaded");
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}

}
<?php

class CredController extends ivController
{

	/**
	 * Log in
	 *
	 */
	public function loginAction()
	{
		$login = (string) $this->_getParam('login');
		$password = (string) $this->_getParam('password');
		if (!empty($login) && !empty($password)) {
			$rememberme = (boolean) $this->_getParam('rememberme');
			$result = ivAuth::authenticate($login, $password, $rememberme);
			if ($result) {
				ivMessenger::add(ivMessenger::NOTICE, "Welcome, $login");
			} else {
				ivMessenger::add(ivMessenger::ERROR, 'Incorrect login or password');
			}
			$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?');
		}

		$guest = $userManager->getUser('guest');
		$this->view->assign('defaultGuest', ($guest && ('35675e68f4b5af7b995d9205ad0fc43842f16450' === $guest->passwordHash)));
	}

	/**
	 * Log out
	 *
	 */
	public function logoutAction()
	{
		ivAuth::authenticate('', '', false);
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}

}
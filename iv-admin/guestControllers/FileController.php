<?php

class FileController extends ivController
{

	/**
	 * Path
	 * @var string
	 */
	public $path;

	/**
	 * Pre-dispatching
	 *
	 */
	public function _preDispatch()
	{
		$path = ivPath::canonizeRelative($this->_getParam('path', '', 'path'), true);
		if (!ivAcl::isAllowedPath($path)) {
			if (false == ivAcl::getAllowedPath()) {
				$this->_forward('login', 'cred');
				return;
			} else {
				$this->_redirect('index.php');
			}
		}

		$fullPath = ROOT_DIR . $path;
		$include = $this->conf->get('/config/imagevue/settings/allowedext');
		if (is_dir($fullPath)) {
			$this->_redirect('index.php?path=' . smart_urlencode($path));
		} elseif (!is_file($fullPath)) {
			$this->_redirect('index.php?c=config');
		} elseif (!in_array(strtolower(ivFilepath::suffix($fullPath)), $include)) {
			$this->_redirect('index.php?path=' . smart_urlencode(ivFilepath::directory($path)));
		}
		$this->path = $path;
	}

	/**
	 * Default action
	 *
	 */
	public function indexAction()
	{
		$contentFolder = ivMapperFactory::getMapper('folder')->find(ivAcl::getAllowedPath());
		$iterator = new ivRecursiveFolderIterator($contentFolder);
		$this->view->assign('folderTreeIterator', new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST));

		$this->view->assign('path', $this->path);
		$this->placeholder->set('path', $this->path);

		$file = ivMapperFactory::getMapper('file')->find($this->path);

		$newdata = $this->_getParam('newdata');
		if (is_string($this->_getParam('save')) && is_array($newdata)) {
			ivMessenger::add(ivMessenger::NOTICE, 'File data succesfully saved');
		}

		$siblings = $file->getSiblings();

		$this->view->assign('file', $file);
		$this->view->assign('nextFile', $siblings->next);
		$this->view->assign('prevFile', $siblings->previous);
		$this->view->assign('current', $siblings->current);
		$this->view->assign('count', $siblings->count);

		$_SESSION['imagevue']['lastManageLink'] = '?c=file&amp;path=' . smart_urlencode($this->path);
	}

	/**
	 * Copy/move file
	 *
	 */
	public function moveAction()
	{
		$isMove = !$this->_getParam('copy', false);
		$targetPath = ivPath::canonizeRelative($this->_getParam('target', null, 'path'));
		$result = false;
		if (!empty($targetPath) && ($folder = ivMapperFactory::getMapper('folder')->find($targetPath)) && ($file = ivMapperFactory::getMapper('file')->find($this->path))) {
			if ($isMove) {
				ivMessenger::add(ivMessenger::NOTICE, "File succesfully moved to <a href=\"index.php?path=" . smart_urlencode($folder->getPrimary()) . "\">" . htmlspecialchars($folder->getTitle()) . "</a>");
			} else {
				ivMessenger::add(ivMessenger::NOTICE, "File succesfully copied to <a href=\"index.php?path=" . smart_urlencode($folder->getPrimary()) . "\">" . htmlspecialchars($folder->getTitle()) . "</a>");
			}
		}
		$this->_redirect('index.php?path=' . smart_urlencode($targetPath));
	}

	/**
	 * Delete file
	 *
	 */
	public function deleteAction()
	{
		ivMessenger::add(ivMessenger::NOTICE, 'File succesfully deleted');
		$this->_redirect('index.php?path=' . smart_urlencode(ivFilepath::directory($this->path)));
	}

	/**
	 * Rename file
	 *
	 */
	function renameAction()
	{
		$file = ivMapperFactory::getMapper('file')->find($this->path);
		$newFileName = (string) $this->_getParam('name');
		if ($file && !empty($newFileName)) {
			ivMessenger::add(ivMessenger::NOTICE, 'File succesfully renamed');
			$this->_redirect('index.php?path=' . smart_urlencode(ivFilepath::directory($this->path)));
		}

		$this->_redirect('index.php?path=' . smart_urlencode($this->path));
	}

	/**
	 * Rotate image
	 *
	 */
	public function rotateAction()
	{
		ivMessenger::add(ivMessenger::NOTICE, 'File succesfully rotated');
		$this->_redirect('index.php?c=file&path=' . smart_urlencode($this->path));
	}

	/**
	 * Post-dispatching
	 *
	 */
	public function _postDispatch()
	{
		$crumbs = ivPool::get('breadCrumbs');
		$brCrumbsKeys = array_explode_trim('/', $this->path);
		if ($brCrumbsKeys !== false) {
			$folder = ivMapperFactory::getMapper('folder')->find('');
			$suffix = '';
			$numOfFiles = $folder->fileCount;
			if ($totalNumOfFiles = $folder->totalFileCount) {
				$suffix =  '[' . (($numOfFiles == $totalNumOfFiles) ? $numOfFiles : $numOfFiles . '/' . $totalNumOfFiles . '') . ']';
			}
			$crumbs->push((strip_icon($folder->getTitle()) ? $folder->getTitle() : $folder->name), 'index.php', $suffix, ($folder->isHidden() ? 'hidden' : ''));

			$lastCrumbKey = end($brCrumbsKeys);
			$path = '';
			foreach ($brCrumbsKeys as $key) {
				if ($lastCrumbKey == $key) {
					$path .= $key;
					$file = ivMapperFactory::getMapper('file')->find($path);
					if (!$file) {
						continue;
					}
					$crumbs->push($file->getTitle(), 'index.php?c=file&amp;path=' . smart_urlencode($path));
				} else {
					$path .= $key . '/';
					$folder = ivMapperFactory::getMapper('folder')->find($path);
					if (!$folder) {
						continue;
					}
					$crumbs->push((strip_icon($folder->getTitle()) ? $folder->getTitle() : $folder->name), 'index.php?path=' . smart_urlencode($path), '', ($folder->isHidden() ? 'hidden' : ''));
				}
			}
		}
	}

}

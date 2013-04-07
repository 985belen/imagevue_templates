<?php

class IndexController extends ivController
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
		$path = ivPath::canonizeRelative($this->_getParam('path', '', 'path'));
		if (!ivAcl::isAllowedPath($path) && !ivAcl::isAllowedPath(ivPath::canonizeRelative($path))) {
			if (false == ivAcl::getAllowedPath()) {
				$this->_forward('login', 'cred');
				return;
			} else {
				$path = ivAcl::getAllowedPath();
			}
		}
		$fullPath = ROOT_DIR . $path;
		if (is_dir($fullPath)) {
			$path = ivPath::canonizeRelative($path);
		} elseif (is_file($fullPath)) {
			$this->_redirect('index.php?c=file&path=' . smart_urlencode($path));
		} else {
			$this->_redirect('index.php?c=config');
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

		$folder = ivMapperFactory::getMapper('folder')->find($this->path);

		// Save folder data
		$newdata = $this->_getParam('newdata');
		if (is_array($newdata)) {
			ivMessenger::add(ivMessenger::NOTICE, 'Folder data saved');
		}

		$this->view->assign('folder', $folder);
		$this->view->assign('sorts', ivFolder::getSortTypes());
		$this->view->assign('contentPath', $this->_getContentDirPath());
		$this->view->assign('uploaderType', $this->conf->get('/config/imagevue/settings/uploader/type'));

		$viewsNode = $this->conf->findByXPath('/config/imagevue/settings/defaultViewAs');
		$this->view->assign('views', $viewsNode->getValues());

		$_SESSION['imagevue']['lastManageLink'] = '?path=' . smart_urlencode($this->path);
	}

	/**
	 * Create new folder
	 *
	 */
	public function createAction()
	{
		ivMessenger::add(ivMessenger::NOTICE, 'Folder created');
		$this->_redirect('index.php?path=' . smart_urlencode($this->path));
	}

	/**
	 * Rename folder
	 *
	 */
	public function renameAction()
	{
		ivMessenger::add(ivMessenger::NOTICE, 'Folder renamed');
		$this->_redirect('index.php?path=' . smart_urlencode($this->path));
	}

	/**
	 * Delete folder
	 *
	 */
	public function deleteAction()
	{
		ivMessenger::add(ivMessenger::NOTICE, 'Folder deleted');
		$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php');
	}

	/**
	 * Move files
	 *
	 */
	public function moveFilesAction()
	{
		$targetPath = ivPath::canonizeRelative($this->_getParam('target', null, 'path'));
		$moved = 0;
		$mapper = ivMapperFactory::getMapper('file');
		$targetFolder = ivMapperFactory::getMapper('folder')->find($targetPath);
		if ($targetFolder) {
			foreach ($this->_getParam('selected', array()) as $filename) {
				if ($file = ivMapperFactory::getMapper('file')->find($this->path . $filename)) {
					$moved++;
				}
			}
			ivMessenger::add(ivMessenger::NOTICE, $moved . ' files moved to <a href="index.php?path=' . smart_urlencode($targetFolder->getPrimary()) . '">' . htmlspecialchars($targetFolder->getTitle()) . '</a>');
		}
		$this->_redirect('index.php?path=' . smart_urlencode($this->path));
	}

	/**
	 * Copy files
	 *
	 */
	public function copyFilesAction()
	{
		$targetPath = ivPath::canonizeRelative($this->_getParam('target', null, 'path'));
		$copied = 0;
		$mapper = ivMapperFactory::getMapper('file');
		$targetFolder = ivMapperFactory::getMapper('folder')->find($targetPath);
		if ($targetFolder) {
			foreach ($this->_getParam('selected', array()) as $filename) {
				if ($file = $mapper->find($this->path . $filename)) {
					$copied++;
				}
			}
			ivMessenger::add(ivMessenger::NOTICE, $copied . ' files copied to <a href="index.php?path=' . smart_urlencode($targetFolder->getPrimary()) . '">' . htmlspecialchars($targetFolder->getTitle()) . '</a>');
		}
		$this->_redirect('index.php?path=' . smart_urlencode($this->path));
	}

	/**
	 * Delete files
	 *
	 */
	public function deleteFilesAction()
	{
		ivMessenger::add(ivMessenger::NOTICE, count($this->_getParam('selected', array())) . ' files deleted');
		$this->_redirect('index.php?path=' . smart_urlencode($this->path));
	}

	/**
	 * Sets given file as preview image for given folder
	 *
	 */
	public function setPreviewAction()
	{
		$folder = ivMapperFactory::getMapper('folder')->find($this->path);
		$file = ivMapperFactory::getMapper('file')->find($this->path . decodeFilenameBase64($this->_getParam('id')));
		if ($folder && $file) {
			ivMessenger::add(ivMessenger::NOTICE, 'File ' . $file->name . ' is set as preview');
			$this->_redirect('index.php?path=' . smart_urlencode($folder->getPrimary()));
		} else if (!$this->_getParam('id') && !$this->_isXmlHttpRequest()) {
			ivMessenger::add(ivMessenger::NOTICE, 'Folder preview dropped');
			$this->_redirect('index.php?path=' . smart_urlencode($folder->getPrimary()));
		} else {
			ivMessenger::add(ivMessenger::NOTICE, 'File not found');
			$this->_redirect('index.php');
		}
	}

	/**
	 * Sort files
	 *
	 */
	public function sortFilesAction()
	{
		if ($this->_isXmlHttpRequest()) {
			$this->_setNoRender();
			echo 'OK';
		} else {
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
	}

	/**
	 * Sort folders
	 *
	 */
	public function sortFoldersAction()
	{
		if ($this->_isXmlHttpRequest()) {
			$this->_setNoRender();
			echo 'OK';
		} else {
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
	}

	/**
	 * Hide folder
	 *
	 */
	public function hideAction()
	{
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}

	/**
	 * Unhide folder
	 *
	 */
	public function unhideAction()
	{
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}

	/**
	 * Upload file
	 *
	 */
	public function uploadAction()
	{
		$this->_setNoRender();
		$imageData = $_FILES['Filedata'];
		if (!ivFilepath::matchSuffix($imageData['name'], $this->conf->get('/config/imagevue/settings/allowedext'))) {
			header("HTTP/1.1 403 Forbidden");
			echo "Error. Wrong extention";
		} else {
			echo "File {$imageData['name']} uploaded";
		}
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
				$path .= $key . '/';
				$folder = ivMapperFactory::getMapper('folder')->find($path);
				if (!$folder) {
					continue;
				}
				if ($lastCrumbKey == $key) {
					$numOfFiles = $folder->fileCount;
					$totalNumOfFiles = $folder->totalFileCount;
					$suffix =  '[' . (($numOfFiles == $totalNumOfFiles) ? $numOfFiles : $numOfFiles . '/' . $totalNumOfFiles . '') . ']';
					$crumbs->push((strip_icon($folder->getTitle()) ? $folder->getTitle() : $folder->name), 'index.php?path=' . smart_urlencode($path), $suffix, ($folder->isHidden() ? 'hidden' : ''));
				} else {
					$crumbs->push((strip_icon($folder->getTitle()) ? $folder->getTitle() : $folder->name), 'index.php?path=' . smart_urlencode($path), '', ($folder->isHidden() ? 'hidden' : ''));
				}
			}
		}
	}

}
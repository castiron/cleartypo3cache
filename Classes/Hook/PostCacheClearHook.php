<?php

namespace CIC\Cleartypo3cache\Hook;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PostCacheClearHook
 * @package CIC\Cleartypo3cache\Hook
 */
class PostCacheClearHook {

	/**
	 *
	 */
	public function execute() {
		$this->runPostClearCommands();
	}

	/**
	 *
	 */
	protected function runPostClearCommands() {
		/**
		 * @var \CIC\Cleartypo3cache\Service\CommandRunner $runner
		 */
		$runner = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get('CIC\\Cleartypo3cache\\Service\\CommandRunner');
		$runner->runConfiguredCommands();
	}
}

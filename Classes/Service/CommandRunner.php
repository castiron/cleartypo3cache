<?php

namespace CIC\Cleartypo3cache\Service;

use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Class CommandRunner
 * @package CIC\Cleartypo3cache\Util
 */

class CommandRunner {
	/**
	 * Run configured commands
	 */
	public function runConfiguredCommands() {
		if ($commands = $this->configuredCommands()) {
			$this->runCommands($commands);
		}
	}

	/**
	 * @param array $cmdOptions Array with 'pwd' and 'command' to run
	 */
	protected function runCommand($cmdOptions) {
		$cmd = $cmdOptions['command'];
		if ($pwd = realpath($cmdOptions['pwd'])) {
			$cmd = 'cd ' . $pwd . ' && ' . escapeshellcmd($cmd);
		}
		if ($paths = $cmdOptions['paths']) {
			$cmd = $this->addPathsCommand($cmdOptions['paths']) . ' && ' . $cmd;
		}
		CommandUtility::exec($cmd, $output, $returnVal);
	}

	/**
	 * @param $paths
	 * @return string
	 */
	protected function addPathsCommand($paths) {
		return escapeshellcmd( 'export PATH=' . $paths ) . ':$PATH';
	}

	/**
	 * Run an array of commands using runCommand()
	 *
	 * @param $commands
	 */
	protected function runCommands($commands) {
		foreach($commands as $cmdOptions) {
			$this->runCommand($cmdOptions);
		}
	}

	/**
	 * Gets commands that are configured to run after clearing cache
	 *
	 * @return array
	 */
	protected function configuredCommands() {
		$cmds = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cleartypo3cache']['postClearCommands'];
		return $cmds && is_array($cmds) ? $cmds : array();
	}
}

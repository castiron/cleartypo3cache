<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE media GmbH <dev@aoemedia.de>
 *  Adapted for later TYPO3 versions by Cast Iron Coding <contact@castironcoding.com>
 *  All rights reserved
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
namespace CIC\Cleartypo3cache;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined ('TYPO3_cliMode')) die ('Access denied: CLI only.');


/**
 * Class ClearCache
 *
 * @package CIC\Cleartypo3cache
 */
class ClearCache extends \TYPO3\CMS\Core\Controller\CommandLineController {
	/**
	 * constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->cli_options = array_merge($this->cli_options, array());
		$this->cli_help = array_merge($this->cli_help, array(
			'name' => 'Clear TYPO3 Cache via CLI',
			'synopsis' => $this->extKey . ' cache-command',
			'description' => 'This script can clear the complete TYPO3-cache (attention: CLI-be_user must have the rights (TS: "options.clearCache.all=1" and "options.clearCache.pages=1") to do this)',
			'examples' => 'typo3/cli_dispatch.phpsh ' . $this->extKey . ' [all|pages]',
			'author' => '(c) 2010 AOE media GmbH <dev@aoemedia.de>; Modified for later TYPO3 versions by Cast Iron Coding;',
		));
	}

	/**
	 * @param $argv
	 * @return int
	 */
	public function cli_main($argv) {
		$this->init();

		$shellExitCode = 0;
		try {
			// select called function
			switch ($this->getAction()) {
				case 'all':
				case 'pages':
					$this->clearTypo3Cache( $this->getAction() );
					break;
				default:
					$this->cli_help();
					break;
			} // END switch
		} catch (Exception $e) {
			$shellExitCode = 1;
		}

		return $shellExitCode;
	}

	/**
	 * Clear caches
	 *
	 * @param string $cacheCmd
	 */
	protected function clearTypo3Cache($cacheCmd) {
		if($cacheCmd === 'all') {
			$this->msg('Flushing caches with CacheManager...');
			GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->flushCaches();

			$this->msg('Destroying all extbase cache tables...');
			$this->forceDestroyReflectionCache();

			$this->msg('Trashing typo3temp/Cache/*...');
			$this->forceEmptyTempDir();
		} else {
			$this->msg('Attempting to flush cache group ' . $cacheCmd . ' with CacheManager');
			GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->flushCachesInGroup($cacheCmd);
		}
		$this->msg('All done');
	}

	protected function msg($msg) {
		echo $msg . "\n";
	}

	/**
	 *
	 */
	protected function forceEmptyTempDir() {
		$cmd = 'rm -rf ' . PATH_site . 'typo3temp/Cache/*';
		CommandUtility::exec($cmd);
	}

	/**
	 * TRUNCATE all tables that are named like 'cf_extbase%'.  This
	 * has occasionally been necessary in the past after running
	 * automated deployments.
	 */
	protected function forceDestroyReflectionCache() {
		$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLES LIKE "cf_extbase%"');
		$truncate = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
			$truncate[] = $row[0];
		}
		if (count($truncate)) { foreach($truncate as $table) {
			$this->msg($table);
			$GLOBALS['TYPO3_DB']->sql_query("TRUNCATE TABLE `$table`");
		}}
	}

	/**
	 * @return string
	 */
	private function getAction() {
		return (string) $this->cli_args['_DEFAULT'][1];
	}
	/**
	 * do initialization
	 */
	private function init() {
		// validate input
		$this->cli_validateArgs();
	}

}

$obj = GeneralUtility::makeInstance('CIC\\Cleartypo3cache\\ClearCache');
exit($obj->cli_main($_SERVER['argv']));

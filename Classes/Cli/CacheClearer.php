<?php
namespace CIC\Cleartypo3cache\Cli;

use CIC\Cleartypo3cache\Service\CommandRunner;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CacheClearer
 *
 * @package CIC\Cleartypo3cache
 */
class CacheClearer extends \Symfony\Component\Console\Command\Command {
	/**
	 * @var \CIC\Cleartypo3cache\Service\CommandRunner
	 */
	var $commandRunner;

	/**
	 * constructor
	 */
	public function configure() {
		$this->setDescription('This script can clear the complete TYPO3-cache');
        $this->addArgument('action', InputArgument::REQUIRED, '`all` or `pages`');
	}

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
	public function execute(InputInterface $input, OutputInterface $output) {
		if (!\TYPO3\CMS\Core\Core\Environment::isCli()) die ('Access denied: CLI only.');

	    $this->commandRunner = GeneralUtility::makeInstance(CommandRunner::class);

		$action = $input->getArgument('action');
        switch ($action) {
            case 'all':
            case 'pages':
                $this->clearTypo3Cache($action);
                break;
            default:
                throw new \Exception('Specify `all` or `pages` for action argument');
        }
	}

	/**
	 * Clear caches
	 *
	 * @param string $cacheCmd
	 */
	protected function clearTypo3Cache($cacheCmd) {
		if($cacheCmd === 'all') {
			$this->flushAll();
		} else {
			$this->flushCacheGroup($cacheCmd);
		}
	}

	/**
	 * Flushes all registered caches and trashes a few sometime-problematic cache files manually
	 */
	protected function flushAll() {
		GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->flushCaches();
		$this->postFlushAll();
	}

	/**
	 * Runs after flushAll()
	 */
	protected function postFlushAll() {
		$this->commandRunner->runConfiguredCommands();
		$this->forceDestroyReflectionCache();
		$this->forceEmptyTempDir();
		$this->clearCompressorFiles();
	}

	/**
	 * @param $grp
	 */
	protected function flushCacheGroup($grp) {
		GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->flushCachesInGroup($grp);
	}

	/**
	 *
	 */
	protected function forceEmptyTempDir() {
	#	$cmd = 'rm -rf ' . PATH_site . 'typo3temp/var/cache/*';
	#	CommandUtility::exec($cmd);
	}

	/**
	 * @return string
	 */
	protected function compressorFilesPath() {
		return PATH_site . 'typo3temp/compressor';
	}

	/**
	 *
	 */
	protected function clearCompressorFiles() {
		$cmd = '[ -d ' . $this->compressorFilesPath() . ' ] && rm -rf ' . $this->compressorFilesPath() . '/*';
		CommandUtility::exec($cmd);
	}

	/**
	 * TRUNCATE all tables that are named like 'cf_extbase%'.  This
	 * has occasionally been necessary in the past after running
	 * automated deployments.
	 */
	protected function forceDestroyReflectionCache() {
//      TODO: implement in version 9 -- easier said than done
//		$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLES LIKE "cf_extbase%"');
//		$truncate = array();
//		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
//			$truncate[] = $row[0];
//		}
//		if (count($truncate)) { foreach($truncate as $table) {
//			$GLOBALS['TYPO3_DB']->sql_query("TRUNCATE TABLE $table");
//		}}
	}
}

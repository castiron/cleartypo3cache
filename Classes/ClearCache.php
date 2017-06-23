<?php
namespace CIC\Cleartypo3cache;

use CIC\Cleartypo3cache\Service\CacheDestroyer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (TYPO3_MODE !== 'BE') die ('Access denied: CLI only.');

/**
 * Class ClearCache
 *
 * @package CIC\Cleartypo3cache
 */
class ClearCache extends \TYPO3\CMS\Core\Controller\CommandLineController {
    /**
     * @var CacheDestroyer
     */
    protected $cacheDestroyer;

    /**
     * constructor
     */
    public function __construct() {
        $this->cli_setArguments($_SERVER['argv']);
        $this->cacheDestroyer = GeneralUtility::makeInstance('CIC\Cleartypo3cache\Service\CacheDestroyer');
        $this->cli_options = array_merge($this->cli_options, array());
        $this->cli_help = array_merge($this->cli_help, array(
            'name' => 'tx_cleartypo3cache_cli_cli',
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
                    $this->cacheDestroyer->clearTypo3Cache( $this->getAction() );
                    break;
                default:
                    $this->cli_help();
                    break;
            } // END switch
        } catch (\Exception $e) {
            $shellExitCode = 1;
        }

        return $shellExitCode;
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

$obj = GeneralUtility::makeInstance('CIC\Cleartypo3cache\ClearCache');
exit($obj->cli_main($_SERVER['argv']));

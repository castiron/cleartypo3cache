<?php namespace CIC\Cleartypo3cache\Service;

use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CacheDestroyer
 * @package CIC\Cleartypo3cache\Service
 */
class CacheDestroyer {

    static $cacheFilesDir = 'typo3temp/var/Cache';

    /**
     * Clear caches
     *
     * @param string $cacheCmd
     */
    public function clearTypo3Cache($cacheCmd) {
        if($cacheCmd === 'all') {
            $this->clearAllCache($cacheCmd);
            return;
        }
        $this->clearCacheByCommand($cacheCmd);
    }

    /**
     * @param $cacheCmd
     */
    protected function clearCacheByCommand($cacheCmd) {
        GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->flushCachesInGroup($cacheCmd);
        $this->callPostClearHooks($cacheCmd);
    }

    /**
     * Clearing all the cache is a bit more nuclear than the other options
     * @param $cacheCmd
     */
    public function clearAllCache($cacheCmd = 'all') {
        GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->flushCaches();
        $this->callPostClearHooks($cacheCmd);
        $this->forceDestroyReflectionCache();
        if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cleartypo3cache']['forceRemoveTempCacheFiles']) {
            static::quickDeleteDir(static::$cacheFilesDir);
        }
    }

    /**
     * Call any post-cache-clearing hooks from extensions, etc. This is lifted more or less verbatim from the
     * DataHandler where it is normally called in the TYPO3 core;
     *
     * @param $cacheCmd
     */
    protected function callPostClearHooks($cacheCmd) {
        $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'])) {
            $_params = array('cacheCmd' => strtolower($cacheCmd));
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'] as $_funcRef) {
                GeneralUtility::callUserFunction($_funcRef, $_params, $dataHandler);
            }
        }
    }

    /**
     * @param $dir
     */
    protected static function quickDeleteDir($dir) {
        /**
         * Only do this inside typo3temp
         */
        if (strpos($dir, 'typo3temp') !== 0) {
            return;
        }

        $directory = PATH_site . $dir;
        $offFolder = "$directory-off";

        /**
         * Make sure the backup folder doesn't exist
         */
        CommandUtility::exec('[ -d ' . $offFolder . ' ] && rm -rf ' . $offFolder);

        /**
         * Move the current folder away to effectively delete it instantly
         */
        CommandUtility::exec('[ -d ' . $directory . ' ] && mv ' . $directory . ' ' . $offFolder);

        /**
         * Remove the moved folder now that it's out of circulation
         */
        CommandUtility::exec('[ -d '.$offFolder.' ] && rm -rf ' . $offFolder);
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
            $GLOBALS['TYPO3_DB']->sql_query("TRUNCATE TABLE $table");
        }}
    }
}

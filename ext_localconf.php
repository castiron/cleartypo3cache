<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
global $TYPO3_CONF_VARS, $_EXTKEY;

if (TYPO3_MODE == 'BE') {
	## Setting up script that can be run through cli_dispatch.phpsh
	$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['cleartypo3cache'] = array(
		'EXT:cleartypo3cache/Classes/ClearCache.php',
		'_CLI_cleartypo3cache'
	);
}


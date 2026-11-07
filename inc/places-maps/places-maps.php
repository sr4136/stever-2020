<?php
/**
 * Places maps bootstrap loader.
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/places-maps-registration.php';
require_once __DIR__ . '/places-maps-data.php';
require_once __DIR__ . '/places-maps-admin.php';
require_once __DIR__ . '/places-maps-frontend.php';

// Optional imports module. Uncomment to enable SQL import and one-click import cleanup tools.
// require_once __DIR__ . '/places-maps-imports.php';

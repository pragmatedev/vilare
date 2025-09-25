<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

define('VILARE_VERSION', '0.1.4');
define('VILARE_ROOT', str_replace(ABSPATH, '/', dirname(__DIR__, 1)));
define('VILARE_PATH', dirname(__DIR__, 1));
define('VILARE_URI', home_url(VILARE_ROOT));
define('VILARE_HMR_HOST', 'http://localhost:5173');
define('VILARE_HMR_URI', VILARE_HMR_HOST . VILARE_ROOT);
define('VILARE_DIST_PATH', VILARE_PATH . '/dist');
define('VILARE_DIST_URI', VILARE_URI . '/dist');

require_once VILARE_PATH . '/inc/bootstrap.php';

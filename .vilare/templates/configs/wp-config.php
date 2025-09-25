<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
require_once __DIR__ . '/wp-config-db.php';

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'QZ6pn/S&ZrpK-)(c3BF|rI:/~M&h&UJC;glTLkDtv:|}Bi;!s6nmEi83z1nc81@M' );
define( 'SECURE_AUTH_KEY',   'kfA~CCEX78Em|NNhq@^@^n^3BDutNBIH:pkIL7!?]zh&ojRKC_M[4#{Uft0S%HN1' );
define( 'LOGGED_IN_KEY',     'G[irg?x4*S@P_dda|0<h.X8rii{LUNt]t9ozCTBV-4kf-GEYafo%*n?ARWjRx^i$' );
define( 'NONCE_KEY',         ':8=N[.OQV^2a?s5=.Gcq?{Ub;+(h@Ob Kw<Z7V?@{m7$sGT`9F(ob7f[?&%rNv[=' );
define( 'AUTH_SALT',         'W>wO/^RrW#rJHTz3Q*,wwaMvLUtZ9-6I1sVi7Xw1Zs:7n$F;QRIX`eE7_E{H_+8E' );
define( 'SECURE_AUTH_SALT',  '_wa,3bMaHap5_VVQ4Tl0#O CPN{wgKt%aB?]|M{pA$nKYd/cn7Z2N(y-scgIqzc$' );
define( 'LOGGED_IN_SALT',    'Wv.w$zi#FBetTT{$(6?xh*/ 0/Di7>U2]%:K51Am||lJs#G2eWadx|#{M@8&2ybE' );
define( 'NONCE_SALT',        '[|%q<fnJa7vH}hx6PujWIW^%Ygqop[f2dFylQ0m{~PalSBfT%_F1jzbd&dFFI9X|' );
define( 'WP_CACHE_KEY_SALT', 'BRP[{P[@3GA48.Aqt no8-wvt95k]1W+$tkw!cK<v)R->fez;At<K8b)KR({4ze|' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/* Add any custom values between this line and the "stop editing" line. */

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}

if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
	define( 'WP_DEBUG_DISPLAY', true );
}

if ( ! defined( 'WP_DEBUG_LOG' ) ) {
	define( 'WP_DEBUG_LOG', true );
}

if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
	define( 'WP_ENVIRONMENT_TYPE', 'development' );
}

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) ) {
	define( 'WP_AUTO_UPDATE_CORE', false );
}

if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', false );
}

if ( ! defined( 'VILARE_ENABLE_WEBP_CONVERSION' ) ) {
	define( 'VILARE_ENABLE_WEBP_CONVERSION', true );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'm07d3698320560');

/** MySQL database username */
define('DB_USER', 'lsnadmin');

/** MySQL database password */
define('DB_PASSWORD', 'x84a9DTPM57Rz9');

/** MySQL hostname */
define('DB_HOST', 'thelawnetwork.cn35tmv7l8la.us-east-1.rds.amazonaws.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '#)}2w43cS--2=XoWYwgmQJ!`:KS}|O 7#Tap&+pKl%G*ob)@=Hhz|IuM!v<Jb@5<');
define('SECURE_AUTH_KEY',  '(zA2z8|cGTaO-TZs`P>U!:}v>5+9H;F#8~XIs3=gCdiR*^9uCF]SP[26?_++.{@/');
define('LOGGED_IN_KEY',    'B?D ?3IH>X?1qfifq)y- (l/yvDNEkvKsW>@fd>8zLqaM=o;cVWn2!(-b7Py>-#O');
define('NONCE_KEY',        '4P?+EpVSx33w>UKr2wI|r3I;eU=4K+|t&#|*NvPb]:hUJd6p%/hRd(Jmc06%z9!N');
define('AUTH_SALT',        '>4E?@Tw@n*6L+=t~s6L{f7PvAu(c/l1y,eq/ieuf[}{P|jX[C2lk G|$wINl#-Zv');
define('SECURE_AUTH_SALT', '+hTPMH-7Ew]_h-BMU1x))]^kOup,v(Z[?g@g`CqmB`Rl@B1;d]m+%|sI-z(gJ$=h');
define('LOGGED_IN_SALT',   '&&/go{$-4~8 P-|m- g`l}uPV[7t4i[0u#0:udPbfAo0]PjaBaf`A.RySv-]qK3X');
define('NONCE_SALT',       '[W|qIEInEp+oZv2it-o /Ws(LW2|ByQa<AsP&pugtmY@N|+~dK9:eT|BFWcnC!Q+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

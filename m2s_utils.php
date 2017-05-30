<?php
/*
Plugin Name: M² Solutions OOP Utilities
Plugin URI: https://m-squared-solutions.eu/wordpress-plugins/oop-utils
Description: A simple collection of classes for developers who prefer a more OOP approach.
Version: 1.0.0
Author: Michael Marcenich
Author URI: https://m-squared-solutions.eu
License: GPLv2 or later
Text Domain: m2s-utils
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

define('M2S_UTILS__VERSION', '1.0.0');
define('M2S_UTILS__MIN_WP_VERSION', '4.7');
define('M2S_UTILS__DIR', plugin_dir_path(__FILE__));

if ( version_compare( $GLOBALS['wp_version'], M2S_UTILS__MIN_WP_VERSION, '<' ) && (!defined('M2S_UTILS__HIDE_VERSION_MESSAGE') || !M2S_UTILS__HIDE_VERSION_MESSAGE) ) {
	load_plugin_textdomain( 'm2s-utils', false, basename( M2S_UTILS__DIR ) . '/l10n' ); // plugin i18n

	add_action( 'admin_notices', function() {
		printf(
			'<div class="error">%s</div>',
			sprintf('<p>%s</p><p>%s</p><br/><code>define(\'M2S_UTILS__HIDE_VERSION_MESSAGE\', true);</code>',
				sprintf(
					esc_html__( 'You are using WordPress %s. M2SUtils is not yet tested on WordPress versions below %s. Use at own risk.', 'm2s-utils'),
					esc_html( $GLOBALS['wp_version'] ),
					M2S_UTILS__MIN_WP_VERSION
				),
				sprintf(
					esc_html__('To disable this message paste the following code into %s at the beginning of the file:', 'm2s-utils'),
					'<code>'.__FILE__.'</code>'
				)
			)
		);
	} );
}

function m2s_utils_autoload($dir) {
	if (is_dir($dir) && $handle = opendir($dir)) {
		while(($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$info = pathinfo($file);
			$file = $dir.'/'.$file;
			if (filetype($file) === 'file' && $info['extension'] === 'php') {
				require_once $file;
			} else if (filetype($file) === 'dir') {
				m2s_utils_autoload($file);
			}
		}
	}
}

m2s_utils_autoload(M2S_UTILS__DIR.'Utils');
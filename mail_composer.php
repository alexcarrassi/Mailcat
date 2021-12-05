<?php
/**
 * Mail Composer
 *
 * @package           Mail_composer
 * @author            ArkDesign
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Mail Composer
 * Plugin URI:        https://www.arkdesign.nl
 * Description:       Create and send your mails, your way
 * Version:           0.1
 * Requires at least: 5.1
 * Author:            Arkdesign
 * Author URI:        https://www.arkdesign.nl
 * Text Domain:       ark_mail_composer
 * Domain Path:       /languages
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Copyright 2021  Alex Zoutewelle
 * Copyright 2013-2021 Arkdesign
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//if(!defined(ABSPATH)) {
//    die();
//}

define("ARK_MAIL_COMPOSER_ROOT_DIR", __DIR__);
define("ARK_MAIL_COMPOSER_ROOT_URI", plugin_dir_url(__FILE__));
define("MAILCAT_CPT_TYPE", "mailcat_mail");



register_activation_hook(__FILE__, array('Ark_Mail_Composer_Extension', 'activate'));
register_deactivation_hook(__FILE__, array('Ark_Mail_Composer_Extension', 'deactivate'));

class Ark_Mail_Composer_Extension {
    public function __construct() {
        add_action('init', array($this, 'init'));

        include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/class-ark_mail_cpt.php");

        if(is_admin()) {
            include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/mailcat_admin_utils.php");

            include_once(ARK_MAIL_COMPOSER_ROOT_DIR . "/includes/admin/admin_notices.php");
        }

    }

    public function init() {
    }

    public function activate() {

    }

    public function deactivate() {

    }
}

$GLOBALS['ark_mail_composer'] = new Ark_Mail_Composer_Extension();

?>


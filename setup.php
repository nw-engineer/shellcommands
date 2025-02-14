<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 shellcommands plugin for GLPI
 Copyright (C) 2009-2022 by the shellcommands Development Team.

 https://github.com/InfotelGLPI/shellcommands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of shellcommands.

 shellcommands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 shellcommands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_SHELLCOMMANDS_VERSION', '4.0.0-rc2');

if (!defined("PLUGIN_SHELLCOMMANDS_DIR")) {
   define("PLUGIN_SHELLCOMMANDS_DIR", Plugin::getPhpDir("shellcommands"));
   define("PLUGIN_SHELLCOMMANDS_NOTFULL_DIR", Plugin::getPhpDir("shellcommands",false));
   define("PLUGIN_SHELLCOMMANDS_WEBDIR", Plugin::getWebDir("shellcommands"));
}

// Init the hooks of the plugins -Needed
function plugin_init_shellcommands() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['shellcommands'] = true;
   $PLUGIN_HOOKS['change_profile']['shellcommands'] = ['PluginShellcommandsProfile', 'changeProfile'];
   //Clean Plugin on Profile delete
   $PLUGIN_HOOKS['pre_item_purge']['shellcommands'] = ['Profile' => ['PluginShellcommandsProfile', 'purgeProfiles']];

   $PLUGIN_HOOKS['add_css']['shellcommands']        = ['shellcommands.css'];
   $PLUGIN_HOOKS['add_javascript']['shellcommands'] = ['shellcommands.js'];

   if (Session::getLoginUserID()) {
      Plugin::registerClass('PluginShellcommandsProfile', ['addtabon' => 'Profile']);
      if (Session::haveRight("plugin_shellcommands", READ)) {
         // Menu
         $PLUGIN_HOOKS['menu_entry']['shellcommands']          = 'front/menu.php';
         $PLUGIN_HOOKS['menu_toadd']['shellcommands']          = ['tools' => 'PluginShellcommandsShellcommand'];
      }

      $PLUGIN_HOOKS['use_massive_action']['shellcommands'] = 1;

      $PLUGIN_HOOKS['post_init']['shellcommands'] = 'plugin_shellcommands_postinit';

   }

   $PLUGIN_HOOKS['webservices']['shellcommands'] = 'plugin_shellcommands_registerWebservicesMethods';
}

// Get the name and the version of the plugin - Needed
function plugin_version_shellcommands() {
   return [
      'name'         => _n('Shell Command', 'Shell Commands', 2, 'shellcommands'),
      'version'      => PLUGIN_SHELLCOMMANDS_VERSION,
      'license'      => 'GPLv2+',
      'oldname'      => 'cmd',
      'author'       => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
      'homepage'     => 'https://github.com/InfotelGLPI/shellcommands',
      'requirements' => [
         'glpi' => [
            'min' => '10.0',
            'max' => '11.0',
            'dev' => false
         ]
      ]
   ];
}

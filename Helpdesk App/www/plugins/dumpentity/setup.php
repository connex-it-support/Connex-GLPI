<?php
/*
 * @version $Id: setup.php 144 2015-09-06 18:05:31Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Dumpentity is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Dumpentity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Dumpentity. If not, see <http://www.gnu.org/licenses/>.

 @package   dumpentity
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2015 Dumpentity plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
*/

function plugin_init_dumpentity() {
   global $PLUGIN_HOOKS,$DB;

   $PLUGIN_HOOKS['csrf_compliant']['dumpentity'] = true;

   Plugin::registerClass('PluginDumpentityProfile',
                         array('addtabon' => 'Profile'));

   $PLUGIN_HOOKS['change_profile']['dumpentity'] = array('PluginDumpentityProfile', 'select');

   $PLUGIN_HOOKS['pre_item_purge']['dumpentity'] = array('Profile' => array('PluginDumpentityProfile',
                                                                            'cleanProfile'),
                                                         'Entity'  => array('PluginDumpentityClient',
                                                                            'cleanEntity'));

   if (isset($_SESSION["glpi_plugin_dumpentity_profile"])) {
      $PLUGIN_HOOKS['menu_entry']['dumpentity'] = true;
   }

   if (Session::haveRight("config","w") || Session::haveRight("profile","w")) {

      $PLUGIN_HOOKS['submenu_entry']['dumpentity']['options']['model']
         = array('title'  => _n('Model', 'Models', 2),
                 'page'   => '/plugins/dumpentity/front/model.php',
                 'links'  => array('search' => '/plugins/dumpentity/front/model.php',
                                   'add'    => '/plugins/dumpentity/front/model.form.php'));

      $PLUGIN_HOOKS['submenu_entry']['dumpentity']['options']['client']
         = array('title'  => _n('Client', 'Clients', 2, 'dumpentity'),
                 'page'   => '/plugins/dumpentity/front/client.php',
                 'links'  => array('search' => '/plugins/dumpentity/front/client.php',
                                   'add'    => '/plugins/dumpentity/front/client.form.php'));
   }
}


function plugin_version_dumpentity() {

   return array('name'           => __('Dump entity', 'dumpentity'),
                'version'        => '1.4.0',
                'author'         => 'Remi Collet, Nelly Mahu-Lasson',
                'license'        => 'GPLv3+',
                'homepage'       => 'https://forge.indepnet.net/projects/dumpentity',
                'minGlpiVersion' => '0.84.5');
}


function plugin_dumpentity_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      echo "This plugin requires GLPI >= 0.84 and GLPI < 0.85";
      return false;
   }
   return true;
}


function plugin_dumpentity_check_config(){
   return true;
}
?>

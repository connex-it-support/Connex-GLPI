<?php
/*
 * @version $Id: checks.php 144 2015-09-06 18:05:31Z yllen $
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

define('DO_NOT_CHECK_HTTP_REFERER', 1);
include ("../../../inc/includes.php");

if (isset($_GET['check'])) {
   if ($_GET['version'] >= PluginDumpentityModel::LOADENTITY_VERSION_REQUIRE
       && $_GET['dbschema'] == PluginDumpentityModel::DUMPENTIY_DB_SCHEMA) {
      echo 0;
   } else {
      if ($_GET['version'] < PluginDumpentityModel::LOADENTITY_VERSION_REQUIRE) {
         echo 1;
      } else {
         echo 2;
      }
   }

} else if (isset($_GET['check_time'])) {
   $client = new PluginDumpentityClient();
   if (!$client->getFromDBForIP()) {
      echo '1';
   } else {
      if (!$client->plugin_dumpentity_checkTimeExclusion()) {
         echo '0';
      } else {
         echo '1';
      }
   }

} else if (isset($_GET['is_recursive'])) {
   $client = new PluginDumpentityClient();
   if (!$client->getFromDBForIP()) {
      echo '0';
   } else {
      if (!$client->fields['is_recursive']) {
         echo '0';
      } else {
         echo '1';
      }
   }
}
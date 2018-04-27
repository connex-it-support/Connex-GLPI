<?php
/*
 * @version $Id: getcsv.php 144 2015-09-06 18:05:31Z yllen $
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

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 1; // Really a big SQL request

ini_set("max_execution_time", "0");

define('DO_NOT_CHECK_HTTP_REFERER', 1);
include ("../../../inc/includes.php");

if (isset($_GET["gzip"])) {
   $gzip = $_GET["gzip"];
} else {
   $gzip = 0;
}

if (isset($_GET["encode"])) {
   $encode = $_GET["encode"];
} else {
   $encode = '';
}

if (isset($_GET["table"])) {
   $table = $_GET["table"];
} else {
   $table = "";
}

$model = new PluginDumpentityModel();

if (isset($_SESSION["glpi_plugin_dumpentity_profile"])) {
   // called from interface


   if (isset($_GET["table"])) {
      $table      = $_GET["table"];
      $model->getFromDB($_SESSION["glpi_plugin_dumpentity_profile"]["models_id"]);
      $recursive  = false;
      $entity     = $_SESSION["glpiactive_entity"];
   } else {
      header('HTTP/1.0 404 Not found');
      die('Missing parameter');
   }

} else {
   // probably wget from a client
   $client = new PluginDumpentityClient();
   if (!$client->getFromDBForIP()) {
      header('HTTP/1.0 403 Forbidden');
      die('Unknown client');
   }
   if (!$model->getFromDB($client->fields['models_id'])) {
      header('HTTP/1.0 403 Forbidden');
      die('Unknown model');
   }
   $entity     = $client->fields["entities_id"];
   $recursive  = $client->fields["is_recursive"];
   if (isset($_GET['entity'])) {
      if ($entity == $_GET['entity']
          || ($recursive && in_array($_GET['entity'], getSonsOf('glpi_entities', $entity)))) {
         $entity    = $_GET['entity'];
         $recursive = false;
      } else {
         header('HTTP/1.0 403 Forbidden');
         die('Unknown entity');
      }
   }
}

$tables = PluginDumpentityModel::getTables();
if (empty($table)) {
   // List of available tables (not compressed)

   header('Content-type: text/comma-separated-values');
   header('Content-Disposition: attachment; filename="tables.csv"');

   echo "name;description\r\n";

   foreach ($tables as $name => $descr) {
      if ($model->fields[$name])
      echo "$name;\"$descr\"\r\n";
   }

} else if (isset($tables[$table]) && $model->fields[$table]) {
   // One Table dump

   $file = PluginDumpentityModel::getCSV($table, $entity, $recursive, $gzip, $encode);

   if ($gzip) {
      header('Content-type: application/x-gzip');
   } else {
      header('Content-type: text/comma-separated-values');
   }
   header("Content-Length: " . filesize($file));
   header('Content-Disposition: attachment; filename="' . $table .($gzip ? '.csv.gz"' : '.csv"'));
   readfile($file);
   unlink($file);

} else {
   header('HTTP/1.0 403 Forbidden');
   die('Unknown table');
}

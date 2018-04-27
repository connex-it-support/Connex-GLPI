<?php
/*
 * @version $Id: getzip.php 144 2015-09-06 18:05:31Z yllen $
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


if (isset($_GET["encode"])) {
   $encode = $_GET["encode"];
} else {
   $encode = '';
}

if (!class_exists("ZipArchive")) {
   die("Not supported");
}

define('DO_NOT_CHECK_HTTP_REFERER', 1);
include ("../../../inc/includes.php");

$model  = new PluginDumpentityModel();
if (isset($_SESSION['glpi_plugin_dumpentity_profile'])
    && $model->getFromDB($_SESSION['glpi_plugin_dumpentity_profile']['models_id'])) {
   $entity = $_SESSION['glpiactive_entity'];
   $todel  = array();

   $filename = PLUGIN_DUMPENTITY_UPLOAD_DIR."$entity-export.zip";
   $todel[]  = $filename;

   $zip = new ZipArchive();
   if (!$zip->open($filename, ZIPARCHIVE::CREATE)) {
      die("cannot create $filename\n");
   }

   $filelist = PLUGIN_DUMPENTITY_UPLOAD_DIR."$entity-tables.csv";
   $ficlist  = fopen($filelist, "wb");
   if ($ficlist) {
      fwrite($ficlist, "name;description;size\r\n");
   }

   foreach (PluginDumpentityModel::getTables() as $table => $descr) {
      if ($model->fields[$table]) {
         $file = PluginDumpentityModel::getCSV($table, $entity, 0, 0, $encode);
         if (($size=filesize($file)) > 0) {
            $zip->addFile($file, $table.".csv");
         }
         if ($ficlist) {
            fwrite($ficlist, "$table;\"$descr\";$size\r\n");
         }
         $todel[] = $file;
      }
   }

   if ($ficlist) {
      fclose($ficlist);
      $zip->addFile($filelist, "tables.csv");
      $todel[] = $filelist;
   }
   $zip->close();

   header("Content-type: application/zip");
   header("Content-Length: " . filesize($filename));
   header("Content-Disposition: inline; filename=export.zip");
   readfile($filename);

   // Clean work files
   foreach ($todel as $file) {
      unlink($file);
   }

} else {
   header('HTTP/1.0 404 Not found');
   die("Not Found");
}

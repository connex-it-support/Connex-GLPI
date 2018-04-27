<?php
/*
 * @version $Id: index.php 144 2015-09-06 18:05:31Z yllen $
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

include ("../../inc/includes.php");

Html::header(__('Dump entity', 'dumpentity'), $_SERVER['PHP_SELF'], 'plugins', 'dumpentity');

if (isset($_GET["gzip"])) {
   $gzip = $_GET["gzip"];
} else  {
   $gzip  =0;
}

$model = new PluginDumpentityModel();

if (Session::haveRight('config', 'r') && !isset($_GET['gzip'])) {
   echo "<table class='tab_cadre' cellpadding='5'><tr class='tab_bg_1'>";

   if (isset($_SESSION["glpi_plugin_dumpentity_profile"])
       && $model->getFromDB($_SESSION["glpi_plugin_dumpentity_profile"]["models_id"])) {
      echo "<tr><th>".__('Dump entity', 'dumpentity')."</th></tr>";
      echo "<tr class='tab_bg_2'><td class='center'><a href='?gzip=0'>".
             $model->getName()."</a></td></tr>";
   }

   echo "<tr><th>".__('Dump entity plugin configuration', 'dumpentity')."</th></tr>\n";

   echo "<tr class='tab_bg_1'><td class='center'>";
   echo "<a href='front/model.php'>".__('Models management', 'dumpentity')."</a></td/></tr>";

   echo "<tr class='tab_bg_1'><td class='center'>";
   echo "<a href='front/client.php'>".__('Rights management by client computer', 'dumpentity').
        "</a></td/></tr>";

   echo "</table>";

} else if (isset($_SESSION["glpi_plugin_dumpentity_profile"])
    && $model->getFromDB($_SESSION["glpi_plugin_dumpentity_profile"]["models_id"])) {
   $model->showForUser($gzip);

} else {
   Html::displayRightError();
}

Html::footer();

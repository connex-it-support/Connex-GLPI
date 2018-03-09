<?php
/*
 * @version $Id: model.form.php 144 2015-09-06 18:05:31Z yllen $
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

include_once ("../../../inc/includes.php");

Session::checkRight("profile","r");

require_once "../inc/model.class.php";
$model = new PluginDumpentityModel();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_POST["add"]) && !empty($_POST["name"])) {
   $model->check(-1, 'w', $_POST);
   $model->add($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $model->check($_POST['id'], 'd');
   $model->delete($_POST);
   $model->redirectToList();

} else  if (isset($_POST["update"])) {
   $model->check($_POST['id'], 'w');
   $model->update($_POST);
   Html::back();
}

Html::header($model->getTypeName(2), $_SERVER["PHP_SELF"], 'plugins', 'dumpentity', 'model');

$model->showForm($_GET["id"]);

Html::footer();

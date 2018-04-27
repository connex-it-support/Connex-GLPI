<?php
/*
 * @version $Id: client.class.php 144 2015-09-06 18:05:31Z yllen $
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

class PluginDumpentityClient extends CommonDBTM{


   static function getTypeName($nb=0) {
      return _n('Client', 'Clients', $nb);
   }


   static function canCreate() {
      return Session::haveRight('profile', 'w');
   }


   static function canView() {
      return Session::haveRight('profile', 'r');
   }


   function getSearchOptions() {

      $tab = array();

      $tab['common']           = _n('Client', 'Clients', 1);

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = __('Name');
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();

      $tab[80]['table']       = 'glpi_entities';
      $tab[80]['field']       = 'completename';
      $tab[80]['name']        = __('Entity');

      $tab[86]['table']        = $this->getTable();
      $tab[86]['field']        = 'is_recursive';
      $tab[86]['name']         = __('Child entities');
      $tab[86]['datatype']     = 'bool';

      $tab[4]['table']         = $this->getTable();
      $tab[4]['field']         =  'comment';
      $tab[4]['name']          =  __('Comments');
      $tab[4]['datatype']      =  'text';

      return $tab;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'PluginDumpentityModel') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $nb = countElementsInTable($this->getTable(), "`models_id`=".$item->getID());
               return self::createTabEntry($this->getTypeName(2), $nb);
            }
         return $this->getTypeName(2);
      }

      return '';
   }


   static function showForModel(PluginDumpentityModel $model) {
      global $DB;

      $client = new self();

      echo "<div class='center'><table class='tab_cadre_fixe'>";

      $req = $DB->request($client->getTable(), array('models_id' => $model->getID()));
      if ($req->numrows()) {
         echo "<tr><th>".__('Name')."</th>";
         echo "<th>".__('IP address')."</th>";
         echo "<th>".__('Entity')."</th>";
         echo "<th>".__('Child entities')."</th></tr>";

         Session::initNavigateListItems(__CLASS__, $model->getTypeName(1)." = ".$model->getName());

         foreach ($req as $data) {
            if ($client->getFromDB($data['id'])) {
               Session::addToNavigateListItems(__CLASS__, $data['id']);

               echo "<tr class='tab_bg_1'><td>";
               echo $client->getLink(true);
               echo "</td><td>";
               echo $client->fields['ip'];
               echo "</td><td>";
               echo Dropdown::getDropdownName('glpi_entities', $client->fields['entities_id']);
               echo "</td><td>";
               echo Dropdown::getYesNo($client->fields['is_recursive']);
               echo "</td></tr>";
            }
         }
      } else  {
         echo "<tr><th>".__('No item to display')."</td></tr>";
      }
      echo "</table>";
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='PluginDumpentityModel') {
         self::showForModel($item);
      } else {
         return false;
      }
      return true;
   }


   function getFromDBForIP() {
      global $DB;

      if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
         $liste = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]);
         $ip    = $liste[0];

      } else if (isset($_SERVER["REMOTE_ADDR"])) {
         $ip = $_SERVER["REMOTE_ADDR"];

      } else {
         return false;
      }

      $ID_profile = 0;
      // Get user profile
      $query = "SELECT `id`
                FROM `glpi_plugin_dumpentity_clients`
                WHERE `ip` = '$ip'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            $ID_profile = $DB->result($result,0,0);
         }
      }

      if ($ID_profile) {
         Toolbox::logInFile("dump_entity", "From IP = $ip : Accepted\n");
         return $this->getFromDB($ID_profile);
      }
      Toolbox::logInFile("dump_entity", "From IP = $ip : Refused\n");
      return false;
   }


   function prepareInputForAdd($input) {

      if (!isset($input["entities_id"])) {
         $input["entities_id"] = 0;
      }
      if (!isset($input["models_id"])) {
         $input["models_id"] = 1;
      }
      return $input;
   }


   function plugin_dumpentity_checkTimeExclusion() {

      if (!$this->fields["use_time_exclusion"]) {
         return true;
      }

      $now = time();
      if (strtotime($this->fields["allow_start_time"]) < strtotime($this->fields["allow_end_time"])) {
         return ($now >= strtotime($this->fields["allow_start_time"])
                 && $now < strtotime($this->fields["allow_end_time"]));
      }
      return ($now >= strtotime($this->fields["allow_end_time"])
              && $now < strtotime($this->fields["allow_start_time"]));
   }


   /**
    * Clean when and Entity is deleted
    *
    * @param $ent Entity Object
   **/
   static function cleanEntity(Entity $ent) {
      global $DB;

      $client = new self();
      foreach ($DB->request($client->getTable(),
                            array('entities_id' => $ent->getField('id'))) as $row) {
         $client->delete(array('id' => $row['id']));
      }
   }


   function showForm ($ID, $options=array()) {
      global $CFG_GLPI;

      if ($ID>0) {
         $this->check($ID, 'r');
      } else {
         $this->check(-1, 'w');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td><td>";
      Html::autocompletionTextField($this, 'name', array('size' => 34));
      echo "</td><td rowspan='7'>".__('Comments')."</td>";
      echo "<td rowspan='7'>";
      echo "<textarea cols='45' rows='10' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('IP address')."</td><td>";
      echo "<input name='ip' size='20' value='" . $this->fields["ip"]. "'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Model', 'Models')."</td><td>";
      Dropdown::show('PluginDumpentityModel', array('name'     => 'models_id',
                                                    'value'    => $this->fields["models_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Use time period restriction', 'dumpentity')."</td><td>";
      Dropdown::showYesNo("use_time_exclusion", $this->fields["use_time_exclusion"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Beginning time for query', 'dumpentity')."</td><td>";
      Dropdown::showHours("allow_start_time",$this->fields["allow_start_time"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('End time for query', 'dumpentity')."</td><td>";
      Dropdown::showHours("allow_end_time",$this->fields["allow_end_time"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Last update')."</td><td>";
      echo Html::convDateTime($this->fields["date_mod"]);
      echo "</td></tr>";


      $this->showFormButtons($options);
      $this->addDivForTabs();
   }
}

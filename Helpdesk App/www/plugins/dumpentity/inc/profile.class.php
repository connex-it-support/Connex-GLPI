<?php
/*
 * @version $Id: profile.class.php 144 2015-09-06 18:05:31Z yllen $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginDumpentityProfile extends CommonDBTM{


   static function cleanProfile(Profile $prof) {

      $plugprof = new self();
      $plugprof->delete(array('id' => $prof->getID()));
   }


   static function select() {

      $prof = new self();
      if ($prof->getFromDBForProfile($_SESSION['glpiactiveprofile']['id'])
          && $prof->fields['models_id'] > 0) {
         $_SESSION["glpi_plugin_dumpentity_profile"] = $prof->fields;
      } else {
          unset($_SESSION["glpi_plugin_dumpentity_profile"]);
      }
//      }
   }


   function getFromDBForProfile($ID){
      global $DB;

      $ID_profile = 0;
      // Get user profile
      $query = "SELECT `id`
                FROM `glpi_plugin_dumpentity_profiles`
                WHERE `profiles_id` = '$ID'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            $ID_profile = $DB->result($result,0,0);
         }
      }

      if ($ID_profile) {
         return $this->getFromDB($ID_profile);
      }
      return false;
   }


   function createaccess($profile) {
      return $this->add(array('profiles_id' => $profile->getField('id')));
   }


   function showFormEdit($profID) {
      global $DB;

      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if (!Session::haveRight("profile","r")) {
         return false;
      }
      $canedit = Session::haveRight("profile","w");

      $this->getFromDBForProfile($profID);

      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre' cellpadding='5'>";
      echo "<tr><th colspan='2'>".__('Profile configuration', 'dumpentity')." '".
      Dropdown::getDropdownName('glpi_profiles',$profID)."' </th></tr>";

      echo "<tr class='tab_bg_1'><td>" . _n('Model', 'Models', 1) . "</td><td>";
      Dropdown::show('PluginDumpentityModel', array('name'     => 'models_id',
                                                    'value'    => $this->fields["models_id"],
                                                    'comments' => true));
      echo "</td></tr>\n";

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td class='center' colspan='2'>";
         echo "<input type='hidden' name='id' value='".$this->fields["id"]."'>";
         echo "<input type='submit' name='update_user_profile' value='"._sx('button', 'Update')."'
                class='submit'>&nbsp;&nbsp;";
         echo "</td></tr>\n";
      }

      echo "</table>";
      if ($canedit) {
         Html::closeForm();
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->getField('id') && ($item->getField('interface') != 'helpdesk')) {
            return array(1 => __('Dump entity', 'dumpentity'));
         }
      }
      if ($item->getType() == 'PluginDumpentityModel') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = countElementsInTable($this->getTable(), "`models_id`=".$item->getID());
            return self::createTabEntry(_n('Profile', 'Profiles', $nb), $nb);
         }
         return _n('Profile', 'Profiles', 1);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         $prof = new self();
         $ID = $item->getField('id');
         if (!$prof->getFromDBForProfile($ID)) {
            $prof->createAccess($item);
         }
         $prof->showFormEdit($ID);
      } else if ($item->getType()=='PluginDumpentityModel') {
         self::showForModel($item);
      } else {
         return false;
      }
      return true;
   }


   static function showForModel(PluginDumpentityModel $model) {
      global $DB;

      $plugprof = new self();
      echo "<div class='center'><table class='tab_cadre_fixe'>";

      $prof = new Profile();
      $req = $DB->request($plugprof->getTable(), array('models_id' => $model->getID()));
      if ($req->numrows()) {
         echo "<tr><th>".__('Name')."</th></tr>";

         Session::initNavigateListItems('Profile', $model->getTypeName(1)." = ".$model->getName());

         foreach ($req as $data) {
            if ($prof->getFromDB($data['profiles_id'])) {
               Session::addToNavigateListItems('Profile', $data['profiles_id']);

               echo "<tr class='tab_bg_1'><td>";
               echo $prof->getLink(true);
               echo "</td></tr>";
            }
         }
      } else  {
         echo "<tr><th>".__('No item to display')."</td></tr>";
      }
      echo "</table>";
   }
}

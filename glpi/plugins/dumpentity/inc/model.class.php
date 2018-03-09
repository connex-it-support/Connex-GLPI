<?php
/*
 * @version $Id: model.class.php 144 2015-09-06 18:05:31Z yllen $
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


if (!defined("PLUGIN_DUMPENTITY_UPLOAD_DIR")){
   define  ("PLUGIN_DUMPENTITY_UPLOAD_DIR",  GLPI_PLUGIN_DOC_DIR."/dumpentity/");
}

class PluginDumpentityModel extends CommonDBTM{

   const LOADENTITY_VERSION_REQUIRE = '1.4.0';
   const DUMPENTIY_DB_SCHEMA        = '1.4';


   static function getTypeName($nb=0) {
      return _n('Model', 'Models', $nb);
   }


   static function canCreate() {
      return Session::haveRight('profile', 'w');
   }


   static function canView() {
      return Session::haveRight('profile', 'r');
   }


   function canUpdateItem() {
      // id=1, complete model, is protected
      return $this->fields['id'] > 1;
   }


   function canDeleteItem() {
      // id=1, complete model, is protected
      return $this->fields['id'] > 1;
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('PluginDumpentityProfile', $ong, $options);
      $this->addStandardTab('PluginDumpentityClient', $ong, $options);

      return $ong;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         return $this->getTypeName(1);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         $item->showTables();
         return true;
      }
      return false;
   }


   function showTables(){
      global $DB;

      if (!Session::haveRight("profile","r")) {
         return false;
      }
      $canedit = $this->can($this->getID(), 'w');

      if ($canedit) {
         echo "<form action='".$this->getFormURL()."' method='post'>";
      }
      echo "<table class='tab_cadre' cellpadding='5'>";
      echo "<tr><th colspan='6'>".sprintf(__('%1$s %2$s'), __('Model configuration', 'dumpentity'),
                                          $this->fields["name"])."</th></tr>";

      $i      = 0;
      $tables = self::getTables();
      asort($tables);
      foreach ($tables as $name => $descr) {
         if ($i%3 == 0) {
            echo "<tr class='tab_bg_1'>";
         }
         if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
            echo "<td>$descr<br>($name)&nbsp;: </td><td>";
         } else {
            echo "<td>$descr&nbsp;: </td><td>";
         }
         if ($canedit) {
            Dropdown::showYesNo($name, $this->fields[$name]);
         } else {
            echo Dropdown::getYesNo($this->fields[$name]);
         }
         echo "</td>";

         if ($i%3 == 2) {
            echo "</tr>\n";
         }
         $i++;
      }
      if ($i%3 == 1) {
         echo "<td colspan='4'></tr>\n";
      }
      if ($i%3 == 2) {
         echo "<td colspan='2'></tr>\n";
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td class='center' colspan='6'>";
         echo "<input type='hidden' name='id' value='".$this->fields['id']."'>";
         echo "<input type='submit' name='update' value='"._sx('button', 'Update')."' class='submit'>";
         echo "&nbsp;&nbsp;<input type='submit' name='delete'
                            value='"._sx('button', 'Delete permanently')."' class='submit'>";
         echo "</td></tr>\n";
      }

      echo "</table>";
      if ($canedit) {
         Html::closeForm();
      }
   }


   function showForUser($gzip) {

      echo "<table class='tab_cadre' cellpadding='5'><tr class='tab_bg_1'>";
      echo "<th colspan='4'>" . $this->fields["name"] . "</th></tr>\n";

      if ($gzip) {
         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<a href='".$_SERVER["PHP_SELF"]."?gzip=0'>".__('Disable compression', 'dumpentity').
              "</a>";
         echo "</td></tr>\n";

      } else if (function_exists("gzopen")) {
         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<a href='".$_SERVER["PHP_SELF"]."?gzip=1'>".__('Enable compression', 'dumpentity').
              "</a>";
         echo "</td></tr>\n";
      }

      $i      = 0;
      $tables = self::getTables();
      asort($tables);

      foreach ($tables as $name => $descr) {
         if ($this->fields[$name]) {
            if ($i%4 == 0) {
               echo "<tr class='tab_bg_1'>";
            }
            echo "<td>&nbsp;<a href='front/getcsv.php?gzip=$gzip&table=$name'>$descr</a>&nbsp;</td>";
            if ($i%4 == 3) {
               echo "</tr>\n";
            }
            $i++;
         }
      }

      if ($i%4) {
         echo "<td colspan='" . (4-($i%4)) . "'>&nbsp;</td></tr>\n";
      }
      if (class_exists("ZipArchive")) {
         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<a href='front/getzip.php'>".__('Full archive in ZIP format', 'dumpentity')."</a>";
         echo "</td></tr>\n";
      }

      $inc = @fopen("Archive/Tar.php", "r", true);
      if ($inc) {
         fclose($inc);
         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<a href='front/gettgz.php'>".__('Full archive in .tar.gz format', 'dumpentity')."</a>";
         echo "</td></tr>\n";
      }

      echo "</table>";
   }


   function showForm ($ID, $options=array()) {
      global $CFG_GLPI;

      if ($ID > 0) {
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
      echo "</td><td rowspan='2'>".__('Comments')."</td>";
      echo "<td rowspan='2'>";
      echo "<textarea cols='45' rows='5' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Last update')."</td><td>";
      echo Html::convDateTime($this->fields["date_mod"]);
      echo "</td></tr>";

      if ($ID == 1) {
         echo "<tr class='tab_bg_1'><td colspan='4' class='center b'>";
         _e('This model cannot be edited.', 'dumpentity');
         echo "</th></td>";
      }

      $this->showFormButtons($options);
      $this->addDivForTabs();
   }


   static function getTables() {
      static $tables = NULL;

      if (is_null($tables)) {
         $tables = array(
            'glpi_alerts'                       => __('Email alarms'),
            // Not exported: glpi_authldapreplicates
            'glpi_authldaps'                    => AuthLDAP::getTypeName(2),
            'glpi_authmails'                    => AuthMail::getTypeName(2),
            'glpi_autoupdatesystems'            => AutoUpdateSystem::getTypeName(2),
            // Not exported: glpi_blacklists
            'glpi_bookmarks'                    => _n('Bookmark', 'Bookmarks', 2),
            'glpi_bookmarks_users'              => sprintf(__('%1$s / %2$s'),
                                                           _n('Bookmark', 'Bookmarks', 1),
                                                           User::getTypeName(1)),
            'glpi_budgets'                      => Budget::getTypeName(2),
            'glpi_calendars'                    => Calendar::getTypeName(2),
            'glpi_calendarsegments'             => CalendarSegment::getTypeName(2),
            'glpi_calendars_holidays'           => sprintf(__('%1$s / %2$s'),
                                                           Calendar::getTypeName(1),
                                                           Holiday::getTypeName(1)),
            'glpi_cartridgeitems'               => CartridgeItem::getTypeName(2),
            'glpi_cartridgeitems_printermodels' => sprintf(__('%1$s / %2$s'),
                                                           CartridgeItem::getTypeName(),
                                                           PrinterModel::getTypeName(1)),
            'glpi_cartridgeitemtypes'           => CartridgeItemType::getTypeName(2),
            'glpi_cartridges'                   => Cartridge::getTypeName(2),
            'glpi_computerdisks'                => ComputerDisk::getTypeName(2),
            'glpi_computermodels'               => ComputerModel::getTypeName(2),
            'glpi_computers'                    => Computer::getTypeName(2),
            'glpi_computers_items'              => sprintf(__('%1$s / %2$s'),
                                                           Computer::getTypeName(1),
                                                           _n('Item', 'Items', 1)),
            'glpi_computers_softwarelicenses'   => sprintf(__('%1$s / %2$s'),
                                                           Computer::getTypeName(1),
                                                           SoftwareLicense::getTypeName(1)),
            'glpi_computers_softwareversions'   => sprintf(__('%1$s / %2$s'),
                                                           Computer::getTypeName(1),
                                                           SoftwareVersion::getTypeName(1)),
            'glpi_computertypes'                => ComputerType::getTypeName(2),
            'glpi_computervirtualmachines'      => ComputerVirtualMachine::getTypeName(2),
            // Not exported: glpi_configs
            'glpi_consumableitems'              => ConsumableItem::getTypeName(2),
            'glpi_consumableitemtypes'          => ConsumableItemType::getTypeName(2),
            'glpi_consumables'                  => Consumable::getTypeName(2),
            'glpi_contacts'                     => Contact::getTypeName(2),
            'glpi_contacts_suppliers'           => sprintf(__('%1$s / %2$s'),
                                                           Contact::getTypeName(1),
                                                           Supplier::getTypeName(1)),
            'glpi_contacttypes'                 => ContactType::getTypeName(2),
            'glpi_contractcosts'                => sprintf(__('%1$s %2$s'),
                                                           ContractCost::getTypeName(2),
                                                           Contact::getTypeName(1)),
            'glpi_contracts'                    => Contract::getTypeName(2),
            'glpi_contracts_items'              => sprintf(__('%1$s / %2$s'),
                                                           Contract::getTypeName(1),
                                                           _n('Item', 'Items', 1)),
            'glpi_contracts_suppliers'          => sprintf(__('%1$s / %2$s'),
                                                           Contract::getTypeName(1),
                                                           Supplier::getTypeName(1)),
            'glpi_contracttypes'                => ContractType::getTypeName(2),
            // Not exported: all about crontasks
            'glpi_devicecases'                  => DeviceCase::getTypeName(2),
            'glpi_devicecasetypes'              => DeviceCaseType::getTypeName(2),
            'glpi_devicecontrols'               => DeviceControl::getTypeName(2),
            'glpi_devicedrives'                 => DeviceDrive::getTypeName(2),
            'glpi_devicegraphiccards'           => DeviceGraphicCard::getTypeName(2),
            'glpi_deviceharddrives'             => DeviceHardDrive::getTypeName(2),
            'glpi_devicememories'               => DeviceMemory::getTypeName(2),
            'glpi_devicememorytypes'            => DeviceMemoryType::getTypeName(2),
            'glpi_devicemotherboards'           => DeviceMotherboard::getTypeName(2),
            'glpi_devicenetworkcards'           => DeviceNetworkCard::getTypeName(2),
            'glpi_devicepcis'                   => DevicePci::getTypeName(2),
            'glpi_devicepowersupplies'          => DevicePowerSupply::getTypeName(2),
            'glpi_deviceprocessors'             => DeviceProcessor::getTypeName(2),
            'glpi_devicesoundcards'             => DeviceSoundCard::getTypeName(2),
            // Not exported: glpi_displaypreferences
            'glpi_documentcategories'           => DocumentCategory::getTypeName(2),
            'glpi_documents'                    => Document::getTypeName(2),
            'glpi_documents_items'              => sprintf(__('%1$s / %2$s'),
                                                           Document::getTypeName(1),
                                                           _n('Item', 'Items', 1)),
            'glpi_documenttypes'                => DocumentType::getTypeName(2),
            'glpi_domains'                      => Domain::getTypeName(2),
            'glpi_entities'                     => Entity::getTypeName(2),
            'glpi_entities_knowbaseitems'       => sprintf(__('%1$s / %2$s'), Entity::getTypeName(1),
                                                           KnowbaseItem::getTypeName(1)),
            'glpi_entities_reminders'           => sprintf(__('%1$s / %2$s'), Entity::getTypeName(1),
                                                           Reminder::getTypeName(1)),
            'glpi_entities_rssfeeds'            => sprintf(__('%1$s / %2$s'), Entity::getTypeName(1),
                                                           RSSFeed::getTypeName(1)),
            // Not exported: glpi_events
            // Not exported: glpi_fieldblacklists - glpi_fieldunicities
            'glpi_filesystems'                  => Filesystem::getTypeName(2),
            'glpi_fqdns'                        => FQDN::getTypeName(2),
            'glpi_groups'                       => Group::getTypeName(2),
            'glpi_groups_knowbaseitems'         => sprintf(__('%1$s / %2$s'), Group::getTypeName(1),
                                                           KnowbaseItem::getTypeName(1)),
            'glpi_groups_problems'              => sprintf(__('%1$s / %2$s'), Group::getTypeName(1),
                                                           Problem::getTypeName(1)),
            'glpi_groups_reminders'             => sprintf(__('%1$s / %2$s'), Group::getTypeName(1),
                                                           Reminder::getTypeName(1)),
            'glpi_groups_rssfeeds'              => sprintf(__('%1$s / %2$s'), Group::getTypeName(1),
                                                           RSSFeed::getTypeName(1)),
            'glpi_groups_tickets'               => sprintf(__('%1$s / %2$s'), Group::getTypeName(1),
                                                           Ticket::getTypeName(1)),
            'glpi_groups_users'                 => sprintf(__('%1$s / %2$s'), Group::getTypeName(1),
                                                           User::getTypeName(1)),
            'glpi_holidays'                     => Holiday::getTypeName(2),
            'glpi_infocoms'                     => Infocom::getTypeName(2),
            'glpi_interfacetypes'               => InterfaceType::getTypeName(2),
            'glpi_ipaddresses'                  => IPAddress::getTypeName(2),
            'glpi_ipaddresses_ipnetworks'       => sprintf(__('%1$s / %2$s'),
                                                           IPAddress::getTypeName(1),
                                                           IPNetwork::getTypeName(1)),
            'glpi_networks'                     => IPNetwork::getTypeName(2),
            'glpi_networks_vlans'               => sprintf(__('%1$s / %2$s'),
                                                           IPNetwork::getTypeName(1),
                                                           Vlan::getTypeName(1)),
            'glpi_items_devicecases'            => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceCase::getTypeName(1)),
            'glpi_items_devicecontrols'         => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceControl::getTypeName(1)),
            'glpi_items_devicedrives'           => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceDrive::getTypeName(1)),
            'glpi_items_devicegraphiccards'     => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceGraphicCard::getTypeName(1)),
            'glpi_items_deviceharddrives'       => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceHardDrive::getTypeName(1)),
            'glpi_items_devicememories'         => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceMemory::getTypeName(1)),
            'glpi_items_devicemotherboards'     => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceMotherboard::getTypeName(1)),
            'glpi_items_devicenetworkcards'     => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceNetworkCard::getTypeName(1)),
            'glpi_items_devicepcis'             => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DevicePci::getTypeName(1)),
            'glpi_items_devicepowersupplies'    => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DevicePowerSupply::getTypeName(1)),
            'glpi_items_deviceprocessors'       => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceProcessor::getTypeName(1)),
            'glpi_items_devicesoundcards'       => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           DeviceSoundCard::getTypeName(1)),
            'glpi_items_problems'               => sprintf(__('%1$s / %2$s'), _n('Item', 'Items', 1),
                                                           Problem::getTypeName(1)),
            'glpi_itilcategories'               => ITILCategory::getTypeName(2),
            'glpi_knowbaseitemcategories'       => KnowbaseItemCategory::getTypeName(2),
            'glpi_knowbaseitems'                => KnowbaseItem::getTypeName(2),
            'glpi_knowbaseitems_profiles'       => sprintf(__('%1$s / %2$s'),
                                                           KnowbaseItem::getTypeName(2),
                                                           Profile::getTypeName(1)),
            'glpi_knowbaseitems_users'          => sprintf(__('%1$s / %2$s'),
                                                           KnowbaseItem::getTypeName(2),
                                                           User::getTypeName(1)),
            'glpi_links'                        => Link::getTypeName(2),
            'glpi_links_itemtypes'              => sprintf(__('%1$s / %2$s'), Link::getTypeName(2),
                                                           __('Itemtype', 'dumpentity')),
            'glpi_locations'                    => Location::getTypeName(2),
            // Not exported: glpi_logs
            // Not exported: glpi_mailcollectors - glpi_mailingsettings
            'glpi_manufacturers'                => Manufacturer::getTypeName(2),
            'glpi_monitormodels'                => MonitorModel::getTypeName(2),
            'glpi_monitors'                     => Monitor::getTypeName(2),
            'glpi_monitortypes'                 => MonitorType::getTypeName(2),
            'glpi_netpoints'                    => Netpoint::getTypeName(2),
            'glpi_networkaliases'               => NetworkAlias::getTypeName(2),
            'glpi_networkequipmentfirmwares'    => NetworkEquipmentFirmware::getTypeName(2),
            'glpi_networkequipmentmodels'       => NetworkEquipmentModel::getTypeName(2),
            'glpi_networkequipments'            => NetworkEquipment::getTypeName(2),
            'glpi_networkequipmenttypes'        => NetworkEquipmentType::getTypeName(2),
            'glpi_networkinterfaces'            => NetworkInterface::getTypeName(2),
            'glpi_networknames'                 => NetworkName::getTypeName(2),
            'glpi_networkportaggregates'        => NetworkPortAggregate::getTypeName(2),
            'glpi_networkportaliases'           => NetworkPortAlias::getTypeName(2),
            'glpi_networkportdialups'           => NetworkPortDialup::getTypeName(2),
            'glpi_networkportethernets'         => NetworkPortEthernet::getTypeName(2),
            'glpi_networkportlocals'            => NetworkPortLocal::getTypeName(2),
            // Not exported: glpi_networkportmigrations (only for migration)
            'glpi_networkports'                 => NetworkPort::getTypeName(2),
            'glpi_networkports_networkports'    => sprintf(__('%1$s / %2$s'),
                                                           NetworkPort::getTypeName(1),
                                                           NetworkPort::getTypeName(1)),
            'glpi_networkports_vlans'           => sprintf(__('%1$s / %2$s'),
                                                           NetworkPort::getTypeName(1),
                                                           Vlan::getTypeName(1)),
            'glpi_networkportwifis'             => NetworkPortWifi::getTypeName(2),
            'glpi_networks'                     => Network::getTypeName(2),
            // Not exported: all about notifications
            // Not exported: glpi_notimportedemails
            'glpi_operatingsystems'             => OperatingSystem::getTypeName(2),
            'glpi_operatingsystemservicepacks'  => OperatingSystemServicePack::getTypeName(2),
            'glpi_operatingsystemversions'      => OperatingSystemVersion::getTypeName(2),
            'glpi_peripheralmodels'             => PeripheralModel::getTypeName(2),
            'glpi_peripherals'                  => Peripheral::getTypeName(2),
            'glpi_peripheraltypes'              => PeripheralType::getTypeName(2),
            'glpi_phonemodels'                  => PhoneModel::getTypeName(2),
            'glpi_phonepowersupplies'           => PhonePowerSupply::getTypeName(2),
            'glpi_phones'                       => Phone::getTypeName(2),
            'glpi_phonetypes'                   => PhoneType::getTypeName(2),
            'glpi_planningrecalls'              => PlanningRecall::getTypeName(2),
            // Not exported: all glpi_plugins_*
            'glpi_printermodels'                => PrinterModel::getTypeName(2),
            'glpi_printers'                     => Printer::getTypeName(2),
            'glpi_printertypes'                 => PrinterType::getTypeName(2),
            'glpi_problems'                     => Problem::getTypeName(2),
            'glpi_problems_suppliers'           => sprintf(__('%1$s / %2$s'),
                                                           Problem::getTypeName(1),
                                                           Supplier::getTypeName(1)),
            'glpi_problems_tickets'             => sprintf(__('%1$s / %2$s'),
                                                           Problem::getTypeName(1),
                                                           Ticket::getTypeName(1)),
            'glpi_problems_users'               => sprintf(__('%1$s / %2$s'),
                                                           Problem::getTypeName(1),
                                                           User::getTypeName(1)),
            'glpi_problemtasks'                 => ProblemTask::getTypeName(2),
            'glpi_profiles'                     => Profile::getTypeName(2),
            'glpi_profiles_reminders'           => sprintf(__('%1$s / %2$s'),
                                                           Profile::getTypeName(1),
                                                           Reminder::getTypeName(1)),
            'glpi_profiles_users'               => sprintf(__('%1$s / %2$s'),
                                                           Profile::getTypeName(1),
                                                           User::getTypeName(1)),
            'glpi_reminders'                    => Reminder::getTypeName(2),
            'glpi_reminders_users'              => sprintf(__('%1$s / %2$s'),
                                                           Reminder::getTypeName(1),
                                                           User::getTypeName(1)),
            // Not exported: glpi_requesttypes
            'glpi_reservationitems'             => ReservationItem::getTypeName(2),
            'glpi_reservations'                 => Reservation::getTypeName(2),
            'glpi_rssfeeds'                     => RSSFeed::getTypeName(2),
            'glpi_rssfeeds_users'               => sprintf(__('%1$s / %2$s'), RSSFeed::getTypeName(1),
                                                           User::getTypeName(1)),
            // Not exported: all about rules
            // Not exported: all about slas
            'glpi_softwarecategories'           => SoftwareCategory::getTypeName(2),
            'glpi_softwarelicenses'             => SoftwareLicense::getTypeName(2),
            'glpi_softwarelicensetypes'         => SoftwareLicenseType::getTypeName(2),
            'glpi_softwares'                    => Software::getTypeName(2),
            'glpi_softwareversions'             => SoftwareVersion::getTypeName(2),
            // Not exported: glpi_solutiontemplates
            'glpi_solutiontypes'                => SolutionType::getTypeName(2),
            'glpi_ssovariables'                 => SsoVariable::getTypeName(2),
            'glpi_states'                       => State::getTypeName(2),
            'glpi_suppliers'                    => Supplier::getTypeName(2),
            'glpi_suppliers_tickets'            => sprintf(__('%1$s / %2$s'),
                                                           Supplier::getTypeName(1),
                                                           Ticket::getTypeName(1)),
            'glpi_suppliertypes'                => SupplierType::getTypeName(2),
            'glpi_taskcategories'               => TaskCategory::getTypeName(2),
            'glpi_ticketcosts'                  => sprintf(__('%1$s %2$s'),
                                                           TicketCost::getTypeName(2),
                                                           Ticket::getTypeName(2)),
            'glpi_ticketfollowups'              => TicketFollowup::getTypeName(2),
            'glpi_ticketrecurrents'             => TicketRecurrent::getTypeName(2),
            'glpi_tickets'                      => Ticket::getTypeName(2),
            'glpi_ticketsatisfactions'          => TicketSatisfaction::getTypeName(2),
            'glpi_tickets_tickets'              => sprintf(__('%1$s / %2$s'), Ticket::getTypeName(1),
                                                           Ticket::getTypeName(1)),
            'glpi_tickets_users'                => sprintf(__('%1$s / %2$s'), Ticket::getTypeName(1),
                                                           User::getTypeName(1)),
            'glpi_tickettasks'                  => TicketTask::getTypeName(2),
            // Not exported: all about tickettemplates
            'glpi_ticketvalidations'            => TicketValidation::getTypeName(2),
            // Not exported: glpi_transfers
            'glpi_usercategories'               => UserCategory::getTypeName(2),
            'glpi_useremails'                   => UserEmail::getTypeName(2),
            'glpi_users'                        => User::getTypeName(2),
            'glpi_usertitles'                   => UserTitle::getTypeName(2),
            // Not exported: all about virtual machines
            'glpi_vlans'                        => Vlan::getTypeName(2),
            'glpi_wifinetworks'                 => WifiNetwork::getTypeName(2)
         );
      }
      return $tables;
   }


   static function getCSV($table, $entity, $recursive=0, $gzip=0, $encode='') {
      global $DB, $CFG_GLPI;

      $entity_in = " IN (".($recursive?implode(getSonsOf("glpi_entities", $entity),','):$entity).")";

      // Waiting for 0.70 stable
      if (!isset($CFG_GLPI["networkport_types"])) {
         $CFG_GLPI["networkport_types"] = array('Computer', 'NetworkEquipment', 'Peripheral', 'Phone',
                                                'Printer');
      }

      if (!function_exists("gzopen")) {
         $gzip = 0;
      }

      $Sql = "SELECT *
              FROM `$table` ";

      if ($entity > 0) {
         $ancestor = "($entity,".implode(getAncestorsOf("glpi_entities", $entity), ', ')."";
      } else {
         $ancestor = "($entity";
      }
      if ($table == "glpi_entities") {
         $Sql = "SELECT *
                 FROM `$table`
                 WHERE `id` IN ".$ancestor;
         if ($recursive) {
            //Dump entity + sub entities + entity's parents
            $Sql .= ",".implode(getSonsOf("glpi_entities", $entity),',').')';
         } else {
            $Sql .= ")";
         }

      } else if ($table == "glpi_networkports_networkports") {

         $Sql = "SELECT `$table`.*
                 FROM `glpi_networkports`
                 INNER JOIN `$table` ON (`glpi_networkports`.`id` = `$table`.`networkports_id_2`
                                         OR `glpi_networkports`.`id` = `$table`.`networkports_id_1`)
                 WHERE (`glpi_networkports`.`entities_id` $entity_in)";

      } else if ($table == "glpi_users") {
         $Sql .= " WHERE `name` != 'glpi'
                         AND `id` IN (SELECT DISTINCT(`users_id`)
                                      FROM `glpi_profiles_users`
                                      WHERE (`entities_id` $entity_in
                                             OR (`is_recursive` = '1'
                                                 AND `entities_id`
                                                      IN ".self::parents($entity) .")))";

      } else if (FieldExists($table, "is_recursive")) {
         $Sql .= " WHERE (`entities_id` $entity_in
                          OR (`is_recursive` = '1'
                              AND `entities_id` IN " . self::parents ($entity) ."))";

      } else if (FieldExists($table, "entities_id")) {
         // for most tables
         $Sql .= " WHERE `entities_id` $entity_in";

      } else if (FieldExists($table, "users_id")) {
         // for glpi_useremails
         $Sql .= "WHERE `users_id` IN (SELECT DISTINCT(`users_id`)
                                       FROM `glpi_profiles_users`
                                       WHERE (`entities_id` $entity_in
                                              OR (`is_recursive` = '1'
                                                  AND `entities_id`
                                                       IN ".self::parents($entity) .")))";

      } else if (FieldExists($table, "networkports_id")) {
         // for glpi_networking_vlan
         $Sql = "SELECT `$table`.*
                 FROM `glpi_networkports`
                 INNER JOIN `$table` ON (`glpi_networkports`.`id` = `$table`.`networkports_id`)
                 WHERE (`glpi_networkports`.`entities_id` $entity_in)";

      } else if (FieldExists($table, "groups_id")) {
         // glpi_users_groups
         $Sql .= "WHERE `groups_id` IN (SELECT `id`
                                        FROM `glpi_groups`
                                        WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "suppliers_id")) {
         // For glpi_contact_enterprise and glpi_contract_enterprise
         $Sql .= "WHERE `suppliers_id` IN (SELECT `id`
                                           FROM `glpi_suppliers`
                                           WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "contracts_id")) {
         // For glpi_contract_device (or glpi_contract_enterprise)
         $Sql .= "WHERE `contracts_id` IN (SELECT `id`
                                           FROM `glpi_contacts`
                                           WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "printers_id")) {
         // For glpi_cartridges
         $Sql .= "WHERE `printers_id` IN (SELECT `id`
                                          FROM `glpi_printers`
                                          WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "computers_id")) {
         // For glpi_computers_softwarelicences, volumes, ...
         $Sql = "SELECT `$table`.*
                 FROM `glpi_computers`
                 INNER JOIN `$table` ON (`glpi_computers`.`id` = `$table`.`computers_id`)
                 WHERE (`glpi_computers`.`entities_id` $entity_in)";

      } else if (FieldExists($table, "calendars_id")) {
         // For glpi_calendars_holidays
         $Sql .= "WHERE `calendars_id` IN (SELECT `id`
                                           FROM `glpi_calendars`
                                           WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "changes_id")) {
         // For glpi_changes_groups, glpi_changes_items, glpi_changes_problems, glpi_changes_tickets,
         // glpi_changes_users, glpi_changetasks
         $Sql .= "WHERE `changes_id` IN (SELECT `id`
                                         FROM `glpi_changes`
                                         WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "problems_id")) {
         // For glpi_items_problems, glpi_problems_tickets, glpi_problems_users, glpi_problemtasks
         $Sql .= "WHERE `problems_id` IN (SELECT `id`
                                          FROM `glpi_problems`
                                          WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "links_id")) {
         // For glpi_links_itemtypes
         $Sql .= "WHERE `links_id` IN (SELECT `id`
                                       FROM `glpi_links`
                                       WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "reservationitems_id")) {
         // For glpi_reservations
         $Sql .= "WHERE `reservationitems_id` IN (SELECT `id`
                                                  FROM `glpi_reservationitems`
                                                  WHERE `entities_id` $entity_in)";

      } else if (FieldExists($table, "tickets_id")) {
         // For glpi_ticketfollowups, glpi_ticketsatisfactions, glpi_tickets_tickets,
         // glpi_tickets_users, glpi_tickettasks
         $Sql .= "WHERE `tickets_id` IN (SELECT `id`
                                         FROM `glpi_tickets`
                                         WHERE `entities_id` $entity_in)";


      } else if (FieldExists($table, "id")) {
         // Global tables like dropdown
         $Sql .= " ORDER BY `id";
      }

      $result = $DB->query($Sql);
      if (!$result) {
         return false;
      }

      $ficname = PLUGIN_DUMPENTITY_UPLOAD_DIR."$entity-$table.csv";
      if ($gzip) {
         $fic = gzopen($ficname, "wb");
      } else {
         $fic = fopen($ficname, "wb");
      }

      if (!$fic) {
         return false;
      }

      if ($data = $DB->fetch_assoc($result)) {
         $str = "";
         foreach ($data as $nom=>$val) {
            if (!empty($str)) {
               $str .= ";";
            }
            $str .= '"' . $nom . '"';
         }
         if ($gzip) {
            gzwrite($fic, $str . "\r\n");
         } else {
            fwrite($fic, $str . "\r\n");
         }
         do {
            $str = "";
            foreach ($data as $nom => $val) {
               if (!empty($str)) {
                  $str.=";";
               }

               if ($val == NULL) {
                  $str .= "NULL";
               } else if (is_numeric($val)) {
                  $str .= $val;
               } else if (!empty($val)) {
                  if ($encode) {
                     $val = Toolbox::decodeFromUtf8($val, $encode);
                  }
                  $str .= '"' . mysql_real_escape_string($val) . '"';
               }
            }
            if ($gzip) {
               gzwrite($fic, $str . "\r\n");
            } else {
               fwrite($fic, $str . "\r\n");
            }
         } while ($data = $DB->fetch_assoc($result));
      }

      if ($gzip) {
         gzclose($fic);
      } else {
         fclose($fic);
      }

      return $ficname;
   }


   static function parents ($entity) {

      $parents = getAncestorsOf("glpi_entities", $entity);
      $list    = "($entity";
      foreach ($parents as $parent) {
       $list .= ", $parent";
      }
      return $list.")";
   }
}

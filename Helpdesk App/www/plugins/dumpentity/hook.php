<?php
/*
 * @version $Id: hook.php 144 2015-09-06 18:05:31Z yllen $
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

require_once ("inc/model.class.php");

function plugin_dumpentity_upgrade() {
   global $DB;

   $migration = new Migration(130);

   $glpi_tables = array('glpi_auth_ldap'                       => 'glpi_authldaps',
                        'glpi_auth_mail'                       => 'glpi_authmails',
                        'glpi_bookmark'                        => 'glpi_bookmarks',
                        'glpi_cartridges_assoc'                => 'glpi_cartridgeitems_printermodels',
                        'glpi_cartridges_printermodels'        => 'glpi_cartridgeitems_printermodels',
                        'glpi_cartridges_type'                 => 'glpi_cartridgeitems',
                        'glpi_connect_wire'                    => 'glpi_computers_items',
                        'glpi_consumables_type'                => 'glpi_consumableitems',
                        'glpi_contact_enterprise'              => 'glpi_contacts_suppliers',
                        'glpi_contract_device'                 => 'glpi_contracts_items',
                        'glpi_contract_enterprise'             => 'glpi_contracts_suppliers',
                        'glpi_device_case'                     => 'glpi_devicecases',
                        'glpi_device_control'                  => 'glpi_devicecontrols',
                        'glpi_device_drive'                    => 'glpi_devicedrives',
                        'glpi_device_gfxcard'                  => 'glpi_devicegraphiccards',
                        'glpi_device_hdd'                      => 'glpi_deviceharddrives',
                        'glpi_device_iface'                    => 'glpi_devicenetworkcards',
                        'glpi_device_moboard'                  => 'glpi_devicemotherboards',
                        'glpi_device_pci'                      => 'glpi_devicepcis',
                        'glpi_device_power'                    => 'glpi_devicepowersupplies',
                        'glpi_device_processor'                => 'glpi_deviceprocessors',
                        'glpi_device_ram'                      => 'glpi_devicememories',
                        'glpi_device_sndcard'                  => 'glpi_devicesoundcards',
                        'glpi_dropdown_auto_update'            => 'glpi_autoupdatesystems',
                        'glpi_dropdown_budget'                 => 'glpi_budgets',
                        'glpi_dropdown_cartridge_type'         => 'glpi_cartridgeitemtypes',
                        'glpi_dropdown_case_type'              => 'glpi_devicecasetypes',
                        'glpi_dropdown_consumable_type'        => 'glpi_consumableitemtypes',
                        'glpi_dropdown_contact_type'           => 'glpi_contacttypes',
                        'glpi_dropdown_contract_type'          => 'glpi_contracttypes',
                        'glpi_dropdown_domain'                 => 'glpi_domains',
                        'glpi_dropdown_enttype'                => 'glpi_suppliertypes',
                        'glpi_dropdown_filesystems'            => 'glpi_filesystems',
                        'glpi_dropdown_firmware'               => 'glpi_networkequipmentfirmwares',
                        'glpi_dropdown_iface'                  => 'glpi_networkinterfaces',
                        'glpi_dropdown_interface'              => 'glpi_interfacetypes',
                        'glpi_dropdown_kbcategories'           => 'glpi_knowbaseitemcategories',
                        'glpi_dropdown_kbitems'                => 'glpi_knowbaseitems',
                        'glpi_dropdown_licensetypes'           => 'glpi_softwarelicensetypes',
                        'glpi_dropdown_locations'              => 'glpi_locations',
                        'glpi_dropdown_manufacturer'           => 'glpi_manufacturers',
                        'glpi_dropdown_model'                  => 'glpi_computermodels',
                        'glpi_dropdown_model_monitors'         => 'glpi_monitormodels',
                        'glpi_dropdown_model_networking'       => 'glpi_networkequipmentmodels',
                        'glpi_dropdown_model_peripherals'      => 'glpi_peripheralmodels',
                        'glpi_dropdown_model_phones'           => 'glpi_phonemodels',
                        'glpi_dropdown_model_printers'         => 'glpi_printermodels',
                        'glpi_dropdown_netpoint'               => 'glpi_netpoints',
                        'glpi_dropdown_network'                => 'glpi_networks',
                        'glpi_dropdown_os'                     => 'glpi_operatingsystems',
                        'glpi_dropdown_os_sp'                  => 'glpi_operatingsystemservicepacks',
                        'glpi_dropdown_os_version'             => 'glpi_operatingsystemversions',
                        'glpi_dropdown_phone_power'            => 'glpi_phonepowersupplies',
                        'glpi_dropdown_ram_type'               => 'glpi_devicememorytypes',
                        'glpi_dropdown_rubdocs'                => 'glpi_documentcategories',
                        'glpi_dropdown_software_category'      => 'glpi_softwarecategories',
                        'glpi_dropdown_state'                  => 'glpi_states',
                        'glpi_dropdown_tracking_category'      => 'glpi_ticketcategories',
                        'glpi_dropdown_user_titles'            => 'glpi_usertitles',
                        'glpi_dropdown_user_types'             => 'glpi_usercategories',
                        'glpi_dropdown_vlan'                   => 'glpi_vlans',
                        'glpi_enterprises'                     => 'glpi_suppliers',
                        'glpi_entities_data'                   => 'glpi_entitydatas',
                        'glpi_inst_software'                   => 'glpi_computers_softwareversions',
                        'glpi_networking'                      => 'glpi_networkequipments',
                        'glpi_networking_ports'                => 'glpi_networkports',
                        'glpi_networking_vlan'                 => 'glpi_networkports_vlans',
                        'glpi_networking_wire'                 => 'glpi_networkports_networkports',
                        'glpi_registry'                        => 'glpi_registrykeys',
                        'glpi_reminder'                        => 'glpi_reminders',
                        'glpi_software'                        => 'glpi_softwares',
                        'glpi_ticketcategories'                => 'glpi_itilcategories',
                        'glpi_type_computers'                  => 'glpi_computertypes',
                        'glpi_type_docs'                       => 'glpi_documenttypes',
                        'glpi_type_monitors'                   => 'glpi_monitortypes',
                        'glpi_type_networking'                 => 'glpi_networkequipmenttypes',
                        'glpi_type_peripherals'                => 'glpi_peripheraltypes',
                        'glpi_type_phones'                     => 'glpi_phonetypes',
                        'glpi_type_printers'                   => 'glpi_printertypes',
                        'glpi_users_groups'                    => 'glpi_groups_users',
                        'glpi_users_profiles'                  => 'glpi_profiles_users');

   $migration->changeField("glpi_plugin_dumpentity_models", "ID", "id", 'autoincrement');

   foreach ($glpi_tables as $original_field => $new_field) {
      $migration->changeField("glpi_plugin_dumpentity_models", "$original_field", "$new_field",
                              'bool');
   }
   $migration->migrationOneTable("glpi_plugin_dumpentity_models");

   foreach (PluginDumpentityModel::getTables() as $name => $label) {
      $migration->addField("glpi_plugin_dumpentity_models", "$name", 'bool',
                           array('update'    => '1',
                                 'condition' => "WHERE `id` = '1'"));
   }
   $migration->addField('glpi_plugin_dumpentity_models', 'comment', 'text',
                        array('after' => 'name'));

   $migration->addField('glpi_plugin_dumpentity_models', 'date_mod', 'datetime',
                        array('after' => 'comment'));

   $migration->dropField("glpi_plugin_dumpentity_models", "glpi_computer_device");
   $migration->dropField("glpi_plugin_dumpentity_models", "glpi_licenses");

   $migration->changeField("glpi_plugin_dumpentity_profiles", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_dumpentity_profiles", "FK_model", "models_id", 'integer');
   $migration->changeField("glpi_plugin_dumpentity_profiles", "FK_profile", "profiles_id",
                           'integer');
   $migration->addKey("glpi_plugin_dumpentity_profiles", "profiles_id", "unicity", 'UNIQUE');

   $migration->dropKey("glpi_plugin_dumpentity_clients", "ifaddr");
   $migration->changeField("glpi_plugin_dumpentity_clients", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_dumpentity_clients", "ifaddr", "ip", 'string');
   $migration->changeField("glpi_plugin_dumpentity_clients", "FK_model", "models_id", 'integer');
   $migration->changeField("glpi_plugin_dumpentity_clients", "FK_entity", "entities_id", 'integer');
   $migration->changeField("glpi_plugin_dumpentity_clients", "FK_entities", "entities_id", 'integer');
   if (FieldExists("glpi_plugin_dumpentity_clients", "recursive")) {
      $migration->changeField("glpi_plugin_dumpentity_clients", "recursive", "is_recursive", 'bool');
   } else {
      $migration->addField("glpi_plugin_dumpentity_clients", "is_recursive", 'bool');
   }
   $migration->addField("glpi_plugin_dumpentity_clients", "use_time_exclusion",
                        "smallint(1) NOT NULL default '0'");
   $migration->addField("glpi_plugin_dumpentity_clients", "allow_start_time",
                        "time NOT NULL default '00:00:00'");
   $migration->addField("glpi_plugin_dumpentity_clients", "allow_end_time",
                        "time NOT NULL default '00:00:00'");

   $migration->addField('glpi_plugin_dumpentity_clients', 'name', 'string',
                        array('after'  => 'id',
                              'update' => '`ip`'));
   $migration->addField('glpi_plugin_dumpentity_clients', 'comment', 'text',
                        array('after' => 'name'));
   $migration->addField('glpi_plugin_dumpentity_clients', 'date_mod', 'datetime',
                        array('after' => 'comment'));

   $migration->executeMigration();
}


function plugin_dumpentity_install() {
   global $DB;

   if (TableExists("glpi_plugin_dumpentity_models")) {
         plugin_dumpentity_upgrade();

   } else {
      $Sql2 = "";
      $Sql = "CREATE TABLE `glpi_plugin_dumpentity_models` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL default '0',
                  `comment` text,
                  `date_mod` datetime default NULL ";

      foreach (PluginDumpentityModel::getTables() as $name => $descr) {
         $Sql .= ", `$name` tinyint(1) NOT NULL default '0'";
         if (empty($Sql2)) {
            $Sql2 = "INSERT INTO `glpi_plugin_dumpentity_models`
                     SET `id` = '1', `name` = '".$LANG['plugin_dumpentity']['setup'][8]."'";
         } else {
         $Sql2 .= ", `$name` = '1'";
         }
      }
      $Sql .= ", PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";

      $Sql3 = "INSERT INTO `glpi_plugin_dumpentity_models`
               SET `id` = '2',
                   `name`= 'Minimum export for local helpdesk',
                   `glpi_computers` = '1',
                   `glpi_printers` = '1',
                   `glpi_monitors` ='1',
                   `glpi_locations` = '1',
                   `glpi_operatingsystems` = '1',
                   `glpi_operatingsystemservicepacks` = '1',
                   `glpi_operatingsystemversions` = '1',
                   `glpi_printermodels` = '1',
                   `glpi_computermodels` = '1',
                   `glpi_monitormodels` = '1',
                   `glpi_printertypes` = '1',
                   `glpi_computertypes` = '1',
                   `glpi_monitortypes` = '1',
                   `glpi_states` = '1',
                   `glpi_softwares` = '1'";

      $DB->queryOrDie($Sql, $DB->error());
      $DB->queryOrDie($Sql2, $DB->error());
      $DB->queryOrDie($Sql3, $DB->error());
   }

   if (!TableExists("glpi_plugin_dumpentity_clients")) {
      $Sql = "CREATE TABLE `glpi_plugin_dumpentity_clients` (
                  `id` int NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL default '0',
                  `comment` text,
                  `date_mod` datetime default NULL,
                  `ip` varchar(255) NOT NULL default '0',
                  `models_id` int(11) NOT NULL default '0',
                  `entities_id` int(11) NOT NULL default '0',
                  `is_recursive` tinyint(1) NOT NULL default '0',
                  `use_time_exclusion` smallint(1) NOT NULL default '0',
                  `allow_start_time` time NOT NULL default '00:00:00',
                  `allow_end_time` time NOT NULL default '00:00:00',
               PRIMARY KEY (`id`),
               UNIQUE `unicity` (`ip`))
               ENGINE=MyISAM DEFAULT CHARSET=utf8";
      $DB->queryOrDie($Sql, $DB->error());
   }

   if (!TableExists("glpi_plugin_dumpentity_profiles")) {
      $Sql = "CREATE TABLE `glpi_plugin_dumpentity_profiles` (
                  `id` int(11) NOT NULL auto_increment,
                  `models_id` INT(11) NOT NULL default '0',
                  `profiles_id` INT(11) NOT NULL default '0',
              PRIMARY KEY (`id`),
              UNIQUE `unicity` (`profiles_id`))
              ENGINE=MyISAM DEFAULT CHARSET=utf8";
      $DB->queryOrDie($Sql, $DB->error());

      // Full Export for Super-Admin
      $Sql = "INSERT INTO `glpi_plugin_dumpentity_profiles`
              SET `models_id` = '1', `profiles_id` = '4'";
      $DB->queryOrDie($Sql, $DB->error());
   }

   if (!is_dir(PLUGIN_DUMPENTITY_UPLOAD_DIR)) {
      mkdir(PLUGIN_DUMPENTITY_UPLOAD_DIR);
   }
   return true;
}


function plugin_dumpentity_uninstall() {
   global $DB;

   $tables = array ("glpi_plugin_dumpentity_models",
                    "glpi_plugin_dumpentity_profiles",
                    "glpi_plugin_dumpentity_clients");

   foreach ($tables as $table) {
      $query = "DROP TABLE IF EXISTS `$table`;";
      $DB->queryOrDie($query, $DB->error());
   }

   if (is_dir(PLUGIN_DUMPENTITY_UPLOAD_DIR)) {
      Toolbox::deleteDir(PLUGIN_DUMPENTITY_UPLOAD_DIR);
   }
}

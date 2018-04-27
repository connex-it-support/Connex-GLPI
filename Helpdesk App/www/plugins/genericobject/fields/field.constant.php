<?php
/*
 This file is part of the genericobject plugin.

 Genericobject plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Genericobject. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   genericobject
 @author    the genericobject plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

global $GO_FIELDS;

$GO_FIELDS['id']['name']          = __("ID");
$GO_FIELDS['id']['input_type']    = 'text';
$GO_FIELDS['id']['massiveaction'] = false;

$GO_FIELDS['name']['name']       = __("Name");
$GO_FIELDS['name']['field']      = 'name';
$GO_FIELDS['name']['input_type'] = 'text';
$GO_FIELDS['name']['autoname']   = true;



$GO_FIELDS['otherserial']['name']       = __("Inventory number");
$GO_FIELDS['otherserial']['field']      = 'otherserial';
$GO_FIELDS['otherserial']['input_type'] = 'text';
$GO_FIELDS['otherserial']['autoname']   = true;

$GO_FIELDS['comment']['name']       = __("Comments");
$GO_FIELDS['comment']['field']      = 'comment';
$GO_FIELDS['comment']['input_type'] = 'multitext';

$GO_FIELDS['other']['name']         = __("Others");
$GO_FIELDS['other']['input_type']   = 'text';

$GO_FIELDS['creationdate']['name']       = __("Creation date");
$GO_FIELDS['creationdate']['input_type'] = 'date';

$GO_FIELDS['expirationdate']['name']       = __("Expiration date");
$GO_FIELDS['expirationdate']['input_type'] = 'date';

$GO_FIELDS['date_mod']['name']       = __("Last update");
$GO_FIELDS['date_mod']['input_type'] = 'datetime';

$GO_FIELDS['date_creation']['name']       = __('Creation date');
$GO_FIELDS['date_creation']['input_type'] = 'datetime';

$GO_FIELDS['url']['name']       = __("URL");
$GO_FIELDS['url']['field']      = 'url';
$GO_FIELDS['url']['input_type'] = 'text';
$GO_FIELDS['url']['datatype']   = 'weblink';

$GO_FIELDS['locations_id']['name']       = __("Location");
$GO_FIELDS['locations_id']['input_type'] = 'dropdown';

$GO_FIELDS['manufacturers_id']['name']       = __("Manufacturer");
$GO_FIELDS['manufacturers_id']['input_type'] = 'text';


// The 'isolated' dropdown type will create a isolated table for each type that will be assigned
// with this field.



//Asset fields
$GO_FIELDS['description_id']['name']          = __("Description");
$GO_FIELDS['description_id']['input_type']    = 'text';

$GO_FIELDS['manu_num']['name']       = __("Manufacturer Number");
$GO_FIELDS['manu_num']['input_type'] = 'text';

$GO_FIELDS['id_tag']['name']          = __("ID tag");
$GO_FIELDS['id_tag']['input_type']    = 'text';

$GO_FIELDS['item_loc']['name']       = __("Item Location");
$GO_FIELDS['item_loc']['input_type'] = 'text';


$GO_FIELDS['supplier_id']['name']       = __("Supplier");
$GO_FIELDS['supplier_id']['input_type'] = 'text';

$GO_FIELDS['serial']['name']       = __("Serial number");
$GO_FIELDS['serial']['input_type'] = 'text';

$GO_FIELDS['memory']['name']       = __("Memory");
$GO_FIELDS['memory']['input_type'] = 'text';



$GO_FIELDS['entities_id']['name']          = __("Entity");
$GO_FIELDS['entities_id']['input_type']    = 'dropdown';
$GO_FIELDS['entities_id']['massiveaction'] = false;


$GO_FIELDS['notepad']['name']       = _n('Note', 'Notes', 2);
$GO_FIELDS['notepad']['input_type'] = 'multitext';

/*

Use the template below if you want to add another field to assets. When the fields are added, go to Setup> Objects Managment > asset > fields.
At the bottom of the page there should be a dropdown with the new field you added. Move it up and down to set up its location on the asset form.
Select Preview on the left side of this page to see how the asset form will look.

You can also update the new field for multiple assets at once by going to the assets page and checking the assets you want.
When you have selected the assets you want to update, click the Actions button at the top or bottom of the page and fill in what you want to update.
///////////////New Field Template/////////////////


$GO_FIELDS['variable name']['name']       = __("Field Name");
$GO_FIELDS['variable name']['input_type'] = 'text';

*/



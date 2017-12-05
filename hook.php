<?php
/*
 -------------------------------------------------------------------------
 MyBarcode plugin for GLPI
 Copyright (C) 2017 by the MyBarcode Development Team.

 https://github.com/pluginsGLPI/mybarcode
 -------------------------------------------------------------------------

 LICENSE

 This file is part of MyBarcode.

 MyBarcode is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 MyBarcode is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with MyBarcode. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_mybarcode_MassiveActions($type) {

 $actions = [];
   switch ($type) {
      case 'Computer' :
      case 'Monitor' :
      case 'Networking' :
      case 'Printer' :
      case 'Peripheral' :
      case 'Phone' :
         $action_key   = 'Generate';
         $action_label = __('Barcode', 'mybarcode')." - ".__('Print barcodes', 'mybarcode');
         $actions["PluginMybarcodeMybarcode".MassiveAction::CLASS_ACTION_SEPARATOR.$action_key]= $action_label;

         break;
   }
   return $actions;
}



/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_mybarcode_install() {
   if (!file_exists(GLPI_PLUGIN_DOC_DIR."/mybarcode")) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/mybarcode");
   }
   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_mybarcode_uninstall() {
   return true;
}

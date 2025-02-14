<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 shellcommands plugin for GLPI
 Copyright (C) 2009-2022 by the shellcommands Development Team.

 https://github.com/InfotelGLPI/shellcommands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of shellcommands.

 shellcommands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 shellcommands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginShellcommandsShellcommand extends CommonDBTM {

   static $types = ['Computer', 'NetworkEquipment', 'Peripheral',
                    'Phone', 'Printer', 'Appliance', 'PluginWebapplicationsAppliance'];

   static $rightname = 'plugin_shellcommands';

   public $dohistory = true;

   const KO_RESULT       = 0;
   const OK_RESULT       = 1;
   const WARNING_RESULT  = 2;
   const CRITICAL_RESULT = 3;

   public static function getTypeName($nb = 0) {
      return _n('Shell Command', 'Shell Commands', $nb, 'shellcommands');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   function getFromDBbyName($name) {
      global $DB;

      $query = "SELECT * FROM `" . $this->gettable() . "` " .
               "WHERE (`name` = '" . $name . "') ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   function cleanDBonPurge() {

      $temp = new PluginShellcommandsShellcommand_Item();
      $temp->deleteByCriteria(['plugin_shellcommands_shellcommands_id' => $this->fields['id']]);

      $path = new PluginShellcommandsShellcommandPath();
      $path->deleteByCriteria(['plugin_shellcommands_shellcommands_id' => $this->fields['id']]);
   }

   function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'       => '1',
         'table'    => $this->getTable(),
         'field'    => 'name',
         'name'     => __('Name'),
         'datatype' => 'itemlink'
      ];

      $tab[] = [
         'id'    => '2',
         'table' => $this->getTable(),
         'field' => 'link',
         'name'  => __('Tag')
      ];

      $tab[] = [
         'id'        => '3',
         'table'     => 'glpi_plugin_shellcommands_shellcommandpaths',
         'field'     => 'name',
         'linkfield' => 'plugin_shellcommands_shellcommandpaths_id',
         'name'      => __('Path', 'shellcommands'),
         'datatype'  => 'itemlink'
      ];

      $tab[] = [
         'id'    => '4',
         'table' => $this->getTable(),
         'field' => 'parameters',
         'name'  => __('Windows', 'shellcommands')
      ];

      $tab[] = [
         'id'            => '5',
         'table'         => 'glpi_plugin_shellcommands_shellcommands_items',
         'field'         => 'itemtype',
         'nosearch'      => true,
         'massiveaction' => false,
         'name'          => _n('Associated item type', 'Associated item types', 2),
         'forcegroupby'  => true,
         'joinparams'    => [
            'jointype' => 'child'
         ],
         'datatype'      => 'dropdown'
      ];

      $tab[] = [
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'integer'
      ];

      $tab[] = [
         'id'       => '80',
         'table'    => 'glpi_entities',
         'field'    => 'completename',
         'name'     => __('Entity'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '86',
         'table'    => $this->getTable(),
         'field'    => 'is_recursive',
         'name'     => __('Child entities'),
         'datatype' => 'bool'
      ];

      return $tab;
   }

   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginShellcommandsShellcommand_Item', $ong, $options);
      $this->addStandardTab('PluginShellcommandsCommandGroup_Item', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
      echo "</td>";

      echo "<td>" . __('Valid tags') . "</td>";
      echo "<td>[ID], [NAME], [URL], [IP], [MAC], [NETWORK], [DOMAIN]</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Tag') . "</td>";
      echo "<td>";
      echo Html::input('link', ['value' => $this->fields['link'], 'size' => 50]);
      echo "</td>";

      echo "<td>" . __('Tag position', 'shellcommands') . "</td>";
      echo "<td>";
      Dropdown::showFromArray("tag_position", [__('Before parameters', 'shellcommands'), __('After parameters', 'shellcommands')], ['value' => $this->fields["tag_position"]]);
      echo "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Windows', 'shellcommands') . "</td>";
      echo "<td>";
      echo Html::input('parameters', ['value' => $this->fields['parameters'], 'size' => 40]);
      echo "</td>";

      echo "<td>" . __('Path', 'shellcommands') . "</td>";
      echo "<td>";
      Dropdown::show('PluginShellcommandsShellcommandPath', ['value' => $this->fields["plugin_shellcommands_shellcommandpaths_id"]]);
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */

   function getSelectLinkedItem() {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_shellcommands_shellcommands_items`
              WHERE `plugin_shellcommands_shellcommands_id`='" . $this->fields['id'] . "'";
   }

   function dropdownCommands($itemtype) {
      global $DB;

      $query = "SELECT `" . $this->gettable() . "`.`id`, `" . $this->gettable() . "`.`name`,`" . $this->gettable() . "`.`link`
          FROM `" . $this->gettable() . "`,`glpi_plugin_shellcommands_shellcommands_items`
          WHERE `" . $this->gettable() . "`.`id` = `glpi_plugin_shellcommands_shellcommands_items`.`plugin_shellcommands_shellcommands_id`
          AND `glpi_plugin_shellcommands_shellcommands_items`.`itemtype` = '" . $itemtype . "'
          AND `" . $this->gettable() . "`.`is_deleted` = '0'
          ORDER BY `" . $this->gettable() . "`.`name`";

      $result   = $DB->query($query);
      $number   = $DB->numrows($result);
      $elements = [Dropdown::EMPTY_VALUE];
      if ($number != "0") {
         while ($data = $DB->fetchAssoc($result)) {
            $elements[$data["id"]] = $data["name"];
         }
      }

      Dropdown::showFromArray('command', $elements);
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @param $type string class name
    * *@since version 1.3.0
    *
    */
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }

   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * Get the specific massive actions
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    **@since version 0.84
    *
    */
   public function getSpecificMassiveActions($checkitem = null) {
      $actions = parent::getSpecificMassiveActions($checkitem);

      $actions['PluginShellcommandsShellcommand' . MassiveAction::CLASS_ACTION_SEPARATOR . 'install']   = _x('button', 'Associate');
      $actions['PluginShellcommandsShellcommand' . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall'] = _x('button', 'Dissociate');

      return $actions;
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "install":
            Dropdown::showItemTypes("item_item", self::getTypes(true));
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-primary']);
            return true;
            break;
         case "uninstall":
            Dropdown::showItemTypes("item_item", self::getTypes(true));
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-primary']);
            return true;
            break;
         case 'generate':
            $PluginShellcommandsShellcommand = new PluginShellcommandsShellcommand();
            $itemtype                        = $ma->getItemtype(false);
            if (in_array($itemtype, PluginShellcommandsShellcommand::getTypes(true))) {
               $PluginShellcommandsShellcommand->dropdownCommands($itemtype);
               echo "<br><br>";
            }
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array         $ids) {
      global $CFG_GLPI;

      $command_item = new PluginShellcommandsShellcommand_Item();

      switch ($ma->getAction()) {

         case 'install' :
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($command_item->addItem($key, $input['item_item'])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            break;
         case 'uninstall':
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($command_item->deleteItemByShellCommandsAndItem($key, $input['item_item'])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            break;
         case 'generate':
            if ($ma->POST['command']) {
               $_SESSION["plugin_shellcommands"]["massiveaction"] = $ma;
               $_SESSION["plugin_shellcommands"]["ids"]           = $ids;

               $ma->results['ok']         = 1;
               $ma->display_progress_bars = false;

               echo "<script type='text/javascript'>";
               echo "location.href='" . PLUGIN_SHELLCOMMANDS_WEBDIR . "/front/massiveexec.php';";
               echo "</script>";

            }
            break;
      }
   }

   /**
    * Handle shellcommand message
    *
    * @param $message
    *
    **/
   static function handleShellcommandResult($error, $message) {

      if (preg_match('/^WARNING/i', $message)) {
         return self::WARNING_RESULT;

      } else if (preg_match('/^OK/i', $message)) {
         return self::OK_RESULT;

      } else if (preg_match('/^CRITICAL/i', $message)) {
         return self::CRITICAL_RESULT;

      } else {
         if ($error) {
            return self::KO_RESULT;
         }
         return self::OK_RESULT;
      }
   }

   /**
    *  Display command result
    *
    * @param $message
    *
    **/
   static function displayCommandResult(PluginShellcommandsShellcommand $shellcommands, $targetParam, $message, $error) {
      global $CFG_GLPI;

      $result = PluginShellcommandsShellcommand::handleShellcommandResult($error, $message);

      // Result icon
      echo "<tr class='tab_bg_1 shellcommands_result_line'>";
      switch ($result) {
         case PluginShellcommandsShellcommand::OK_RESULT :
            echo "<td class='center'><i style='color:forestgreen' class='fas fa-check-circle fa-2x'></i></td>";
            break;
         case PluginShellcommandsShellcommand::KO_RESULT:
         case PluginShellcommandsShellcommand::WARNING_RESULT :
            echo "<td class='center'><i style='color:orange' class='fas fa-exclamation-triangle fa-2x'></i></td>";
            break;
         case PluginShellcommandsShellcommand::CRITICAL_RESULT :
            echo "<td class='center'><i style='color:darkred' class='fas fa-times-circle fa-2x'></i></td>";
            break;
      }

      echo "<td class='center'>" . $shellcommands->getName() . "</td>";

      // Result short message
      switch ($result) {
         case PluginShellcommandsShellcommand::OK_RESULT :
            echo "<td class='center'><div class='shellcommands_result_ok'>OK</div></td>";
            break;
         case PluginShellcommandsShellcommand::WARNING_RESULT :
            echo "<td class='center'><div class='shellcommands_result_warning'>WARNING</div></td>";
            break;
         case PluginShellcommandsShellcommand::KO_RESULT :
            echo "<td class='center'><div class='shellcommands_result_ko'>KO</div></td>";
            break;
         case PluginShellcommandsShellcommand::CRITICAL_RESULT :
            echo "<td class='center'><div class='shellcommands_result_ko'>CRITICAL</div></td>";
            break;
      }

      echo "<td>";
      if ($command = PluginShellcommandsShellcommand_Item::getCommandLine($shellcommands->getID(), $targetParam)) {
         echo "<b> > " . $command . "</b><br>";
      }
      if ($shellcommands->getName() !== PluginShellcommandsShellcommand_Item::WOL_COMMAND_NAME) {
         echo "<span class='shellcommands_font_blue'>" . nl2br($message) . "</span>";
      } else {
         echo nl2br($message);
      }
      echo "</td>";
      echo "</tr>";
   }

   static function getMenuContent() {
      $plugin_page = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR."/front/menu.php";
      $menu        = [];
      //Menu entry in helpdesk
      $menu['title']           = self::getTypeName(2);
      $menu['page']            = $plugin_page;
      $menu['links']['search'] = $plugin_page;

      $menu['options']['shellcommand']['title']           = _n('Shell Command', 'Shell Commands', 2, 'shellcommands');
      $menu['options']['shellcommand']['page']            = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR.'/front/shellcommand.php';
      $menu['options']['shellcommand']['links']['add']    = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR.'front/shellcommand.form.php';
      $menu['options']['shellcommand']['links']['search'] = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR.'front/shellcommand.php';

      $menu['options']['commandgroup']['title']           = _n('Command group', 'Command groups', 2, 'shellcommands');
      $menu['options']['commandgroup']['page']            = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR.'front/commandgroup.php';
      $menu['options']['commandgroup']['links']['add']    = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR.'front/commandgroup.form.php';
      $menu['options']['commandgroup']['links']['search'] = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR.'front/commandgroup.php';

      $menu['options']['advanced_execution']['title'] = _n('Advanced execution', 'Advanced executions', 2, 'shellcommands');
      $menu['options']['advanced_execution']['page']  = PLUGIN_SHELLCOMMANDS_NOTFULL_DIR.'front/advanced_execution.php';

      $menu['icon'] = self::getIcon();

      return $menu;
   }

   /**
    * @return string
    */
   static function getIcon() {
      return "ti ti-keyboard";
   }


   /**
    * Custom fonction to process shellcommand massive action
    **/
   function doMassiveAction(MassiveAction $ma, array $ids) {

      if (!empty($ids)) {
         $input = $ma->getInput();

         $itemtype    = $ma->getItemType(false);
         $commands_id = $input['command'];

         switch ($ma->getAction()) {
            case 'generate':
               $dbu                = new DbUtils();
               $shellcommands_item = new PluginShellcommandsShellcommand_Item();
               $shellcommands      = new PluginShellcommandsShellcommand();
               $item               = $dbu->getItemForItemtype($itemtype);

               echo "<div class='center'>";
               echo "<table class='tab_cadre_fixe center'>";
               echo "<tr class='tab_bg_1'>";
               echo "<th colspan='4'>" . PluginShellcommandsShellcommand::getTypeName(2) . "</th>";
               echo "</tr>";

               $error   = 1;
               $message = '';
               foreach ($ids as $key => $items_id) {
                  if (!$shellcommands_item->getFromDBbyShellCommandsAndItem($commands_id, $itemtype)) {
                     continue;
                  }
                  $shellcommands->getFromDB($commands_id);
                  $item->getFromDB($items_id);
                  $targetParam = PluginShellcommandsShellcommand_Item::resolveLinkOfCommand($shellcommands->getID(), $item);
                  // Exec command on each targets : stop on first success
                  $selectedTarget = null;
                  if ($targetParam !== false && !empty($targetParam)) {
                     foreach ($targetParam as $target) {
                        list($error, $message) = PluginShellcommandsShellcommand_Item::execCommand($shellcommands->getID(), $target);
                        if (!$error) {
                           $selectedTarget = $target;
                           break;
                        }
                     }
                  }

                  echo "<tr class='tab_bg_1 shellcommands_result_line'>";
                  echo "<td class='center' colspan='4'>" . __($item->getType()) . ' : ' . $item->getLink() . " - " . $selectedTarget . "</td>";
                  echo "</tr>";

                  PluginShellcommandsShellcommand::displayCommandResult($shellcommands, $selectedTarget, $message, $error);
               }
               echo "</table>";
               echo "</div>";
               break;
         }
      }
   }

}


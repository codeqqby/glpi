<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2017 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

/** @file
* @brief
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 *  Stat class
**/
class Stat extends CommonGLPI {

   static $rightname = 'statistic';


   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }


   static function getTypeName($nb=0) {
      return __('Statistics');
   }


   /**
    * @see CommonGLPI::getMenuShorcut()
    *
    * @since version 0.85
   **/
   static function getMenuShorcut() {
      return 'a';
   }


   /**
    * @param $itemtype
    * @param $date1
    * @param $date2
    * @param $type
    * @param $parent    (default 0)
   **/
   static function getItems($itemtype, $date1, $date2, $type, $parent=0) {
      global $CFG_GLPI, $DB;

      if (!$item = getItemForItemtype($itemtype)) {
         return;
      }
      $val  = array();
      $cond = '';

      switch ($type) {
         case "technicien" :
            $val = $item->getUsedTechBetween($date1, $date2);
            break;

         case "technicien_followup" :
            $val = $item->getUsedTechTaskBetween($date1, $date2);
            break;

         case "suppliers_id_assign" :
            $val = $item->getUsedSupplierBetween($date1, $date2);
            break;

         case "user" :
            $val = $item->getUsedAuthorBetween($date1, $date2);
            break;

         case "users_id_recipient" :
            $val = $item->getUsedRecipientBetween($date1, $date2);
            break;

         case 'group_tree' :
         case 'groups_tree_assign' :
            // Get all groups
            $query = "SELECT `id`, `name`
                      FROM `glpi_groups`".
                      getEntitiesRestrictRequest(" WHERE", "glpi_groups", '', '', true)."
                            AND (`id` = $parent OR `groups_id` = '$parent')
                            AND ".(($type == 'group_tree') ? '`is_requester`' : '`is_assign`')."
                      ORDER BY `completename`";

            $result = $DB->query($query);
            $val    = array();
            if ($DB->numrows($result) >= 1) {
               while ($line = $DB->fetch_assoc($result)) {
                  $tmp['id']   = $line["id"];
                  $tmp['link'] = $line["name"];
                  $val[]       = $tmp;
               }
            }
            break;

         case "itilcategories_tree" :
            $cond = "AND (`id` = '$parent'
                          OR `itilcategories_id` = '$parent')";
            // nobreak

         case "itilcategories_id" :
            // Get all ticket categories for tree merge management
            $query = "SELECT DISTINCT `glpi_itilcategories`.`id`,
                             `glpi_itilcategories`.`".($cond?'name':'completename')."` AS category
                      FROM `glpi_itilcategories`".
                      getEntitiesRestrictRequest(" WHERE", "glpi_itilcategories", '', '', true)."
                            $cond
                      ORDER BY `completename`";

            $result = $DB->query($query);
            $val    = array();
            if ($DB->numrows($result) >= 1) {
               while ($line = $DB->fetch_assoc($result)) {
                  $tmp['id']   = $line["id"];
                  $tmp['link'] = $line["category"];
                  $val[]       = $tmp;
               }
            }
            break;

         case 'locations_tree' :
            $cond = "AND (`id` = '$parent'
                          OR `locations_id` = '$parent')";
            // nobreak

         case 'locations_id' :
            // Get all locations for tree merge management
            $query = "SELECT DISTINCT `glpi_locations`.`id`,
                             `glpi_locations`.`".($cond?'name':'completename')."` AS location
                      FROM `glpi_locations`".
                      getEntitiesRestrictRequest(' WHERE', 'glpi_locations', '', '', true)."
                            $cond
                      ORDER BY `completename`";

            $result = $DB->query($query);
            $val    = array();
            if ($DB->numrows($result) >= 1) {
               while ($line = $DB->fetch_assoc($result)) {
                  $tmp['id']   = $line['id'];
                  $tmp['link'] = $line['location'];
                  $val[]       = $tmp;
               }
            }
            break;

         case "type" :
            $types = $item->getTypes();
            $val   = array();
            foreach ($types as $id => $v) {
               $tmp['id']   = $id;
               $tmp['link'] = $v;
               $val[]       = $tmp;
            }
            break;

         case "group" :
            $val = $item->getUsedGroupBetween($date1, $date2);
            break;

         case "groups_id_assign" :
            $val = $item->getUsedAssignGroupBetween($date1, $date2);
            break;

         case "priority" :
            $val = $item->getUsedPriorityBetween($date1, $date2);
            break;

         case "urgency" :
            $val = $item->getUsedUrgencyBetween($date1, $date2);
            break;

         case "impact" :
            $val = $item->getUsedImpactBetween($date1, $date2);
            break;

         case "requesttypes_id" :
            $val = $item->getUsedRequestTypeBetween($date1, $date2);
            break;

         case "solutiontypes_id" :
            $val = $item->getUsedSolutionTypeBetween($date1, $date2);
            break;

         case "usertitles_id" :
            $val = $item->getUsedUserTitleOrTypeBetween($date1, $date2, true);
            break;

         case "usercategories_id" :
            $val = $item->getUsedUserTitleOrTypeBetween($date1, $date2, false);
            break;

         // DEVICE CASE
         default :
            if (($item = getItemForItemtype($type))
                && ($item instanceof CommonDevice)) {
               $device_table = $item->getTable();

               //select devices IDs (table row)
               $query = "SELECT `id`, `designation`
                         FROM `".$device_table."`
                         ORDER BY `designation`";
               $result = $DB->query($query);

               if ($DB->numrows($result) >= 1) {
                  $i = 0;
                  while ($line = $DB->fetch_assoc($result)) {
                     $val[$i]['id']   = $line['id'];
                     $val[$i]['link'] = $line['designation'];
                     $i++;
                  }
               }

            } else {
               // Dropdown case for computers
               $field = "name";
               $table = getTableFOrItemType($type);
               if (($item = getItemForItemtype($type))
                   && ($item instanceof CommonTreeDropdown)) {
                  $field = "completename";
               }
               $where = '';
               $order = " ORDER BY `$field`";
               if ($item->isEntityAssign()) {
                  $where = getEntitiesRestrictRequest(" WHERE", $table);
                  $order = " ORDER BY `entities_id`, `$field`";
               }

               $query = "SELECT *
                         FROM `$table`
                         $where
                         $order";

               $val    = array();
               $result = $DB->query($query);
               if ($DB->numrows($result) > 0) {
                  while ($line = $DB->fetch_assoc($result)) {
                     $tmp['id']   = $line["id"];
                     $tmp['link'] = $line[$field];
                     $val[]       = $tmp;
                  }
               }
            }
      }
      return $val;
   }


   /**
    * @param $itemtype
    * @param $type
    * @param $date1
    * @param $date2
    * @param $start
    * @param $value     array
    * @param $value2             (default '')
   **/
   static function getData($itemtype, $type, $date1, $date2, $start, array $value, $value2="") {

      $export_data = array();

      if (is_array($value)) {
         $end_display = $start+$_SESSION['glpilist_limit'];
         $numrows     = count($value);

         for ($i=$start; $i< $numrows && $i<($end_display); $i++) {
            //le nombre d'intervention - the number of intervention
            $opened    = self::constructEntryValues($itemtype, "inter_total", $date1, $date2,
                                                    $type, $value[$i]["id"], $value2);
            $nb_opened = array_sum($opened);
            $export_data['opened'][$value[$i]['link']] = $nb_opened;

            //le nombre d'intervention resolues - the number of solved intervention
            $solved    = self::constructEntryValues($itemtype, "inter_solved", $date1, $date2,
                                                    $type, $value[$i]["id"], $value2);
            $nb_solved = array_sum($solved);
            $export_data['solved'][$value[$i]['link']] = $nb_solved;

            //le nombre d'intervention resolues - the number of solved late intervention
            $late      = self::constructEntryValues($itemtype, "inter_solved_late", $date1, $date2,
                                                  $type, $value[$i]["id"], $value2);
            $nb_late   = array_sum($late);
            $export_data['late'][$value[$i]['link']] = $nb_late;

            //le nombre d'intervention closes - the number of closed intervention
            $closed    = self::constructEntryValues($itemtype, "inter_closed", $date1, $date2,
                                                    $type, $value[$i]["id"], $value2);
            $nb_closed = array_sum($closed);
            $export_data['closed'][$value[$i]['link']] = $nb_closed;

            if ($itemtype == 'Ticket') {
               //open satisfaction
               $opensatisfaction    = self::constructEntryValues($itemtype, "inter_opensatisfaction",
                                                                 $date1, $date2, $type,
                                                                 $value[$i]["id"], $value2);
               $nb_opensatisfaction = array_sum($opensatisfaction);
               $export_data['opensatisfaction'][$value[$i]['link']] = $nb_opensatisfaction;
            }

         }
      }
      return $export_data;
   }


   /**
    * @param $itemtype
    * @param $type
    * @param $date1
    * @param $date2
    * @param $start
    * @param $value     array
    * @param $value2          (default '')
    *
    * @since version 0.85 (before show with same parameters)
   **/
   static function showTable($itemtype, $type, $date1, $date2, $start, array $value, $value2="") {
      global $CFG_GLPI;

      // Set display type for export if define
      $output_type = Search::HTML_OUTPUT;
      if (isset($_GET["display_type"])) {
         $output_type = $_GET["display_type"];
      }

      if ($output_type == Search::HTML_OUTPUT) { // HTML display
         echo "<div class ='center'>";
      }

      if (is_array($value)) {
         $end_display = $start+$_SESSION['glpilist_limit'];
         $numrows     = count($value);

         if (isset($_GET['export_all'])) {
            $start       = 0;
            $end_display = $numrows;
         }

         $nbcols = 8;
         if ($output_type != Search::HTML_OUTPUT) { // not HTML display
            $nbcols--;
         }

         echo Search::showHeader($output_type, $end_display-$start+1, $nbcols);
         $subname = '';
         switch ($type) {
            case 'group_tree' :
            case 'groups_tree_assign' :
               $subname = Dropdown::getDropdownName('glpi_groups', $value2);
               break;

            case 'itilcategories_tree' :
               $subname = Dropdown::getDropdownName('glpi_itilcategories', $value2);
               break;

            case 'locations_tree' :
               $subname = Dropdown::getDropdownName('glpi_locations', $value2);
               break;
         }

         if ($output_type == Search::HTML_OUTPUT) { // HTML display
            echo Search::showNewLine($output_type);
            $header_num = 1;

            if (($output_type == Search::HTML_OUTPUT)
                && strstr($type, '_tree')
                && $value2) {
               // HTML display
               $link = $_SERVER['PHP_SELF'].
                       "?date1=$date1&amp;date2=$date2&amp;itemtype=$itemtype&amp;type=$type".
                       "&amp;value2=0";
               $link = "<a href='$link'>".__('Back')."</a>";
               echo Search::showHeaderItem($output_type, $link, $header_num);
            } else {
               echo Search::showHeaderItem($output_type, "&nbsp;", $header_num);
            }
            echo Search::showHeaderItem($output_type, '', $header_num);

            echo Search::showHeaderItem($output_type, _x('quantity', 'Number'),
                                        $header_num, '', 0, '', "colspan='4'");
            if ($itemtype =='Ticket') {
               echo Search::showHeaderItem($output_type, __('Satisfaction'), $header_num, '',
                                           0, '', "colspan='3'");
            }
            echo Search::showHeaderItem($output_type, __('Average time'), $header_num, '', 0, '',
                                        $itemtype =='Ticket'?"colspan='3'":"colspan='2'");
            echo Search::showHeaderItem($output_type,
                                        __('Real duration of treatment of the ticket'),
                                        $header_num, '', 0, '', "colspan='2'");
         }

         echo Search::showNewLine($output_type);
         $header_num    = 1;
         echo Search::showHeaderItem($output_type, $subname, $header_num);

         if ($output_type == Search::HTML_OUTPUT) { // HTML display
            echo Search::showHeaderItem($output_type, "", $header_num);
         }
         if ($output_type != Search::HTML_OUTPUT) {
            echo Search::showHeaderItem($output_type, __('Number of opened tickets'), $header_num);
            echo Search::showHeaderItem($output_type, __('Number of solved tickets'), $header_num);
            echo Search::showHeaderItem($output_type, __('Number of late tickets'), $header_num);
            echo Search::showHeaderItem($output_type, __('Number of closed tickets'), $header_num);
         } else {
            echo Search::showHeaderItem($output_type, _nx('ticket', 'Opened', 'Opened', Session::getPluralNumber()), $header_num);
            echo Search::showHeaderItem($output_type, _nx('ticket', 'Solved', 'Solved', Session::getPluralNumber()),
                                        $header_num);
            echo Search::showHeaderItem($output_type, __('Late'), $header_num);
            echo Search::showHeaderItem($output_type, __('Closed'), $header_num);
         }

         if ($itemtype =='Ticket') {
            if ($output_type != Search::HTML_OUTPUT) {
               echo Search::showHeaderItem($output_type, __('Number of opened satisfaction survey'),
                                           $header_num);
               echo Search::showHeaderItem($output_type,
                                           __('Number of answered satisfaction survey'),
                                           $header_num);
               echo Search::showHeaderItem($output_type, __('Average satisfaction'),
                                           $header_num);

            } else {
               echo Search::showHeaderItem($output_type, _nx('survey', 'Opened', 'Opened', Session::getPluralNumber()),
                                           $header_num);
               echo Search::showHeaderItem($output_type, _nx('survey', 'Answered', 'Answered', Session::getPluralNumber()),
                                           $header_num);
               echo Search::showHeaderItem($output_type, __('Average'), $header_num);
            }
         }

         if ($output_type != Search::HTML_OUTPUT) {
            if ($itemtype =='Ticket') {
               echo Search::showHeaderItem($output_type, __('Average time to take into account'),
                                          $header_num);
            }
            echo Search::showHeaderItem($output_type, __('Average time to resolution'), $header_num);
            echo Search::showHeaderItem($output_type, __('Average time to closure'), $header_num);
         } else {
            if ($itemtype =='Ticket') {
               echo Search::showHeaderItem($output_type, __('Take into account'), $header_num);
            }
            echo Search::showHeaderItem($output_type, __('Resolution'), $header_num);
            echo Search::showHeaderItem($output_type, __('Closure'), $header_num);
         }

         if ($output_type != Search::HTML_OUTPUT) {
            echo Search::showHeaderItem($output_type,
                                        __('Average real duration of treatment of the ticket'),
                                        $header_num);
            echo Search::showHeaderItem($output_type,
                                        __('Total real duration of treatment of the ticket'),
                                        $header_num);
         } else {
            echo Search::showHeaderItem($output_type, __('Average'), $header_num);
            echo Search::showHeaderItem($output_type, __('Total duration'), $header_num);
         }
         // End Line for column headers
         echo Search::showEndLine($output_type);
         $row_num = 1;

         for ($i=$start; ($i<$numrows) && ($i<$end_display); $i++) {
            $row_num  ++;
            $item_num = 1;
            echo Search::showNewLine($output_type, $i%2);
            if (($output_type == Search::HTML_OUTPUT)
                && strstr($type, '_tree')
                && ($value[$i]['id'] != $value2)) {
               // HTML display
               $link = $_SERVER['PHP_SELF'].
                       "?date1=$date1&amp;date2=$date2&amp;itemtype=$itemtype&amp;type=$type".
                       "&amp;value2=".$value[$i]['id'];
               $link = "<a href='$link'>".$value[$i]['link']."</a>";
               echo Search::showItem($output_type, $link, $item_num, $row_num);
            } else {
               echo Search::showItem($output_type, $value[$i]['link'], $item_num, $row_num);
            }

            if ($output_type == Search::HTML_OUTPUT) { // HTML display
               $link = "";
               if ($value[$i]['id'] > 0) {
                  $link = "<a href='stat.graph.php?id=".$value[$i]['id'].
                            "&amp;date1=$date1&amp;date2=$date2&amp;itemtype=$itemtype&amp;type=$type".
                            (!empty($value2)?"&amp;champ=$value2":"")."'>".
                          "<img src='".$CFG_GLPI["root_doc"]."/pics/stats_item.png' alt='' title=''>".
                          "</a>";
               }
               echo Search::showItem($output_type, $link, $item_num, $row_num);
            }

            //le nombre d'intervention - the number of intervention
            $opened    = self::constructEntryValues($itemtype, "inter_total", $date1, $date2, $type,
                                                    $value[$i]["id"], $value2);
            $nb_opened = array_sum($opened);
            echo Search::showItem($output_type, $nb_opened, $item_num, $row_num);

            //le nombre d'intervention resolues - the number of solved intervention
            $solved    = self::constructEntryValues($itemtype, "inter_solved", $date1, $date2,
                                                    $type, $value[$i]["id"], $value2);
            $nb_solved = array_sum($solved);
            echo Search::showItem($output_type, $nb_solved, $item_num, $row_num);

            //le nombre d'intervention resolues - the number of solved intervention
            $solved_late    = self::constructEntryValues($itemtype, "inter_solved_late", $date1,
                                                         $date2, $type, $value[$i]["id"], $value2);
            $nb_solved_late = array_sum($solved_late);
            echo Search::showItem($output_type, $nb_solved_late, $item_num, $row_num);

            //le nombre d'intervention closes - the number of closed intervention
            $closed    = self::constructEntryValues($itemtype, "inter_closed", $date1, $date2,
                                                    $type, $value[$i]["id"], $value2);
            $nb_closed = array_sum($closed);

            echo Search::showItem($output_type, $nb_closed, $item_num, $row_num);

            if ($itemtype =='Ticket') {
               //Satisfaction open
               $opensatisfaction    = self::constructEntryValues($itemtype, "inter_opensatisfaction",
                                                                 $date1, $date2, $type,
                                                                 $value[$i]["id"], $value2);
               $nb_opensatisfaction = array_sum($opensatisfaction);
               echo Search::showItem($output_type, $nb_opensatisfaction, $item_num, $row_num);

               //Satisfaction answer
               $answersatisfaction    = self::constructEntryValues($itemtype,
                                                                   "inter_answersatisfaction",
                                                                   $date1, $date2, $type,
                                                                   $value[$i]["id"], $value2);
               $nb_answersatisfaction = array_sum($answersatisfaction);
               echo Search::showItem($output_type, $nb_answersatisfaction, $item_num, $row_num);

               //Satisfaction rate
               $satisfaction = self::constructEntryValues($itemtype, "inter_avgsatisfaction", $date1,
                                                          $date2, $type, $value[$i]["id"], $value2);
               foreach ($satisfaction as $key2 => $val2) {
                  $satisfaction[$key2] *= $answersatisfaction[$key2];
               }
               if ($nb_answersatisfaction > 0) {
                  $avgsatisfaction = round(array_sum($satisfaction)/$nb_answersatisfaction, 1);
                  if ($output_type == Search::HTML_OUTPUT) {
                     $avgsatisfaction = TicketSatisfaction::displaySatisfaction($avgsatisfaction);
                  }
               } else {
                  $avgsatisfaction = '&nbsp;';
               }
               echo Search::showItem($output_type, $avgsatisfaction, $item_num, $row_num);

               //Le temps moyen de prise en compte du ticket - The average time to take a ticket into account
               $data = self::constructEntryValues($itemtype, "inter_avgtakeaccount", $date1, $date2,
                                                  $type, $value[$i]["id"], $value2);
               foreach ($data as $key2 => $val2) {
                  $data[$key2] *= $solved[$key2];
               }

               if ($nb_solved > 0) {
                  $timedisplay = array_sum($data)/$nb_solved;
               } else {
                  $timedisplay = 0;
               }

               if (($output_type == Search::HTML_OUTPUT)
                  || ($output_type == Search::PDF_OUTPUT_LANDSCAPE)
                  || ($output_type == Search::PDF_OUTPUT_PORTRAIT)) {
                  $timedisplay = Html::timestampToString($timedisplay, 0, false);
               }
               echo Search::showItem($output_type, $timedisplay, $item_num, $row_num);
            }

            //Le temps moyen de resolution - The average time to resolv
            $data = self::constructEntryValues($itemtype, "inter_avgsolvedtime", $date1, $date2,
                                               $type, $value[$i]["id"], $value2);
            foreach ($data as $key2 => $val2) {
               $data[$key2] = round($data[$key2]*$solved[$key2]);
            }

            if ($nb_solved > 0) {
               $timedisplay = array_sum($data)/$nb_solved;
            } else {
               $timedisplay = 0;
            }
            if (($output_type == Search::HTML_OUTPUT)
                || ($output_type == Search::PDF_OUTPUT_LANDSCAPE)
                || ($output_type == Search::PDF_OUTPUT_PORTRAIT)) {
               $timedisplay = Html::timestampToString($timedisplay, 0, false);
            }
            echo Search::showItem($output_type, $timedisplay, $item_num, $row_num);

            //Le temps moyen de cloture - The average time to close
            $data = self::constructEntryValues($itemtype, "inter_avgclosedtime", $date1, $date2,
                                               $type, $value[$i]["id"], $value2);
            foreach ($data as $key2 => $val2) {
               $data[$key2] = round($data[$key2]*$solved[$key2]);
            }

            if ($nb_closed > 0) {
               $timedisplay = array_sum($data)/$nb_closed;
            } else {
               $timedisplay = 0;
            }
            if (($output_type == Search::HTML_OUTPUT)
                || ($output_type == Search::PDF_OUTPUT_LANDSCAPE)
                || ($output_type == Search::PDF_OUTPUT_PORTRAIT)) {
               $timedisplay = Html::timestampToString($timedisplay, 0, false);
            }
            echo Search::showItem($output_type, $timedisplay, $item_num, $row_num);

            //Le temps moyen de l'intervention reelle - The average actiontime to resolv
            $data = self::constructEntryValues($itemtype, "inter_avgactiontime", $date1, $date2,
                                               $type, $value[$i]["id"], $value2);
            foreach ($data as $key2 => $val2) {
               if (isset($solved[$key2])) {
                  $data[$key2] *= $solved[$key2];
               } else {
                  $data[$key2] *= 0;
               }
            }
            $total_actiontime = array_sum($data);

            if ($nb_solved > 0) {
               $timedisplay = $total_actiontime/$nb_solved;
            } else {
               $timedisplay = 0;
            }

            if (($output_type == Search::HTML_OUTPUT)
                || ($output_type == Search::PDF_OUTPUT_LANDSCAPE)
                || ($output_type == Search::PDF_OUTPUT_PORTRAIT)) {
               $timedisplay = Html::timestampToString($timedisplay, 0, false);
            }
            echo Search::showItem($output_type, $timedisplay, $item_num, $row_num);
            //Le temps total de l'intervention reelle - The total actiontime to resolv
            $timedisplay = $total_actiontime;

            if (($output_type == Search::HTML_OUTPUT)
                || ($output_type == Search::PDF_OUTPUT_LANDSCAPE)
                || ($output_type == Search::PDF_OUTPUT_PORTRAIT)) {
               $timedisplay = Html::timestampToString($timedisplay, 0, false);
            }
            echo Search::showItem($output_type, $timedisplay, $item_num, $row_num);

            echo Search::showEndLine($output_type);
         }
         // Display footer
         echo Search::showFooter($output_type);

      } else {
         _e('No statistics are available');
      }

      if ($output_type == Search::HTML_OUTPUT) { // HTML display
         echo "</div>";
      }
   }


   /**
    * @param $itemtype
    * @param $type
    * @param $begin              (default '')
    * @param $end                (default '')
    * @param $param              (default '')
    * @param $value              (default '')
    * @param $value2             (default '')
    */
   static function constructEntryValues($itemtype, $type, $begin="", $end="", $param="", $value="",
                                        $value2="") {
      global $DB;

      if (!$item = getItemForItemtype($itemtype)) {
         return;
      }
      $table          = $item->getTable();
      $fkfield        = $item->getForeignKeyField();

      if (!($userlinkclass = getItemForItemtype($item->userlinkclass))) {
         return;
      }
      $userlinktable  = $userlinkclass->getTable();
      if (!$grouplinkclass = getItemForItemtype($item->grouplinkclass)) {
         return;
      }
      $grouplinktable = $grouplinkclass->getTable();

      if (!($supplierlinkclass = getItemForItemtype($item->supplierlinkclass))) {
         return;
      }
      $supplierlinktable = $supplierlinkclass->getTable();

      $tasktable      = getTableForItemType($item->getType().'Task');

      $closed_status  = $item->getClosedStatusArray();
      $solved_status  = array_merge($closed_status, $item->getSolvedStatusArray());

      $query             = "";
      $WHERE             = "WHERE NOT `$table`.`is_deleted` ".
                                 getEntitiesRestrictRequest("AND", $table);
      $LEFTJOIN          = "";
      $LEFTJOINUSER      = "LEFT JOIN `$userlinktable`
                              ON (`$userlinktable`.`$fkfield` = `$table`.`id`)";
      $LEFTJOINGROUP     = "LEFT JOIN `$grouplinktable`
                              ON (`$grouplinktable`.`$fkfield` = `$table`.`id`)";
      $LEFTJOINSUPPLIER  = "LEFT JOIN `$supplierlinktable`
                              ON (`$supplierlinktable`.`$fkfield` = `$table`.`id`)";

      switch ($param) {
         case "technicien" :
            $LEFTJOIN = $LEFTJOINUSER;
            $WHERE   .= " AND (`$userlinktable`.`users_id` = '$value'
                               AND `$userlinktable`.`type`='".CommonITILActor::ASSIGN."')";
            break;

         case "technicien_followup" :
            $WHERE   .= " AND `$tasktable`.`users_id` = '$value'";
            $LEFTJOIN = " LEFT JOIN `$tasktable`
                              ON (`$tasktable`.`$fkfield` = `$table`.`id`)";
            break;

         case "user" :
            $LEFTJOIN = $LEFTJOINUSER;
            $WHERE   .= " AND (`$userlinktable`.`users_id` = '$value'
                               AND `$userlinktable`.`type` ='".CommonITILActor::REQUESTER."')";
            break;

         case "usertitles_id" :
            $LEFTJOIN  = $LEFTJOINUSER;
            $LEFTJOIN .= " LEFT JOIN `glpi_users`
                              ON (`glpi_users`.`id` = `$userlinktable`.`users_id`)";
            $WHERE    .= " AND (`glpi_users`.`usertitles_id` = '$value'
                                AND `$userlinktable`.`type` = '".CommonITILActor::REQUESTER."')";
            break;

         case "usercategories_id" :
            $LEFTJOIN  = $LEFTJOINUSER;
            $LEFTJOIN .= " LEFT JOIN `glpi_users`
                              ON (`glpi_users`.`id` = `$userlinktable`.`users_id`)";
            $WHERE    .= " AND (`glpi_users`.`usercategories_id` = '$value'
                                AND `$userlinktable`.`type` = '".CommonITILActor::REQUESTER."')";
            break;

         case "itilcategories_tree" :
            if ($value == $value2) {
               $categories = array($value);
            } else {
               $categories = getSonsOf("glpi_itilcategories", $value);
            }
            $condition  = implode("','", $categories);
            $WHERE     .= " AND `$table`.`itilcategories_id` IN ('$condition')";
            break;

         case 'locations_tree' :
            if ($value == $value2) {
               $categories = array($value);
            } else {
               $categories = getSonsOf('glpi_locations', $value);
            }
            $condition  = implode("','", $categories);
            $WHERE     .= " AND `$table`.`locations_id` IN ('$condition')";
            break;

         case 'group_tree' :
         case 'groups_tree_assign' :
            $grptype = (($param == 'group_tree') ? CommonITILActor::REQUESTER
                                                 : CommonITILActor::ASSIGN);
            if ($value == $value2) {
               $groups = array($value);
            } else {
               $groups = getSonsOf("glpi_groups", $value);
            }
            $condition = implode("','", $groups);

            $LEFTJOIN  = $LEFTJOINGROUP;
            $WHERE    .= " AND (`$grouplinktable`.`groups_id` IN ('$condition')
                                AND `$grouplinktable`.`type` = '$grptype')";
            break;

         case "group" :
            $LEFTJOIN = $LEFTJOINGROUP;
            $WHERE   .= " AND (`$grouplinktable`.`groups_id` = '$value'
                               AND `$grouplinktable`.`type` = '".CommonITILActor::REQUESTER."')";
            break;

         case "groups_id_assign" :
            $LEFTJOIN = $LEFTJOINGROUP;
            $WHERE   .= " AND (`$grouplinktable`.`groups_id` = '$value'
                               AND `$grouplinktable`.`type` = '".CommonITILActor::ASSIGN."')";
            break;

         case "suppliers_id_assign" :
            $LEFTJOIN = $LEFTJOINSUPPLIER;
            $WHERE   .= " AND (`$supplierlinktable`.`suppliers_id` = '$value'
                               AND `$supplierlinktable`.`type` = '".CommonITILActor::ASSIGN."')";
            break;

         case "requesttypes_id" :
         case "solutiontypes_id" :
         case "urgency" :
         case "impact" :
         case "priority" :
         case "users_id_recipient" :
         case "type" :
         case "itilcategories_id" :
         case 'locations_id' :
            $WHERE .= " AND `$table`.`$param` = '$value'";
            break;

         case "device":
            $devtable = getTableForItemType('Computer_'.$value2);
            $fkname   = getForeignKeyFieldForTable(getTableForItemType($value2));
            //select computers IDs that are using this device;
            $LEFTJOIN = '';
            $linkdetable = $table;
            if ($itemtype == 'Ticket') {
               $linkedtable = 'glpi_items_tickets';
               $LEFTJOIN .= " LEFT JOIN `glpi_items_tickets`
                                 ON (`glpi_tickets`.`id` = `glpi_items_tickets`.`tickets_id`)";
            }
            $LEFTJOIN .= " INNER JOIN `glpi_computers`
                              ON (`glpi_computers`.`id` = `$linkedtable`.`items_id`
                                  AND `$linkedtable`.`itemtype` = 'Computer')
                          INNER JOIN `$devtable`
                              ON (`glpi_computers`.`id` = `$devtable`.`computers_id`
                                  AND `$devtable`.`$fkname` = '$value')";
            $WHERE   .= " AND `glpi_computers`.`is_template` <> '1' ";
            break;

         case "comp_champ" :
            $ftable   = getTableForItemType($value2);
            $champ    = getForeignKeyFieldForTable($ftable);
                  $LEFTJOIN = '';
            $linkdetable = $table;
            if ($itemtype == 'Ticket') {
               $linkedtable = 'glpi_items_tickets';
               $LEFTJOIN .= " LEFT JOIN `glpi_items_tickets`
                                 ON (`glpi_tickets`.`id` = `glpi_items_tickets`.`tickets_id`)";
            }
            $LEFTJOIN .= " INNER JOIN `glpi_computers`
                              ON (`glpi_computers`.`id` = `$linkedtable`.`items_id`
                                  AND `$linkedtable`.`itemtype` = 'Computer')";
            $WHERE   .= " AND `glpi_computers`.`$champ` = '$value'
                          AND `glpi_computers`.`is_template` <> '1'";
            break;
      }

      switch ($type) {
         case "inter_total" :
            $WHERE .= " AND ".getDateRequest("`$table`.`date`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`date`),'%Y-%m')
                                  AS date_unix,
                             COUNT(`$table`.`id`) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`date`";
            break;

         case "inter_solved" :
            $WHERE .= " AND `$table`.`status` IN ('".implode("','", $solved_status)."')
                        AND `$table`.`solvedate` IS NOT NULL
                        AND ".getDateRequest("`$table`.`solvedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`solvedate`),'%Y-%m')
                                 AS date_unix,
                              COUNT(`$table`.`id`) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`solvedate`";
            break;

         case "inter_solved_late" :
            $WHERE .= " AND `$table`.`status` IN ('".implode("','", $solved_status)."')
                        AND `$table`.`solvedate` IS NOT NULL
                        AND `$table`.`due_date` IS NOT NULL
                        AND ".getDateRequest("`$table`.`solvedate`", $begin, $end)."
                        AND `$table`.`solvedate` > `$table`.`due_date`";

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`solvedate`),'%Y-%m')
                                 AS date_unix,
                              COUNT(`$table`.`id`) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`solvedate`";
            break;

         case "inter_closed" :
            $WHERE .= " AND `$table`.`status` IN ('".implode("','", $closed_status)."')
                        AND `$table`.`closedate` IS NOT NULL
                        AND ".getDateRequest("`$table`.`closedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`closedate`),'%Y-%m')
                                 AS date_unix,
                              COUNT(`$table`.`id`) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`closedate`";
            break;

         case "inter_avgsolvedtime" :
            $WHERE .= " AND `$table`.`status` IN ('".implode("','", $solved_status)."')
                        AND `$table`.`solvedate` IS NOT NULL
                        AND ".getDateRequest("`$table`.`solvedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`solvedate`),'%Y-%m')
                                 AS date_unix,
                              AVG(solve_delay_stat) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`solvedate`";
            break;

         case "inter_avgclosedtime" :
            $WHERE .= " AND  `$table`.`status` IN ('".implode("','", $closed_status)."')
                        AND `$table`.`closedate` IS NOT NULL
                        AND ".getDateRequest("`$table`.`closedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`closedate`),'%Y-%m')
                                 AS date_unix,
                              AVG(close_delay_stat) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`closedate`";
            break;

         case "inter_avgactiontime" :
            if ($param == "technicien_followup") {
               $actiontime_table = $tasktable;
            } else {
               $actiontime_table = $table;
            }
            $WHERE .= " AND `$actiontime_table`.`actiontime` > '0'
                        AND ".getDateRequest("`$table`.`solvedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`solvedate`),'%Y-%m')
                                 AS date_unix,
                              AVG(`$actiontime_table`.`actiontime`) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`solvedate`";
            break;

         case "inter_avgtakeaccount" :
            $WHERE .= " AND `$table`.`status` IN ('".implode("','", $solved_status)."')
                        AND `$table`.`solvedate` IS NOT NULL
                        AND ".getDateRequest("`$table`.`solvedate`", $begin, $end);

            $query  = "SELECT `$table`.`id`,
                              FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`solvedate`),'%Y-%m')
                                 AS date_unix,
                              AVG(`$table`.`takeintoaccount_delay_stat`) AS total_visites
                       FROM `$table`
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`solvedate`";
            break;

         case "inter_opensatisfaction" :
            $WHERE .= " AND `$table`.`status` IN ('".implode("','", $closed_status)."')
                        AND `$table`.`closedate` IS NOT NULL
                        AND ".getDateRequest("`$table`.`closedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`closedate`),'%Y-%m')
                                 AS date_unix,
                              COUNT(`$table`.`id`) AS total_visites
                       FROM `$table`
                       INNER JOIN `glpi_ticketsatisfactions`
                           ON (`$table`.`id` = `glpi_ticketsatisfactions`.`tickets_id`)
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`closedate`";
            break;

         case "inter_answersatisfaction" :
            $WHERE .= " AND `$table`.`status` IN ('".implode("','", $closed_status)."')
                        AND `$table`.`closedate` IS NOT NULL
                        AND `glpi_ticketsatisfactions`.`date_answered` IS NOT NULL
                        AND ".getDateRequest("`$table`.`closedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`closedate`),'%Y-%m')
                                 AS date_unix,
                              COUNT(`$table`.`id`) AS total_visites
                       FROM `$table`
                       INNER JOIN `glpi_ticketsatisfactions`
                           ON (`$table`.`id` = `glpi_ticketsatisfactions`.`tickets_id`)
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`closedate`";
            break;

         case "inter_avgsatisfaction" :
            $WHERE .= " AND `glpi_ticketsatisfactions`.`date_answered` IS NOT NULL
                        AND `$table`.`status` IN ('".implode("','", $closed_status)."')
                        AND `$table`.`closedate` IS NOT NULL
                        AND ".getDateRequest("`$table`.`closedate`", $begin, $end);

            $query  = "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(`$table`.`closedate`),'%Y-%m')
                                 AS date_unix,
                              AVG(`glpi_ticketsatisfactions`.`satisfaction`) AS total_visites
                       FROM `$table`
                       INNER JOIN `glpi_ticketsatisfactions`
                           ON (`$table`.`id` = `glpi_ticketsatisfactions`.`tickets_id`)
                       $LEFTJOIN
                       $WHERE
                       GROUP BY date_unix
                       ORDER BY `$table`.`closedate`";
            break;
      }

      $entrees = array();
      $count   = array();
      if (empty($query)) {
         return array();
      }

      $result = $DB->query($query);
      if ($result
          && ($DB->numrows($result) > 0)) {
         while ($row = $DB->fetch_assoc($result)) {
            $date             = $row['date_unix'];
            //$visites = round($row['total_visites']);
            $entrees["$date"] = $row['total_visites'];
         }
      }

      $end_time   = strtotime(date("Y-m", strtotime($end))."-01");
      $begin_time = strtotime(date("Y-m", strtotime($begin))."-01");

      $current = $begin_time;

      while ($current <= $end_time) {
         $curentry = date("Y-m", $current);
         if (!isset($entrees["$curentry"])) {
            $entrees["$curentry"] = 0;
         }
         $month   = date("m", $current);
         $year    = date("Y", $current);
         $current = mktime(0, 0, 0, intval($month)+1, 1, intval($year));
      }
      ksort($entrees);

      return $entrees;
   }

   /**
    * @param $target
    * @param $date1
    * @param $date2
    * @param $start
   **/
   static function showItems($target, $date1, $date2, $start) {
      global $DB, $CFG_GLPI;

      $view_entities = Session::isMultiEntitiesMode();

      if ($view_entities) {
         $entities = getAllDatasFromTable('glpi_entities');
      }

      $output_type = Search::HTML_OUTPUT;
      if (isset($_GET["display_type"])) {
         $output_type = $_GET["display_type"];
      }
      if (empty($date2)) {
         $date2 = date("Y-m-d");
      }
      $date2 .= " 23:59:59";

      // 1 an par defaut
      if (empty($date1)) {
         $date1 = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")-1));
      }
      $date1 .= " 00:00:00";

      $query = "SELECT `glpi_items_tickets`.`itemtype`,
                       `glpi_items_tickets`.`items_id`,
                       COUNT(*) AS NB
                FROM `glpi_tickets`
                LEFT JOIN `glpi_items_tickets`
                   ON (`glpi_tickets`.`id` = `glpi_items_tickets`.`tickets_id`)
                WHERE `date` <= '$date2'
                      AND `glpi_tickets`.`date` >= '$date1' ".
                      getEntitiesRestrictRequest("AND", "glpi_tickets")."
                      AND `glpi_items_tickets`.`itemtype` <> ''
                      AND `glpi_items_tickets`.`items_id` > 0
                GROUP BY `glpi_items_tickets`.`itemtype`, `glpi_items_tickets`.`items_id`
                ORDER BY NB DESC";

      $result  = $DB->query($query);
      $numrows = $DB->numrows($result);

      if ($numrows > 0) {
         if ($output_type == Search::HTML_OUTPUT) {
            Html::printPager($start, $numrows, $target,
                             "date1=".$date1."&amp;date2=".$date2.
                                 "&amp;type=hardwares&amp;start=$start",
                             'Stat');
            echo "<div class='center'>";
         }

         $end_display = $start+$_SESSION['glpilist_limit'];
         if (isset($_GET['export_all'])) {
            $end_display = $numrows;
         }
         echo Search::showHeader($output_type, $end_display-$start+1, 2, 1);
         $header_num = 1;
         echo Search::showNewLine($output_type);
         echo Search::showHeaderItem($output_type, _n('Associated element', 'Associated elements', 2), $header_num);
         if ($view_entities) {
            echo Search::showHeaderItem($output_type, __('Entity'), $header_num);
         }
         echo Search::showHeaderItem($output_type, __('Number of tickets'), $header_num);
         echo Search::showEndLine($output_type);

         $DB->data_seek($result, $start);

         $i = $start;
         if (isset($_GET['export_all'])) {
            $start = 0;
         }

         for ($i=$start; ($i<$numrows) && ($i<$end_display); $i++) {
            $item_num = 1;
            // Get data and increment loop variables
            $data = $DB->fetch_assoc($result);
            if (!($item = getItemForItemtype($data["itemtype"]))) {
               continue;
            }
            if ($item->getFromDB($data["items_id"])) {
               echo Search::showNewLine($output_type, $i%2);
               echo Search::showItem($output_type, sprintf(__('%1$s - %2$s'), $item->getTypeName(),
                                                           $item->getLink()),
                                     $item_num, $i-$start+1,
                                     "class='center'"." ".($item->isDeleted()?" class='deleted' "
                                                                             :""));
               if ($view_entities) {
                  $ent = $item->getEntityID();
                  $ent = $entities[$ent]['completename'];
                  echo Search::showItem($output_type, $ent, $item_num, $i-$start+1,
                                        "class='center'"." ".($item->isDeleted()?" class='deleted' "
                                                                                :""));
               }
               echo Search::showItem($output_type, $data["NB"], $item_num, $i-$start+1,
                                     "class='center'"." ".($item->isDeleted()?" class='deleted' "
                                                                             :""));
            }
         }

         echo Search::showFooter($output_type);
         if ($output_type == Search::HTML_OUTPUT) {
            echo "</div>";
         }
      }
   }


   /**
    * @since version 0.84
   **/
   static function title() {
      global $PLUGIN_HOOKS, $CFG_GLPI;

      $opt_list["Ticket"]                             = __('Tickets');

      $stat_list["Ticket"]["Ticket_Global"]["name"]   = __('Global');
      $stat_list["Ticket"]["Ticket_Global"]["file"]   = "stat.global.php?itemtype=Ticket";
      $stat_list["Ticket"]["Ticket_Ticket"]["name"]   = __('By ticket');
      $stat_list["Ticket"]["Ticket_Ticket"]["file"]   = "stat.tracking.php?itemtype=Ticket";
      $stat_list["Ticket"]["Ticket_Location"]["name"] = __('By hardware characteristics');
      $stat_list["Ticket"]["Ticket_Location"]["file"] = "stat.location.php?itemtype=Ticket";
      $stat_list["Ticket"]["Ticket_Item"]["name"]     = __('By hardware');
      $stat_list["Ticket"]["Ticket_Item"]["file"]     = "stat.item.php";

      if (Problem::canView()) {
         $opt_list["Problem"]                               = _n('Problem', 'Problems', Session::getPluralNumber());

         $stat_list["Problem"]["Problem_Global"]["name"]    = __('Global');
         $stat_list["Problem"]["Problem_Global"]["file"]    = "stat.global.php?itemtype=Problem";
         $stat_list["Problem"]["Problem_Problem"]["name"]   = __('By problem');
         $stat_list["Problem"]["Problem_Problem"]["file"]   = "stat.tracking.php?itemtype=Problem";
      }

      if (Change::canView()) {
         $opt_list["Change"]                             = _n('Change', 'Changes', Session::getPluralNumber());

         $stat_list["Change"]["Change_Global"]["name"]   = __('Global');
         $stat_list["Change"]["Change_Global"]["file"]   = "stat.global.php?itemtype=Change";
         $stat_list["Change"]["Change_Change"]["name"]   = __('By change');
         $stat_list["Change"]["Change_Change"]["file"]   = "stat.tracking.php?itemtype=Change";
      }

      //Affichage du tableau de presentation des stats
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Select statistics to be displayed')."</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>";

      $values   = array($CFG_GLPI["root_doc"].'/front/stat.php' => Dropdown::EMPTY_VALUE);

      $i        = 0;
      $selected = -1;
      $count    = count($stat_list);
      foreach ($opt_list as $opt => $group) {
         while ($data = each($stat_list[$opt])) {
            $name    = $data[1]["name"];
            $file    = $data[1]["file"];
            $comment ="";
            if (isset($data[1]["comment"])) {
               $comment = $data[1]["comment"];
            }
            $key                  = $CFG_GLPI["root_doc"]."/front/".$file;
            $values[$group][$key] = $name;
            if (stripos($_SERVER['REQUEST_URI'], $key) !== false) {
               $selected = $key;
            }
         }
      }

      // Manage plugins
      $names    = array();
      $optgroup = array();
      if (isset($PLUGIN_HOOKS["stats"]) && is_array($PLUGIN_HOOKS["stats"])) {
         foreach ($PLUGIN_HOOKS["stats"] as $plug => $pages) {
            if (is_array($pages) && count($pages)) {
               foreach ($pages as $page => $name) {
                  $names[$plug.'/'.$page] = array("name" => $name,
                                                  "plug" => $plug);
                  $optgroup[$plug] = Plugin::getInfo($plug, 'name');
               }
            }
         }
         asort($names);
      }

      foreach ($optgroup as $opt => $title) {
         $group = $title;
         foreach ($names as $key => $val) {
            if ($opt == $val["plug"]) {
               $file                  = $CFG_GLPI["root_doc"]."/plugins/".$key;
               $values[$group][$file] = $val["name"];
               if (stripos($_SERVER['REQUEST_URI'], $file) !== false) {
                  $selected = $file;
               }
            }
         }
      }

      Dropdown::showFromArray('statmenu', $values,
                              array('on_change' => "window.location.href=this.options[this.selectedIndex].value",
                                    'value'     => $selected));
      echo "</td>";
      echo "</tr>";
      echo "</table>";
   }


   /**
    * @since version 0.85
   **/
   function getRights($interface='central') {

      $values[READ] = __('Read');
      return $values;
   }

   /**
    * Display line graph
    *
    * @param string   $title  Graph title
    * @param string[] $labels Labels to display
    * @param array    $series Series data. An array of the form:
    *                 [
    *                    ['name' => 'a name', 'data' => []],
    *                    ['name' => 'another name', 'data' => []]
    *                 ]
    * @param integer  $witdh    Graph width. Defaults to 900
    * @param boolean  $display  Whether to display directly; defauts to true
    *
    * @return void
    */
   public function displayLineGraph($title, $labels, $series, $options = null, $display = true) {

      $param = [
         'width'   => 900,
         'height'  => 300,
         'tooltip' => true,
         'legend'  => true,
         'animate' => true
      ];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $param[$key] = $val;
         }
      }

      $slug = str_replace('-', '_', Toolbox::slugify($title));
      $this->checkEmptyLabels($labels);
      $out = "<h2 class='center'>$title</h2>";
      $out .= "<div id='$slug' class='chart'></div>";
      Html::requireJs('charts');
      $out .= "<script type='text/javascript'>
                  $(function() {
                     var chart_$slug = new Chartist.Line('#$slug', {
                        labels: ['" . implode('\', \'', Toolbox::addslashes_deep($labels))  . "'],
                        series: [";

      $first = true;
      foreach ($series as $serie) {
         if ($first === true) {
            $first = false;
         } else {
            $out .= ",\n";
         }
         if (isset($serie['name'])) {
            $out .= "{'name': '{$serie['name']}', 'data': [" . implode(', ', $serie['data']) . "]}";
         } else {
            $out .= "[" . implode(', ', $serie['data']) . "]";
         }
      }

      $out .= "
                        ]
                     }, {
                        low: 0,
                        showArea: true,
                        width: '{$param['width']}',
                        height: '{$param['height']}',
                        fullWidth: true,
                        lineSmooth: Chartist.Interpolation.simple({
                           divisor: 10,
                           fillHoles: false
                        }),
                        axisX: {
                           labelOffset: {
                              x: -" . mb_strlen($labels[0]) * 7  . "
                           }
                        }";

      if ($param['legend'] === true || $param['tooltip'] === true) {
         $out .= ", plugins: [";
         if ($param['legend'] === true) {
            $out .= "Chartist.plugins.legend()";
         }
         if ($param['tooltip'] === true) {
            $out .= ($param['legend'] === true ? ',' : '') . "Chartist.plugins.tooltip()";
         }
         $out .= "]";
      }

      $out .= "});";

      if ($param['animate'] === true) {
                  $out .= "
                     chart_$slug.on('draw', function(data) {
                        if(data.type === 'line' || data.type === 'area') {
                           data.element.animate({
                              d: {
                                 begin: 300 * data.index,
                                 dur: 500,
                                 from: data.path.clone().scale(1, 0).translate(0, data.chartRect.height()).stringify(),
                                 to: data.path.clone().stringify(),
                                 easing: Chartist.Svg.Easing.easeOutQuint
                              }
                           });
                        }
                     });
                  });";
      }
      $out .="</script>";

      if ($display) {
         echo $out;
         return;
      }
      return $out;
   }

   /**
    * Display pie graph
    *
    * @param string   $title  Graph title
    * @param string[] $labels Labels to display
    * @param array    $series Series data. An array of the form:
    *                 [
    *                    ['name' => 'a name', 'data' => []],
    *                    ['name' => 'another name', 'data' => []]
    *                 ]
    * @param boolean  $display  Whether to display directly; defauts to true
    *
    * @return void
    */
   public function displayPieGraph($title, $labels, $series, $display = true) {
      $slug = str_replace('-', '_', Toolbox::slugify($title));
      $this->checkEmptyLabels($labels);
      $out = "<h2 class='center'>$title</h2>";
      $out .= "<div id='$slug' class='chart'></div>";
      $out .= "<script type='text/javascript'>
                  $(function() {
                     var $slug = new Chartist.Pie('#$slug', {
                        labels: ['" . implode('\', \'', Toolbox::addslashes_deep($labels))  . "'],
                        series: [";

      $first = true;
      foreach ($series as $label => $serie) {
         if ($first === true) {
            $first = false;
         } else {
            $out .= ",\n";
         }

         $label = Toolbox::addslashes_deep($label);
         $out .= "{'meta': '{$label}', 'value': '{$serie}'}";
      }

      $out .= "
                        ]
                     }, {
                        donut: true,
                        showLabel: false,
                        height: 300,
                        width: 300,
                        plugins: [
                           Chartist.plugins.legend(),
                           Chartist.plugins.tooltip()
                        ]
                     });

                     $slug.on('draw', function(data) {
                        if(data.type === 'slice') {
                           // Get the total path length in order to use for dash array animation
                           var pathLength = data.element._node.getTotalLength();

                           // Set a dasharray that matches the path length as prerequisite to animate dashoffset
                           data.element.attr({
                              'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
                           });

                           // Create animation definition while also assigning an ID to the animation for later sync usage
                           var animationDefinition = {
                              'stroke-dashoffset': {
                                 id: 'anim' + data.index,
                                 dur: 300,
                                 from: -pathLength + 'px',
                                 to:  '0px',
                                 easing: Chartist.Svg.Easing.easeOutQuint,
                                 // We need to use `fill: 'freeze'` otherwise our animation will fall back to initial (not visible)
                                 fill: 'freeze'
                              }
                           };

                           // We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
                           data.element.attr({
                              'stroke-dashoffset': -pathLength + 'px'
                           });

                           // We can't use guided mode as the animations need to rely on setting begin manually
                           // See http://gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
                           data.element.animate(animationDefinition, false);
                        }
                     });
                  });
              </script>";

      if ($display) {
         echo $out;
         return;
      }
      return $out;
   }

   /**
    * Display search form
    *
    * @param string  $itemtype Item type
    * @param string  $date1    First date
    * @param string  $date2    Second date
    * @param boolean $display  Whether to display directly; defauts to true
    *
    * @return void|string
    */
   public function displaySearchForm($itemtype, $date1, $date2, $display = true) {
      $out = "<form method='get' name='form' action='stat.global.php'><div class='center'>";
      // Keep it at first parameter
      $out .= "<input type='hidden' name='itemtype' value='$itemtype'>";

      $out .= "<table class='tab_cadre'>";
      $out .= "<tr class='tab_bg_2'><td class='right'>".__('Start date')."</td><td>";
      $out .= Html::showDateField(
         'date1',
         array(
            'value'   => $date1,
            'display' => false
         )
      );
      $out .= "</td><td rowspan='2' class='center'>";
      $out .= "<input type='submit' class='submit' value='".__s('Display report')."'></td></tr>";

      $out .= "<tr class='tab_bg_2'><td class='right'>".__('End date')."</td><td>";
      $out .= Html::showDateField(
         'date2',
         array(
            'value'   => $date2,
            'display' => false
         )
      );
      $out .= "</td></tr>";
      $out .= "</table></div>";
      // form using GET method : CRSF not needed
      $out .= Html::closeForm(false);

      if ($display) {
         echo $out;
         return;
      }
      return $out;
   }

   /**
    * Check and replace empty labels
    *
    * @param array $labels Labels
    *
    * @return void
    */
   private function checkEmptyLabels(&$labels) {
      foreach ($labels as &$label) {
         if (empty($label)) {
            $label = '-';
         }
      }
   }
}

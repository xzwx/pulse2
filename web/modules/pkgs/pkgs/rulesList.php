<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com
 *
 * $Id$
 *
 * This file is part of Mandriva Management Console (MMC).
 *
 * MMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * MMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


require("graph/navbar.inc.php");
require("modules/pkgs/pkgs/localSidebar.php");
require_once("modules/pkgs/includes/xmlrpc.php");

$p = new PageGenerator(_T("Rules List",'pkgs'));
$p->setSideMenu($sidemenu);
$p->display();

if(isset($_GET['success']))
{
  new NotifyWidgetSuccess(_T("The rule has been removed", "pkgs"));
}


$rules = list_all_extensions();

$rule_orders = [];
$rule_names = [];
$propositions = [];
$descriptions = [];

$params = [];
foreach($rules as $id=>$rule)
{
    $rule_names[] = $rule['rule_name'];
    $rule_orders[] = $rule['rule_order'];
    $propositions[] = $rule['proposition'];
    $descriptions[] = $rule['description'];
    $params[] = ['id'=>$rule['id']];
}

$n = new OptimizedListInfos($rule_names, _T("Rule Name", "pkgs"));
$n->disableFirstColumnActionLink();
$n->addExtraInfo($rule_orders, _T("Rule Order", "pkgs"));
$n->addExtraInfo($descriptions, _T("Rule Description", "pkgs"));
$n->addExtraInfo($propositions, _T("Rule Proposition", "pkgs"));

// parameters are :
// - label
// - action
// - class (icon)
// - profile get parameter
// - module
// - submodule


$action_upOrder = new ActionItem(_T("Raise order priority","pkgs"), "upRule", "up","rule","pkgs","pkgs");
$action_downOrder = new ActionItem(_T("Lower order priority","pkgs"), "downRule", "down","rule","pkgs","pkgs");
$action_editRule = new ActionItem(_T("Edit Profil",'kiosk'), "editRule", "edit", "rule", "pkgs", "pkgs");
$action_deleteRule = new ActionPopupItem(_T("Delete Profil",'kiosk'), "deleteRule", "delete", "pkgs", "pkgs", "pkgs");

$n->setParamInfo($params);
$n->addActionItemArray($action_upOrder);
$n->addActionItemArray($action_downOrder);
$n->addActionItemArray($action_editRule);
$n->addActionItemArray($action_deleteRule);
$n->setNavBar(new AjaxNavBar(0, 0));

$n->display();
?>

<script>
jQuery(jQuery(".up")[0]).hide();
var length = jQuery(".up").length;

jQuery(jQuery(".up")[0]).hide();

if(jQuery(".down")[length-1] != "undefined")
{
  jQuery(jQuery(".down")[length-1]).hide();
  jQuery(jQuery(".up")[length-1]).css("margin-right", "35px");
}

</script>

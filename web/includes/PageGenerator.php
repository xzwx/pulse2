<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id$
 *
 * This file is part of LMC.
 *
 * LMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * LMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>
<?php
require ("FormGenerator.php");


/**
 * return an uniqId (use full for javascript auto generation
 */
function getUniqId() {
    global $__uniqID;
    $__uniqID++;
    return $__uniqID;
}

/**
 * can echo obj and string with the same function
 * similar to "echo" in PHP5
 */
function echo_obj($obj) {
if (!is_object($obj)){
        echo ($obj);
   } else
   {
        echo $obj->__toString();
   }

}

/**
 * class for action encapsulation
 * Abstract class for the moment
 * @see EditInPlace
 */
class ActionEncapsulator {
    function ActionEncapsulator() {

    }

    function __toString() {
        return "default action encapsulator";
    }
}

/**
 * AutoGenerate an EditInPlace text
 * based on scriptaculous javascript
 */
class EditInPlace extends ActionEncapsulator{

    var $origText;
    var $url;
    var $param;

    function EditInPlace($origText,$url,$param) {
        $this->origText = $origText;
        $this->url = $url;
        $this->param = $param;

    }

    function __toString() {

        $param = array();

        foreach ($this->param as $key=>$value) {
            $param[] = "$key=$value";
        }

        $urlparam = implode("&",$param);

        if ($this->origText== '') {
            $this->origText= "n/a";
        }

        $idx = getUniqId();

        $str = '';
        $str.= "<span id=\"id$idx\" class=\"editinplace\">".$this->origText."</span>";


       $str .= '<script type="text/javascript">';
       $str .= "     new Ajax.InPlaceEditor($('id$idx'),'".$this->url."', {\n
                okButton: true, cancelLink: true, cancelText : '"._('Cancel')."',
                highlightcolor : '#FF9966',
                ajaxOptions: {method: 'get' },\n
                callback: function(form,value) {\n
                    return '$urlparam&value='+value\n
                }\n
            });\n
        </script>\n";
      return $str;
    }

}

/**
 *	class for action in various application
 */
class ActionItem {
    var $desc;
    var $action;
    var $classCss;
    var $paramString;

    /**
     *	Constructor
     * @param $desc description
     * @param $action string include in the url
     * @param $classCss class for CSS like "supprimer" or other class define
     * 	  in the CSS global.css
     * @param $paramString add "&$param=" at the very end of the url
     */
    function ActionItem($desc,$action,$classCss,$paramString, $module = null, $submod = null) {
        $this->desc=$desc;
        $this->action=$action;
        $this->classCss=$classCss;
        $this->paramString=$paramString;
	if ($module == null) $this->module = $_GET["module"];
	else $this->module = $module;
	if ($submod == null) $this->submod = $_GET["submod"];
	else $this->submod = $submod;
	
    }

    /**
     *	display a link for the action
     * 	@param add &$this->param=$param at the very end of the url
     *  display "displayWithRight" if you have correct right
     */
    function display($param, $extraParams = array()) {
        if (hasCorrectAcl($this->module,$this->submod,$this->action)) {
            $this->displayWithRight($param, $extraParams);
        } else {
            $this->displayWithNoRight($param, $extraParams);
        }
    }

    /**
     * display function if you have correct right on this action
     */
    function displayWithRight($param, $extraParams = array()) {
        echo "<li class=\"".$this->classCss."\">";
        if (is_array($extraParams) & !empty($extraParams)) $urlChunk = $this->buildUrlChunk($extraParams);
        else $urlChunk = "&amp;" . $this->paramString."=" . rawurlencode($extraParams);
        echo "<a title=\"".$this->desc."\" href=\"main.php?module=".$this->module."&amp;submod=".$this->submod."&amp;action=".$this->action. $urlChunk . "\">&nbsp;</a>";
        echo "</li>";
    }

    /**
     * display function if you don't have the right for this action
     */
    function displayWithNoRight($param, $extraParams = array()) {
        echo "<li class=\"".$this->classCss."\" style=\"opacity: 0.30;\">";
        echo "<a title=\"".$this->desc."\" href=\"#\" onclick='return false;'>&nbsp;</a>";
        echo "</li>";            
    }

    /**
     * transform $obj param in link for this action
     */
    function encapsulate($obj, $extraParams = Array()) {
        if (hasCorrectAcl($this->module,$this->submod,$this->action)) {
            if (is_array($extraParams) & !empty($extraParams)) {
                $urlChunk = $this->buildUrlChunk($extraParams);
            } else {
                $urlChunk = "&amp;" . $this->paramString."=" . rawurlencode($obj);
            }
	    $str= "<a title=\"".$this->desc."\" href=\"main.php?module=".$this->module."&amp;submod=".$this->submod."&amp;action=".$this->action . $urlChunk ."\">";
            $str.= "$obj";
            $str.=" </a>";
            return $str;
        } else {
            $str= "<a title=\"".$this->desc."\" href=\"#\">";
            $str.= "$obj";
            $str.=" </a>";
            return $str;
        }
    }

    /**
     * Build an URL chunk using a array of option => value
     */
    function buildUrlChunk($arr) {
        $urlChunk = "";
        foreach($arr as $option => $value) {
            $urlChunk .= "&amp;" . $option . "=" . urlencode($value);
        }        
        return $urlChunk;
    }


    /**
     * display help (not use for the moment)
     */
    function strHelp() {
        $str = "";
        $str.= "<li class=\"".$this->classCss."\">";
        $str.= "<a title=\"".$this->desc."\" href=\"#\">";
        $str.= " </a>".$this->desc."</li>";
        return $str;
    }
}

/**
 * dislay action in a popup
 * @see ActionItem
 * @see showPopup (js)
 */
class ActionPopupItem extends ActionItem {
    function displayWithRight($param, $extraParams = array()) {
        if (is_array($extraParams) & !empty($extraParams)) {
            $urlChunk = $this->buildUrlChunk($extraParams);
        } else {
            $urlChunk = "&amp;" . $this->paramString."=" . rawurlencode($param);
        }
        echo "<li class=\"".$this->classCss."\">";
        echo "<a title=\"".$this->desc."\" href=\"main.php?module=".$this->module."&amp;submod=".$this->submod."&amp;action=" . $this->action . $urlChunk . "\"";
        echo " onclick=\"showPopup(event,'main.php?module=".$this->module."&amp;submod=".$this->submod."&amp;action=" .$this->action . $urlChunk . "'); return false;\">&nbsp;</a>";
        echo "</li>";
    }

    function encapsulate($obj, $extraParams = array()) {
        if (is_array($extraParams) & !empty($extraParams)) {
            $urlChunk = $this->buildUrlChunk($extraParams);
        } else {
            $urlChunk = "&amp;" . $this->paramString."=" . rawurlencode($obj);
        }
        $str= "<a title=\"".$this->desc."\" href=\"main.php?module=".$this->module."&amp;submod=".$this->submod."&amp;action=".$this->action . $urlChunk . "\" ";
        $str.= "  onclick=\"showPopup(event,'main.php?module=".$this->module."&amp;submod=".$this->submod."&amp;action=".$this->action . $urlChunk . "'); return false;\">";
        $str.= "$obj";
        $str.=" </a>";
        return $str;
    }
}


class EmptyActionItem extends ActionItem {

    function EmptyActionItem() {        
    }

    function display($param = null) {
        print '<li class="empty"><a href="#" onclick="return false;">&nbsp;</a></li>';
    }
    
}

/**
 *	class who maintain array presentation of information
 */
class ListInfos {
    var $arrInfo; /**< main list */
    var $extraInfo;
    var $paramInfo;
    var $name;
    var $arrAction; /**< list of possible action */
    var $end, $start;

    var $description; /**< list of description (not an obligation) */
    var $col_witdh; /**can specify column width

    /**
     * constructor
     * @param $tab must be an array of array
     */
    function ListInfos($tab, $description ="", $extranavbar = "") {
        $this->arrInfo=$tab;
        $this->arrAction=array();
        $this->description[] = $description;
        $this->initVar();
        $this->col_width = array();
	$this->extranavbar = $extranavbar;
        $this->firstColumnActionLink = True;
    }

    function setAdditionalInfo($addinfo) {
        $this->_addInfo = $addinfo;
    }

    /**
     *	add an ActionItem
     *  @param $objActionItem object ActionItem
     */
    function addActionItem($objActionItem) {
        $this->arrAction[] = &$objActionItem;
    }

    /**
     * Add an array of ActionItem
     * Useful if all action items are not the same for each row of the list
     *
     */
    function addActionItemArray($objActionItemArray) {
        $this->arrAction[] = &$objActionItemArray;
    }
    

    /**
     *	add an array String to display
     *	@param $arrString an Array String to display
     */
    function addExtraInfo($arrString, $description= "",$width="") {
        $this->extraInfo[] = &$arrString;
        $this->description[] = $description;
        $this->col_width[] = $width;
    }

    /**
     *  set parameters array for main action
     *  @param $arrString an Array of string to be used as parameters for the main action
     */
    function setParamInfo($arrString) {
        $this->paramInfo = $arrString;
    }

    /**
     * Set the left padding of the table header.
     * It will be set to 32 by default
     * @param $padding an integer
     */
    function setTableHeaderPadding($padding) {
        $this->first_elt_padding = $padding;
    }

    /**
     * Disable the link to the first available action in the table
     * This link is always done by default
     */
    function disableFirstColumnActionLink() {
        $this->firstColumnActionLink = False;
    }

    /**
     *	init class' vars
     */
    function initVar() {

	$this->name="Elements";

	global $conf;

	if (!isset($_GET["start"]))
            {
                if (!isset($_POST["start"]))
                    {
                        $this->start = 0;

                        if (count($this->arrInfo) > 0)      		{
                            $this->end = $conf["global"]["maxperpage"] - 1;
                        }

                        else   					{
                            $this->end = 0;
      			}
                    }
            }
	else		{
            $this->start = $_GET["start"];
            $this->end = $_GET["end"];
	}
	$this->maxperpage = $conf["global"]["maxperpage"];
    }


    /**
     *	set the name of the array (for CSS)
     */
    function setName($name) {
        $this->name=$name;
    }

    /**
     *	set the cssclass of a row
     */
    function setCssClass($name) {
        $this->cssClass=$name;
    }

    /**
     *	draw number of page etc...
     */
    function drawHeader($navbar=1) {      
        if ($navbar) {
            print_nav($this->start, $this->end, $this->arrInfo, 0, $this->extranavbar);
        }
        echo "<p class=\"listInfos\">";
        echo $this->name." <strong>".min(($this->start + 1), count($this->arrInfo))."</strong>\n ";
        echo _("to")." <strong>".min(($this->end + 1), count($this->arrInfo))."</strong>\n";
        printf (_(" - Total <b>%s </b>")."\n",count($this->arrInfo));
        /* Display page counter only when possible */
        if ($this->maxperpage >= ($this->end - $this->start)) {
            echo "("._("page")." ";
            printf("%.0f", ($this->end + 1) / $this->maxperpage);
            echo " / ";
            $pages = intval((count($this->arrInfo) / $this->maxperpage)) ;
            if ((count($this->arrInfo) % $this->maxperpage > 0) && (count($this->arrInfo) > $this->maxperpage))
                $pages++;
            else if ((count($this->arrInfo) > 0) && ($pages < 1))
                $pages = 1;
            else if ($pages < 0)
                $pages = 0;            
            printf("%.0f", $pages);
            echo ")\n";
        }
        echo "</p>";
    }

    /**
     * display main action (first action
     */
    function drawMainAction($idx) {
        echo "<td class=\"".$this->cssClass."\">";
        if (is_a($this->arrAction[0], 'ActionItem')) {
            $firstAction = $this->arrAction[0];
        } else if (is_array($this->arrAction[0])) {
            $firstAction = $this->arrAction[0][$idx];
        }
        echo $firstAction->encapsulate($this->arrInfo[$idx], $this->paramInfo[$idx]);
        if ($this->_addInfo[$idx]) {
            print $this->_addInfo[$idx];
        }
        echo "</td>";
    }

    function drawTable($navbar = 1) {
	echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" class=\"listinfos\">\n";
        echo "<thead><tr>";
        foreach ($this->description as $key => $desc) {
            if ($this->col_width[$key]) {
                $width_styl = 'width: '.$this->col_width[$key].';';
            }
            if (!$first) {

                if (!$this->first_elt_padding) {
                    $this->first_elt_padding = 32;
                }
                echo "<td style=\"$width_styl\"><span style=\"color: #777; padding-left: ".$this->first_elt_padding."px;\">$desc</span></td>";
                $first = 1;

            } else {
                echo "<td style=\"$width_styl\"><span style=\"color: #777;\">$desc</span></td>";
            }
        }

        if (count($this->arrAction)!=0) { //if we have actions
            echo "<td style=\"text-align:right;\"><span style=\"color: #AAA;\" >Actions</span></td>";
        }

        echo "</tr></thead>";

	for ( $idx = $this->start;
             ($idx < count($this->arrInfo)) && ($idx <= $this->end);
              $idx++)       {
                if (($this->start - $idx) % 2) {
                    echo "<tr>";
                }
                else
                {
                    echo "<tr class=\"alternate\">";
                }

                //link to first action (if we have an action)
                if (count($this->arrAction) && $this->firstColumnActionLink) {
                    $this->drawMainAction($idx);
                } else {
                    echo "<td class=\"".$this->cssClass."\">";
                    echo $this->arrInfo[$idx];
                    echo "</td>";
                }

                if ($this->extraInfo)
                    foreach ($this->extraInfo as $arrayTMP) {
                        echo "<td>";
                        echo_obj($arrayTMP[$idx]);
                        echo "</td>";
                    }
                if (count($this->arrAction)!=0) {
                    echo "<td class=\"action\">";
                    echo "<ul class=\"action\">";
                    foreach ($this->arrAction as $objActionItem) {
                        if (is_a($objActionItem, 'ActionItem')) {
                            $objActionItem->display($this->arrInfo[$idx], $this->paramInfo[$idx]);
                        } else if (is_array($objActionItem)) {
                            $obj = $objActionItem[$idx];
                            $obj->display($this->arrInfo[$idx], $this->paramInfo[$idx]);
                        }
                    }
                    echo "</ul>";
                    echo "</td>";
                }
                echo "</tr>\n";
            }


	echo "</table>\n";

	if ($navbar) {
            print_nav($this->start, $this->end, $this->arrInfo, 0, $this->extranavbar);
        }
        if (false) {
            /* Code disabled because not used and make javavascript errors */
            print '<script type="text/javascript"><!--';
            print '$(\'help\').innerHTML=\'\''."\n";
            print '$(\'help\').innerHTML+=\'<ul>\''."\n";
            print '$(\'help\').innerHTML+=\'<li><h3>Aide contextuelle</h3></li>\''."\n";
            foreach ($this->arrAction as $objActionItem) {
                $content = $objActionItem->strHelp();
                print '$(\'help\').innerHTML+=\''.$content.'\';'."\n";
            }
            print '$(\'help\').innerHTML+=\'</ul>\''."\n";
            print '--></script>';
        }

    }

    function display($navbar = 1, $header =1 ) {
        if (!isset($this->paramInfo)) {
	  $this->paramInfo = $this->arrInfo;
	}

        if ($header == 1) {
            $this->drawHeader($navbar);
        }
        $this->drawTable($navbar);
    }
}


/**
 * A modified version of Listinfos
 */
class OptimizedListInfos extends ListInfos {

    /**
     * Allow to set another item count
     */
    function setItemCount($count) {
        $this->itemCount = $count;
    }

    function getItemCount() {
        return $this->itemCount;
    }

    /**
     *	init class' vars
     */
    function initVar() {        
	$this->name="Elements";
	global $conf;
	if (!isset($_GET["start"]))
            {
                if (!isset($_POST["start"]))
                    {
                        $this->start = 0;
                        if (count($this->arrInfo) > 0)      		{
                            $this->end = $conf["global"]["maxperpage"] - 1;
                        } else {
                            $this->end = 0;
      			}
                    }
            }
	else		{
            $this->start = $_GET["start"];
            $this->end = $_GET["end"];
	}
	$this->maxperpage = $conf["global"]["maxperpage"];
        $this->setItemCount(count($this->arrInfo));
        $this->startreal = $this->start;
        $this->endreal = $this->end;            
    }

    /**
     *	draw number of page etc...
     */
    function drawHeader($navbar=1) {      
        $count = $this->getItemCount();
        if ($navbar) {
            print_nav($this->start, $this->end, $this->arrInfo, 0, $this->extranavbar);
        }
        echo "<p class=\"listInfos\">";
        echo $this->name." <strong>".min(($this->startreal + 1), $count) . "</strong>\n ";
        echo _("to")." <strong>".min(($this->endreal + 1), $count)."</strong>\n";
        printf (_(" - Total <b>%s </b>")."\n", $count);
        /* Display page counter only when possible */
        if ($this->maxperpage >= ($this->endreal - $this->startreal)) {
            echo "("._("page")." ";
            printf("%.0f", ($this->endreal + 1) / $this->maxperpage);
            echo " / ";
            $pages = intval(($count / $this->maxperpage)) ;
            if (($count % $this->maxperpage > 0) && ($count > $this->maxperpage))
                $pages++;
            else if (($count > 0) && ($pages < 1))
                $pages = 1;
            else if ($pages < 0)
                $pages = 0;            
            printf("%.0f", $pages);
            echo ")\n";
        }
        echo "</p>";
    }
    
}

/**
 * specific class for UserDisplay
 */
class UserInfos extends OptimizedListInfos {
    var $css = array(); //css for first column

    function drawMainAction($idx) {
        echo "<td class=\"".$this->css[$idx]."\">";
        echo $this->arrAction[0]->encapsulate($this->arrInfo[$idx], $this->paramInfo[$idx]);
        echo "</td>";
    }

}

/**
 *	side menu items class
 *     this class is required by SideMenu class
 *     each SideMenuItem is all necessary information to
 *     create a link.
 *
 *     ex: create action "bar" in module "foo" with submodule "subfoo"
 *     new SideMenuItem("foobar example","foo","subfoo","bar");
 */
class SideMenuItem {
    var $text, $module, $submod, $action, $activebg, $inactivebg;
    /**
     *	main constructor
     * @param $text text for the link
     * @param $module module for link
     * @param $submod sub module for link
     * @param $action action param for link /!\ use for class id too
     * @param $activebg background image to use when menu is currently activated
     * @param $inactivebg background image to use when menu is currently inactivated
     */
    function SideMenuItem($text,$module,$submod,$action, $activebg = "", $inactivebg = "") {
        $this->text = $text;
        $this->module = $module;
        $this->submod = $submod;
        $this->action = $action;
        $this->activebg = $activebg;
        $this->inactivebg = $inactivebg;
    }

    /**
     * @return a formated link like: main.php?module=base&submod=users&action=add
     *
     */
    function getLink() {
        return 'main.php?module='.$this->module.'&amp;submod='.$this->submod.'&amp;action='.$this->action;
    }


    /**
     *	display the SideMenuItem on the screen
     */
    function display() {
        if (hasCorrectAcl($this->module, $this->submod, $this->action)) {
            echo '<li id="'.$this->action.'">';
	    echo '<a href="'.$this->getLink().'" target="_self">'.$this->text.'</a></li>'."\n";
        }
    }

    /**
     * Return the menu item CSS
     *
     * @param active: this menu item is active
     */
    function getCss($active = False) {
        $ret = "";
        if ($this->activebg != "" && $this->inactivebg != "") {
            if ($active) $bgi = "background-image: url(" . $this->activebg . ");";
            else $bgi = "background-image: url(" . $this->inactivebg . ");";
        }

        if ($active) {
            $color = "#FFF";
            $border = "#FFF";
        } else {
            $color = "#EEE";
            $border = "#666";
        }

        $ret = "#sidebar ul." . $this->submod . " li#" . $this->action . " a {
                    background-color: " . $color . ";
                    color: #666;
                    border-right: 1px solid ". $border . ";"
                    . $bgi . "
                }";
        if (!$active)
                $ret = $ret . "
                #sidebar ul." . $this->submod . " li#" . $this->action ." a:hover {
                    color: #666;
                    background-color: #F7F7F7;"
                    . $bgi . "
                }";
        return $ret;
    }
}


/**
 *	SideMenu class
 *     this class display side menu item
 *     side menu is lmc's left menu, it regroups
 *     possible actions we can do in a spécific module
 *     like index/configuration/add machine/ add share in
 *     samba module
 *     this class require SideMenuItem
 */
class SideMenu {
    var $itemArray;
    var $className;
    var $activatedItem;

    /**
     *	SideMenu default constructor
     *     initalize empty itemArray for SideMenuItem
     */
    function SideMenu() {
        $this->itemArray = array();
        $this->activatedItem = null;
    }

    /**
     *	add a sideMenu Item into the SideMenu
     * @param $objSideMenuItem object SideMenuItem
     */
    function addSideMenuItem($objSideMenuItem) {
        $this->itemArray[]=&$objSideMenuItem;
    }

    /**
     * CSS class
     */
    function setClass($class) {
        $this->className=$class;
    }

    /**
     * @return className for CSS
     */
    function getClass() {
        return $this->className;
    }


    /**
     *	print the SideMenu and the sideMenuItem
     */
    function display() {
        echo "<div id=\"sidebar\">\n";
        echo "<ul class=\"".$this->className."\">\n";
        foreach ($this->itemArray as $objSideMenuItem) {
            $objSideMenuItem->display();
        }
        echo "</ul>\n";
        echo "</div>\n";
    }

    /**
     *	@return return the Css content for a sidebar
     *	static method to get SideBarCss String
     */
    function getSideBarCss() {
        $css = "";
        foreach ($this->itemArray as $objSideMenuItem) {
            $active = (($objSideMenuItem->action == $_GET["action"]) || ($objSideMenuItem->action == $this->activatedItem));
            $css = $css . $objSideMenuItem->getCss($active);
        }
        return $css;
    }

    /**
     * Force a menu item to be displayed as activated
     * Useful for pages that don't have a dedicated tab
     */
    function forceActiveItem($item) {
        $this->activatedItem = $item;
    }

}

/**
 *	PageGenerator class
 */
class PageGenerator {
    var $sidemenu;	/*< SideMenu Object */
    var $content;	/*< array who contains contents Objects */

    /**
     *	Constructor
     */
    function PageGenerator($title = "") {
        $content=array();
        $this->title = $title;
    }

    /**
     *	set the sideMenu object
     */
    function setSideMenu($objSideMenu) {
        $this->sidemenu=$objSideMenu;
    }

    /**
     * Set the page title
     */
    function setTitle($title) {        
        $this->title = $title;
    }


    /**
     *	display the whole page
     */
    function display() {
        $this->displaySideMenu();
        $this->displayTitle();
        /* On IE, make the page have a minimal length, else the sidemenu may be cut */
        print '<div class="fixheight"></div>';
    }

    function displayCss() {
        echo'<style type="text/css">'."\n";
        echo '<!--'."\n";
        echo $this->sidemenu->getSideBarCss();
        echo '-->'."\n";
        echo '</style>'."\n\n";
    }

    /**
     *	display the side Menu
     */
    function displaySideMenu() {
        $this->displayCss();
        $this->sidemenu->display();
    }

    /**
     *	display the page title
     */
    function displayTitle() {
        if (isset($this->title)) print "<h2>" . $this->title . "</h2>\n";
    }
}

/**
 * display popup window if notify add in queue
 *
 */
class NotifyWidget {

    /**
     * default constructor
     */
    function NotifyWidget() {
    }

    /**
     * Add a string in notify widget
     * @param $str any HTML CODE
     */
    function add($str) {

        if (!isset($_SESSION['__notify'])) {
            $_SESSION['__notify'] = array();
        }
        $_SESSION['__notify'][] = $str;
    }

    /**
     * set width size
     * @param $size size in px
     */
    function setSize($size) {
        $_SESSION['__notify_size'] = $size;
    }

    /**
     * private internal function
     */
    function getSize() {
        if ($_SESSION['__notify_size']) {
            return $_SESSION['__notify_size'];
        } else {
            return 300; //default value
        }
    }

    /**
     * @brief set level (change icon in widget)
     * @param $level level must be beetween 0 and 5
     * level must be beetween 0,5
     * 0: info (default, blue info bubble)
     * <= 1: error for the moment (red icon)
     * 5 is critical
     */
    function setLevel($level) {
        $_SESSION['__notify_level'] = $level;
    }

    /**
     * private internal function
     */
    function getLevel() {
        if ($_SESSION['__notify_level']) {
            return $_SESSION['__notify_level'];
        } else {
            return 0; //default level is 0
        }
    }

    /**
     * private internal function
     */
    function getImgLevel() {
        if ($this->getLevel()!=0) {
            return "img/common/icn_alert.gif";
        }

        return "img/common/big_icn_info.png";

    }

    /**
     * private internal function
     */
    function get() {
        $_SESSION['__notify'];
    }

    /**
     * private internal function
     */
    function showJS() {
        if (!isset($_SESSION['__notify'])) {
            return;
        }

        echo "<script>\n";
        echo "$('popup').style.width='".$this->getSize()."px';";
        echo "  showPopupCenter('includes/notify.php');";
        echo "</script>\n";
    }


    /**
     * private internal function
     */
    function display() {
        if (!isset($_SESSION['__notify'])) {
            return;
        }
        echo '<table style="border-width: 0 0 0 0; border-style: none;"><tr style="border-width: 0 0 0 0; border-style: none;"><td style="border-width: 0 0 0 0; border-style: none;">';
        echo '<div id="popupicon" style="float: left;"><img src="'.$this->getImgLevel().'"></div>';
        echo '</td><td style="vertical-align: center; border-width: 0 0 0 0; text-align: justify; width:100%;">';
        foreach ($_SESSION['__notify'] as $info) {
            echo "<p style=\"margin: auto;\">$info</p>";
        }
        echo '<div style="text-align: center;">';
        echo '<input name="breset" type="reset" class="btnSecondary" onclick="getStyleObject(\'popup\').display=\'none\'; return false;" value="'._("Ok").'" />';
        echo '</div>';
        echo '</td></tr></table>';
        $this->flush();
    }

    function flush() {
        unset($_SESSION['__notify']);
        unset ($_SESSION['__notify_size']);
        unset ($_SESSION['__notify_level']);
    }
}

/**
 * display a popup window with a message for a successful operation
 *
 */
class NotifyWidgetSuccess extends NotifyWidget {
    
    function NotifyWidgetSuccess($message) {
        $this->flush();
        $this->add("<div id=\"validCode\">$message</div>");
        $this->setLevel(0);
        $this->setSize(600);
    }

}

/**
 * display a popup window with a message for a failure
 *
 */
class NotifyWidgetFailure extends NotifyWidget {
    
    function NotifyWidgetFailure($message) {        
        $this->flush();
        $this->add("<div id=\"errorCode\">$message</div>");
        $this->setLevel(4);
        $this->setSize(600);
    }

}

/**
 * Create an URL
 *
 * @param $link string except format like "module/submod/action"
 * @param $param assoc array with param to add in GET method
 * @param $ampersandEncode bool defining if we want ampersand to be encoded in URL
 */
function urlStr($link, $param = array(), $ampersandEncode = True) {
    $arr = array();
    $arr = explode ('/',$link);

    if ($ampersandEncode) $amp = "&amp;";
    else $amp = "&";

    $enc_param = "";
    foreach ($param as $key=>$value) {
        $enc_param.= "$amp"."$key=$value";
    }

    return "main.php?module=".$arr[0]."$amp"."submod=".$arr[1]."$amp"."action=".$arr[2].$enc_param;
}

function urlStrRedirect($link, $param = array()) {
    return(urlStr($link, $param, False));
}

function findInSideBar($sidebar,$query) {
    foreach ($sidebar['content'] as $arr) {
        if (ereg($query,$arr['link'])) {
            return $arr['text'];
        }
    }
}

function findFirstInSideBar($sidebar) {
    return $sidebar['content'][0]['text'];
}


class HtmlElement {
    
    var $options;

    function HtmlElement() {
        $this->options = array();
    }

    function setOptions($options) {
        $this->options = $options;
    }

    function hasBeenPopped() {
        return True;
    }

    function display() {
        die("Must be implemented by the subclass");
    }

}

class HtmlContainer {
    
    var $elements;
    var $index;
    var $popped;
    var $debug;

    function HtmlContainer() {
        $this->elements = array();
        $this->popped = False;
        $this->index = -1;
    }

    function begin() {
        die("Must be implemented by the subclass");        
    }

    function end() {
        die("Must be implemented by the subclass");        
    }
    
    function display() {
        print "\n" . $this->begin() . "\n";
        foreach($this->elements as $element) $element->display();
        print "\n" . $this->end() . "\n";
    }

    function add($element, $options = array()) {
        $element->setOptions($options);
        $this->push($element);        
    }

    function push($element) {
        if ($this->index == -1) {
            /* Add first element to container */
            $this->index++;
            $this->elements[$this->index] = $element;
            //print "pushing " . $element->options["id"] . " into " . $this->options["id"] . "<br>";
        } else {
            if ($this->elements[$this->index]->hasBeenPopped()) {
                /* All the contained elements have been popped, so add the new element in the list */
                $this->index++;
                $this->elements[$this->index] = $element;            
                //print "pushing " . $element->options["id"] . " into " . $this->options["id"] . "<br>";               
            } else {
                /* Recursively push a new element into the container */
                $this->elements[$this->index]->push($element);
            }
        }
    }

    function hasBeenPopped() {
        if ($this->popped) $ret = True;
        else if ($this->index == -1) $ret = False;
        return $ret;
    }

    function pop() {
        if (!$this->popped) {
            if ($this->index == -1)
                $this->popped = True;
            else if ($this->elements[$this->index]->hasBeenPopped())
                $this->popped = True;
            else $this->elements[$this->index]->pop();
            //if ($this->popped) print "popping " . $this->options["id"] . "<br>";
        } else die("Nothing more to pop");
    }

}


class Div extends HtmlContainer {
    
    function Div($options = array()) {
        $this->HtmlContainer();
        $this->options = $options;
        $this->display = True;
    }

    function begin() {
        $str = "";
        foreach($this->options as $key => $value) $str.= " $key=\"$value\"";
        if (!$this->display) $displayStyle = ' style =" display: none;"';
        else $displayStyle = "";        
        return "<div$str$displayStyle>";
    }
    
    function end () {
        return "</div>";
    }

    function setVisibility($flag) {
        $this->display = $flag;
    }

}


class Form extends HtmlContainer {

    function Form($options = array()) {
        $this->HtmlContainer();
        if (!isset($options["method"])) $options["method"] = "post";        
        $this->options = $options;
        $this->buttons = array();
    }
    
    function begin() {
        $str = "";
        foreach($this->options as $key => $value) $str.= " $key=\"$value\"";
        return "<form$str>";
    }

    function end() {
        $str = "";
        foreach($this->buttons as $button) $str .= "\n$button\n";
        $str .= "\n</form>\n";
        return $str;
    }

    function getButtonString($name, $value) {
        return "<input type=\"submit\" name=\"$name\" value=\"$value\" class=\"btnPrimary\">";
    }

    function addButton($name, $value) {
        $this->buttons[] = $this->getButtonString($name, $value);
    }

    function addExpertButton($name, $value) {
        $d = new DivExpertMode();
        $this->buttons[] = $d->begin() . $this->getButtonString($name, $value) . $d->end();
    }

}

class ValidatingForm extends Form {

    function ValidatingForm($options = array()) {
        $this->Form($options);
        $this->options["id"] = "edit";
        $this->options["onsubmit"] = "return validateForm();";
    }

    function end() {
        $str = parent::end();
        $str .= "<script type=\"text/javascript\">Form.focusFirstElement(" . "'" . $this->options["id"] . "'" . ")</script>\n";
        return $str;
    }
}

class Table extends HtmlContainer {

    function Table($options = array()) {
        $this->HtmlContainer();
        $this->options = $options;
    }

    function begin() {
        return '<table cellspacing="0">';
    }

    function end() {
        return "</table>";
    }

}

class DivForModule extends Div {
    
    function DivForModule($title, $color, $options = array()) {
        $options["style"] = "background-color: " . $color;
        $options["class"] = "formblock";
        $this->Div($options);
        $this->title = $title;
        $this->color = $color;        
    }

    function begin() {
        print parent::begin();
        print "<h3>" . $this->title . "</h3>";
    }
}

class DivExpertMode extends Div {

    function begin() {
        $str = '<div id="expertMode" ';
        if ($_SESSION["expert_mode_var"] == 1) {
            $str .= ' style="display: inline;"';
        } else {
            $str .= ' style="display: none;"';
        }
        return $str . ' >';
    }

}

?>

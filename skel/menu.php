<?php
  /**
   * Builds a navigation menu for the ATK application. 
   * Items for the main application can be added within the
   * config.menu.inc file with the menuitem() method. Modules
   * can register menuitems in their constructor. The menu
   * has support for enabling/disabling items on a user profile base.
   *
   * For more information check the atkmoduletools.inc file!
   *
   * @author Peter Verhage <peter@ibuildings.nl>
   * @version $Revision$
   *
   * $Id$   
   * $Log$
   * Revision 4.16  2001/09/10 12:32:47  ivo
   * Improved module support. Modules can now create modifiers for other nodes.
   * Modules and external modules are now treated equally.
   * Added ability to specify order of menuitems
   *
   * Revision 4.15  2001/07/15 16:37:19  ivo
   * New atk.inc includefile in skel.
   * New feature: extended search.
   * Fixed a bug in session management; style.php and other files that get
   * loaded between two dispatches could corrupt a session.
   *
   * Revision 4.14  2001/06/24 07:13:30  sandy
   * - Updated the Skel directory with the new atksession function, so
   * people can make a new admin module again from the skel dir.
   *
   * Revision 4.13  2001/06/14 12:26:45  ivo
   * Submenu's are now opened with SESSION_DEFAULT instead of SESSION_NEW
   *
   * Revision 4.12  2001/06/14 09:50:43  ivo
   * Submenu's opened a new session. This corrupted the session. Now they
   * continue in the same session.
   *
   * Revision 4.11  2001/06/11 08:23:40  ivo
   * Replaced '<<' in submenu's by translatable 'back to' string.
   *
   * Revision 4.10  2001/06/01 12:52:49  peter
   * Fixed a bug in menu.php (atksecure() was called to late).
   * Updated english language file month names (long names to short).
   * Fixed bug in atknode selectPage() -> atktarget was not decoded.
   *
   * Revision 4.9  2001/05/23 07:57:57  ivo
   * Updated the files in skel with the new atksecure() call.
   *
   * Revision 4.8  2001/05/10 09:42:28  sandy
   * bugfix in menu.php (config menu delimiter) and for the clear posting
   *
   * Revision 4.7  2001/05/10 08:31:01  ivo
   * Major upgrade. Changes:
   * * Deprecated the m_records/m_currentRec feature of atknode. Nodes are now
   *   singletons by default, and nodefunctions pass around recordsets.
   * + Session management for forms. If you now leave a page through a click on
   *   a link, the session remembers everything from your form and restores it
   *   when you return.
   * + New relation: oneToOneRelation
   * + Reimplemented the embedded editForm feature (forms inside forms)
   *
   * Revision 4.6  2001/05/07 15:13:49  ivo
   * Put config_atkroot in all files.
   *
   * Revision 4.5  2001/05/01 09:49:49  ivo
   * Replaced all require() and include() calls by require_once() and
   * include_once() calls. The if(!DEFINED)... inclusion protection in files
   * is now obsolete.
   *
   * Revision 4.4  2001/05/01 09:15:51  ivo
   * Initial session based atk version.
   *
   * Revision 4.3  2001/04/24 13:51:50  ivo
   * Fixed some small bugs, and updated the language files, improved the menu.
   *
   * Revision 4.2  2001/04/23 15:59:07  peter
   * Removed something that didn't belong there...
   *
   * Revision 4.1  2001/04/23 13:21:22  peter
   * Introduction of module support. An ATK application can now have zero
   * or more modules which can, but don't have to, contain ATK nodes.
   *
   */  
  include_once("atk.inc");  

  atksession();
  atksecure();

  /* get main menuitems */
  include_once($config_atkroot."config.menu.inc");

  /* first add module menuitems */
  foreach ($g_modules as $modname => $modpath)
  {
    $module = getModule($modname);
    menuitems($module->getMenuItems());
  }

  if (!isset($atkmenutop)||$atkmenutop=="") $atkmenutop = "main";

  /* output html */
  $g_layout->output("<html>");
  $g_layout->head($txt_app_title);
  $g_layout->body();
  $g_layout->output("<br><div align='center'>"); 
  $g_layout->ui_top(text("menu_".$atkmenutop));
  $g_layout->output("<br>");

  /* build menu */    
  
  $menu = "";  
  
  function menu_cmp($a,$b)
  {
    if ($a["order"] == $b["order"]) return 0;
    return ($a["order"] < $b["order"]) ? -1 : 1;
  }
  usort($g_menu[$atkmenutop],"menu_cmp");   

  for ($i = 0; $i < count($g_menu[$atkmenutop]); $i++)
  {
    $name = $g_menu[$atkmenutop][$i]["name"];
    $url = $g_menu[$atkmenutop][$i]["url"];
    $enable = $g_menu[$atkmenutop][$i]["enable"];

    /* delimiter ? */
    if ($g_menu[$atkmenutop][$i]["name"] == "-") $menu .= "<br>";
    
    /* submenu ? */
    else if (empty($url) && $enable) $menu .= href('menu.php?atkmenutop='.$name,text("menu_$name"),SESSION_DEFAULT).$config_menu_delimiter;
    else if (empty($url) && !$enable) $menu .=text("menu_$name").$config_menu_delimiter;
      
    /* normal menu item */
    else if ($enable) $menu .= href($url,text("title_$name"),SESSION_NEW,false,'target="main"').$config_menu_delimiter;
    else $menu .= text("menu_$name").$config_menu_delimiter;    
  }
  
  /* previous */
  if ($atkmenutop != "main")
  {
    $parent = $g_menu_parent[$atkmenutop];
    $menu .= $config_menu_delimiter;
    $menu .= href('menu.php?atkmenutop='.$parent,text("back_to").' '.text("menu_$parent"),SESSION_DEFAULT).'<br>';  
  }

  /* bottom */
  $g_layout->output($menu);
  $g_layout->output("<br><br>");
  $g_layout->ui_bottom();
  $g_layout->output("</div></html>"); 
  $g_layout->outputFlush();
?>

<?php

/**
 * @package   JCE
 * @copyright   Copyright (c) 2009-2016 Ryan Demmer. All rights reserved.
 * @license   GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_WF_EXT') or die('RESTRICTED');

class WblinksWbcatalog extends JObject {

  var $_option = 'com_wbcatalog';

  /**
   * Constructor activating the default information of the class
   *
   * @access  protected
   */
  public function __construct($options = array()) {

  }

  /**
   * Returns a reference to a editor object
   *
   * This method must be invoked as:
   *  <pre>  $browser =JContentEditor::getInstance();</pre>
   *
   * @access  public
   * @return  JCE  The editor object.
   * @since 1.5
   */
  public static function getInstance() {
    static $instance;

    if (!is_object($instance)) {
      $instance = new JoomlalinksMenu();
    }
    return $instance;
  }

  public function getOption() {
    return $this->_option;
  }

  public function getList() {
    $wf = WFEditorPlugin::getInstance();

    if ($wf->checkAccess('links.joomlalinks.menu', 1)) {
      return '<li id="index.php?option=com_wbcatalog"><div class="tree-row"><div class="tree-image"></div><span class="folder menu nolink"><a href="javascript:;">' . WFText::_('WF_LINKS_WBLINKS_WBCATALOG', 'wbCatalog') . '</a></span></div></li>';
    }
  }

  public function getLinks($args) {
    $items = array();
    $view  = @$args->view ?: 'browse';
    switch ($view) {
      default:
        $row = self::_browse( $args );
        if ($row->categories) {
          foreach ($row->categories AS $row_category) {
            $items[] = array(
              'id'    => 'index.php?option=com_wbcatalog&cid=' . $row_category->id,
              'name'  => $row_category->name . ' (' . $row_category->title . ')',
              'class' => 'folder'
            );
          }
        }
        if ($row->items) {
          foreach ($row->items AS $row_item) {
            $items[] = array(
              'id'    => 'index.php?option=com_wbcatalog&cid=' . $row_item->cat_id .'&id=' . $row_item->id,
              'name'  => $row_item->name . ' (' . $row_item->sku . ')',
              'class' => 'file'
            );
          }
        }
        break;
    }
    return $items;
  }

  private function _browse( $args ) {
    $db = JFactory::getDBO();
    $cat_where = array();
    $item_where = array();
    if ($args->cid) {
      $cat_where[] = "`cat`.`pid` = '". (int)$args->cid ."'";
      $item_where[] = "`item_xref`.`cat_id_left` = '". (int)$args->cid ."'";
    }
    else {
      $cat_where[] = "`cat`.`pid` = 0";
      $item_where[] = "`item_xref`.`cat_id_left` = 0";
    }
    return (object)array(
      'categories' => $db
        ->setQuery("
          SELECT `cat`.*
          FROM #__wbcatalog_cat AS `cat`
          ". ($cat_where ? "WHERE " . implode(' AND ', $cat_where) : '') ."
          ORDER BY `cat`.`pid`
            , `cat`.`ordering`
            , `cat`.`title`
          ")
        ->loadObjectList(),
      'items' => $db
        ->setQuery("
          SELECT `item`.*
            , `item_xref`.`cat_id_left` AS `cat_id`
          FROM `#__wbcatalog_item` AS `item`
          LEFT JOIN `#__wbcatalog_item_xref` AS `item_xref`
            ON `item_xref`.`item_id_left` = `item`.`id`
            AND `item_xref`.`type` = 'nested'
          ". ($item_where ? "WHERE " . implode(' AND ', $item_where) : '') ."
          ORDER BY `item_xref`.`ordering_left`
            , `item`.`name`
          ")
        ->loadObjectList()
    );

  }

  private function _types() {
    $db = JFactory::getDBO();

    $query = $db->getQuery(true);

    if (is_object($query)) {
      $query->select('*')->from('#__menu_types')->order('title');
    } else {
      $query = 'SELECT * FROM #__menu_types ORDER By title';
    }

    $db->setQuery($query, 0);
    return $db->loadObjectList();
  }


  private function getLangauge($language) {
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);

    $link = '';

    if (is_object($query)) {
      $query->select('a.sef AS sef');
      $query->select('a.lang_code AS lang_code');
      $query->from('#__languages AS a');
      $db->setQuery($query);
      $langs = $db->loadObjectList();

      foreach ($langs as $lang) {
        if ($language == $lang->lang_code) {
          $language = $lang->sef;
          $link .= '&lang=' . $language;
        }
      }
    }

    return $link;
  }

  private static function route($url) {
    $wf = WFEditorPlugin::getInstance();

    if ($wf->getParam('links.joomlalinks.sef_url', 0)) {
      $url = WFLinkExtension::route($url);
    }

    return $url;
  }

}

?>

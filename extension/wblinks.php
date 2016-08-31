<?php

/**
 * @package   	Webuddha JCE Extension wbLinks
 * @copyright 	Copyright (c) 2016 Webuddha. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

defined('_WF_EXT') or die('RESTRICTED');

class WFLinkBrowser_Wblinks {

  var $_option = array();
  var $_adapters = array();

  /**
   * [__construct description]
   * @param array $options [description]
   */
  public function __construct($options = array()) {
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');
    $path = dirname(__FILE__) . '/wblinks';
    $files = JFolder::files($path, '\.(php)$');
    if (!empty($files)) {
      foreach ($files as $file) {
        $name = basename($file, '.php');
        require_once( $path . '/' . $file );
        $classname = 'Wblinks' . ucfirst($name);
        if (class_exists($classname)) {
          $this->_adapters[] = new $classname;
        }
      }
    }
  }

  /**
   * [display description]
   * @return [type] [description]
   */
  public function display() {
    $document = WFDocument::getInstance();
    $document->addStyleSheet(array('wblinks'), 'extensions/links/wblinks/css');
  }

  /**
   * [isEnabled description]
   * @return boolean [description]
   */
  public function isEnabled() {
    $wf = WFEditorPlugin::getInstance();
    return $wf->checkAccess($wf->getName() . '.links.wblinks.enable', 1);
  }

  /**
   * [getOption description]
   * @return [type] [description]
   */
  public function getOption() {
    foreach ($this->_adapters as $adapter) {
      $this->_option[] = $adapter->getOption();
    }
    return $this->_option;
  }

  /**
   * [getList description]
   * @return [type] [description]
   */
  public function getList() {
    $list = '';
    foreach ($this->_adapters as $adapter) {
      $list .= $adapter->getList();
    }
    return $list;
  }

  /**
   * [getLinks description]
   * @param  [type] $args [description]
   * @return [type]       [description]
   */
  public function getLinks($args) {
    foreach ($this->_adapters as $adapter) {
      if ($adapter->getOption() == $args->option) {
        return $adapter->getLinks($args);
      }
    }
  }

}

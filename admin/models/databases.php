<?php
/**
 * Description of ShopMigrator
 *
 * @version  1.0
 * @author Daniel Eliasson Stilero Webdesign http://www.stilero.com
 * @copyright  (C) 2012-okt-07 Stilero Webdesign, Stilero AB
 * @category Components
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of databases.
 * 
 * ShopMigrator is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ShopMigrator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ShopMigrator.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.model');
 
class ShopMigratorModelDatabases extends JModel{
    protected $_items;
    private $_table;
    static private $_tableName = '#__shopmigrator_databases';

    public function __construct() {
        parent::__construct();
        $db =& JFactory::getDbo();
        $this->_table = $db->nameQuote(self::$_tableName);
    }
    public function getItems(){
        $db =& JFactory::getDbo();
        $query = "SELECT * FROM ".$this->_table;
        $db->setQuery($query);
        $this->_items = $db->loadObjectList();
        return $this->_items;
    }

    public function getItem($id){
        $db =& JFactory::getDbo();
        $key = $db->nameQuote('id');
        $query = "SELECT * FROM ".$this->_table.
                " WHERE ".$key." = ".$id;
        $db->setQuery($query);
        $item = $db->loadObject();
        if($item === null){
            JError::raiseError(500, 'Item '.$id.' Not found');
        }else{
            return $item;
        }
    }
    
    function getNewItem(){
        $newItem =& $this->getTable( 'databases' );
        $newItem->id = 0;
        return $newItem;
    }
    
    public function store(){
        $table =& $this->getTable();
        $data = JRequest::get('post');
        $table->reset();
        if(!$table->bind($data)){
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        if(!$table->check()){
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        if(!$table->store()){
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        return true;
    }

    public function delete($cids){
        $db =& JFactory::getDbo();
        $id = $db->nameQuote('id');
        $ids = implode(', ', $cids);
        $query = 'DELETE FROM '.$this->_table.
                ' WHERE '.$id.' IN ('.$ids.')';
        $db->setQuery($query);
        if( !$db->query() ){
            $errorMsg = $this->getDBO()->getErrorMsg();
            JError::raiseError(500, 'Error deleting: '.$errorMsg);
        }
    }
    
    public function unpublish($cids){
        $db =& JFactory::getDbo();
        $id = $db->nameQuote('id');
        $published = $db->nameQuote('published');
        $ids = implode(', ', $cids);
        $query = 'UPDATE '.$this->_table.
                ' SET '.$published.' = 0'.
                ' WHERE '.$id.' IN ('.$ids.')';
        $db->setQuery($query);
        if( !$db->query() ){
            $errorMsg = $this->getDBO()->getErrorMsg();
            JError::raiseError(500, 'Error Unpublishing: '.$errorMsg);
        }
    }
    
    public function publish($cids){
        $db =& JFactory::getDbo();
        $id = $db->nameQuote('id');
        $published = $db->nameQuote('published');
        $ids = implode(', ', $cids);
        $query = 'UPDATE '.$this->_table.
                ' SET '.$published.' = 1'.
                ' WHERE '.$id.' IN ('.$ids.')';
        $db->setQuery($query);
        if( !$db->query() ){
            $errorMsg = $this->getDBO()->getErrorMsg();
            JError::raiseError(500, 'Error Unpublishing: '.$errorMsg);
        }
    }
}
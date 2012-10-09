<?php
/**
 * ShopMigrator
 *
 * @version  1.0
 * @package Stilero
 * @subpackage ShopMigrator
 * @author Daniel Eliasson Stilero Webdesign http://www.stilero.com
 * @copyright  (C) 2012-okt-09 Stilero Webdesign, Stilero AB
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class MigrateCategories extends Migrate{
    
    protected static $_catTableName = '#__category';
    protected static $_destTable = '#__virtuemart_categories';
    protected static $_catToStoreTableName = '#__category_to_store';
    protected static $_catDescTableName = '#__category_description';
    protected static $_vmCatTableName = '#__virtuemart_categories';

    public function __construct($MigrateSrcDB, $MigrateDestDB, $storeid=0) {
        parent::__construct($MigrateSrcDB, $MigrateDestDB, $storeid=0);
    }
    
    public function getCategoriesForStore(){
        $db =& $this->_sourceDB;
        $catToStoreTable = $db->nameQuote(self::$_catToStoreTableName);
        $catTable = $db->nameQuote(self::$_catTableName);
        $query = "SELECT b.* FROM ".$catToStoreTable." a"
                ." INNER JOIN ".$catTable." b"
                ." ON a.category_id = b.category_id"
                ." WHERE a.store_id = ".(int)$this->_storeid;
        $db->setQuery($query);
        $this->_items = $db->loadObjectList();
        return $this->_items;
    } 
    
    public function getDescriptionForLang($langcode=1){
        $db =& $this->_sourceDB;
        $langKey = $db->nameQuote('language_id');
        $langVal = (int)$langcode;
        $table = $db->nameQuote(self::$_catDescTableName);
        $query = "SELECT * FROM ".$table
                ." WHERE ".$langKey." = ".$langVal;
        $db->setQuery($query);
        $this->_items = $db->loadObjectList();
        return $this->_items;
    }
    
    public function setCategories(){
        $db =& $this->_destDB;
        $ocCategories = $this->getCategoriesForStore();
        $table = $db->nameQuote(self::$_vmCatTableName);
        $query = "INSERT INTO ".$table." ("
            ."`virtuemart_category_id` ,
            `virtuemart_vendor_id`
            )VALUES";
        $queries = array();
        foreach ($ocCategories as $ocCategory) {
            $queries[] =
                "("
                ."'".$ocCategory->category_id."',"
                ."'1'"
                .")";
        }
        $query .= implode(',', $queries);
        $db->setQuery($query);
        $results = $db->query();
        return $results;                
    }
    
}

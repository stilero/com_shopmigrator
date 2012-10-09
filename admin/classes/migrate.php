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

class Migrate{
    
    protected $_srcDB;
    protected $_storeid;
    protected $_sourceDB;
    protected $_destDB;
    protected $_sourceData;
    protected $_destinationData;
    
    public function __construct($MigrateSrcDB, $MigrateDestDB, $storeid=0) {
        $this->_sourceDB =& $MigrateSrcDB;
        $this->_destDB =& $MigrateDestDB;
        $this->_storeid = $storeid;
    }
    
    function getSourceData(){
        
    }
    
    function setDestinationData(){
        
    }
    
    function convertDestinationData(){
        
    }
}

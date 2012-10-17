<?php
/**
 * Description of ShopMigrator
 *
 * @version  1.0
 * @author Daniel Eliasson Stilero Webdesign http://www.stilero.com
 * @copyright  (C) 2012-okt-17 Stilero Webdesign, Stilero AB
 * @category Components
 * @license	GPLv2
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * HelloWorld Model
 */
class ShopMigratorModelMigration extends JModelItem
{
	protected $_migrateType;
 
	public function getTasks($migrateType){
            $tasks = array();
            $tasks[] =  array('categories' => 'migrateCategories');
            $tasks[] =  array('categories' => 'migrateCategoryCategories');
            $tasks[] =  array('categories' => 'migrateImages');
            $tasks[] =  array('categories' => 'migrateDescriptions');
            $tasks[] =  array('manufacturer' => 'migrateManufacturers');
            $tasks[] =  array('manufacturer' => 'migrateImages');
            return $tasks;
	}
}
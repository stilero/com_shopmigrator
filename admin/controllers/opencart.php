<?php
/**
 * Description of ShopMigrator
 *
 * @version  1.0
 * @author Daniel Eliasson Stilero Webdesign http://www.stilero.com
 * @copyright  (C) 2012-okt-17 Stilero Webdesign, Stilero AB
 * @category Components
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of opencart.
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

// no direct access
defined('_JEXEC') or die('Restricted access'); 

// import joomla controller library
jimport('joomla.application.component.controller');

//Generic Classes
JLoader::register('Migrate', SHOPMIGRATOR_CLASSES.DS.'migrate.php');
JLoader::register('MigrateDB', SHOPMIGRATOR_CLASSES.DS.'MigrateDB.php');
JLoader::register('MigrateError', SHOPMIGRATOR_CLASSES.DS.'MigrateError.php');
JLoader::register('MigrateTable', SHOPMIGRATOR_CLASSES.DS.'MigrateTable.php');
JLoader::register('MigrateURL', SHOPMIGRATOR_CLASSES.DS.'MigrateURL.php');
JLoader::register('MigrateDB', SHOPMIGRATOR_CLASSES.DS.'MigrateDB.php');
JLoader::register('MigrateFiles', SHOPMIGRATOR_CLASSES.DS.'MigrateFiles.php');
//HelperClasses
JLoader::register('MigrateCountries', SHOPMIGRATOR_CLASSES.DS.'opencart'.DS.'migrate.php');
JLoader::register('MigrateCurrencies', SHOPMIGRATOR_CLASSES.DS.'opencart'.DS.'migrate.php');
JLoader::register('MigrateStatus', SHOPMIGRATOR_CLASSES.DS.'opencart'.DS.'migrate.php');

class ShopMigratorControllerOpencart extends JController{
    
    public static $viewName = 'opencart';
    private $_mainView;
    
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_mainView =& $this->getView( self::$viewName, 'raw' );
        $migrateType = JRequest::getWord('migrateType');
        $foundRequestedClass = JFile::exists(SHOPMIGRATOR_CLASSES.DS.'opencart'.DS.'migrate.'.$migrateType.'.php');
        $foundRequestedView = JFile::exists(JPATH_COMPONENT.DS.'views'.DS.'opencart'.DS.'tmpl'.DS.$migrateType.'.php');
        if($foundRequestedClass && $foundRequestedView){
            $this->_mainView->setLayout($migrateType);
        }
        $this->registerClasses();
        $this->configure();
    }
    
    protected function registerClasses(){
        $mainClasses = array(
            'categories',
            'manufacturer',
            'products',
            'reviews',
            'users'
        );
        foreach ($mainClasses as $mainClass) {
            JLoader::register('Migrate'.JString::ucfirst($mainClass), SHOPMIGRATOR_CLASSES.DS.'opencart'.DS.'migrate.'.$mainClass.'.php');
        }
    }
    
    private function configure(){
        $srcMigrateDB = new MigrateDB('localhost', 'root', 'jkebML00', 'opencart');
        $srcDB = & $srcMigrateDB->getDB();
        $MigrateDestDB = new MigrateDB('localhost', 'root', 'jkebML00', 'joomla_svn25', 'mysql', 'pnq93_');
        $destDB = & $MigrateDestDB->getDB();
        $storeUrl = 'http://localhost/opencart/';
        $thumbWidth = 100;
        $thumbHeight = 100;
        $this->_mainView->assignRef('thumbWidth', $thumbWidth);
        $this->_mainView->assignRef('thumbHeight', $thumbHeight);
        $this->_mainView->assignRef('srcDB', $srcDB);
        $this->_mainView->assignRef('destDB', $destDB);
        $this->_mainView->assignRef('storeUrl', $storeUrl);
        $migrateTask = JRequest::getWord('migrateTask');
        $this->_mainView->assignRef('migrateTask', $migrateTask);
    }


    public function display(){
        $this->_mainView->display();
    }
}
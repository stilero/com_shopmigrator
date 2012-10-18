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
 * This file is part of default.
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
JRequest::checkToken('get') or die('Invalid Token');
$MigrateCategories = new MigrateCategories($this->srcDB, $this->destDB, $this->storeUrl);
$wasSuccessful = false;
$output = '';
$MigrateCategories->clearData();
$error = 'error';
switch ($this->migrateTask) {
    case 'hasNoConflict':
        $wasSuccessful = $MigrateCategories->hasNoConflict();
        break;
    case 'migrateCategories':
        $wasSuccessful = $MigrateCategories->migrateCategories();
        break;
    case 'migrateCategoryCategories':
        $wasSuccessful = $MigrateCategories->migrateCategoryCategories();
        break;
    case 'migrateImages':
        $wasSuccessful = $MigrateCategories->migrateImages();
        break;
    case 'migrateDescriptions':
        $wasSuccessful = $MigrateCategories->migrateDescriptions();
        break;
    default:
        break;
}
$results = array('code' => 0, 'message' => 'ok');
if(!$wasSuccessful){
    $errorMessage = $MigrateCategories->getError();
    $results = array('code' => 1, 'message' => $errorMessage['message']);
}
print $json = json_encode($results);
?>
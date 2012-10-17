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
$MigrateProducts = new MigrateProducts($this->srcDB, $this->destDB, $this->storeUrl);
$wasSuccessful = false;
$output = '';
//$MigrateProducts->clearData();
$error = 'error';
$result = $MigrateProducts->hasConflict();
if($result != false){
    $error = 'conflict in id:'.  implode(', ', $result);
    $wasSuccessful = false;
}else{
    switch ($this->migrateTask) {
        case 'migrateProducts':
            $wasSuccessful = $MigrateProducts->migrateProducts();
            break;
        case 'migrateImages':
            $wasSuccessful = $MigrateProducts->migrateImages();
            break;
        case 'migrateProdCategories':
            $wasSuccessful = $MigrateProducts->migrateProdCategories();
            break;
        case 'migrateRelated':
            $wasSuccessful = $MigrateProducts->migrateRelated();
            break;
        default:
            break;
    }
}
$results = array();
if($wasSuccessful){
    $results[] = array('code' => 0, 'message' => 'ok');
}else{
    //To-do: Get acual error
    $results[] = array('code' => 1, 'message' => $error);
}
print $json = json_encode($results);
?>
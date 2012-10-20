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
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken('get') or die('Invalid Token');

class MigrateEntity {
    
    public static function jsonResult($entity, $srcDB, $destDB, $storeUrl, $mediaPath, $migrateTask){
        $className = 'Migrate'.ucfirst($entity);
        $MigrationClass = new $className($srcDB, $destDB, $storeUrl);
        if($mediaPath!=''){
            $MigrationClass->setImageFolder($mediaPath);
        }
        $wasSuccessful = false;
        //$MigrationClass->clearData();
        if(method_exists($MigrationClass, $migrateTask) ){
            $wasSuccessful =  $MigrationClass->$migrateTask();
        }
        $results = array('code' => 0, 'message' => 'ok');
        if(!$wasSuccessful){
            $errorMessage = $MigrationClass->getError();
            $results = array('code' => 1, 'message' => $errorMessage['message']);
        }
        unset($MigrationClass);
        return $json = json_encode($results);
    }
    
}
//
//$MigrateCategories = new MigrateCategories($this->srcDB, $this->destDB, $this->storeUrl);
//$MigrateCategories->setImageFolder($this->mediaCategoryPath);
//$wasSuccessful = false;
//$output = '';
////$MigrateCategories->clearData();
//$error = 'error';
//switch ($this->migrateTask) {
//    case 'hasNoConflict':
//        $wasSuccessful = $MigrateCategories->hasNoConflict();
//        break;
//    case 'migrateCategories':
//        $wasSuccessful = $MigrateCategories->migrateCategories();
//        break;
//    case 'migrateCategoryCategories':
//        $wasSuccessful = $MigrateCategories->migrateCategoryCategories();
//        break;
//    case 'migrateImages':
//        $wasSuccessful = $MigrateCategories->migrateImages();
//        break;
//    case 'migrateDescriptions':
//        $wasSuccessful = $MigrateCategories->migrateDescriptions();
//        break;
//    default:
//        break;
//}
//$results = array('code' => 0, 'message' => 'ok');
//if(!$wasSuccessful){
//    $errorMessage = $MigrateCategories->getError();
//    $results = array('code' => 1, 'message' => $errorMessage['message']);
//}
//print $json = json_encode($results);
?>
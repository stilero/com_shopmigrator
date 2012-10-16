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
    protected static $_catToStoreTable = '#__category_to_store';
    protected static $_catDescTable = '#__category_description';
    protected static $_vmCatTable = '#__virtuemart_categories';
    protected static $_vmMediasTable = '#__virtuemart_medias';
    protected static $_vmCatMediaTable = '#__virtuemart_category_medias';
    protected static $_vmCatDescTable = '#__virtuemart_categories_en_gb';
    protected static $_vmCatCatsTable = '#__virtuemart_category_categories';
    protected static $destImagesFolder = 'images/stories/virtuemart/category/';
    protected static $destImagesThumbFolder = 'images/stories/virtuemart/category/resized/';
    protected  $srcImagesFolderURL;
    protected $_vmImagePath;
    protected $_vmThumbPath;
    protected $_thumbWidth;
    protected $_thumbHeight;


    public function __construct($MigrateSrcDB, $MigrateDestDB, $storeUrl, $storeid=0) {
        parent::__construct($MigrateSrcDB, $MigrateDestDB, $storeUrl, $storeid);
        $this->srcImagesFolderURL = $storeUrl.'image/';
        $this->_vmImagePath = self::$destImagesFolder;
        $this->_vmThumbPath = self::$destImagesThumbFolder;
        $this->_thumbHeight = 90;
        $this->_thumbWidth = 90;
    }
    
    public function setImageFolder($folderPathRelativeToJoomlaRoot){
        $this->_vmImagePath = $folderPathRelativeToJoomlaRoot;
        $this->_vmThumbPath = $folderPathRelativeToJoomlaRoot.'resized/';
    }
    
    public function setThumbSize($width, $height){
        $this->_thumbHeight = $height;
        $this->_thumbWidth = $width;
    }
    
    public function getCategoriesForStore($langcode=1){
        if(isset($this->_sourceData)){
            return $this->_sourceData;
        }
        $db =& $this->_sourceDB;
        $query = $db->getQuery(true);
        $query->select('cat.*, dsc.name, dsc.description, dsc.meta_description, dsc.meta_keyword, CONCAT(\''.$this->srcImagesFolderURL.'\', cat.image) AS \'imageurl\'');
        $query->from(self::$_catToStoreTable.' cts');
        $query->innerJoin(self::$_catTableName.' cat ON cat.category_id=cts.category_id');
        $query->innerJoin(self::$_catDescTable.' dsc ON dsc.category_id=cts.category_id');
        $query->where('cts.store_id = '.(int)$this->_storeid.' AND dsc.language_id='.(int)$langcode);
        $db->setQuery($query);
        $this->_sourceData = $db->loadObjectList();
        return $this->_sourceData;
    } 
    
    public function clearData(){
        $isSuccessful = true;
        $tables = array(
            self::$_vmCatTable,
            self::$_vmCatMediaTable,
            self::$_vmCatDescTable
        );
        $db =& $this->_destDB;
        foreach ($tables as $table) {
            $query = $db->getQuery(true);
            $query->delete($table);
            $query->where('virtuemart_category_id > 5');
            $db->setQuery($query);
            $result = $db->query();
            if(!$result){
               $isSuccessful *= false;
            }
        }
        $query =& $db->getQuery(true);
        $query->delete(self::$_vmCatCatsTable);
        $query->where('category_child_id > 5' );
        $db->setQuery($query);
        $result = $db->query();
        if(!$result){
           $isSuccessful *= false;
        }
        return (bool)$isSuccessful;
    }
    
    protected function setDescription($desc){
        $isSuccessful = true;
        $slug = strtolower(str_replace(' ', '', $desc->name));
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmCatDescTable);
        $query->set('virtuemart_category_id = '.(int)$desc->category_id);
        $query->set('category_name = '.$db->quote($desc->name));
        $query->set('category_description = '.$db->quote($desc->description));
        $query->set('metadesc = '.$db->quote($desc->meta_description));
        $query->set('metakey = '.$db->quote($desc->meta_keyword));
        $query->set('slug = '.$db->quote($slug));
        $db->setQuery($query);
        $result = $db->query();
        if(!$result){
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed setting category description'.$desc->category_id);
            $isSuccessful = false;
        }
        return $isSuccessful;
    }
    
    public function migrateDescriptions(){
        $isSuccessful = true;
        $descs = $this->getCategoriesForStore();
        foreach ($descs as $desc) {
            $isSuccessful *= $this->setDescription($desc);
        }
        return (bool)$isSuccessful;
    }
    
    protected function setCategory($catId){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmCatTable);
        $query->set('virtuemart_category_id = '.(int)$catId);
        $query->set('virtuemart_vendor_id = 1');
        $query->set('category_template = \'\'');
        $query->set('category_layout = \'\'');
        $query->set('category_product_layout = \'\'');
        $query->set('products_per_row = 4');
        $db->setQuery($query);
        $result = $db->query();
        if(!$result){
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed setting category '.$catId);
            return $false;
        }
        return true;
    }
    
    public function migrateCategories(){
        $isSuccessful = true;
        $ocCategories = $this->getCategoriesForStore();
        foreach ($ocCategories as $ocCategory) {
            $isSuccessful *= $this->setCategory($ocCategory->category_id);
        }
        return (bool)$isSuccessful;
    }
        
    protected function setCategoryCategories($catId, $parentId){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmCatCatsTable);
        $query->set('category_parent_id = '.(int)$parentId);
        $query->set('category_child_id = '.(int)$catId);
        $db->setQuery($query);
        $result = $db->query();
        if(!$result){
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed setting Category Categories'.$catId);
            return false;
        }
        return true;
    }
    
    public function migrateCategoryCategories(){
        $isSuccessful = true;
        $ocCategories = $this->getCategoriesForStore();
        foreach ($ocCategories as $ocCategory) {
            $isSuccessful *= $this->setCategoryCategories($ocCategory->category_id, $ocCategory->parent_id);
        }
        return (bool)$isSuccessful;
    }
    
    protected function setImage($catID, $bigImage, $thumbImage){
        $isSuccessful = true;
        $file_title = str_replace('.'.JFile::getExt($bigImage), '', JFile::getName($bigImage));
        $imgprop = JImage::getImageFileProperties($bigImage);
        $mime_type = $imgprop->type;
        $shortPath = str_replace(JPATH_ROOT.DS, '', $bigImage);
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmMediasTable);
        $query->set('virtuemart_vendor_id = 1');
        $query->set('file_title = '.$db->quote($file_title));
        $query->set('file_meta = '.$db->quote($file_title));
        $query->set('file_mimetype = '.$db->quote('image/jpeg'));
        $query->set('file_type = '.$db->quote('category'));
        $query->set('file_url = '.$db->quote($shortPath));
        $query->set('file_url_thumb = '.$db->quote($thumbImage));
        $db->setQuery($query);
        $result = $db->query();
        if(!$result){
            $isSuccessful = false;
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed Setting image for Cateogry '.$catId);
        }
        $lastRowId = $db->insertid();
        $query2 = $db->getQuery(true);
        $query2->insert(self::$_vmCatMediaTable);
        $query2->set('virtuemart_category_id = '.(int)$catID);
        $query2->set('virtuemart_media_id = '.(int)$lastRowId);
        $db->setQuery($query2);
        $result = $db->query();
        if(!$result){
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed setting image for Category in media '.$catId);
            $isSuccessful *= false;
        }
        return $isSuccessful;
    }
    
    public function migrateImages(){
        $isSuccessful = true;
        $images = $this->getCategoriesForStore();
        foreach ($images as $image) {
            if($image->image != ''){
                $bigImage = $this->migrateFile($image->imageurl, $this->_vmImagePath);
                $thumbImage = $this->_vmThumbPath.JFile::getName($bigImage);
                 $this->resizeImage($bigImage, $this->_thumbHeight, $this->_thumbWidth, JPATH_BASE.DS.$thumbImage);
                if($bigImage != FALSE){
                    $isSuccessful *= $this->setImage($image->category_id, $bigImage, $thumbImage);
                 }else{
                     $error[] = array(MigrateError::FILE_MOVE_PROBLEM => $bigImage);
                     $isSuccessful *= false;
                 }
            }
        }
        return (bool)$isSuccessful;
    }

    
    public function __set($name, $value) {
        $this->$name = $value;
    }

}

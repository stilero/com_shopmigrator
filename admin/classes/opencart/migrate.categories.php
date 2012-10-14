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
    protected  $srcImagesFolderURL;
    protected static $destImagesFolder = 'images/stories/virtuemart/category/';
    protected static $destImagesThumbFolder = 'images/stories/virtuemart/category/resized/';

    public function __construct($MigrateSrcDB, $MigrateDestDB, $storeUrl, $storeid=0) {
        parent::__construct($MigrateSrcDB, $MigrateDestDB, $storeUrl, $storeid);
        $this->srcImagesFolderURL = $storeUrl.'image/';
    }
    
    public function getCategoriesForStore($langcode=1){
        if(isset($this->_sourceData)){
            return $this->_sourceData;
        }
        $db =& $this->_sourceDB;
        //$db =& JFactory::getDbo();
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
    
    public function deleteMigratedCategories(){
        $db =& $this->_destDB;
        $categories = $this->getCategoriesForStore();
        $catIds = array();
        foreach ($categories as $category) {
            $catIds[] = $category->category_id;
        }
        $query =& $db->getQuery(true);
        $query->delete(self::$_vmCatTable);
        $query->where('virtuemart_category_id > 5' );
        $db->setQuery($query);
        $db->query();
        
        $query =& $db->getQuery(true);
        $query->delete(self::$_vmCatMediaTable);
        $query->where('virtuemart_category_id > 5' );
        $db->setQuery($query);
        $db->query();
        
        $query =& $db->getQuery(true);
        $query->delete(self::$_vmCatDescTable);
        $query->where('virtuemart_category_id > 5' );
        $db->setQuery($query);
        $db->query();
        
        $query =& $db->getQuery(true);
        $query->delete(self::$_vmCatCatsTable);
        $query->where('category_child_id > 5' );
        $db->setQuery($query);
        $db->query();
    }
    
    protected function setDescription($catID, $catName, $catDesc, $metaDesc='', $metaKey=''){
        $slug = strtolower(str_replace(' ', '', $catName));
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmCatDescTable);
        $query->set('virtuemart_category_id = '.(int)$catID);
        $query->set('category_name = '.$db->quote($catName));
        $query->set('category_description = '.$db->quote($catDesc));
        $query->set('metadesc = '.$db->quote($metaDesc));
        $query->set('metakey = '.$db->quote($metaKey));
        $query->set('slug = '.$db->quote($slug));
        $db->setQuery($query);
        $result = $db->query();
        if(!$result){
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed setting category description'.$catId);
        }
    }
    
    public function migrateDescriptions(){
        $descs = $this->getCategoriesForStore();
        foreach ($descs as $desc) {
            $this->setDescription(
                    $desc->category_id, 
                    $desc->name, 
                    $desc->description,
                    $desc->meta_description,
                    $desc->meta_keyword
            );
        }
        return true;
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
        }
    }
    
    public function migrateCategories(){
        $ocCategories = $this->getCategoriesForStore();
        foreach ($ocCategories as $ocCategory) {
            $this->setCategory($ocCategory->category_id);
        }
        return true;
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
        }
    }
    
    public function migrateCategoryCategories(){
        $ocCategories = $this->getCategoriesForStore();
        foreach ($ocCategories as $ocCategory) {
            $result = $this->setCategoryCategories($ocCategory->category_id, $ocCategory->parent_id);
            if(!$result){
                $this->_error[] = array(MigrateError::DB_ERROR, $ocCategory->category_id);
            }
        }
        return true;
    }
    
    protected function setImage($catID, $bigImage, $thumbImage){
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
        }
    }
    
    public function migrateImages(){
        $images = $this->getCategoriesForStore();
        foreach ($images as $image) {
            if($image->imageurl != $this->srcImagesFolderURL){
                $bigImage = $this->migrateFile($image->imageurl, self::$destImagesFolder);
                $thumbImage = self::$destImagesThumbFolder.JFile::getName($bigImage);
                 $this->resizeImage($bigImage, 90, 90, JPATH_BASE.DS.$thumbImage);
                if($bigImage != FALSE){
                    $this->setImage($image->category_id, $bigImage, $thumbImage);
                 }else{
                     $error[] = array(MigrateError::FILE_MOVE_PROBLEM => $bigImage);
                 }
            }
        }
        return true;
    }

    
    public function __set($name, $value) {
        $this->$name = $value;
    }

}

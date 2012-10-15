<?php
/**
 * ShopMigrator
 *
 * @version  1.0
 * @package Stilero
 * @subpackage ShopMigrator
 * @author Daniel Eliasson Stilero Webdesign http://www.stilero.com
 * @copyright  (C) 2012-okt-14 Stilero Webdesign, Stilero AB
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class MigrateProducts extends Migrate{
    
    protected static $_prodTable = '#__product';
    protected static $_prodToStoreTable = '#__product_to_store';
    protected static $_prodDescTable = '#__product_description';
    protected static $_prodImageTable = '#__product_image';
    protected static $_prodRelatedTable = '#__product_related';
    protected static $_vmProductTable = '#__virtuemart_products';
    protected static $_vmProductDescTable = '#__virtuemart_products_en_gb';
    protected static $_vmProductPriceTable = '#__virtuemart_product_prices';
    protected static $_vmProductMediasTable = '#__virtuemart_product_medias';
    protected static $_vmMediasTable = '#__virtuemart_medias';
    protected static $_vmProdRelationsTable = '#__virtuemart_product_relations';
    protected static $_vmProdManufacturerTable = '#__virtuemart_product_manufacturers';
    private $vmCurrencyCode;
    protected  $srcImagesFolderURL;
    protected static $destImagesFolder = 'images/stories/virtuemart/category/';
    protected static $destImagesThumbFolder = 'images/stories/virtuemart/category/resized/';


    public function __construct($MigrateSrcDB, $MigrateDestDB, $storeUrl, $storeid=0, $vmCurrencyCode=144) {
        parent::__construct($MigrateSrcDB, $MigrateDestDB, $storeUrl, $storeid);
        $this->srcImagesFolderURL = $storeUrl.'image/';
        $this->vmCurrencyCode = $vmCurrencyCode;
    }
    
    public function getData(){
        if(isset($this->_sourceData)){
            return $this->_sourceData;
        }
        $db =& $this->_sourceDB;
        $query = $db->getQuery(true);
        $query->select('p.*, dsc.name, dsc.description, dsc.meta_description, dsc.meta_keyword, dsc.tag, i.product_image_id, CONCAT(\''.$this->srcImagesFolderURL.'\', i.image) AS \'imageurl\'');
        $query->from(self::$_prodToStoreTable.' pts');
        $query->innerJoin(self::$_prodTable.' p ON p.product_id = pts.product_id');
        $query->innerJoin(self::$_prodDescTable.' dsc ON dsc.product_id = pts.product_id');
        $query->innerJoin(self::$_prodImageTable.' i ON i.product_id = pts.product_id');
        $query->where('pts.store_id = '.(int)$this->_storeid);
        $query->group('i.product_id');
        $db->setQuery($query);
        $this->_sourceData = $db->loadObjectList();
        return $this->_sourceData;
    }
    
    public function clearMigratedData(){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->delete(self::$_vmProductTable);
        $query->where('virtuemart_product_id > 16');
        $db->setQuery($query);
        $db->query();
        
        $query = $db->getQuery(true);
        $query->delete(self::$_vmProdManufacturerTable);
        $query->where('virtuemart_product_id > 16');
        $db->setQuery($query);
        $db->query();
        
        $query = $db->getQuery(true);
        $query->delete(self::$_vmProdRelationsTable);
        $query->where('virtuemart_product_id > 16');
        $db->setQuery($query);
        $db->query();
        
        $query = $db->getQuery(true);
        $query->delete(self::$_vmProductDescTable);
        $query->where('virtuemart_product_id > 16');
        $db->setQuery($query);
        $db->query();
        
        $query = $db->getQuery(true);
        $query->delete(self::$_vmProductMediasTable);
        $query->where('virtuemart_product_id > 16');
        $db->setQuery($query);
        $db->query();
        
    }
    
    protected function setProduct($product){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmProductTable);
        $query->set('virtuemart_product_id = '.(int)$product->product_id);
        $query->set('virtuemart_vendor_id = 1');
        $query->set('product_sku = '.$db->quote($product->sku));
        $query->set('product_weight = '.$db->quote($product->weight)); //Check weight class
        $query->set('product_length = '.$db->quote($product->length));
        $query->set('product_width = '.$db->quote($product->width));
        $query->set('product_height = '.$db->quote($product->height)); //Check length class
        $query->set('product_in_stock = '.(int)$product->quantity);
        $date =& JFactory::getDate($product->date_available);
        $query->set('product_available_date = '.$db->quote($date->toMySQL()));
        $query->set('hits = '.$db->quote($product->viewed));
        $query->set('created_on = '.$db->quote($product->date_added));
        $query->set('modified_on = '.$db->quote($product->date_modified));
        print $query->dump();
        $db->setQuery($query);
        $db->query();
    }
    
    protected function setProductDescription($product){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmProductDescTable);
        $query->set('virtuemart_product_id = '.(int)$product->product_id);
        $query->set('product_s_desc = '.$db->quote(JHtmlString::truncate(html_entity_decode($product->description), 140, true, false)));
        $query->set('product_desc = '.$db->quote($product->description));
        $query->set('product_name = '.$db->quote($product->name));
        $query->set('metadesc = '.$db->quote($product->meta_description));
        $query->set('metakey = '.$db->quote($product->meta_keyword));
        $query->set('slug = '.$db->quote(str_replace(' ', '', $product->name)));
        $db->setQuery($query);
        $db->query();
    }
    
    protected function setProductPrice($product){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmProductPriceTable);
        $query->set('virtuemart_product_id = '.(int)$product->product_id);
        $query->set('product_price = '.$db->quote($product->price));
        $query->set('product_currency = '.(int)$this->vmCurrencyCode);
        $db->setQuery($query);
        $db->query();
    }
    
    protected function setProductManufacturer($product){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmProdManufacturerTable);
        $query->set('virtuemart_product_id = '.(int)$product->product_id);
        $query->set('virtuemart_manufacturer_id = '.(int)$product->manufacturer_id);
        $db->setQuery($query);
        $db->query();
    }
    
    protected function setImage($prodId, $bigImage, $thumbImage){
        $file_title = str_replace('.'.JFile::getExt($bigImage), '', JFile::getName($bigImage));
        $imgprop = JImage::getImageFileProperties($bigImage);
        $shortPath = str_replace(JPATH_ROOT.DS, '', $bigImage);
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmMediasTable);
        $query->set('virtuemart_vendor_id = 1');
        $query->set('file_title = '.$db->quote($file_title));
        $query->set('file_meta = '.$db->quote($file_title));
        $query->set('file_mimetype = '.$db->quote('image/jpeg'));
        $query->set('file_type = '.$db->quote('product'));
        $query->set('file_url = '.$db->quote($shortPath));
        $query->set('file_url_thumb = '.$db->quote($thumbImage));
        $db->setQuery($query);
        $result = $db->query();
        if(!$result){
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed Setting image for Product '.$catId);
        }
        $lastRowId = $db->insertid();
        $query2 = $db->getQuery(true);
        $query2->insert(self::$_vmProductMediasTable);
        $query2->set('virtuemart_product_id = '.(int)$prodId);
        $query2->set('virtuemart_media_id = '.(int)$lastRowId);
        $db->setQuery($query2);
        $result = $db->query();
        if(!$result){
            $this->_error[] = array(MigrateError::DB_ERROR, 'Failed setting image for Product in media '.$catId);
        }
    }
    
    public function migrateImages(){
        $images = $this->getData();
        foreach ($images as $image) {
            if($image->image != ''){
                $bigImage = $this->migrateFile($image->imageurl, self::$destImagesFolder);
                $thumbImage = self::$destImagesThumbFolder.JFile::getName($bigImage);
                 $this->resizeImage($bigImage, 90, 90, JPATH_BASE.DS.$thumbImage);
                if($bigImage != FALSE){
                    $this->setImage($image->product_id, $bigImage, $thumbImage);
                 }else{
                     $error[] = array(MigrateError::FILE_MOVE_PROBLEM => $bigImage);
                 }
            }
        }
        return true;
    }
        
    public function migrateProducts(){
        $products = $this->getData();
        foreach ($products as $product) {
            $this->setProduct($product);
            $this->setProductDescription($product);
            $this->setProductPrice($product);
            $this->setProductManufacturer($product);
        }
    }
    
    protected function setRelative($relative){
        $db =& $this->_destDB;
        $query = $db->getQuery(true);
        $query->insert(self::$_vmProdRelationsTable);
        $query->set('virtuemart_product_id = '.(int)$relative->product_id);
        $query->set('related_products = '.(int)$relative->related_id);
        $db->setQuery($query);
        $db->query();
    }


    public function migrateRelated(){
        $db =& $this->_sourceDB;
        $query =& $db->getQuery(true);
        $query->select('*');
        $query->from(self::$_prodRelatedTable);
        $db->setQuery($query);
        $relatives = $db->loadObjectList();
        foreach ($relatives as $relative) {
            $this->setRelative($relative);
        }
    }
    
    
    
   
}

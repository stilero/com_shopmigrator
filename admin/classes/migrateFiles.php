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

class MigrateFiles{
    
    public $tmpDir;
    public $destPath;
    public $destFile;
    
    public function __construct($destPath) {
        $this->tmpDir = JPATH_BASE.DS.'tmp';
        $this->destPath = $destPath;
    }
    
    public function migrateFile($src, $destPath = ''){
        if($destPath != ''){
            $this->destPath = $destPath;
        }
        $this->destFile = $this->destPath.JFile::getName($src);
        $this->preCheckDestFile();
        $tmpFile = $this->tmpDir.DS.JFile::getName($src);
        $content = file_get_contents($src);
        file_put_contents($tmpFile, $content);
        JFile::move($tmpFile, $this->destFile);
        if( ! JFile::exists($this->destFile)){
            return false;
        }
        return true;
    }
    
    private function preCheckDestFile(){
        $isExisting = JFile::exists($this->destFile);
        if($isExisting){
            $uniqId = substr(uniqid(), 0, 5);
            $this->destFile = $this->destFile.$uniqId;
            $this->preCheckDestFile();
        }
        return;
    }
    
}

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
$bootstrap_uri = JURI::base().'components/com_shopmigrator/assets/bootstrap';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Bootstrap 101 Template</title>
        <!-- Bootstrap -->
        <link href="<?php print $bootstrap_uri.'/css/bootstrap.min.css'  ?>" rel="stylesheet">
    </head>
    <body>
        <h1>Hello, world!</h1>
        <script src="http://code.jquery.com/jquery-latest.js"></script>
        <script src="<?php print $bootstrap_uri.'/js/bootstrap.min.js' ?>"></script>
    </body>
</html>
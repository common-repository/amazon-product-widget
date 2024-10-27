<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Uninstall script
 *
 * Uninstall.php
 *
 * PHP versions 5
 *
 * LICENSE:
 * Amazon Product Widget
 *
 * Copyright (c) 2011, Benjamin Carl - All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - All advertising materials mentioning features or use of this software
 *   must display the following acknowledgement: This product includes software
 *   developed by Benjamin Carl and other contributors.
 * - Neither the name Benjamin Carl nor the names of other contributors
 *   may be used to endorse or promote products derived from this
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Please feel free to contact us via e-mail: opensource@clickalicious.de
 *
 * @category   Wordpress
 * @package    Wordpress_Amazon_Product_Widget
 * @subpackage Wordpress_Amazon_Product_Widget
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    SVN: $Id: $
 */

// base path for include operations
define('APW_BASE_DIRECTORY', dirname(__FILE__));
define('APW_LIB_DIRECTORY', APW_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'Lib');

// get autoloader classfile
require_once APW_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'Apw' . DIRECTORY_SEPARATOR . 'Autoload.php';

/**
 * Amazon Product Widget - Class
 *
 * Amazon Productlink-Widget displays a random Amazon-Product (image and description) from a list
 * of ASIN's (IDs). It makes use of the Amazon-AWS to retrieve the product-description and further information.
 *
 * @category   Wordpress
 * @package    Wordpress_Amazon_Product_Widget
 * @subpackage Wordpress_Amazon_Product_Widget
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @link       http://www.phpfluesterer.de/projects/open-source/amazon-product-widgets
 */
class Amazon_Product_Widget_Uninstall
{
    /**
     * contains an instance of the apw config (defaults ...)
     *
     * @var object An instance of the apw config (defaults ...)
     * @access private
     */
	private $_configuration;

    /**
     * contains an instance of the wordpress-OOP-interface-class
     *
     * @var object An instance of the wordpress oop-interface-class
     * @access private
     * @static
     */
	private $_wordpress;

    /**
     * contains an instance of PHP-Dependeny DI-container
     *
     * @var object An instance of the PHP-Dependency DI-container
     * @access private
     * @static
     */
	private $_container;


    /**
     * creates a new "Amazon_Product_Widget_Uninstall"
     *
     * This method is intend to create a new instance which:
     * uninstalls the plugin and removes the data
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function __construct()
	{
    	// include Pd's bootstrapper
    	include_once APW_LIB_DIRECTORY . DIRECTORY_SEPARATOR . 'Pd' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

    	// autoloader for APW-Plugin
		$autoloadApw = new Apw_Autoload(null, APW_BASE_DIRECTORY);
    	$autoloadApw->register();

    	// autoloader for Dp-Framework
    	$autoloadDp = new Apw_Autoload('Pd', APW_LIB_DIRECTORY);
    	$autoloadDp->setNamespaceSeparator('_');
    	$autoloadDp->register();

    	// get configuration
    	$this->_wordpress = new Apw_Wordpress_Interface();

		// get DI-container and setup dependencies
		$this->_container = Pd_Container::get();
		$this->_container->dependencies()->set('wordpress', $this->_wordpress);

		// @var self::$_configuration Apw_Configuration
		$this->_configuration = Pd_Make::name('Apw_Configuration');
	}

    /**
     * cleans up when plugin is deactivated
     *
     * This method is intend to remove the fields from database (cleanup) when the plugin is deactivated.
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function cleanup()
	{
		// assume empty backup
		$backup = '';

		// get all fields stored in wordpress' database
        $fieldsets = $this->_configuration->getFields();

		// iterate over fields
        foreach ($fieldsets as $area => $fields) {
        	foreach ($fields as $field => $config) {
        	    // if the field isn't a key or a password -> back it up
        		if (!stristr($field, 'key') && !stristr($field, 'pass')) {
            		$backup .= $field . ' = ' . $this->_configuration->getOption($field) . "\n";
        		}

				if ($config['cleanup']) {
					$this->_configuration->deleteOption($field);
				}
        	}
        }

        // TODO: make this available via GUI and let the user im- and export settings
        // write the backup
        /*
		$this->_writeBackup(
			APW_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'apw-backup-'.date('Y-m-d_H-i-s').'.txt',
			$backup
		);
		*/
	}

    /**
     * writes backup-data to a backup-file
     *
     * This method is intend to write the given backup-data to a backup-file.
     *
     * @param string $filename The name of the file (including path) to write data to
     * @param string $data     The data to write to file
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	private function _writeBackup($filename, $data)
	{
		// try to write backup-file
        file_put_contents($filename, $data);
	}
}

// check first if the request is a correct - wordpress initiated - uninstall request
if (defined('ABSPATH') && defined('WP_UNINSTALL_PLUGIN')) {
	$uninstall = new Amazon_Product_Widget_Uninstall();
	$uninstall->cleanup();
}

?>

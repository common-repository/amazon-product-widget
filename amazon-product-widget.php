<?php
/*
Plugin Name: Amazon Product Widget
Plugin URI:  http://www.phpfluesterer.de/projekte/open-source/amazon-product-widget
Description: This plugin displays a random Product from Amazon. You only need to define one or more Amazon Standard Identification Number(s) (ASIN). The complete data like image, title, description and so on is retrieved from the Amazon Web Service (AWS) API. The plugin is multi-widget (sidebar) ready and fully customizable through a editable HTML-template for displaying the products.
Author:      Benjamin Carl
Version:     1.2.0
Author URI:  http://www.phpfluesterer.de/ueber-mich/
License:     BSD License
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
class Amazon_Product_Widget extends WP_Widget
{
    /**
     * contains an instance of the front part
     * (display in template/sidebar)
     *
     * @var object An instance of the front-class
     * @access private
     * @static
     */
	private static $_front;

    /**
     * contains an instance of the back part
     * (configuration and setup in widget-config)
     *
     * @var object An instance of the back-class
     * @access private
     * @static
     */
	private static $_back;

    /**
     * contains an instance of the request-class used
     * for requesting data from AWS
     *
     * @var object An instance of the request-class
     * @access private
     * @static
     */
	private static $_request;

    /**
     * contains an instance of the data-class used for
     * retrieving data from cache or amazon
     *
     * @var object An instance of the data-class
     * @access private
     * @static
     */
	private static $_data;

    /**
     * contains an instance of the configuration-class
     * (contains the configuration and defaults)
     *
     * @var object An instance of the configuration-class
     * @access private
     * @static
     */
	private static $_configuration;

    /**
     * contains an instance of the widget-class
     *
     * @var object An instance of the widget-class
     * @access private
     * @static
     */
	private static $_widget;

    /**
     * contains an instance of the plugin-class
     *
     * @var object An instance of the plugin-class
     * @access private
     * @static
     */
	private static $_plugin;

    /**
     * contains an instance of the template-class
     *
     * @var object An instance of the template-class
     * @access private
     * @static
     */
	private static $_template;

    /**
     * contains an instance of the wordpress-OOP-interface-class
     *
     * @var object An instance of the wordpress oop-interface-class
     * @access private
     * @static
     */
	private static $_wordpress;

    /**
     * contains an instance of PHP-Dependeny DI-container
     *
     * @var object An instance of the PHP-Dependency DI-container
     * @access private
     * @static
     */
	private static $_container;


    /**
     * creates a new "Amazon_Product_Widget"
     *
     * This method is intend to create a new instance which:
     * is the main instance of this plugin
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function Amazon_Product_Widget()
    {
    	// @var self::$_request Apw_Request
		self::$_request = Pd_Make::name('Apw_Request');

		// store request dependency
		self::$_container->dependencies()->set('request', self::$_request);

		// @var self::$_template Apw_Template
		self::$_template = Pd_Make::name('Apw_Template');

		// store configuraiton dependency
		self::$_container->dependencies()->set('template', self::$_template);

		// @var self::$_data Apw_Data
		self::$_data = Pd_Make::name('Apw_Data');

		// store configuraiton dependency
		self::$_container->dependencies()->set('data', self::$_data);

		// @var self::$_front Apw_Front
		self::$_front = Pd_Make::name('Apw_Front');

		// store request dependency
		self::$_container->dependencies()->set('wp_widget', $this);

		// @var self::$_widget Apw_Widget
		self::$_widget = Pd_Make::name('Apw_Widget');

		// store configuraiton dependency
		self::$_container->dependencies()->set('widget', self::$_widget);

		// @var self::$_plugin Apw_Plugin
		self::$_plugin = Pd_Make::name('Apw_Plugin');

		// store configuraiton dependency
		self::$_container->dependencies()->set('plugin', self::$_plugin);

		// @var self::$_back Apw_Back
		self::$_back = Pd_Make::name('Apw_Back');

		// description of this plugin
		$description = 'Displays a random Amazon-Product (including Image, Title and Description [all optional]) '.
					   'from a given list of ASIN\'s. It retrieves all information from the Amazon (AWS) API';

        // classname and description to display in widget-menu
        $widgetOps = array(
            'classname'   => get_class($this),
            'description' => $description
        );

        // call the parent constructor
        parent::WP_Widget(false, self::$_configuration->getPluginName(), $widgetOps);
    }

    /**
     * dispatches the wordpress call to the front-class
     *
     * This method dispatches the wordpress internal call to display the widget (html output)
     * to the front-class. The front-class contains all output related functionallity.
     *
     * @param array $arguments The arguments for display
     * @param array $instance  The instance
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function widget($arguments, $instance)
    {
		self::$_front->displayWidget($arguments, $instance);
    }

    /**
     * dispatches the wordpress call to the back-class
     *
     * This method dispatches the wordpress internal call to display the widget (html output
     * for configuring the widget instance) to the back-class. The back-class contains all
     * backend related functionallity.
     *
     * @param array $instance The data for the current widget instance
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function form($instance)
    {
		self::$_back->displayWidget($instance);
    }

    /**
     * dispatches the wordpress call to the back-class
     *
     * This method dispatches the wordpress internal call to pre-process the data before
     * the updated data gets written to db.
     *
     * @param array $newInstance The updated data
     * @param array $oldInstance The old data
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function update($newInstance, $oldInstance)
    {
		return self::$_back->updateWidget($newInstance, $oldInstance);
    }

    /**
     * initializes the plugin
     *
     * This method is intend to start the whole plugin process.
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public static function initPlugin()
    {
		self::$_back->initPlugin(plugin_basename(__FILE__));
    }

    /**
     * initializes the widget
     *
     * This method is intend to start the whole widget process.
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @static
     */
    public static function initWidget()
    {
    	// bootstrap
    	self::_bootstrap();

		// get classname of this plugin
    	$classname = self::$_configuration->getPluginClassName();

    	// assume we can use the widget so register it
    	self::$_wordpress->widget->register($classname);

    	// but now check if config done -> if not! hide it from widget control
    	// TODO: check for a cleaner/better way to do this
    	if (!self::$_configuration->isComplete()) {
			// remove widget from admin-view
    		self::$_wordpress->widget->unregister($classname);
    	}
    }

    /**
     * bootstrapping operations
     *
     * This method is intend to do the basic operations like init the autoloader
     * and some more to ensure operability of the class.
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @static
     */
    private static function _bootstrap()
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

    	// initialize
    	self::_init();
    }

    /**
     * init operations
     *
     * This method is intend to do the basic init operations. This inlcude the instanciation
     * of the DI-container, the wordpress-interface (OOP) and the configuration-class.
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @static
     */
    private static function _init()
    {
    	// get configuration
    	self::$_wordpress = new Apw_Wordpress_Interface();

		// get DI-container and setup dependencies
		self::$_container = Pd_Container::get();
		self::$_container->dependencies()->set('wordpress', self::$_wordpress);

		// @var self::$_configuration Apw_Configuration
		self::$_configuration = Pd_Make::name('Apw_Configuration');

		// store configuraiton dependency
		self::$_container->dependencies()->set('configuration', self::$_configuration);
    }
}


// register the main-init methods
add_action('init', array('Amazon_Product_Widget', 'initPlugin'));
add_action('widgets_init', array('Amazon_Product_Widget', 'initWidget'));

?>

<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Back Class
 *
 * Back.php
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
 * @package    Wordpress_Apw
 * @subpackage Wordpress_Apw_Back
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Back Class
 *
 * This class is responsible for managing the data in the backend (wordpress admin).
 * It generates and displays the configuration/options form and the widget
 *
 * @category   Wordpress
 * @package    Wordpress_Apw
 * @subpackage Wordpress_Apw_Back
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Back
{
    /**
     * contains an instance of the apw config (defaults ...)
     *
     * @var object An instance of the apw config (defaults ...)
     * @access private
     */
	private $_configuration;

    /**
     * contains an instance of the widget-class which contains all
     * widget-related backend functionallity
     *
     * @var object An instance of the widget-class
     * @access private
     */
	private $_widget;

    /**
     * contains an instance of the plugin-class which contains all
     * plugin-related backend functionallity
     *
     * @var object An instance of the plugin-class
     * @access private
     */
	private $_plugin;


	/*******************************************************************************************************************
	 * WIDGET
	 ******************************************************************************************************************/

    /**
     * preprocess the data before it gets updated
     *
     * This method is intend to preprocess the data before it gets updated.
     *
     * @param array $newInstance The updated data
     * @param array $oldInstance The old data
     *
     * @return  array The data which should be used for update by the wordpress backend
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function updateWidget($newInstance, $oldInstance)
	{
		return $this->_widget->update($newInstance, $oldInstance);
	}

    /**
     * displays the widget-form in widget menu sidebar-control
     *
     * This method is intend to display the widget-form in widget menu sidebar-control.
     *
     * @param array $configuration The data for the current widget instance
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function displayWidget($configuration)
	{
		$this->_widget->display($configuration);
	}

	/*******************************************************************************************************************
	 * PLUGIN
	 ******************************************************************************************************************/

    /**
     * initializes the whole plugin process
     *
     * This method is intend to initialize the whole plugin process.
     *
     * @param string $pluginName The name of the plugin
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function initPlugin($pluginName)
	{
		$this->_plugin->init($pluginName);
	}


	/*******************************************************************************************************************
	 * Dependency-Injection (DI)
	 ******************************************************************************************************************/

    /**
     * sets the dependency for configuration
	 *
     * This method is intend to set the dependency for configuration.
     *
     * @param object $configuration An object of the configuration class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject configuration
     */
    public function setConfiguration($configuration)
    {
        // store instance of configuration (for reading configuration)
        // @var $this->_configuration Apw_Configuration
		$this->_configuration = $configuration;
    }

    /**
     * sets the dependency for widget
	 *
     * This method is intend to set the dependency for widget.
     *
     * @param object $widget An instance of the widget class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject widget
     */
    public function setWidget($widget)
    {
		// store instance of widget-class
		// @var $this->_widget Apw_Widget
		$this->_widget = $widget;
    }

    /**
     * sets the dependency for plugin
	 *
     * This method is intend to set the dependency for plugin.
     *
     * @param object $plugin An instance of the plugin class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject plugin
     */
    public function setPlugin($plugin)
    {
		// store instance of plugin-class
		// @var $this->_plugin Apw_Plugin
		$this->_plugin = $plugin;
    }
}

?>

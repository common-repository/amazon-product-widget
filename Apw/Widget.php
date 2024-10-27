<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Widget Class
 *
 * Widget.php
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
 * @package    Wordpress_APW
 * @subpackage Wordpress_APW_Widget
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Amazon Product Widget Widget - Widget Class
 *
 * This class is responsible for managing the data in the backend (wordpress admin).
 * It generates and displays the configuration/options form and the widget
 *
 * @category   Wordpress
 * @package    Wordpress_APW
 * @subpackage Wordpress_APW_Widget
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class APW_Widget
{
    /**
     * contains an instance of the wordpress oop interface
     *
     * @var object An instance of the wordpress oop interface
     * @access private
     */
	private $_wordpress;

    /**
     * contains an instance of the apw config (defaults ...)
     *
     * @var object An instance of the apw config (defaults ...)
     * @access private
     */
	private $_configuration;

    /**
     * contains an instance of the template class
     *
     * @var object An instance of the template class
     * @access private
     */
	private $_template;

    /**
     * contains an instance of main class (WP_Widget child)
     *
     * @var object An instance of main class (WP_Widget child)
     * @access private
     */
	private $_wpWidget;


    /**
     * preprocess the data before it gets updated
     *
     * This method is intend to preprocess the data before it gets updated.
     *
     * @param array $newInstance The updated data
     * @param array $oldInstance The old data
     *
     * @return  array The data which should get saved by wordpress backend
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function update($newInstance, $oldInstance)
	{
        $instance = $oldInstance;

        // get widget config fields
        $fields = $this->_configuration->getFieldsByType('widget');

        // iterate over config-fields
        foreach ($fields as $fieldName => $fieldWidgetConfig) {
        	// assume we use the field 1:1 as we received it
        	$instance[$fieldName] = $newInstance[$fieldName];

        	// if not html flag or flag set to false strip tags from field
    		if (!isset($fieldWidgetConfig['html']) || $fieldWidgetConfig['html'] === false) {
    			$instance[$fieldName] = strip_tags($instance[$fieldName]);
    		}

    		// if a separator is set we need to prepare (split/merge) the data in a special way
    		if (isset($fieldWidgetConfig['separator'])) {
    			$list = explode($fieldWidgetConfig['separator'], $instance[$fieldName]);

    			$instance[$fieldName] = array();

        		foreach ($list as $listItem) {
        			$listItem = trim($listItem);
        			if (strlen($listItem)) {
        				$instance[$fieldName][] = $listItem;
        			}
        		}
    		}
    	}

        // return the prepared data
        return $instance;
	}

    /**
     * displays the widget-form in widget menu sidebar-control
     *
     * This method is intend to display the widget-form in widget menu sidebar-control.
     *
     * @param array $configuration The configuration
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function display($configuration)
	{
		// assume empty HTML
        $html = '';

        // set up some default widget settings
        $defaults = array(
            'title'    => $this->_configuration->getPluginName(),
            'list'     => array($this->_configuration->getDefault('asin')),
        	'template' => $this->_configuration->getDefault('template')
        );

        // get settings
        $configuration = $this->_wordpress->generic->parseArguments((array)$configuration, $defaults);

        // get fields for widget-config
        $fields = $this->_configuration->getFieldsByType('widget');

		// iterate over fields
        foreach ($fields as $fieldName => $fieldWidgetConfig) {
			$html .= $this->_template->get(
				'form',
				'Formfield'.ucfirst($fieldWidgetConfig['type']),
				array(
					'field-name'  => $this->_wpWidget->get_field_name($fieldName),
        			'field-id'    => $this->_wpWidget->get_field_id($fieldName),
        			'field-value' => $this->_parseFieldValue($configuration[$fieldName], $fieldWidgetConfig),
        			'field-label' => $fieldWidgetConfig['label'],
        			'field-help'  => $fieldWidgetConfig['help']
        		)
       		);
		}

		// get template for widget config
		$html = $this->_template->get('widget', 'config', array('widget-config-html' => $html));

		// echo result
		echo $html;
	}

    /**
     * parses a fields value
     *
     * This method is intend to parse out the value from a given field.
     * For example if the field contains an array it returns the value as
     * displayable string.
     *
     * @param mixed $value  The value to process
     * @param array $config The config to retrieve seperator from
     *
     * @return string The parsed value
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	private function _parseFieldValue($value, $config)
	{
		// check if
		if (is_array($value)) {
			if (!$config['separator']) {
				$config['separator'] = "\n";
			}

			$value = implode($config['separator'], $value);
 		}

 		return $value;
	}

	/*******************************************************************************************************************
	 * Dependency-Injection (DI)
	 ******************************************************************************************************************/

    /**
     * sets the dependency for wordpress
	 *
     * This method is intend to set the dependency for wordpress.
     *
     * @param object $wordpress An instance of the wordpress class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject wordpress
     */
    public function setWordpress($wordpress)
    {
		// store wordpress oop interface instance
		$this->_wordpress = $wordpress;
    }

    /**
     * sets the dependency for configuration
	 *
     * This method is intend to set the dependency for configuration.
     *
     * @param object $configuration An instance of the configuration class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject configuration
     */
    public function setConfiguration($configuration)
    {
        // store instance of configuration (for reading configuration)
		$this->_configuration = $configuration;
    }

    /**
     * sets the dependency for template
	 *
     * This method is intend to set the dependency for template.
     *
     * @param object $template An instance of the template class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject template
     */
    public function setTemplate($template)
    {
        // store instance of template
		$this->_template = $template;
    }

    /**
     * sets the dependency for wp_widget
	 *
     * This method is intend to set the dependency for wp_widget.
     *
     * @param object $wpWidget An instance of the wp_widget class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject wp_widget
     */
    public function setWpWidget($wpWidget)
    {
        // store instance of the main class
		$this->_wpWidget = $wpWidget;
    }
}

?>

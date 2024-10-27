<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Plugin Class
 *
 * Plugin.php
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
 * @subpackage Wordpress_APW_Plugin
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Amazon Product Widget Plugin - Plugin Class
 *
 * This class is responsible for managing the data in the backend (wordpress admin).
 * It generates and displays the configuration/options form and the widget
 *
 * @category   Wordpress
 * @package    Wordpress_APW
 * @subpackage Wordpress_APW_Plugin
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class APW_Plugin
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
     * contains an instance of the request class
     *
     * @var object An instance of the request class
     * @access private
     */
	private $_request;

    /**
     * contains all error-messages to display
     *
     * @var array contains all error-messages to display
     * @access private
     */
	private $_error = array();

    /**
     * contains all notice-messages to display
     *
     * @var array contains all notice-messages to display
     * @access private
     */
	private $_notice = array();


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
	public function init($pluginName)
	{
	    // admin actions
        if (is_admin()) {
        	// initializing
			add_action('admin_notices', array($this, 'checkNoticeRequirementsFailed'));
			add_action('admin_notices', array($this, 'checkNoticeConfigIncomplete'));
    		add_action('admin_menu', array($this, 'displayConfigurationMenuEntry'));
            add_action('admin_init', array($this, 'registerSettings'));
        }
	}

    /**
     * checks if the config is completed and trigger the display of a notice if not
     *
     * This method is intend to check if config is completed and trigger the display of a notice if not.
     *
     * @param boolean $override True to override
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function checkNoticeConfigIncomplete($override = false)
	{
		// show config notice if config is incomplete in any way
		if (!$this->_configuration->isComplete() && (!$this->isUpdateRequest() || $override)) {
			$this->_displayNoticeConfigIncomplete();
		}
	}
	
    /**
     * checks if the requirements are fulfilled
     *
     * This method is intend to check if the requirements are fulfilled.
     *
     * @param boolean $override True to override
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function checkNoticeRequirementsFailed($override = false)
	{
		// show config notice if requirements are not fulfilled
		if (!$this->requirementsFulfilled() && (!$this->isUpdateRequest() || $override)) {
			$this->_displayNoticeRequirementsFailed();
		}
	}

    /**
     * checks if the requirements are fulfilled
     *
     * This method is intend to check if the requirements are fulfilled.
     *
     * @return boolean TRUE if requirements fulfilled, otherwise FALSE
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
	 * @todo   Move to Config.php (Class Config)
     */
	public function requirementsFulfilled()
	{
		// check requirements and return status as boolean
		return (
			ini_get('allow_url_fopen') === '1'
		);
	}

    /**
     * whitelists the options allowed for html-form
     *
     * This method is intend to whitelist the register the option-fields for the option-form.
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    public function registerSettings()
    {
    	// get the form-fields from configuration
    	$formFields = $this->_configuration->getFieldsByType('plugin');

    	// iterate over fields
        foreach ($formFields as $formField => $config) {
        	$this->_wordpress->plugin->register->setting(
        		$this->_configuration->getIdentifier(), $this->_configuration->getPrefix() . $formField
			);
        }
    }
	
    /**
     * displays a notice that the configuration isn't completed
     *
     * This method is intend to display a notice that the configuration isn't completed
     * for users which can manage options (administrative rights)
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	private function _displayNoticeConfigIncomplete()
	{
		// check if user can manage options and not is on configuration page of apw
        if (current_user_can('manage_options')) {
        	if (!$this->isConfigurationPage()) {
        		$notice = $this->_configuration->getNotice(
					'config-incomplete',
					array('CONFIG-URL' => $this->_configuration->getConfigUrl(__FILE__))
				);
        	} else {
        		$notice = $this->_configuration->getNotice(
					'complete-keys'
				);
        	}

        	// display the notice
			echo $notice;
        }
	}

    /**
     * displays a notice that the requirements are not fulfilled
     *
     * This method is intend to display a notice that the requirements of the 
	 * plugin are not fulfilled
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	private function _displayNoticeRequirementsFailed()
	{
		// check if user can manage options and not is on configuration page of apw
        if (current_user_can('manage_options')) {
        	if (!$this->isConfigurationPage()) {
        		$notice = $this->_configuration->getNotice(
					'requirements-failure',
					array('CONFIG-URL' => $this->_configuration->getConfigUrl(__FILE__))
				);
        	}

        	// display the notice
			echo $notice;
        }
	}
	
    /**
     * returns the current active page in wordpress admin
     *
     * This method is intend to return the current active page in wordpress admin.
     *
     * @return  string The active page (scriptname)
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getCurrentPage()
    {
        // get current page from wordpress core
        global $pagenow;
        return $pagenow;
	}

    /**
     * returns the configuration page-url for this plugin
     *
     * This method is intend to return the configuration page-url for this plugin.
     *
     * @return  string The configuration page-url
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    public function getConfigurationPage()
    {
    	return plugin_basename(__FILE__);
    }

    /**
     * returns the options scriptname of wordpress
     *
     * This method is intend to return the options scriptname of wordpress.
     *
     * @return  string The options scriptname
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getWordpressOptionPage()
    {
        return 'options-general.php';
    }

    /**
     * checks if the current displayed page in wordpress admin is the configuration
     * page of the plugin.
     *
     * This method is intend to check if the current displayed page in wordpress admin
     * is the configuration page of the plugin.
     *
     * @return boolean TRUE if current page is configuration-page of the plugin, otherwise FALSE
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function isConfigurationPage()
	{
		$currentPage         = $this->_getCurrentPage();
		$configurationPage   = $this->getConfigurationPage();
        $wordpressOptionPage = $this->_getWordpressOptionPage();

		return ($currentPage == $wordpressOptionPage && isset($_GET['page']) && $_GET['page'] == $configurationPage);
	}

    /**
     * checks if the current request is an update-request
     *
     * This method is intend to check if the current request is an update-request and returns the result.
     *
     * @return boolean TRUE if current request is an update-request, otherwise FALSE
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function isUpdateRequest()
	{
		return (isset($_POST['action']) && $_POST['action'] == 'update');
	}

    /**
     * checks if the current request is an clear-cache-request
     *
     * This method is intend to check if the current request is an clear-cache-request and returns the result.
     *
     * @return boolean TRUE if current request is an clear-cache-request, otherwise FALSE
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function isClearCacheRequest()
	{
		return (isset($_POST['action']) && $_POST['action'] == 'clearcache' &&
			$_POST[$this->_configuration->getPrefix() . 'clearcache'] == 'on');
	}

    /**
     * clears the cached data
     *
     * This method is intend to clear the cached data from store (wordpress-db).
     *
     * @return  void
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _clearCache()
    {
        // clear the cache
        return $this->_configuration->resetOption('cache');
    }

    /**
     * creates a menu entry under "settings" in wordpress administration pages
     *
     * This method is intend to create a menu entry under "settings" for user
     * which can manage options (administrative rights)
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function displayConfigurationMenuEntry()
	{
		// check if user can manage options and not is on configuration page of apw
        if (is_admin() || current_user_can('manage_options')) {
			// add an options page menu entry
	        add_options_page(
	            $this->_configuration->getPluginName().'-Configuration',
	            $this->_configuration->getPluginName(),
	            9,
				__FILE__,
	            array($this, 'displayConfigurationPage')
	        );
        }
	}

    /**
     * creates a menu entry under "settings" in wordpress administration pages
     *
     * This method is intend to create a menu entry under "settings" for user
     * which can manage options (administrative rights)
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function displayConfigurationPage()
	{
        // trigger important wordpress form functions
        settings_fields($this->_configuration->getIdentifier());
        do_settings_fields($this->getConfigurationPage(), $this->_configuration->getIdentifier());

        // first get active options
        $this->_getActiveOptionValues();

        // check for updated options (submission)
        if ($this->isUpdateRequest()) {
			if ($this->_saveUpdatedConfiguration()) {

				// check for completed config
				$this->checkNoticeConfigIncomplete(true);

				// add notice
                $this->_notice[] = 'config-saved';
			} else {
				// add error
				$this->_error[] = 'config-saved';
			}
        } elseif ($this->isClearCacheRequest()) {
            // clear the cache
			if ($this->_clearCache()) {
				// add notice
				$this->_notice[] = 'cache-cleared';
			} else {
				// add error
        		$this->_error[] = 'cache-cleared';
			}
        }

		// get HTML of configuration page
		$html = $this->_template->get(
			'plugin',
			'config',
			array(
				'name'        => $this->_configuration->getPluginName(),
				'notice'      => $this->getNotice(),
				'error'       => $this->getError(),
				'help'        => $this->_configuration->getMessage('plugin-config'),
				'form-action' => $_SERVER['PHP_SELF'] . '?page=' . $_GET['page'],
				'form-fields' => $this->_getFormFields(),
				'form-submit' => '<input type="submit" name="Submit" value="' . __('Save Changes') . '" />'
			)
		);

		// print the HTML-code
		echo $html;
	}

    /**
     * returns the HTML-Code for the form-fields
     *
     * This method is intend to return the HTML-Code for the form-fields.
     *
     * @return  string HTML-Code of the form-fields
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getFormFields()
    {
    	// assume empty form
    	$html = '';

        // get the form-fields from configuration
    	$formFields = $this->_configuration->getFieldsByType('plugin');

    	// iterate over fields
        foreach ($formFields as $formField => $config) {
			$html .= $this->_template->get(
				'form',
				'Formfield'.ucfirst($config['type']),
				array(
					'field-name'  => $this->_configuration->getPrefix() . $formField,
					'field-id'    => $this->_configuration->getPrefix() . $formField,
					'field-value' => ($config['type'] == 'select') ? $this->_getOptions($config) : $config['value'],
					'field-label' => $config['label'],
					'field-help'  => $config['help']
				)
			);
        }

        // return filled html for form-fields
        return $html;
    }

    /**
     * creates the HTML-Code for the options from a select field
     *
     * This method is intend to create the HTML-Code for the options from a select field.
     *
     * @param array $config The config for the options field
     *
     * @return  string HTML-Code of the options
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getOptions($config)
    {
    	// assume empty options
    	$html = '';

    	if (isset($config['datasource'])) {
			$options = call_user_func(array($this->_configuration, $config['datasource']));
    	} else {
    		$options = $config['value'];
    	}

		foreach ($options as $option) {
			$html .= $this->_template->get(
				'form',
				'FormfieldSelectOption',
				array(
					'field-value'    => $option['marketplace'],
					'field-selected' => ($option['marketplace'] == $config['value']) ? ' selected="selected"' : ''
				)
			);
		}

		// return filled options
		return $html;
    }

    /**
     * returns the notice-HTML for all notices
     *
     * This method is intend to return the notice-HTML for all notices.
     *
     * @return  string HTML-Code for all notices
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getNotice()
	{
		$html = '';

		foreach ($this->_notice as $notice) {
			$html .= $this->_configuration->getNotice($notice);
		}

		return $html;
	}

    /**
     * returns the error-HTML for all notices
     *
     * This method is intend to return the error-HTML for all errors.
     *
     * @return  string HTML-Code for all errors
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getError()
	{
		$html = '';

		foreach ($this->_error as $error) {
			if (!is_array($error)) {
			    $html .= $this->_configuration->getError($error);
			} else {
			    $html .= $this->_configuration->getError($error[0], $error[1]);
			}
		}

		return $html;
	}

    /**
     * validates the submitted option/value-pairs
     *
     * This method is intend to validate the submitted/updates options
     *
     * @return  boolean TRUE if options valid, otherwise FALSE
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _validateUpdatedConfiguration()
    {
		return ($this->_validateNotEmpty() && $this->_validateAwsCredentials());
    }

    /**
     * validates the submitted option/value-pairs
     *
     * This method is intend to validate the submitted/updates options - credentials valid
     *
     * @return  boolean TRUE if options valid, otherwise FALSE
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _validateAwsCredentials()
    {
		// we retrieve the values for validation directly from field via getField() instead
		// of using getOption! cause we want to retrieve the active values for the current
		// request and not the stored values ...
        $region     = $this->_configuration->getValueByFieldnameAndType('aws_region');
        $publicKey  = $this->_configuration->getValueByFieldnameAndType('aws_public_key');
        $privateKey = $this->_configuration->getValueByFieldnameAndType('aws_private_key');

        // only validate if credentials given -> e.g. on removing them from config we
        // do not check the credentials cause this would end up in file_open exception
        if ($publicKey != ''
        	&& $privateKey != ''
        	&& ($this->_configuration->getOption('aws_last_valid_credentials') != md5($publicKey . $privateKey))
		) {
        	// update data in our request class
            $this->_request->setRegion($region);
            $this->_request->setPublicKey($publicKey);
            $this->_request->setPrivateKey($privateKey);

            // try to fetch the result from Amazon
            try {
                $result = $this->_request->send(
                	array(
                    	'Operation'     => 'ItemLookup',
                    	'ItemId'        => '0439136369',
                    	'ResponseGroup' => 'Small'
                	)
				);

                // store error for invalid credentials
                if (!$result->Items->Request->IsValid == 'True') {
                    $this->_error[] = 'aws-credentials';
                } else {
                    // store last valid result so we don't need to revalidate against the AWS API
                    $this->_configuration->setOption('aws_last_valid_credentials', md5($publicKey . $privateKey));

                    // store notice success
                    $this->_notice[] = 'aws-credentials';
                }

            } catch (Exception $e) {
                $this->_error[] = $e->getMessage();
            }
        }

        // return result of validation
        return empty($this->_error);
    }

    /**
     * validates the submitted option/value-pairs
     *
     * This method is intend to validate the submitted/updates options - empty
     *
     * @return  boolean TRUE if options valid, otherwise FALSE
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _validateNotEmpty()
    {
    	// get the form-fields from configuration
    	$formFields = $this->_configuration->getFieldsByType('plugin');

    	// iterate over fields
        foreach ($formFields as $formField => $config) {
        	// generate complete name
        	$formFieldName = $this->_configuration->getPrefix() . $formField;

            if (isset($_POST[$formFieldName]) && !$config['allowempty'] && $_POST[$formFieldName] == '') {
                $this->_error[] = array('field-empty', array('field-name' => $formField));
            }
        }

        // return result of validation
        return empty($this->_error);
    }

    /**
     * saves the updated options
     *
     * This method is intend to save the updated options to wordpress-db.
     *
     * @return  boolean TRUE on success, otherwise FALSE
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _saveUpdatedConfiguration()
    {
    	// valid update?
    	if ($this->_validateUpdatedConfiguration()) {
	    	// get the form-fields from configuration
    		$formFields = $this->_configuration->getFieldsByType('plugin');

	        // store configuration
	        foreach ($formFields as $formField => $config) {
	            $this->_configuration->setOption($formField, $config['value']);
	        }

	        // set the links to product and to tracking image
			$this->_setMarketPlaceLinks(strtolower($formFields['aws_region']['value']));

			// return success
    		return true;
    	}

    	// return failure
    	return false;
    }

    /**
     * retrieves and stores the product-link and tracking-link template
     *
     * This method is intend to retrieve and store the product-link and tracking-link template
     * from marketplace-data (configuration).
     *
     * @param string $region The region for the AWS
     *
     * @return  boolean TRUE on success, otherwise FALSE
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _setMarketPlaceLinks($region)
    {
		// get data for market-place
        $awsMarketPlaceData = $this->_configuration->getRegions();

        // get product url (the link to the product on the amazon website)
        $productUrl = $awsMarketPlaceData[$region]['url']['product'];

        // construct tracking url -> without this tracking-pixel -> you won't get paid!
        $trackingUrl = str_replace(
        	'[[TLD]]',
			$awsMarketPlaceData[$region]['tld'],
			$awsMarketPlaceData[$region]['url']['tracking']
		);

		// store settings in database
		$this->_configuration->setOption('aws_product_url', $productUrl);
		$this->_configuration->setOption('aws_image_tracking_url', $trackingUrl);

		// success
		return true;
    }

    /**
     * sets the current active value for form fields
     *
     * This method is intend to set the current active value for form fields.
     * The order for retrieving the value is: Form-Submit, Database, Default-Value.
     *
     * @return  void
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getActiveOptionValues()
    {
    	// get the form-fields from configuration
    	$formFields = $this->_configuration->getFieldsByType('plugin');

    	// iterate over form-fields
        foreach ($formFields as $formField => $config) {
        	// generate complete name
        	$formFieldName = $this->_configuration->getPrefix() . $formField;

            // use submitted as 1st source
            if ($this->isUpdateRequest()) {
                $formFields[$formField]['value'] = (isset($_POST[$formFieldName])) ? $_POST[$formFieldName] : '';

            } else {
                // otherwise try stored settings
                $formFields[$formField]['value'] = ($this->_configuration->getOption($formField) != '') ?
                	$this->_configuration->getOption($formField) :
                	'';
            }
        }

		// update the form-field values through updating the data in configuration
		$this->_configuration->setFieldsByType('plugin', $formFields);
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
     * sets the dependency for request
	 *
     * This method is intend to set the dependency for request.
     *
     * @param object $request An instance of the request class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject request
     */
    public function setRequest($request)
    {
        // store instance of request
		$this->_request = $request;
    }
}

?>

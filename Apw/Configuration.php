<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Configuration Class
 *
 * Configuration.php
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
 * @subpackage Wordpress_Apw_Configuration
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Configuration Class
 *
 * This class is responsible for managing the settings/options of the Amazon-Product-Widget.
 *
 * @category   Wordpress
 * @package    Wordpress_Apw
 * @subpackage Wordpress_Apw_Configuration
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Configuration
{
    /**
     * contains an instance of the wordpress oop interface
     *
     * @var object An instance of the wordpress oop interface
     * @access private
     */
	private $_wordpress;

    /**
     * contains the prefix of the plugin
     *
     * @var string The prefix of this plugin
     * @access private
     */
	private $_identifier = 'apw';

    /**
     * contains the name of the plugin
     *
     * @var string The name of this plugin
     * @access public
     */
	private $_pluginName = 'Amazon Product Widget';

    /**
     * contains the name of the plugin
     *
     * @var string The name of this plugin
     * @access public
     */
	private $_pluginClassName = 'Amazon_Product_Widget';

    /**
     * holds all messages like error-, notice- or help-messages
     *
     * @var array The messages
     * @access private
     */
    private $_messages = array(
        'error' => array(
            'field-empty'              => 'The Field [[FIELD-NAME]] was left empty. Please complete this field to enable the widget!',
            'aws-credentials'          => 'Your Amazon Web Services (AWS) credentials seem to be invalid. Please correct your public- and private-key to enable the widget!',
    		'aws-incomplete'           => 'Either your AWS private- or public-key is missing!',
            'config-saved'             => 'Configuration could not be saved!',
    		'cache-cleared'            => 'The cache could not be cleared. Maybe it is already empty.',
            'file_get_contents_failed' => 'file_get_contents() failed. Please ensure that PHP\'s native function file_get_contents() is allowed to read URL\'s'
        ),
        'notice' => array(
			'config-incomplete'        => '<b>[[PLUGIN-NAME]]</b> is almost ready to use! Please complete the <a href="[[CONFIG-URL]]">configuration</a> to enable it\'s widgets!',
			'complete-keys'            => '<b>[[PLUGIN-NAME]]</b> is just one step away from working properly! It just needs your AWS Private- and Public-Key to retrieve data of ASIN\'s from Amazon-Web-Service API. Please configure your <a href="http://s3.amazonaws.com/mturk/tools/pages/aws-access-identifiers/aws-identifier.html" target="_blank">Amazon AWS-Keys</a>.',
			'config-saved'		       => '<b>[[PLUGIN-NAME]]</b> Configuration successfully saved!',
        	'cache-cleared'	           => '<b>[[PLUGIN-NAME]]</b> Cache successfully cleared!',
        	'aws-credentials'          => '<b>[[PLUGIN-NAME]]</b> Your AWS-credentials could be successfully validated!',
			'requirements-failure'     => '<b>[[PLUGIN-NAME]]</b> Requirements not fulfilled! Enable "<a href=\'http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen\' target=\'_blank\'>allow_url_fopen</a>" in php.ini or plugin can\'t operate.'
        ),
    	'help' => array(
    		'plugin-config'            => 'After providing your AWS private- and public-key in this configuration the [[PLUGIN-NAME]] is able to receive further product information like description, title and many more by a given ASIN automatically.'
		)
    );

    /**
     * holds all fields used by this plugin. no matter if used
     * for plugin- or widget-configuration or database (internal) only.
     *
     * @var array The fields used by this plugin
     * @access private
     */
	private $_fields = array(
		'widget' => array(
			'title' => array(
				'label'     => 'Title',
				'type'      => 'text',
				'cleanup'   => false,
				'help'		=> 'The text get displayed as Headline (e.g. &lt;h2&gt;) right before the widget'
			),
			'list' => array(
				'label'     => 'ASIN(s)',
				'type'      => 'textarea',
				'separator' => "\n",
				'cleanup'   => false,
				'help'		=> 'The ASIN(s) to display'
			),
			'template' => array(
				'label'     => 'Template',
				'type'      => 'textarea',
				'html'      => true,
				'cleanup'   => false,
				'help'		=> 'The HTML-Code used as template for displaying products'
			)
		),
		'plugin' => array(
	        'aws_region' => array(
	            'label'      => 'Region',
				'type'       => 'select',
	            'datasource' => 'getRegions',
	            'value'      => '',
	            'allowempty' => false,
	            'display'    => true,
	            'default'	 => 'DE',
				'cleanup'    => true,
	            'help'       => 'The region (ca, com, co.uk, de, fr, jp) to use for the Amazon AWS queries.',
	        ),
	        'aws_associate_id' => array(
	            'label'      => 'Associate-ID',
	            'type'       => 'text',
	            'value'      => '',
	            'allowempty' => false,
	            'display'    => true,
	            'default'	 => null,
	        	'cleanup'    => true,
	            'help'       => 'Your Amazon Associate-ID. You can use "phpfluesterer-21" if you don\'t have one. Or <a href="https://affiliate-program.amazon.com/gp/flex/associates/apply-login.html" target="_blank">signup here</a> to retrieve your own.'
	        ),
	        'aws_public_key' => array(
	            'label'      => 'AWS Access Key ID',
	            'type'       => 'text',
	            'value'      => '',
	            'allowempty' => true,
	            'display'    => true,
	            'default'    => null,
	        	'cleanup'    => true,
	            'help'       => 'Your Amazon Web Services (AWS) "Access Key ID" (public key). Signup <a href="https://aws-portal.amazon.com/gp/aws/developer/registration/index.html" target="_blank">here</a> to retrieve a key pair.'
	        ),
	        'aws_private_key' => array(
	            'label'      => 'AWS Secret Access Key',
	            'type'       => 'text',
	            'value'      => '',
	            'allowempty' => true,
	            'display'    => true,
	            'default'    => null,
	        	'cleanup'    => true,
	            'help'       => 'Your Amazon Web Services (AWS) "Secret Access Key" (private-key). Signup <a href="https://aws-portal.amazon.com/gp/aws/developer/registration/index.html" target="_blank">here</a> to retrieve a key pair.'
	        )
		),
		'databaseonly' => array(
			'cache' => array(
				'cleanup'   => true
			),
			'aws_image_tracking_url' => array(
				'cleanup'   => true
			),
			'aws_product_url' => array(
				'cleanup'   => true
			),
			'aws_region' => array(
				'cleanup'   => true
			),
			'aws_last_valid_credentials' => array(
				'cleanup'   => true
			)
		)
	);

	/**
	 * contains the default values for widget and plugin
	 *
	 * @var array
	 * @access public
	 */
	private $_defaults = array(
		'aws_region'   => 'DE',
		'plugin-name'  => 'Amazon Product Widget',
		'asin'         => '0132350882',
		'associate_id' => 'phpfluesterer-21',
		'template'     => '<div class="apw_covershot" id="apw_covershot_[[ASIN]]">
		                  <a href="[[PRODUCT-URL]]" title="Picture of [[DESCRIPTION]]" rel="external" target="_blank">
                          <img src="[[IMAGE-TINY-URL]]" width="[[IMAGE-TINY-WIDTH]]" height="[[IMAGE-TINY-HEIGHT]]"
                          border="0" alt="Picture of [[DESCRIPTION]]" /></a><img src="[[IMAGE-TRACKING-URL]]"
                          border="0" width="1" height="1" /></div><div class="apw_description"><a
                          href="[[PRODUCT-URL]]" rel="external" target="_blank" id="apw_description_[[ASIN]]">
                          <b>[[AUTHOR]]</b> [[DESCRIPTION]]</a></div>'
	);

    /**
     * returns the regions for the amazon affiliate program
     *
     * This method is intend to return the regions for the amazon affiliate program
     *
     * @return  array The regions of the amazon affiliate program
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    public function getRegions()
    {
        return array(
            'ca' => array(
				'lang_iso_code' => 'en_CA',
				'marketplace' =>   'CA',
				'name' =>          __('Amazon Canada', 'awshortcode'),
				'suffix' =>        '-20',
				'tld' =>           'ca',
				'url' => array(
					'affiliate' =>           'https://associates.amazon.ca/',
					'product' =>             'http://www.amazon.ca/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.ca/'
                ),
            ),
            'cn' => array(
				'lang_iso_code' => 'zh_CN',
				'marketplace' =>   'CN',
				'name' =>          __('Amazon China', 'awshortcode'),
				'suffix' =>        '-23',
				'tld' =>           'cn',
				'url' => array(
					'affiliate' =>           'https://associates.amazon.cn/',
					'product' =>             'http://www.amazon.cn/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.cn/'
                ),
            ),			
			'de' => array(
				'lang_iso_code' => 'de_DE',
				'marketplace' =>   'DE',
				'name' =>          __('Amazon Germany', 'awshortcode'),
				'suffix' =>        '-21',
				'tld' =>           'de',
				'url' => array(
					'affiliate' =>           'https://partnernet.amazon.de/',
					'product' =>             'http://www.amazon.de/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.de/'
				),
			),
			'es' => array(
				'lang_iso_code' => 'es_ES',
				'marketplace' =>   'ES',
				'name' =>          __('Amazon Spain', 'awshortcode'),
				'suffix' =>        '-21',
				'tld' =>           'es',
				'url' => array(
					'affiliate' =>           'https://afiliados.amazon.es/',
					'product' =>             'http://www.amazon.es/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.es/'
				),
			),			
			'fr' => array(
				'lang_iso_code' => 'fr_FR',
				'marketplace' =>   'FR',
				'name' =>          __('Amazon France', 'awshortcode'),
				'suffix' =>        '-21',
				'tld' =>           'fr',
				'url' => array(
					'affiliate' =>           'https://partenaires.amazon.fr/',
					'product' =>             'http://www.amazon.fr/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.fr/'
				),
			),
			'it' => array(
				'lang_iso_code' => 'it_IT',
				'marketplace' =>   'IT',
				'name' =>          __('Amazon Italia', 'awshortcode'),
				'suffix' =>        '-21',
				'tld' =>           'it',
				'url' => array(
					'affiliate' =>           'https://programma-affiliazione.amazon.it/',
					'product' =>             'http://www.amazon.it/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.it/'
				),
			),
			'jp' => array(
				'lang_iso_code' => 'ja_JP',
				'marketplace' =>   'JP',
				'name' =>          __('Amazon Japan', 'awshortcode'),
				'suffix' =>        '-22',
				'tld' =>           'co.jp',
				'url' => array(
					'affiliate' =>           'https://affiliate.amazon.co.jp/',
					'product' =>             'http://www.amazon.co.jp/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.co.jp/'
				),
			),
			'uk' => array(
				'lang_iso_code' => 'en_UK',
				'marketplace' =>   'UK',
				'name' =>          __('Amazon United Kingdom', 'awshortcode'),
				'suffix' =>        '-21',
				'tld' =>           'co.uk',
				'url' => array(
					'affiliate' =>            'https://affiliate-program.amazon.co.uk/',
					'product' =>              'http://www.amazon.co.uk/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>             'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                 'http://www.amazon.co.uk/'
				),
			),
			'us' => array(
				'lang_iso_code' => 'en_US',
				'marketplace' =>   'US',
				'name' =>          __('Amazon USA', 'awshortcode'),
				'suffix' =>        '-20',
				'tld' =>           'com',
				'url' => array(
					'affiliate' =>           'https://affiliate-program.amazon.com/',
					'product' =>             'http://www.amazon.com/dp/[[ASIN]]/?tag=[[ASSOCIATE-ID]]',
					'tracking' =>            'http://www.assoc-amazon.[[TLD]]/e/ir?t=[[ASSOCIATE-ID]]&l=as2&o=3&a=[[ASIN]]',
					'site' =>                'http://www.amazon.com/'
				),
			),
		);
    }

    /**
     * returns the name of this plugin
	 *
     * This method is intend to return the name of this plugin.
     *
     * @return  string The name of this plugin
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getPluginName()
	{
		return $this->_pluginName;
	}

    /**
     * returns the name of this plugin
	 *
     * This method is intend to return the name of this plugin.
     *
     * @return  string The name of this plugin
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getPluginClassName()
	{
		return $this->_pluginClassName;
	}

    /**
     * returns a list of fields used by this plugin
     *
     * This method is intend to return the complete list of fields used by this plugin.
     * The returned array contains all types of entries like "widget"-fields, "plugin"-fields
     * and "databaseonly"-fields.
     *
     * @return  array A list of all fields
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getFields()
	{
		return $this->_fields;
	}

    /**
     * sets the list of fields used by this plugin
     *
     * This method is intend to set the complete list of fields used by this plugin.
     *
     * @param array $fields The fields to set
     *
     * @return  boolean TRUE on success, otherwise FALSE
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function setFields(array $fields)
	{
		return ($this->_fields = $fields);
	}

    /**
     * returns a single field by its name and given type
     *
     * This method is intend to return a single field by its name and a given type.
     *
     * @param string $name The name of the field
     * @param string $type The type of the field
     *
     * @return  array A single field
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getField($name, $type = 'plugin')
	{
		return $this->_fields[$type][$name];
	}

    /**
     * returns a single fields value by its name and given type
     *
     * This method is intend to return a single fields value by its name and a given type.
     *
     * @param string $name The name of the field
     * @param string $type The type of the field
     *
     * @return  array A single fields value
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getValueByFieldnameAndType($name, $type = 'plugin')
	{
		return $this->_fields[$type][$name]['value'];
	}

    /**
     * returns the fields used by this plugin by type
     *
     * This method is intend to return the fields used by this plugin by type.
     *
     * @param string $type The type of fields to return
     *
     * @return  array A list of all fields with given type
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getFieldsByType($type)
	{
		return $this->_fields[strtolower($type)];
	}

    /**
     * sets the fields used by this plugin by type
     *
     * This method is intend to set the fields used by this plugin by type.
     *
     * @param string $type The type of fields to return
     * @param array  $data The data to set for the given type
     *
     * @return  boolean TRUE on success, otherwise FALSE
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function setFieldsByType($type, array $data)
	{
		return ($this->_fields[strtolower($type)] = $data);
	}

    /**
     * returns the identifier
     *
     * This method is intend to return the identifier.
     *
     * @return  string The identifier of this plugin
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getIdentifier()
	{
		return $this->_identifier;
	}

    /**
     * sets the identifier
     *
     * This method is intend to set the identifier.
     *
     * @param string $identifier The identifier to set
     *
     * @return  string The identifier of this plugin
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function setIdentifier($identifier)
	{
		return ($this->_identifier = $identifier);
	}

    /**
     * returns the prefix of this plugin
     *
     * This method is intend to return the prefix of this plugin. The prefix
     * is the "identifier" of the plugin followed by an underscore (e.g. Foo_).
     *
     * @return  string The prefix of this plugin
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getPrefix()
	{
		return $this->_identifier . '_';
	}

    /**
     * returns the completion status of the plugin configuration
     *
     * This method is intend to return the completion status of the configuration of
     * this plugin.
     *
     * @return  boolean TRUE if configuration is completely done, otherwise FALSE
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function isComplete()
	{
		return (
			$this->getOption('aws_public_key') != '' &&
			$this->getOption('aws_private_key') != '' &&
			$this->getOption('aws_region') != ''
		);
	}

    /**
     * returns the configuration URL of this plugin
     *
     * This method is intend to return the configuration URL of this plugin
     *
     * @param string $file The file to return the config URL for
     *
     * @return  string The configuration URL
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getConfigUrl($file)
	{
		return $this->_wordpress->admin->url() . 'options-general.php?page='.$this->_wordpress->plugin->basename($file);
	}

    /**
     * returns the name of this file
     *
     * This method is intend to return the name of this file.
     *
     * @return  string The name of this file
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getFile()
	{
		return __FILE__;
	}

    /**
     * returns the default value of a given option
     *
     * This method is intend to return the default value of a given option.
     *
     * @param string $option The option to retrieve default value for
     *
     * @return  mixed The value of the option if exist, otherwise NULL
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getDefault($option)
	{
		$optionValue = null;

		if (isset($this->_defaults[$option])) {
			$optionValue = $this->_defaults[$option];
		}

		return $optionValue;
	}

    /**
     * returns the complete list of default values
     *
     * This method is intend to return the complete list of default values as array.
     *
     * @return  array The default values
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getDefaults()
	{
		return $this->_defaults;
	}

    /**
     * returns a notice including replaced vars
     *
     * This method is intend to return a full prepared notice message with
     * all placeholders replaced.
     *
     * @param string $notice       The identifier for the notice to return
     * @param array  $replacements The default value overrides
     *
     * @return  string The notice-message
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getNotice($notice, array $replacements = array())
	{
		// merge given values with defaults (default = fallback if not overridden)
		$replacements = array_merge($this->_defaults, $replacements);

		// get notice by given identifier
		$notice = $this->_messages['notice'][$notice];

		// replace placeholder from string
		foreach ($replacements as $replacement => $value) {
			$notice = str_replace('[[' . strtoupper($replacement) . ']]', $value, $notice);
		}

		// return prepared notice
		return '<div class="updated"><p>' . $notice . '</p></div>';
	}

    /**
     * returns a error including replaced vars
     *
     * This method is intend to return a full prepared error message with
     * all placeholders replaced.
     *
     * @param string $error        The identifier for the error to return
     * @param array  $replacements The default value overrides
     *
     * @return  string The error-message
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getError($error, array $replacements = array())
	{
		// merge given values with defaults (default = fallback if not overridden)
		$replacements = array_merge($this->_defaults, $replacements);

		// get notice by given identifier
		$error = $this->_messages['error'][$error];

		// replace placeholder from string
		foreach ($replacements as $replacement => $value) {
			$error = str_replace('[[' . strtoupper($replacement) . ']]', $value, $error);
		}

		// return prepared error
		return '<div class="error"><p>' . $error . '</p></div>';
	}

    /**
     * returns a message (can be either of type "error", "notice" or "help" or ...)
     *
     * This method is intend to return a message of type "error", "notice" or "help" or ...
     *
     * @param string $message      The identifier for the message to return
     * @param string $type         The type of the message
     * @param array  $replacements The default value overrides
     *
     * @return  string The message
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getMessage($message, $type = 'help', array $replacements = array())
	{
		// merge given values with defaults (default = fallback if not overridden)
		$replacements = array_merge($this->_defaults, $replacements);

		// get notice by given identifier
		$message = $this->_messages[$type][$message];

		// replace placeholder from string
		foreach ($replacements as $replacement => $value) {
			$message = str_replace('[[' . strtoupper($replacement) . ']]', $value, $message);
		}

		// return prepared message
		return $message;
	}

    /**
     * returns a value from store or it's default value
	 *
     * This method is intend to return an option-value from store or it's default value
     * if database entry missing or empty!
     *
     * @param string  $option               The option to return value of
     * @param boolean $usePrefix            True to use prefix
     * @param boolean $returnDefaultIfEmpty True to return the default value if empty
     *
     * @return  mixed The value of a given option
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getOption($option, $usePrefix = true, $returnDefaultIfEmpty = true)
	{
		// create full option name
		$optionName = ($usePrefix) ? $this->getPrefix() . $option : $option;

		// try to get value from database
		$optionValue = $this->_wordpress->option->get($optionName);

		// check value and if we should return default value (if set)
		if ((!$optionValue || $optionValue == '') && $returnDefaultIfEmpty && isset($this->_defaults[$option])) {
			$optionValue = $this->getDefault($option);
		}

		// return result
		return $optionValue;
	}

    /**
     * sets a value for a given option
	 *
     * This method is intend to set a value for a given option.
     *
     * @param string  $option    The name of the option to set value for
     * @param mixed   $value     The value to set
     * @param boolean $usePrefix True to use prefix for option-name
     *
     * @return  boolean TRUE on success, otherwise FALSE
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function setOption($option, $value, $usePrefix = true)
	{
		if ($value == '') {
			return $this->resetOption($option, $value, $usePrefix);

		} else {
			// create full option name
			$optionName = ($usePrefix) ? $this->getPrefix() . $option : $option;

			// return result
			return $this->_wordpress->option->update($optionName, $value);
		}
	}

    /**
     * resets a value for a given option
	 *
     * This method is intend to reset a value for a given option.
     * For this this method removes (delete) the option from store and
     * rewrite an empty value.
     *
     * @param string  $option    The option to reset
     * @param boolean $usePrefix True to use prefix for option-name
     *
     * @return  boolean TRUE on success, otherwise FALSE
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function resetOption($option, $usePrefix = true)
	{
		// create full option name
		$optionName = ($usePrefix) ? $this->getPrefix() . $option : $option;

		//return (delete_option($optionName) && update_option($optionName, ''));
		return ($this->_wordpress->option->delete($optionName) && $this->_wordpress->option->update($optionName, ''));
	}

    /**
     * deletes a value for a given option
	 *
     * This method is intend to delete a value for a given option.
     *
     * @param string  $option    The name of the option to delete
     * @param boolean $usePrefix True to use prefix for option-name
     *
     * @return  boolean TRUE on success, otherwise FALSE
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function deleteOption($option, $usePrefix = true)
	{
		// create full option name
		$optionName = ($usePrefix) ? $this->getPrefix() . $option : $option;

		//return delete_option($optionName);
		return $this->_wordpress->option->delete($optionName);
	}

	/*******************************************************************************************************************
	 * Dependency-Injection (DI)
	 ******************************************************************************************************************/

    /**
     * sets the dependency for wordpress
	 *
     * This method is intend to set the dependency for wordpress.+
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
}

?>

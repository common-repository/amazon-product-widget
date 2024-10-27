<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Template Class
 *
 * Template.php
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
 * @subpackage Wordpress_Apw_Template
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Template Class
 *
 * This class is responsible for managing the data in the Templateend (wordpress admin).
 * It generates and displays the configuration/options form and the widget
 *
 * @category   Wordpress
 * @package    Wordpress_Apw
 * @subpackage Wordpress_Apw_Template
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Template
{
    /**
     * contains an instance of the apw config (defaults ...)
     *
     * @var object An instance of the apw config (defaults ...)
     * @access private
     */
	private $_configuration;

    /**
     * contains the extension of the template-files
     *
     * @var string The extension of the template-files
     * @access private
     */
	private $_tplExtension = '.tpl';

    /**
     * contains the directory which contains the template-files
     *
     * @var string The directory which contains the template-files
     * @access private
     */
	private $_templateDir = 'Template';

    /**
     * contains the types of templates
     *
     * @var array The type of templates
     * @access private
     */
	private static $_templates = array(
		'widget',
		'plugin',
		'form'
	);


    /**
     * returns the content (HTML) for a requested template
     *
     * This method is intend to return the content (HTML) for a requested template.
     *
     * @param string $type         The type of the template
     * @param string $template     The template
     * @param array  $replacements The replacement overrides
     *
     * @return string The content of the template
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function get($type, $template, array $replacements = array())
	{
		// check if this template was already loaded before or not
		if (!isset(self::$_templates[$type][$template])) {
			$this->_load($type, $template);
		}

		// parse, process and return template
		return $this->parse(self::$_templates[$type][$template], $replacements);
	}

    /**
     * loads and stores the content of a template
     *
     * This method is intend to load and store the content of a template.
     *
     * @param string $type     The type of the template
     * @param string $template The template
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	private function _load($type, $template)
	{
		$templateDir = str_replace(ucfirst($this->_configuration->getIdentifier()).'/', '', plugin_dir_path(__FILE__)).
					   $this->_templateDir . DIRECTORY_SEPARATOR;

		$templateFile = $templateDir . ucfirst($type) . DIRECTORY_SEPARATOR . ucfirst($template) . $this->_tplExtension;

		// finally store loaded data
		self::$_templates[$type][$template] = file_get_contents($templateFile);
	}

    /**
     * parses a template and inserts the values for placeholders
     *
     * This method is intend to parse a template and insert the values for the placeholders.
     *
     * @param string $tpl          The template content
     * @param array  $replacements The replacements to insert
     *
     * @return string The parsed template
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
	public function parse($tpl, $replacements)
	{
		// process the template variables ($replacements)
		$replacements = array_merge($this->_configuration->getDefaults(), $replacements);

		foreach ($replacements as $replacement => $value) {
			$tpl = str_replace('[[' . strtoupper($replacement) . ']]', $value, $tpl);
		}

		// return parsed template
		return $tpl;
	}

	/*******************************************************************************************************************
	 * Dependency-Injection (DI)
	 ******************************************************************************************************************/

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
        // store instance of config
		$this->_configuration = $configuration;
    }
}

?>

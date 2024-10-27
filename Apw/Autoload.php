<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Autoload Class
 *
 * Autoload.php
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
 * @subpackage Wordpress_APW_Autoload
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Amazon Product Widget Autoload - Autoloader Class
 *
 * PSR-0 autoloader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 * http://groups.google.com/group/php-standards/web/final-proposal
 * http://groups.google.com/group/php-standards/web/psr-0-final-proposal?pli=1
 *
 *  // Example which loads classes for the Doctrine Common package in the Doctrine\Common namespace.
 *  $classLoader = new Apw_Autoload('Doctrine\Common', '/path/to/doctrine');
 *  $classLoader->register();
 *
 *  // Example which loads classes for the PHP-Dependency framework.
 *  $classLoader = new Apw_Autoload('Pd', '/path/to/PHP-Dependency');
 *  $classLoader->setNamespaceSeparator('_');
 *  $classLoader->register();
 *
 * @category   Wordpress
 * @package    Wordpress_APW_Wordpress
 * @subpackage Wordpress_Apw_Wordpress_Autoload
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Roman S. Borschel <roman@code-factory.org>
 * @author     Matthew Weier O'Phinney <matthew@zend.com>
 * @author     Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.org>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Autoload
{
	/**
	 * The file-extension for the files to be loaded by autoloader
	 *
	 * @var string The file-extension
	 * @access private
	 */
    private $_fileExtension = '.php';

	/**
	 * The namespace for the files to be loaded by autoloader
	 *
	 * @var string The namespace
	 * @access private
	 */
    private $_namespace;

	/**
	 * The include path used as base-path when loading files
	 *
	 * @var string The include path
	 * @access private
	 */
    private $_includePath;

	/**
	 * The namespace separator used when autoloading files
	 *
	 * @var string The namespace separator
	 * @access private
	 */
    private $_namespaceSeparator = '\\';

	/**
	 * The directory separator for current OS
	 *
	 * @var string The directory separator
	 * @access private
	 */
    private $_separator = DIRECTORY_SEPARATOR;


    /**
     * creates a new "Autoload"
     *
     * This method is intend to create a new instance which:
     * load classes of the specified namespace
     *
     * @param string $namespace   The namespace to use
     * @param string $includePath The include-path to use as base
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function __construct($namespace = null, $includePath = null)
    {
        $this->_namespace = $namespace;
        $this->_includePath = $includePath;
    }

    /**
     * sets the namespace separator
     *
     * This method is intend to set the namespace separator
     * used by classes in the namespace of this class loader.
     *
     * @param string $separator The separator to use
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setNamespaceSeparator($separator)
    {
        $this->_namespaceSeparator = $separator;
    }

    /**
     * returns the namespace seperator
     *
     * This method is intend to return the namespace seperator used
     * by classes in the namespace of this class loader.
     *
     * @return string The namespace separator
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function getNamespaceSeparator()
    {
        return $this->_namespaceSeparator;
    }

    /**
     * sets the base include path
     *
     * This method is intend to set the base include path for all
     * class files in the namespace of this class loader.
     *
     * @param string $includePath The base path for file includes
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setIncludePath($includePath)
    {
        $this->_includePath = $includePath;
    }

    /**
     * returns the base include path
     *
     * This method is intend to return the base include path for all
     * class files in the namespace of this class loader.
     *
     * @return string The include path
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function getIncludePath()
    {
        return $this->_includePath;
    }

    /**
     * sets the file extension
     *
     * This method is intend to set the file extension of class
     * files in the namespace of this class loader.
     *
     * @param string $fileExtension The extension for files which should be loaded by autoloader
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setFileExtension($fileExtension)
    {
        $this->_fileExtension = $fileExtension;
    }

    /**
     * returns the file extension
     *
     * This method is intend to return the file extension of
     * class files in the namespace of this class loader.
     *
     * @return string $fileExtension
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function getFileExtension()
    {
        return $this->_fileExtension;
    }

    /**
     * registers this class loader on the SPL autoload stack
     *
     * This method is intend to register this class loader on the SPL autoload stack.
     *
     * @return boolean TRUE on success, otherwise FALSE
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function register()
    {
        return spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * unregisters/removes this class loader from the SPL autoloader stack
     *
     * This method is intend to unregister/remove this class loader from the
     * SPL autoloader stack.
     *
     * @return boolean TRUE on success, otherwise FALSE
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function unregister()
    {
        return spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * loads the given class or interface
     *
     * This method is intend to load the given class or interface.
     *
     * @param string $classname The name of the class to load.
     *
     * @return void
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function loadClass($classname)
    {
    	// get namespace from classname
    	$namespace = substr($classname, 0, strlen($this->_namespace . $this->_namespaceSeparator));

    	// check if requested class must be loaded by this instance of loader
        if ($this->_namespace === null || $this->_namespace.$this->_namespaceSeparator === $namespace) {
            $fileName = '';
            $namespace = '';

            if (($lastNsPos = strripos($classname, $this->_namespaceSeparator)) !== false) {
                $namespace = substr($classname, 0, $lastNsPos);
                $classname = substr($classname, $lastNsPos + 1);
                $fileName = str_replace($this->_namespaceSeparator, $this->_separator, $namespace) . $this->_separator;
            }

            $fileName .= str_replace('_', $this->_separator, $classname) . $this->_fileExtension;

            if ($this->_includePath !== null) {
            	$fileName = $this->_includePath . $this->_separator . $fileName;
            }

            // check first if file exists and load it
            if (file_exists($fileName)) {
            	include_once $fileName;
            }
        }
    }
}
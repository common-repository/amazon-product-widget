<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP-Dependency - Container
 *
 * Container.php
 *
 * PHP versions 5
 *
 * LICENSE:
 * PHP-Dependency
 *
 * Copyright (c) 2010 - 2011, Ryan, Benjamin Carl - All rights reserved.
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
 * @category   PHP
 * @package    PHP_Pd
 * @subpackage PHP_Pd_Container
 * @author     Ryan
 * @copyright  2010 - 2011, Ryan, Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    GIT: $Id: $
 */

require_once PD_BASE_DIRECTORY . 'Container/Maps.php';
require_once PD_BASE_DIRECTORY . 'Container/Dependencies.php';

/**
 * PHP-Dependency - Container
 *
 * Singleton (eww) that holds dependencies/maps.
 *
 * @category   PHP
 * @package    PHP_Pd
 * @subpackage PHP_Pd_Container
 * @author     Ryan
 * @author     $LastChangedBy: $
 * @copyright  2010 - 2011, Ryan, Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    1.0
 * @link       http://www.potstuck.com/2010/09/09/php-dependency-a-php-dependency-injection-framework/
 */
class Pd_Container {

    private static $_instance = array();

    /**
     * @var Pd_Container_Maps
     */
    private $_maps;

    /**
     * @var Pd_Container_Dependencies
     */
    private $_dependencies;

    private $_name;


    /**
     * Returns one instance singleton
     *
     * @return Pd_Container
     */
    public static function get($container = 'main') {

        if (!isset(self::$_instance[$container])) {
            self::$_instance[$container] = new self();
            self::$_instance[$container]->setName($container);
            self::$_instance[$container]->setup();
        }

        return self::$_instance[$container];

    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function name() {
        return $this->_name;
    }

    /**
     * @return Pd_Container_Maps
     */
    public function maps() {
        return $this->_maps;
    }

    /**
     * @return Pd_Container_Dependencies
     */
    public function dependencies() {
        return $this->_dependencies;
    }

    /**
     * Sets up the container by creating a new map
     * and dependency holder.  This function doesn't really
     * need to ever be called, since the get() function
     * calls it when creating a 'new' container.
     */
    public function setup() {
        $this->_maps = new Pd_Container_Maps();
        $this->_dependencies = new Pd_Container_Dependencies();
    }


    private function __construct()
    {
		// prevent direct instanciation
    }

    private function __clone()
    {
		// prevent cloning
    }
}

?>

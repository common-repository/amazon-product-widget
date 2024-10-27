<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Wordpress generic OOP Interface Class
 *
 * Interface.php
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
 * @package    Wordpress_APW_Wordpress
 * @subpackage Wordpress_Apw_Wordpress_Interface
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Amazon Product Widget Interface - Interface Class
 *
 * This class is intend to provide an OOP Interface to the Wordpress Functionality.
 *
 * @category   Wordpress
 * @package    Wordpress_APW_Wordpress
 * @subpackage Wordpress_Apw_Wordpress_Interface
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Wordpress_Interface
{
	/**
	 * contains a matrix for mapping oop-style method calls to
	 * wordpress stuctural function calls.
	 *
     * @var array The matrix
     * @access private
     */
	private $_methodMatrix = array(
		'admin' => array(
			'url'            => 'admin_url',
			'notice'         => 'CALL CLASS FOR NOTICE FORMATTING'
		),
		'filters' => array(
			'apply'          => 'apply_filters'
		),
		'generic' => array(
			'parseArguments' => 'wp_parse_args'
		),
		'option' => array(
			'get'            => 'get_option',
			'set'            => 'set_option',
			'delete'         => 'delete_option',
			'update'         => 'update_option'
		),
		'plugin' => array(
			'basename'       => 'plugin_basename',
			'url' => array(
				'plugins'    => 'plugins_url',
				'menu'       => 'menu_page_url'
			),
			'setting' => array(
				'register'   => 'register_setting',
				'unregister' => 'unregister_setting',
				'fields'     => 'settings_fields'
			),
			'hook' => array(
				'register' => array(
					'activation'   => 'register_activation_hook',
					'deactivation' => 'register_deactivation_hook'
				)
			)
		),
		'user' => array(
			'create'         => 'wp_create_user',
			'insert'         => 'wp_insert_user',
			'update'         => 'wp_update_user',
			'delete'         => 'wp_delete_user',
			'isLoggedin'     => 'is_user_logged_in',
			'login'          => 'wp_signon',
			'logout'         => 'wp_logout'
		),
		'widget' => array(
			'register'       => 'register_widget'
		)
	);

	/**
	 * holds the chain-structure (used for method chaining = virtual object structure)
	 *
	 * @var array The chain(s) of the corresponding object-structure
	 * @access private
	 */
	private $_chains = array();


    /**
     * generic caller for wordpress methods
     *
     * This magic-method is used as a generic wrapper/interface for the wordpress non-oop
     * functions. It retrieves a method name and the arguments for the method-call
     * and wrap it throught the method call translation matrix ($_methodMatrix) to
     * the corresponding wordpress function. The method makes use of a helper in form
     * of the private var $_chains.
     *
     * I've tried to map the existing Wordpress functions like get_option or apply_filters
     * to its corresponding objects. So we got this structure for the both examples:
     *
     * @param string $method    The name of the method called
     * @param array  $arguments The arguments to pass to method
     *
     * @example "get_option(...)"    becomes "(Apw_Wordpress_Interface)->option->get(...)"
     *          "apply_filters(...)" becomes "(Apw_Wordpress_Interface)->filters->apply(...)"
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function __call($method, $arguments)
	{
		// get matrix
		$matrix = $this->_methodMatrix;

        // iterate over matrix to retrieve chained method
        foreach ($this->_chains as $chain) {
			$matrix = $matrix[$chain];
        }

        // get wordpress-function to call from matrix
		$function = $matrix[$method];

		// reset state
		$this->_chains = array();

		// check for given and existing function
		if ($function && strlen($function)) {
		    // check for arguments and call function and retrieve result
			if (count($arguments) > 0) {
				$result = call_user_func_array($function, $arguments);
			} else {
				$result = call_user_func($function);
			}
		}

		// return the result of function call
		return $result;
	}

    /**
     * generic chaining for wordpress object structure
     *
     * This magic-method is used for generic chaining. On each request of a class-variable
     * this magic method gets called. So we store the requested variable-name as virtual-object.
     * Receiving a method-call through magic __call() signalize the end of chaining and
     * triggers a function-execute and a chain array reset.
     *
     * @param string $virtualObject The name of virtual object
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function __get($virtualObject)
	{
		// store chain
		$this->_chains[] = $virtualObject;

		// return instance of class (chaining!)
		return $this;
	}
}

?>

<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Amazon-Product-Widget-Front - Front Class
 *
 * amazon-product-widget-front.php
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
 * @subpackage Wordpress_Apw_Front
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Amazon Product Widget Front - Front Class
 *
 * This class is responsible for displaying the data in the frontend (template) e.g. within a sidebar.
 *
 * @category   Wordpress
 * @package    Wordpress_Apw
 * @subpackage Wordpress_Apw_Front
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Front
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
     * contains an instance of the data class
     *
     * @var object An instance of the data class
     * @access private
     */
	private $_data;

    /**
     * contains an instance of the template class
     *
     * @var object An instance of the template class
     * @access private
     */
	private $_template;


    /**
     * displays the widget in the sidebar/template
     *
     * This method is responsible for retrieving data and generating HTML for
     * displaying it in the sidebar.
     *
     * @param array $arguments The arguments for display
     * @param array $instance  The instance
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    public function displayWidget($arguments, $instance)
    {
        $beforeWidget = $arguments['before_widget'];
        $afterWidget  = $arguments['after_widget'];
        $beforeTitle  = $arguments['before_title'];
        $afterTitle   = $arguments['after_title'];

        // user-selected settings for widget
        $title    = $this->_wordpress->filters->apply('widget_title', $instance['title']);
		$list     = $instance['list'];
        $template = $instance['template'];

        // get a random ASIN from list of available ASINs
        $asin = $list[array_rand($list)];

        // before widget (defined by themes).
        echo $beforeWidget;

        // title of widget (before and after defined by themes)
        if ($title) {
            echo $beforeTitle . $title . $afterTitle;
        }

        // the HTML-template
        if ($template) {
            // try to get product information
            try {
				$itemData = $this->_data->getItemData($asin);
            } catch (Exception $e) {
				// empty data
				$itemData = array();
			}

			// add additional data to item-data
			$itemData = array_merge($itemData, $this->_getAdditionalData($asin));

			// prepare additional data for template parser
			$template = $this->_template->parse($template, $itemData);

			// echo HTML-Code
        	echo $template;
        }

        // after widget (defined by themes)
        echo $afterWidget;
    }

    /**
     * returns the additional data like tracking-image-url,
     * associate-id and product-url as array
     *
     * This method is responsible for returning the additional data like tracking-image-url,
     * associate-id and product-url as array.
     *
     * @param string $asin The current ASIN to use
     *
     * @return  array The array containing the additional data
     * @access  private
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getAdditionalData($asin)
    {
		// get associate id
        $associateId = $this->_configuration->getOption('aws_associate_id');

        // construct tracking-image-url
        $trackingImageUrl = str_replace(
            '[[ASIN]]', $asin, str_replace(
        	    '[[ASSOCIATE-ID]]',
        		$associateId,
        		$this->_configuration->getOption('aws_image_tracking_url')
			)
		);

		// construct product-url
        $productUrl = str_replace(
            '[[ASIN]]', $asin, str_replace(
                '[[ASSOCIATE-ID]]',
        		$associateId, $this->_configuration->getOption('aws_product_url')
			)
		);

		// return additional data
		return array(
			'associate-id'       => $associateId,
        	'image-tracking-url' => $trackingImageUrl,
        	'product-url'        => $productUrl
        );
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
     * sets the dependency for data
	 *
     * This method is intend to set the dependency for data.
     *
     * @param object $data An instance of the data class
     *
     * @return   void
     * @access   public
     * @author   Benjamin Carl <opensource@clickalicious.de>
     * @PdInject data
     */
    public function setData($data)
    {
        // store instance of data (for retrieving product/asin information)
		$this->_data = $data;
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
}

?>

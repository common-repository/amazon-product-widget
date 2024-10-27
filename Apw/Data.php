<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Data Class
 *
 * Data.php
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
 * @subpackage Wordpress_Apw_Data
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Data Class
 *
 * This class is responsible for managing the settings/options of the Amazon-Product-Widget.
 * It creates a menu-entry (settings) in the administration-menu, generates and shows the options.
 *
 * @category   Wordpress
 * @package    Wordpress_Apw
 * @subpackage Wordpress_Apw_Data
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Data
{
    /**
     * contains the cached product item data from wordpress DB
     *
     * @var mixed FALSE as long as not readed, afterwards ARRAY
     * @access private
     * @static
     */
    private static $_cache = false;

    /**
     * contains the region of the Amazon Market Place
     *
     * @var string The Amazon Market Place region
     * @access private
     * @static
     */
    private static $_region;

    /**
     * contains the public key for accessing the Amazon Web Service (AWS)
     *
     * @var string The public key for accessing the AWS
     * @access private
     * @static
     */
    private static $_publicKey;

    /**
     * contains the private key for accessing the Amazon Web Service (AWS)
     *
     * @var string The private key for accessing the AWS
     * @access private
     * @static
     */
    private static $_privateKey;

    /**
     * contains the status of initialization
     *
     * @var boolean The status of initialization
     * @access private
     * @static
     */
    private static $_initDone = false;

    /**
     * contains an instance of the configuration class
     *
     * @var object An instance of the configuration class
     * @access private
     */
    private $_configuration;

    /**
     * contains an instance of the request class
     *
     * @var object An instance of the request class
     * @access private
     */
	private $_request;


	/*******************************************************************************************************************
	 * Dependency-Injection (DI)
	 * done through constructor caused by _init() and _configureRequest()
	 * TODO: workaround possible?
	 ******************************************************************************************************************/

    /**
     * constructs the class
     *
     * constructor builds the class
     *
     * @param object $configuration An instance of the configuration class
     * @param object $request       An instance of the request class
     *
     * @return   void
     * @access   public
     * @PdInject configuration
     * @PdInject request
     */
    public function __construct($configuration, $request)
    {
        // store instance of config
		$this->_configuration = $configuration;

    	// store instance of request
    	$this->_request = $request;

		// init data like cache, private and public key ...
    	$this->_init();

		// configure request class
		$this->_configureRequest();
    }

    /**
     * configures the request instance
     *
     * This method is intend to configure the request instance. The configuration
     * includes region, public- and private-key.
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    private function _configureRequest()
    {
    	// setup basic data for
        $this->_request->setAssociateId($this->_configuration->getOption('aws_associate_id'));
        $this->_request->setRegion($this->_configuration->getOption('aws_region'));
        $this->_request->setPublicKey($this->_configuration->getOption('aws_public_key'));
        $this->_request->setPrivateKey($this->_configuration->getOption('aws_private_key'));
    }

    /**
     * inits the basic data for this instance
     *
     * This method is intend to init the basic data for this instance.
     *
     * @return void
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    private function _init()
    {
    	if (!self::$_initDone) {

			// set cached data
			self::$_cache = $this->_getCachedData();

			// set region
			self::$_region = $this->_configuration->getOption('aws_region');

			// set public-key
			self::$_publicKey = $this->_configuration->getOption('aws_public_key');

			// set private-key
			self::$_privateKey = $this->_configuration->getOption('aws_private_key');

			// init done
			self::$_initDone = true;
    	}
    }

    /**
     * returns the data for a given ASIN
     *
     * This method is intend to return the data for a given ASIN. It first tries to read the
     * data from cache if not exist it tries to receive the data from Amazon Web Service (AWS) API.
     *
     * @param string $asin The ASIN to return data for
     *
     * @return array The data for the given ASIN
     * @access public
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function getItemData($asin)
    {
    	// try to get data from cache
	    $itemData = $this->_getItemDataFromCache($asin);

	    // if not in cache ...
	    if (!$itemData) {
	    	// ... try to get data from amazon web service API
	    	$itemData = $this->_getItemDataFromAmazon($asin);

	    	// and write to db for further created instances
		    $this->_addToCache($itemData);
	    }

	    // store given asin in prepared data
	    $itemData['asin'] = $asin;

	    // return result
	    return $itemData;
    }

    /**
     * returns the data for a given ASIN from AWS
     *
     * This method is intend to return the data for a given ASIN. It tries to receive the data from
     * Amazon Web Service (AWS) API.
     *
     * @param string $asin The ASIN to return data for
     *
     * @return array The data for the given ASIN
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getItemDataFromAmazon($asin)
    {
    	// assume empty data
    	$itemData = array();

		// try to get data from amazon
		$data = $this->_request->send(
			array('Operation'     => 'ItemLookup',
				  'ItemId'        => $asin,
				  'ResponseGroup' => 'Medium'
			)
		);

		// store ordered
		if (!empty($data)) {
			$itemData = array(
				'asin'				  => $asin,
				'author'              => strval($data->Items->Item->ItemAttributes->Author),
				'description'         => strval($data->Items->Item->ItemAttributes->Title),
				'image-small-url'     => strval($data->Items->Item->ImageSets->ImageSet->SmallImage->URL),
				'image-small-width'   => strval($data->Items->Item->ImageSets->ImageSet->SmallImage->Width),
				'image-small-height'  => strval($data->Items->Item->ImageSets->ImageSet->SmallImage->Height),
				'image-tiny-url'      => strval($data->Items->Item->ImageSets->ImageSet->TinyImage->URL),
				'image-tiny-width'    => strval($data->Items->Item->ImageSets->ImageSet->TinyImage->Width),
				'image-tiny-height'   => strval($data->Items->Item->ImageSets->ImageSet->TinyImage->Height),
				'image-medium-url'    => strval($data->Items->Item->ImageSets->ImageSet->MediumImage->URL),
				'image-medium-width'  => strval($data->Items->Item->ImageSets->ImageSet->MediumImage->Width),
				'image-medium-height' => strval($data->Items->Item->ImageSets->ImageSet->MediumImage->Height),
				'image-large-url'     => strval($data->Items->Item->ImageSets->ImageSet->LargeImage->URL),
				'image-large-width'   => strval($data->Items->Item->ImageSets->ImageSet->LargeImage->Width),
				'image-large-height'  => strval($data->Items->Item->ImageSets->ImageSet->LargeImage->Height)
			);
		}

	    // finally return the requested data
	    return $itemData;
    }

    /**
     * returns the data for a given ASIN from cache
     *
     * This method is intend to return the data for a given ASIN. It tries to receive the data from
     * the cache.
     *
     * @param string $asin The ASIN to return data for
     *
     * @return array The data for the given ASIN
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getItemDataFromCache($asin)
    {
    	// check if asin exists in cache then return (or false if not)
    	return (isset(self::$_cache[$asin])) ? self::$_cache[$asin] : false;
    }

    /**
     * loads returns the complete cached data from db
     *
     * This method is intend to load and return the cached data for a given ASIN.
     * It tries to receive the data from the cache.
     *
     * @return array The data for the given ASIN
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    private function _getCachedData()
    {
        // get cache from configuration class
        $cache = $this->_configuration->getOption('cache');

        // return cache if retrieved otherwise empty array to fill
        return (!$cache) ? array() : $cache;
    }

    /**
     * adds data for a ASIN to cache
     *
     * This method is intend to add data for a ASIN to cache.
     *
     * @param array $data The data to add
     *
     * @return boolean TRUE on success, otherwise FALSE
     * @access private
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    private function _addToCache($data)
    {
	    // store data in our local cache variable
		self::$_cache[$data['asin']] = $data;

		// and write it to database through our configuration class
		return $this->_configuration->setOption('cache', self::$_cache);
    }
}

?>

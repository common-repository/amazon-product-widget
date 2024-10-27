<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * APW - Request Class (to access the AWS API)
 *
 * Request.php
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
 * @subpackage Wordpress_Apw_Request
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Request Class
 *
 * This class is responsible for managing the settings/options of the Amazon-Product-Widget.
 * It creates a menu-entry (settings) in the administration-menu, generates and shows the options.
 *
 * @category   Wordpress
 * @package    Wordpress_Apw
 * @subpackage Wordpress_Apw_Request
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2011 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Apw_Request
{
    /**
     * Contains the Associate-Id
     * required for AWS data retrieval since November 1, 2011
     * https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html
     *
     * @var string containing the associate-id (tag)
     * @access private
     */
    private $_associateId;

    /**
     * holds the region
     *
     * @var string containing the region
     * @access private
     */
    private $_region;

    /**
     * holds the public key
     *
     * @var string containing the public key
     * @access private
     */
    private $_publicKey;

    /**
     * holds the private key
     *
     * @var string containing the private key
     * @access private
     */
    private $_privateKey;

    /**
     * constructs the class
     *
     * constructor builds the class
     *
     * @param string $region     The AWS region to use for requesting data
     * @param string $publicKey  The public key to use for request
     * @param string $privateKey The private key to use for request
     *
     * @return  void
     * @access  public
     */
    public function __construct($region = '', $publicKey = '', $privateKey = '')
    {
        // store basic config
        $this->_region     = $region;
        $this->_privateKey = $privateKey;
        $this->_publicKey  = $publicKey;
    }

    /**
     * sends a signed request to the Amazon (AWS) API to retrieve product information
     *
     * This method is intend to send a signed request to the Amazon (AWS) API to retrieve product information.
	 *
	 * @param array $parameters The parameters to use for the request
     *
     * @return    mixed Object of type "simplexml" if successful, otherwise boolean FALSE
     * @access    public
     * @author    Benjamin Carl <opensource@clickalicious.de>
     * @author    Ulrich Mierendorff
     *
     * @copyright 2009 Ulrich Mierendorff
     *
     * Permission is hereby granted, free of charge, to any person obtaining a
     * copy of this software and associated documentation files (the "Software"),
     * to deal in the Software without restriction, including without limitation
     * the rights to use, copy, modify, merge, publish, distribute, sublicense,
     * and/or sell copies of the Software, and to permit persons to whom the
     * Software is furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
     * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
     * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
     * DEALINGS IN THE SOFTWARE.
     *
     * @see http://mierendo.com/software/aws_signed_query/
     */
    public function send(array $parameters)
    {
        // some paramters
        $method = 'GET';
        $host   = 'ecs.amazonaws.'.strtolower($this->_region);
        $uri    = '/onca/xml';

        // additional parameters
        $parameters['Service']        = 'AWSECommerceService';
        $parameters['AWSAccessKeyId'] = $this->_publicKey;
        
        // Associate Tag (Id) required since November 1, 2011
        // https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html
        $parameters['AssociateTag'] = $this->_associateId;
                
        // GMT timestamp
        $parameters['Timestamp']      = gmdate('Y-m-d\TH:i:s\Z');
        // API version
        $parameters['Version']        = '2009-03-31';

        // sort the parameters
        ksort($parameters);

        // create the canonicalized query
        $canonicalizedQuery = array();

	    foreach ($parameters as $param => $value) {
	        $param = str_replace('%7E', '~', rawurlencode($param));
	        $value = str_replace('%7E', '~', rawurlencode($value));
	        $canonicalizedQuery[] = $param.'='.$value;
	    }

        $canonicalizedQuery = implode('&', $canonicalizedQuery);

        // create the string to sign
        $stringToSign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalizedQuery;

        // calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $this->_privateKey, true));

        // encode the signature for the request
        $signature = str_replace('%7E', '~', rawurlencode($signature));

        // create request
        $request = 'http://' . $host . $uri . '?' . $canonicalizedQuery . '&Signature=' . $signature;

        // do request
        $response = @file_get_contents($request);
        
        if ($response === false) {
        	throw new Exception('file_get_contents_failed');

        	// failed
        	return false;
        } else {
            // parse XML
            $pxml = simplexml_load_string($response);
            
            if ($pxml === false) {
            	// no xml
                return false;
	        } else {
                // valid result
                return $pxml;
	        }
	    }
	}

	/**
	 * setter for associateId
	 *
	 * Setter for Amazon associate Id / Tag
	 *
	 * @param string $associateId The associate Id to use for queries
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
    public function setAssociateId($associateId)
    {
        $this->_associateId = $associateId;
    }

	/**
	 * getter for associateId
	 *
	 * Getter for Amazon associate Id / Tag
	 *
     * @return  string The associate-id (tag)
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getAssociateId()
	{
		return $this->_associateId;
	}

	/**
	 * setter for region
	 *
	 * Setter for Amazon(r) region (ca, com, co.uk, de, fr, jp)
	 *
	 * @param string $region The region to use for queries
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function setRegion($region)
	{
		$this->_region = $region;
	}

	/**
	 * getter for region
	 *
	 * Getter for Amazon(r) region (ca, com, co.uk, de, fr, jp)
	 *
     * @return  string The region set
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getRegion()
	{
		return $this->_region;
	}

	/**
	 * setter for public-key
	 *
	 * Setter for AWS public-key
	 *
	 * @param string $publicKey The public key to use for AWS queries
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function setPublicKey($publicKey)
	{
		$this->_publicKey = $publicKey;
	}

	/**
	 * getter for public-key
	 *
	 * Getter for AWS public-key
	 *
     * @return  string The public-key set
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getPublicKey()
	{
		return $this->_publicKey;
	}

	/**
	 * setter for private-key
	 *
	 * Setter for AWS private-key
	 *
	 * @param string $privateKey The private key to use for AWS queries
     *
     * @return  void
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function setPrivateKey($privateKey)
	{
		$this->_privateKey = $privateKey;
	}

	/**
	 * getter for private-key
	 *
	 * Getter for AWS private-key
	 *
     * @return  string The private-key set
     * @access  public
     * @author  Benjamin Carl <opensource@clickalicious.de>
     */
	public function getPrivateKey()
	{
		return $this->_privateKey;
	}
}

?>

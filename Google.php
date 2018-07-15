<?php
/**
 * Description of Currencyimport
 * @package   Codewix_Currencyimport
 * @company   Codewix - http://www.codewix.com/
 * @author    Ravinder <codewix@gmail.com>
 */
class Codewix_Currencyimport_Model_Currency_Import_Google extends Mage_Directory_Model_Currency_Import_Abstract {

/*    protected $_url = 'http://www.google.com/finance/converter?a=1&from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}';
*/
/**    protected $_url = 'https://finance.google.com/finance/converter?a=1&from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}';
*/
/*    protected $_url = 'https://finance.google.co.uk/bctzjpnsun/converter?a=1&from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}';
*/

    protected $_url = 'http://free.currencyconverterapi.com/api/v3/convert?q={{CURRENCY_FROM}}_{{CURRENCY_TO}}';

    protected $_messages = array();

    protected $_httpClient;

    public function __construct()
    {
        $this->_httpClient = new Varien_Http_Client();
    }

    protected function _convert($currencyFrom, $currencyTo, $retry=0)
    {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
			$resultKey = $currencyFrom.'_'.$currencyTo;
            $response = $this->_httpClient
                ->setUri($url)
                ->request('GET')
                ->getBody();

			$data = Mage::helper('core')->jsonDecode($response);
			$result = $data['results'][$resultKey];
			$queryCount = $data['query']['count'];
			
            if( !$queryCount && !isset($result)) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $this->_url);
                return null;
            }

            return (float) $result['val'] * 1.0;
        }
        catch (Exception $e) {
            if( $retry == 0 ) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
            }
        }
    }

}

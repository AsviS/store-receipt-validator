<?php
namespace ReceiptValidator\iTunes;

use ReceiptValidator\RunTimeException;

class Response
{

    /**
     * Response Codes
     *
     * @var int
     */
    const RESULT_OK = 0;

    // The App Store could not read the JSON object you provided.
    const RESULT_APPSTORE_CANNOT_READ = 21000;

    // The data in the receipt-data property was malformed or missing.
    const RESULT_DATA_MALFORMED = 21002;

    // The receipt could not be authenticated.
    const RESULT_RECEIPT_NOT_AUTHENTICATED = 21003;

    // The shared secret you provided does not match the shared secret on file for your account.
    // Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.
    const RESULT_SHARED_SECRET_NOT_MATCH = 21004;

    // The receipt server is not currently available.
    const RESULT_RECEIPT_SERVER_UNAVAILABLE = 21005;

    // This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response.
    // Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.
    const RESULT_RECEIPT_VALID_BUT_SUB_EXPIRED = 21006;

    // This receipt is from the test environment, but it was sent to the production environment for verification. Send it to the test environment instead.
    // special case for app review handling - forward any request that is intended for the Sandbox but was sent to Production, this is what the app review team does
    const RESULT_SANDBOX_RECEIPT_SENT_TO_PRODUCTION = 21007;

    // This receipt is from the production environment, but it was sent to the test environment for verification. Send it to the production environment instead.
    const RESULT_PRODUCTION_RECEIPT_SENT_TO_SANDBOX = 21008;

    /**
     * Result Code
     *
     * @var int
     */
    protected $_code;

    /**
     * bundle_id (app) belongs to the receipt
     *
     * @var string
     */
    protected $_bundle_id;

    /**
     * receipt info
     *
     * @var array
     */
    protected $_receipt = array();

    /**
     * latest receipt (needs for auto-renewable subscriptions)
     *
     * @var string
     */
    protected $_latest_receipt;

    /**
     * latest receipt info (needs for auto-renewable subscriptions)
     *
     * @var array
     */
    protected $_latest_receipt_info;

    /**
     * purhcases info
     * @var array
     */
    protected $_purchases = array();

    /**
     * Constructor
     *
     * @param array $jsonResponse
     * @return Response
     */
    public function __construct($jsonResponse = null)
    {
        if ($jsonResponse !== null) {
            $this->parseJsonResponse($jsonResponse);
        }
    }

    /**
     * Get Result Code
     *
     * @return int
     */
    public function getResultCode()
    {
        return $this->_code;
    }

    /**
     * Set Result Code
     *
     * @param int $code
     * @return Response
     */
    public function setResultCode($code)
    {
        $this->_code = $code;

        return $this;
    }

    /**
     * Get purchases info
     *
     * @return array
     */
    public function getPurchases()
    {
        return $this->_purchases;
    }

    /**
     * Get receipt info
     *
     * @return array
     */
    public function getReceipt()
    {
        return $this->_receipt;
    }

    /**
     * Get latest receipt info
     *
     * @return array
     */
    public function getLatestReceiptInfo()
    {
        return $this->_latest_receipt_info;
    }

    /**
     * Get latest receipt
     *
     * @return string
     */
    public function getLatestReceipt()
    {
        return $this->_latest_receipt;
    }

    /**
     * Get the bundle id assoicated with the receipt
     *
     * @return string
     */
    public function getBundleId()
    {
        return $this->_bundle_id;
    }

    /**
     * returns if the receipt is valid or not
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->_code == self::RESULT_OK) {
            return true;
        }

        return false;
    }

    /**
     * Parse JSON Response
     *
     * @param string $jsonResponse
     *
     * @return Response
     * @throws RunTimeException
     */
    public function parseJsonResponse($jsonResponse)
    {
        if (!is_array($jsonResponse)) {
            throw new RuntimeException('Response must be a scalar value');
        }

        if (array_key_exists('status', $jsonResponse)) {
            if (array_key_exists('receipt', $jsonResponse) && is_array($jsonResponse['receipt'])) {
                $receipt = $jsonResponse['receipt'];
            } else {
                $receipt = [];
            }
            if (array_key_exists('in_app', $receipt) && is_array($receipt['in_app'])) {
                $inApp = $jsonResponse['in_app'];
            } else {
                $inApp = [];
            }
            $this->_code = $jsonResponse['status'];
            $this->_receipt = $receipt;
            if ($inApp) {
                $this->_purchases = $inApp;
            } else {
                $this->_purchases = [$receipt];
            }
            if (array_key_exists('bundle_id', $receipt)) {
                $this->_bundle_id = $receipt['bundle_id'];
            } elseif (array_key_exists('bid', $receipt)) {
                $this->_bundle_id = $receipt['bid'];
            }

            if (array_key_exists('latest_receipt_info', $jsonResponse)) {
                $this->_latest_receipt_info = $jsonResponse['latest_receipt_info'];
            } elseif (array_key_exists('latest_expired_receipt_info', $jsonResponse)) {
                $this->_latest_receipt_info = $jsonResponse['latest_expired_receipt_info'];
            }

        } else {
            $this->_code = self::RESULT_DATA_MALFORMED;
        }

        return $this;
    }
}

<?php
require_once('includes/autoload.php');
use \Firebase\JWT\JWT;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function zaincashmodule_MetaData()
{
    return array(
        'DisplayName' => 'Zain Cash Payment Gateway',
        'APIVersion' => '1.0',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
        'GatewayType' => 'ZainCash',
    );
}

function zaincashmodule_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Zain Cash Payment Gateway',
        ),

        'msisdn' => array(
            'FriendlyName' => 'MSISDN',
            'Type' => 'text',
            'Size' => '256',
            'Default' => '',
            'Description' => 'Enter your MSISDN here',
        ),

        'secretKey' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'password',
            'Size' => '256',
            'Default' => '',
            'Description' => 'Enter secret key here',
        ),

        'merchantId' => array(
            'FriendlyName' => 'Merchant ID',
            'Type' => 'text',
            'Size' => '256',
            'Default' => '',
            'Description' => 'Enter your Merchant ID here',
        ),

        'isProduction' => array(
            'FriendlyName' => 'Is Production',
            'Type' => 'yesno',
            'Description' => 'Tick if this is a production environment',
        ),

        'language' => array(
            'FriendlyName' => 'Language',
            'Type' => 'radio',
            'Options' => 'en,ar',
            'Description' => 'Choose your preferred language!',
        ),
    );
}

function zaincashmodule_link($params)
{
    // Gateway Configuration Parameters
    $msisdn = $params['msisdn'];
    $secretKey = $params['secretKey'];
    $merchantId = $params['merchantId'];
    $isProduction = $params['isProduction'];
    $language = $params['language'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];

    $isProductionLink = $isProduction ? "https://api.zaincash.iq" : "https://test.zaincash.iq";
    $redirectURL = $systemUrl . 'modules/gateways/callback/zaincashmodule.php';

    // Validate required parameters
    if (empty($msisdn) || empty($secretKey) || empty($merchantId)) {
        return "Error: Missing required gateway configuration parameters.";
    }


    $requestPayload = array(
        'amount' => $amount,
        'serviceType' => $description,
        'msisdn' => $msisdn,
        'orderId' => $invoiceId,
        'redirectUrl' => $redirectURL,
        'iat' => time(),
        'exp' => time() + 60 * 60 * 4
    );

    try {
        $newToken = JWT::encode($requestPayload, $secretKey, 'HS256');
    } catch (Exception $e) {
        return "Error creating token: " . $e->getMessage();
    }


    $dataToPost = array(
        'merchantId' => $merchantId,
        'token' => $newToken,
        'lang' => $language,
    );


    $ch = curl_init();
    $finalUrl = $isProductionLink . '/transaction/init';

    curl_setopt_array($ch, array(
        CURLOPT_URL => $finalUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($dataToPost),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json'
        ),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'WHMCS ZainCash Module',
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Debug logging (you might want to log this to WHMCS module log)
    /*
    var_dump("ZainCash Debug: URL: " . $finalUrl .
        " | HTTP Code: " . $httpCode .
        " | Response: " . $response .
        " | Error: " . $curlError);
   */

    if ($response === false) {
        return "Connection failed: " . $curlError;
    }

    if ($httpCode !== 200) {
        return "HTTP Error {$httpCode}: " . $response;
    }

    $arrayResponse = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Invalid JSON response: " . $response;
    }

    if (!isset($arrayResponse['id'])) {
        return "Error: Transaction ID not received. Response: " . $response;
    }

    $transactionId = $arrayResponse['id'];
    $newLocationURL = $isProductionLink . '/transaction/pay?id=' . $transactionId;

    // Create payment form
    $code = '
                <a href="' . $newLocationURL . '" class="btn btn-primary">' . $langPayNow . '</a>
';
    return $code;
}



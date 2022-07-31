<?php

namespace SoerBV\Api;

/**
 * @author Rick de Boer <r.deboer@soer.nl>
 */

class Client
{
    protected string $host;
    protected string $administration;
    protected string $apiKey;

    public function __construct(string $host, string $administration, string $apiKey)
    {
        $this->host = $host;
        $this->administration = $administration;
        $this->apiKey = $apiKey;
    }

    public function sendRequest($endpoint, $method, $params = [], $data = null)
    {
        $curl = curl_init();
        $url = $this->host . "/" . $endpoint . "?" . http_build_query($params);

        switch ($method) {
            case "GET":
                curl_setopt($curl, CURLOPT_HTTPGET, 1);
                break;
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'X-ApiKey: ' . $this->apiKey,
            'administration: ' . $this->administration,
            'Content-Type: application/json'
        ));

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * Fetch all items with a maximum of 100 from administration
     */
     public function getAllItems($start, $limit, $changeDate = null)
     {
         if ($limit > 100) return "Limit is higher than allowed. The maximum amount of items is 100.";

         $params = array(
             'skip' => $start,
             'take' => $limit,
                'changeDate' => $changeDate
         );

         return $this->sendRequest('api/Item', 'GET', $params);
     }

     /**
      * Fetch Item by Item SKU
      */
     public function getItemByCode($code)
     {
         return $this->sendRequest("api/Account/ByItemCode/" . $code, "GET");
     }

     public function getSupplierInfo($code)
     {
            return $this->sendRequest("api/Account/ByCred/" . $code, "GET");
     }

     public function getAllAccounts($start, $limit, $changeDate = null)
     {
         if ($limit > 100) return "Limit is higher than allowed. The maximum amount of accounts is 100.";

         $params = array(
             'skip' => $start,
             'take' => $limit,
             'changeDate' => $changeDate
         );

         return $this->sendRequest('api/Account', 'GET', $params);
     }

    public function retrieveSalesOrder($orderNumber)
    {
        return $this->sendRequest("api/SalesOrder/ByNumber/" . $orderNumber, "GET");
    }

    public function lockSalesOrder($orderNumber)
    {
        return $this->sendRequest("api/SalesOrder/" . $orderNumber . "/Lock/", "GET");
    }

    public function unlockSalesOrder($orderNumber)
    {
        return $this->sendRequest("api/SalesOrder/" . $orderNumber . "/Unlock/", "GET");
    }

    public function fulfillSalesOrder($orderNumber, $date) {
        $params = array(
            'date' => $date
        );
        return $this->sendRequest("api/SalesOrder/" . $orderNumber . "/Fulfill/", "GET", $params);
    }
}
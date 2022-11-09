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
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
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
         if ($limit > 500) return "Limit is higher than allowed. The maximum amount of items is 500.";

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
         return $this->sendRequest("api/Item/ByItemCode/" . $code, "GET");
     }

     public function getSupplierInfo($code)
     {
            return $this->sendRequest("api/Account/ByCred/" . $code, "GET");
     }

     public function getAllAccounts($start, $limit, $changeDate = null, $customerType = null)
     {
         if ($limit > 500) return "Limit is higher than allowed. The maximum amount of accounts is 500.";

         $params = array(
             'skip' => $start,
             'take' => $limit,
             'changeDate' => $changeDate,
             'CustomerType' => $customerType
         );

         return $this->sendRequest('api/Account', 'GET', $params);
     }

    public function retrieveSalesOrder($orderNumber)
    {
        return $this->sendRequest("api/SalesOrder/ByNumber/" . $orderNumber, "GET");
    }

    public function retrieveAllSalesOrders($start, $limit, $changeDate = null)
    {
        if ($limit > 500) return "Limit is higher than allowed. The maximum amount of sales orders is 500.";

        $params = array(
            'skip' => $start,
            'take' => $limit,
            'modified' => $changeDate
        );

        return $this->sendRequest('api/SalesOrder', 'GET', $params);
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

    public function retrieveAllPurchaseOrders($start, $limit, $changeDate = null)
    {
        if ($limit > 500) return "Limit is higher than allowed. The maximum amount of purchase orders is 500.";

        $params = array(
            'skip' => $start,
            'take' => $limit,
            'changeDate' => $changeDate
        );

        return $this->sendRequest("api/PurchaseOrder", "GET", $params);
    }

    public function retrievePurchaseOrder($orderNumber)
    {
        return $this->sendRequest("api/PurchaseOrder/ByNumber/" . $orderNumber, "GET");
    }

    public function getItemStock($itemCode = null, $warehouse = null, $start = null, $limit = null, $stockDate = null, $changeDate = null)
    {
        $params = array(
            'itemCode' => $itemCode,
            'warehouse' => $warehouse,
            'skip' => $start,
            'limit' => $limit,
            'stockDate' => $stockDate,
            'changeDate' => $changeDate
        );
        return $this->sendRequest("api/Stock/Current", "GET", $params);
    }

    public function sendCustomQuery($query)
    {
        $verificationCode = json_decode($this->sendRequest("api/CustomQuery", "GET", [], $query));
        $verificationCode = $verificationCode->VerificationCode;

        $post = array(
            'query' => $query,
            'verificationCode' => $verificationCode
        );

        return $this->sendRequest("api/CustomQuery", "POST", [], json_encode($post));
    }

    public function updateItem($data)
    {
        return $this->sendRequest("api/Item", "PUT", [], $data);
    }

    public function newStockCount($itemcode, $quantity, $grtbk, $description = null)
    {
        $data = array(
            'Itemcode' => $itemcode,
            'Quantity' => $quantity,
            'Description' => $description,
            'GLAccountCost' => $grtbk
        );

        return $this->sendRequest("/api/Stock", "POST", [], $data);
    }

}
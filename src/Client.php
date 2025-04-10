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

    /**
     * @throws Exception
     */
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
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
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
        $headerInfo = curl_getinfo($curl);
        // throw error if $result returns other header then status 200
        $acceptedHeaders = [200, 201, 204];

        if ($headerInfo['http_code'] != in_array($headerInfo['http_code'], $acceptedHeaders)) {
            throw new Exception('Status' . $headerInfo['http_code'] . ' received: ' . $result);
        }

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
         $params = array(
             'itemCode' => $code,
         );
         return $this->sendRequest("api/Item/ByItemCode", "GET", $params);
     }

     public function getSupplierInfo($code)
     {
         return $this->sendRequest("api/Account/ByCred/" . $code, "GET");
     }

     public function getDebtorInfo($code)
     {
         return $this->sendRequest("api/Account/ByDeb/" . $code, "GET");
     }

     public function getDebtorBalance($skip = 0, $take = 0, $debtorNumber = '', $details = false, $expiredReceivables = false)
     {
         $params = array(
             'skip' => $skip,
             'take' => $take,
             'debtorNumber' => $debtorNumber,
             'details' => $details,
             'expiredReceivables' => $expiredReceivables
         );

         return $this->sendRequest("api/Account/DebtorBalance", "GET", $params);
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

    /**
     * Create a new creditor or debtor in the Exact Globe administration
     * @throws Exception
     */
    public function createNewAccount($data)
     {
         return $this->sendRequest('api/Account', 'POST', [], $data);
     }

    /**
     * Update account data in the Exact Globe Administration based on AccountCode
     * @throws Exception
     */
    public function updateAccount($data)
     {
         return $this->sendRequest('api/Account', 'PUT', [], $data);
     }

    /**
     * @throws Exception
     */
    public function retrieveSalesOrder($orderNumber)
    {
        return $this->sendRequest("api/SalesOrder/ByNumber/" . $orderNumber, "GET");
    }

    /**
     * @throws Exception
     */
    public function retrieveAllSalesOrders($start, $limit, $modifiedDate = null)
    {
        if ($limit > 500) return "Limit is higher than allowed. The maximum amount of sales orders is 500.";

        $params = array(
            'skip' => $start,
            'take' => $limit,
            'modified' => $modifiedDate
        );

        return $this->sendRequest('api/SalesOrder', 'GET', $params);
    }

    /**
     * @throws Exception
     */
    public function createSalesOrder($data)
    {
        return $this->sendRequest("api/SalesOrder", "POST", [], $data);
    }

    /**
     * @throws Exception
     */
    public function createPurchaseOrder($data)
    {
        return $this->sendRequest("api/PurchaseOrder", "POST", [], $data);
    }

    /**
     * @throws Exception
     */
    public function lockSalesOrder($orderNumber)
    {
        return $this->sendRequest("api/SalesOrder/" . $orderNumber . "/Lock/", "GET");
    }

    /**
     * @throws Exception
     */
    public function unlockSalesOrder($orderNumber)
    {
        return $this->sendRequest("api/SalesOrder/" . $orderNumber . "/Unlock/", "GET");
    }

    /**
     * @throws Exception
     */
    public function fulfillSalesOrder($orderNumber, $date) {
        if(is_null($orderNumber) || is_null($date)) {
            return 'The salesOrderNumber and date fields are mandatory.';
        }
        $params = array(
            'salesOrderNumber' => $orderNumber,
            'processDate' => $date
        );
        return $this->sendRequest("api/SalesOrder/" . $orderNumber . "/Fulfill/", "GET", $params);
    }

     public function fulfillSalesOrderPartially($data)
    {
        return $this->sendRequest("api/SalesOrder/FulfillPartial", "POST", [], $data);
    }

    /**
     * @throws Exception
     */
    public function retrieveAllPurchaseOrders($start, $limit, $modifiedDate = null)
    {
        if ($limit > 500) return "Limit is higher than allowed. The maximum amount of purchase orders is 500.";

        $params = array(
            'skip' => $start,
            'take' => $limit,
            'modified' => $modifiedDate
        );

        return $this->sendRequest("api/PurchaseOrder", "GET", $params);
    }

    public function receiptPurchaseOrderPartially($data)
    {
        return $this->sendRequest("api/PurchaseOrder/ReceiptPartial", "POST", [], $data);
    }

    public function receiptPurchaseOrder($purchaseOrderNumber, $processDate)
    {
        $params = [
            'processDate' => $processDate
        ];
        return $this->sendRequest("api/PurchaseOrder/" . $purchaseOrderNumber . "/Receipt", "POST", $params);
    }

    public function printPurchaseOrder($purchaseOrderNumber, $printLayout)
    {
        $params = [
            'PurchaseOrderNumber' => $purchaseOrderNumber,
            'ProcessMode' => '1',
            'PrintDestination' => '2',
            'PrintLayout' => $printLayout
        ];
        return $this->sendRequest("api/PurchaseOrder/Print", "POST", [], $params);
    }

    /**
     * @throws Exception
     */
    public function retrievePurchaseOrder($orderNumber)
    {
        return $this->sendRequest("api/PurchaseOrder/ByNumber/" . $orderNumber, "GET");
    }

    /**
     * @throws Exception
     */
    public function getItemStock($itemCode = null, $warehouse = null, $start = null, $limit = null, $stockDate = null, $changeDate = null)
    {
        $params = array(
            'itemCode' => $itemCode,
            'warehouse' => $warehouse,
            'start' => $start,
            'limit' => $limit,
            'stockDate' => $stockDate,
            'changeDate' => $changeDate
        );
        return $this->sendRequest("api/Stock/Current", "GET", $params);
    }

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
    public function createItem($data)
    {
        return $this->sendRequest("api/Item", "POST", [], $data);
    }

    /**
     * @throws Exception
     */
    public function updateItem($data)
    {
        return $this->sendRequest("api/Item", "PUT", [], $data);
    }
    public function getItemAccountByCode($code)
    {
        $params = array(
            'itemCode' => $code,
        );
        return $this->sendRequest("api/ItemAccount/ByItem", "GET", $params);
    }

    public function createItemAccount($data)
    {
        return $this->sendRequest("api/ItemAccount", "POST", [], $data);
    }

    /**
     * @throws Exception
     */
    public function newStockCount($itemcode, $quantity, $grtbk, $description = null)
    {
        $data = array(
            'Itemcode' => $itemcode,
            'Warehouse' => '1   ',
            'Quantity' => $quantity,
            'Description' => $description,
            'GLAccountCost' => $grtbk
        );

        return $this->sendRequest("api/Stock", "POST", [], $data);
    }


    /**
     * PRICELIST
     * These function can be used the get, update or create a pricelist.
     */

    /**
     * Get PriceList
     * @throws Exception
     */
    public function getPriceList($itemCode = null, $debtorNumber = null, $modified = null)
    {
        $params = array(
            'itemCode' => $itemCode,
            'debtorNumber' => $debtorNumber,
            'modified' => $modified,
        );

        return $this->sendRequest('api/PriceList', "GET", $params);
    }

    /**
     * Get all staffels from a singular PriceList
     * @throws Exception
     */
    public function getPriceListByCode($code, $start, $take)
    {
        $params = array(
            'code' => $code,
            'start' => $start,
            'take' => $take,
        );

        return $this->sendRequest('api/PriceList/ByCode', "GET", $params);
    }

    /**
     * Get all pricelists that exist in the Exact Globe administration
     * @throws Exception
     */
    public function getAllPriceLists()
    {
        return $this->sendRequest('api/PriceList/GetAll', "GET");
    }

    /**
     * Get item price for debtor
     * @throws Exception
     */
    public function getDebtorItemPrice($itemCode, $debtorNumber, $quantity, $priceDate)
    {
        $params = array(
            'itemCode' => $itemCode,
            'debtorNumber' => $debtorNumber,
            'quantity' => $quantity,
            'priceDate' => $priceDate
        );

        return $this->sendRequest('api/PriceList/ItemPrice', "GET", $params);
    }

    /**
     * Update a PriceList
     * @throws Exception
     */
    public function updatePriceList($data)
    {
        return $this->sendRequest("api/PriceList", "PUT", [], $data);
    }

    /**
     * Create a new PriceList
     * @throws Exception
     */
    public function newPriceList($data)
    {
        return $this->sendRequest("api/PriceList", "POST", [], $data);
    }
    

}

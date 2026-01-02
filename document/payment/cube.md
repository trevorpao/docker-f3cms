
# 國泰世華金流 (EPOS) API 規格說明書

### 版本紀錄

| 版本 | 說明 | 日期 |
| :--- | :--- | :--- |
| V10.0.0 | 初版發行 | 2017/02/18 |
| V11.0.0 | 新增 (取消)請款、(取消)退款、取消交易 JSON 格式 | 2021/01/27 |
| V11.2.4 | 調整代碼說明與新增接收頁回應規範 | 2022/05/30 |

---

### 1. 系統對接環境與基礎設定

#### 1.1 基礎網址 (Base URLs)
*   **訂單初始化網頁 (Redirect 付款頁):** `https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx`
*   **後端服務 API (Web Service/WSDL):** `https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl`

#### 1.2 核心安全規範
*   **來源 IP 綁定 (Whitelist):** 調用 API 的伺服器 IP 必須先提供予銀行端進行白名單設定，否則連線將被拒絕。
*   **TLS 協議:** 必須支援 TLS 1.1 或 TLS 1.2。
*   **訂單編號規範:** 長度上限 20 碼，僅限大寫英文字母 (A-Z) 與數字 (0-9)，禁止使用小寫字母。
*   **維護窗口 (Blackout Window):** 每日 **00:00 - 01:00** 為結帳批次時間，此期間執行 API 極可能收到 `M011` (交易逾時) 錯誤。

---

### 2. 安全驗證機制 (CAVALUE 計算)

本 API 使用 MD5 演算法進行訊息摘要驗證。**欄位順序必須嚴格一致**，且須串接銀行提供的 `CUBKEY`。

| 交易類型 | CAVALUE 組合規則 |
| :--- | :--- |
| **付款請求 (TRS0004)** | `MD5(STOREID + ORDERNUMBER + AMOUNT + LANGUAGE + CUBKEY)` |
| **分期付款 (TRS0005)** | `MD5(STOREID + ORDERNUMBER + AMOUNT + PERIODNUMBER + LANGUAGE + CUBKEY)` |
| **授權結果接收 (S2S)** | `MD5(STOREID + ORDERNUMBER + AMOUNT + AUTHSTATUS + AUTHCODE + CUBKEY)` |
| **訂單查詢 (ORD0001)** | `MD5(STOREID + ORDERNUMBER + AMOUNT + CUBKEY)` |

---

### 3. API 接口定義

#### 3.1 建立付款訂單 (訂單初始化後授權)
將客戶導向國泰世華收單頁面進行信用卡資訊輸入。

*   **Method:** `POST`
*   **MSGID:** `TRS0004` (一般交易) / `TRS0005` (分期交易)
*   **Content-Type:** `application/x-www-form-urlencoded`
*   **Request Body 欄位:** `strRqXML` (XML 格式字串)

**XML 欄位規格 (`MERCHANTXML`):**

| 標籤名稱 | 最大長度 | 必填 | 說明 |
| :--- | :--- | :--- | :--- |
| **CAVALUE** | - | Y | MD5 驗證值 |
| **STOREID** | 15 | Y | 特店代號 (StoreID = MID) |
| **ORDERNUMBER** | 20 | Y | 訂單編號 (僅限大寫英數) |
| **AMOUNT** | 8 | Y | 授權金額 (純數字) |
| **LANGUAGE** | 5 | Y | 語系: `ZH-TW`, `EN-US`, `JA-JP`, `KO-KR` |
| **PERIODNUMBER**| 2 | Y | **(僅限 TRS0005)** 分期期數，須大於 1 |

---

#### 3.2 訂單查詢 API
主動發查特定訂單的最新狀態（如授權、請款或退款狀態）。

*   **Method:** `POST` (SOAP/Web Service)
*   **MSGID:** `ORD0001`

**請求 XML 範例:**
```xml
<MERCHANTXML>
  <MSGID>ORD0001</MSGID>
  <CAVALUE>[MD5_HASH]</CAVALUE>
  <ORDERINFO>
    <STOREID>999990001</STOREID>
    <ORDERNUMBER>XDT12125</ORDERNUMBER>
    <AMOUNT>2</AMOUNT>
  </ORDERINFO>
</MERCHANTXML>
```

---

#### 3.3 請款 (Capture) 與 退貨 (Refund) API
*   **請款 (ORD0005):** 對已授權成功之訂單發起扣款。
*   **退貨 (ORD0003):** 對已請款成功之訂單發起退費。

**請款請求關鍵欄位 (`CAPTUREORDERINFO`):**
*   **AMOUNT:** 請款金額。
*   **AUTHCODE:** 銀行回傳的 6 碼授權碼。
*   **注意:** 紅利折抵或分期交易不可部分請款。

---

### 4. 授權結果接收與 Callback 機制

這是最核心的對接環節，商店必須撰寫一個 Web-based 程式來處理銀行端的背景通知。

#### 流程 A：銀行端 HttpRequest 通知 (Server-to-Server)
1.  銀行以 `Method="POST"` 傳送 `strRsXML` 到商家的接收 URL。
2.  商家**必須在 15 秒內**給予回應。
3.  **商家回應格式 (必填):**
    ```xml
    <MERCHANTXML>
      <CAVALUE>MD5(RETURL_Domain + CUBKEY)</CAVALUE>
      <RETURL>https://example.com/OrderResult</RETURL>
    </MERCHANTXML>
    ```
    *   **重要:** `RETURL` 內容不得包含 `&` 連接符號。

#### 流程 B：客戶端網頁導回 (Redirect)
銀行確認收到正確回應後，會將客戶 Redirect 回商家提供的 `RETURL`，並附帶 `strOrderInfo` (內含 `STOREID` 與 `ORDERNUMBER`) 供商家進行最終頁面比對。

---

### 5. 狀態代碼表 (STATUS)

| 代碼 | 說明 |
| :--- | :--- |
| **0000** | 交易成功 (通用成功代碼，含請/退款) |
| **0202** | 授權成功 |
| **0303** | 授權失敗 |
| **1212** | 已請款已結帳 |
| **2222** | 已退款已結帳 |
| **M011** | 交易逾時 (請檢查是否在結帳批次時間內發送) |

---

### 💡 Principal Engineer 的對接建議 (AI 提示優化)

*   **XML 解析:** 銀行回傳的 XML 字串 (如 `strRsXML`) 通常需要先進行 URL Decode 後再進行 XML Parsing。
*   **驗證順序:** 在 AI 產出雜湊碼程式時，請強調 `CUBKEY` 必須放在字串的最末端進行連接。
*   **異步處理:** 由於 Callback 有 15 秒限制，建議接收程式在驗證完 `CAVALUE` 並儲存狀態後立即回傳 XML，後續的業務邏輯處理應放入 Queue 中執行。

身為一名 Principal Engineer 與資深產品經理，我深知在對接金流系統時，最關鍵的環節在於如何將內部的「商務實體（Business Entity）」，精準地轉換為銀行端要求的「通訊格式（Communication Protocol）」。

針對您提供的 **範例訂單 JSON**，該訂單編號為 `TX3M7QTLAXXN_3MKTL`，金額為 `800`。根據國泰世華 EPOS 的規範，訂單編號必須限制在 20 碼內且僅限大寫英數。

以下為您產出的 PHP 金流串接類別範例，這份程式碼參考了來源中的 `TPPhelper` 結構，並嚴格遵循國泰世華的 XML 電文規格與驗證邏輯。

---

## 訂單範例 json
```json
{
    "id": 26,
    "member_id": 2,
    "order_no": "TX3M7QTLAXXN_3MKTL",
    "affiliate_id": 0,
    "engaging_id": 0,
    "status": "New",
    "payment": "TRANSFER",
    "discount": 0,
    "amount": 800,
    "paid_ts": null,
    "pay_code": "",
    "last_ts": "2025-10-31 12:52:06",
    "last_user": 2,
    "insert_ts": "2025-10-31 12:52:06",
    "insert_user": 2,
    "items": [
    {
        "id": 38,
        "plan_id": 4,
        "sorter": 0,
        "type": "Echelon",
        "qty": 1,
        "price": 800,
        "discount": 0,
        "title": "full_price - 瑜珈睡眠術(11-07 15:30)",
        "cover": ""
    }],
    "meta":
    {
        "delivery": "{\"shipment\":\"None\"}"
    },
    "buyer":
    {
        "name": "本本",
        "email": "aquqfish2728@gmail.com",
        "mobile": "",
        "zipcode": "",
        "address": null
    },
    "invoice":
    {
        "id": 26,
        "member_id": 2,
        "order_id": 26,
        "cashier_id": 0,
        "status": "New",
        "carrier": "Member",
        "type": "Duplicate",
        "mobile_code": "",
        "cdc_code": "",
        "donation": "",
        "realname": "",
        "company": "",
        "vat_number": "",
        "county": "",
        "zipcode": "",
        "address": "",
        "amount": 800,
        "title": "",
        "issue_date": "0000-00-00",
        "last_ts": "2025-10-31 12:52:06",
        "last_user": 2,
        "insert_ts": "2025-10-31 12:52:06",
        "insert_user": 2
    },
    "logs": [
    {
        "status": "New",
        "insert_ts": "2025-10-31 12:52:06"
    }]
}
```

## 串接 PHP 範例 (基於提供之 JSON 結構)

```php
<?php

namespace F3CMS;

/**
 * CUBEposHelper - 基於國泰世華 EPOS 規範與範例 JSON 結構設計
 */
class CUBEposHelper extends Helper
{
    private $_store_id = '999990001'; // 特店代號 (StoreID / MID)
    private $_cub_key  = 'YOUR_CUBKEY_HERE'; // 銀行提供之加密金鑰
    private $_gateway_url = 'https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx'; //

    /**
     * 將內部的 Order JSON 轉換並發起支付請求
     * 
     * @param array $orderData 參考範例 JSON 轉換後的陣列
     * @return string 返回自動提交的 HTML 表單
     */
    public function generatePaymentForm($orderData)
    {
        // 1. 提取並預處理資料
        // 規範：訂單編號限大寫英數，20碼內
        $orderNo = strtoupper(substr($orderData['order_no'], 0, 20)); 
        $amount  = (string)$orderData['amount']; // 授權金額
        $lang    = 'ZH-TW'; // 語系預設為繁中

        // 2. 計算 CAVALUE (雜湊驗證值)
        // 組合順序：STOREID + ORDERNUMBER + AMOUNT + LANGUAGE + CUBKEY
        $rawString = $this->_store_id . $orderNo . $amount . $lang . $this->_cub_key;
        $caValue   = md5($rawString); // 採用 MD5 演算法

        // 3. 建立符合 TRS0004 規範的 XML 電文
        $xmlBody = "<?xml version='1.0' encoding='UTF-8'?>
        <MERCHANTXML>
            <CAVALUE>{$caValue}</CAVALUE>
            <MSGID>TRS0004</MSGID>
            <ORDERINFO>
                <STOREID>{$this->_store_id}</STOREID>
                <ORDERNUMBER>{$orderNo}</ORDERNUMBER>
                <AMOUNT>{$amount}</AMOUNT>
                <LANGUAGE>{$lang}</LANGUAGE>
            </ORDERINFO>
        </MERCHANTXML>";

        // 4. 產生前端自動提交表單
        return $this->_renderHiddenForm($xmlBody);
    }

    /**
     * 處理銀行後端通知 (HttpRequest 回傳)
     */
    public function handleCallback($strRsXML)
    {
        // 銀行回傳 XML
        $xml = simplexml_load_string($strRsXML);
        if (!$xml) return false;

        // 驗證回傳的 CAVALUE
        // 順序：STOREID + ORDERNUMBER + AMOUNT + AUTHSTATUS + AUTHCODE + CUBKEY
        $checkStr = (string)$xml->ORDERINFO->STOREID . 
                    (string)$xml->ORDERINFO->ORDERNUMBER . 
                    (string)$xml->ORDERINFO->AMOUNT . 
                    (string)$xml->AUTHINFO->AUTHSTATUS . 
                    (string)$xml->AUTHINFO->AUTHCODE . 
                    $this->_cub_key;

        if (md5($checkStr) === (string)$xml->CAVALUE) {
            // 授權成功代碼為 0000
            if ((string)$xml->AUTHINFO->AUTHSTATUS === '0000') {
                return true; // 交易成功
            }
        }
        return false;
    }

    /**
     * 授權結果接收程式必須回傳的回應電文
     */
    public function respondToBank($redirectUrl)
    {
        // 規範：CAVALUE 計算方式為 (RETURL 網域名稱 + CUBKEY)
        $domain = parse_url($redirectUrl, PHP_URL_HOST);
        $caValue = md5($domain . $this->_cub_key);

        // 規範：RETURL 內容不得包含 "&" 連接符號
        header('Content-Type: text/xml');
        echo "<?xml version='1.0' encoding='UTF-8'?>
        <MERCHANTXML>
            <CAVALUE>{$caValue}</CAVALUE>
            <RETURL>{$redirectUrl}</RETURL>
        </MERCHANTXML>";
    }

    private function _renderHiddenForm($xml)
    {
        $action = htmlspecialchars($this->_gateway_url);
        $value  = htmlspecialchars($xml);
        return <<<HTML
        <form id="cub_epos_form" action="{$action}" method="post">
            <input type="hidden" name="strRqXML" value="{$value}">
        </form>
        <script>document.getElementById('cub_epos_form').submit();</script>
HTML;
    }
}
```

---

### Principal Engineer 的關鍵實作建議

1.  **資料清洗 (Data Sanitization)：** 在範例 JSON 中，訂單編號包含底線 `_`。雖然部分系統允許底線，但國泰官方文件明確要求僅限 `[A-Z, 0-9]`。在轉換時，建議利用 `strtoupper()` 強制轉大寫，並移除不合法字元，以避免拋出 `L100` (資料格式有誤) 的錯誤。
2.  **安全性防護：** 
    *   **IP 綁定：** 請務必確認您的 Web Server IP 已提供給銀行端設定白名單，否則 API 連線會失敗。
    *   **TLS 版本：** 確保伺服器支援 TLS 1.1 或 TLS 1.2。
3.  **回應時效性：** 國泰世華要求特店的接收網頁必須在 **15 秒內** 給予回應。因此，在 `handleCallback` 中，建議僅執行「雜湊校驗」與「寫入資料庫」，複雜的後續邏輯（如發送通知信）應交由非同步任務處理。
4.  **RETURL 限制：** 銀行規範 `RETURL` 不能包含 `&` 符號。若您的系統需要傳遞參數，建議使用 Path Variable (如 `/order/finish/26`) 或 Session 來儲存狀態。



身為一名 Principal Engineer 及資深產品經理，我非常重視程式碼在對接舊型銀行系統時的**魯棒性（Robustness）**。國泰世華 EPOS 系統對格式有極其嚴格的限制（例如：訂單編號僅限大寫英數、長度 20 碼內），任何一個底線或小寫字母都會導致交易直接被攔截並回傳 L100 錯誤。

針對您提供的範例訂單 JSON（編號：`TX3M7QTLAXXN_3MKTL`，金額：`800`），以下是基於 `F3CMS` 框架風格撰寫的 PHP 呼叫範例。

## 國泰世華 EPOS 金流串接呼叫範例 (PHP)

此範例展示了如何接收 JSON 訂單實體，進行必要的商務邏輯清洗（Sanitization），並產出符合銀行規範的自動提交表單。

```php
<?php

namespace F3CMS;

// 假設這是從資料庫或 API 取得的範例 JSON 訂單轉成的陣列
$orderJson = [
    "id" => 26,
    "order_no" => "TX3M7QTLAXXN_3MKTL",
    "amount" => 800,
    "buyer" => [
        "name" => "本本",
        "email" => "aquqfish2728@gmail.com"
    ]
];

/**
 * 實作呼叫流程
 */
try {
    // 1. 初始化 Helper (建議透過 Config 取得金鑰)
    $storeId = '999990001'; // 銀行提供的特店代號 (MID)
    $cubKey  = 'ABC123456789'; // 銀行提供之 CUBKEY
    
    $paymentHelper = new CUBEposHelper($storeId, $cubKey);

    // 2. 進行商務邏輯清洗 (Principal Engineer 的預判)
    // 銀行規範：訂單編號僅限 [A-Z, 0-9]，且不可超過 20 碼
    // 範例中的 'TX3M7QTLAXXN_3MKTL' 含有底線，必須移除或替換
    $cleanOrderNo = strtoupper(preg_replace('/[^A-Z0-9]/', '', $orderJson['order_no']));
    $cleanOrderNo = substr($cleanOrderNo, 0, 20); 

    // 3. 準備支付請求資料 (符合 TRS0004 規格)
    $payData = [
        'order_no' => $cleanOrderNo,
        'amount'   => $orderJson['amount'],
        'language' => 'ZH-TW' // 支援多語系：ZH-TW, EN-US, JA-JP, KO-KR
    ];

    // 4. 產生自動提交表單 (包含 strRqXML)
    // 方法內部會自動計算 CAVALUE: MD5(STOREID+ORDERNUMBER+AMOUNT+LANGUAGE+CUBKEY)
    echo "<h3>正在導向國泰世華安全支付頁面...</h3>";
    echo $paymentHelper->generatePaymentForm($payData);

} catch (\Exception $e) {
    // 異常處理邏輯
    $logger = new \Log('payment_error.log');
    $logger->write('CUB EPOS Error: ' . $e->getMessage());
    echo "支付初始化失敗，請稍後再試。";
}
```

---

### 技術與產品面深度解析

從 Principal Engineer 與產品經理的視角，這份呼叫範例處理了以下關鍵細節：

1.  **訂單編號規範化**：
    *   範例 JSON 中的 `order_no` 為 `TX3M7QTLAXXN_3MKTL`。雖然底線在許多系統中是合法的，但在國泰世華的規格文件中明確定義 `ORDERNUMBER` 為 `[A-Z,0-9]`。
    *   我在呼叫範例中加入了 `preg_replace` 過濾非英數字元，這是為了避免銀行端回傳 `L100` (資料格式有誤)。

2.  **CAVALUE 雜湊完整性**：
    *   Helper 內部必須嚴格遵守 `STOREID + ORDERNUMBER + AMOUNT + LANGUAGE + CUBKEY` 的字串串接順序進行 MD5 加密。
    *   如果是分期交易（`MSGID: TRS0005`），則必須額外加入 `PERIODNUMBER` 欄位參與雜湊。

3.  **環境限制與維護**：
    *   **IP 綁定**：在執行此程式碼前，必須確認 Web Server 的公網 IP 已提供給銀行設定。
    *   **批次維護**：產品設計上應避開每日 `00:00 - 01:00` 的銀行結帳批次時間。在此期間呼叫 API 常會收到 `M011` (交易逾時)。

4.  **安全通訊**：
    *   程式執行環境需確認支援 `TLS 1.1` 或 `TLS 1.2` 協議。
    *   銀行端接收頁面的回應必須在 **15 秒內** 完成，且回傳的 `RETURL` 不得包含 `&` 符號。
  

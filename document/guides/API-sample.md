# API 範例

## 會員

### 登出
#### 流程說明
會員登出。

    網址：/api/member/logout

#### 回傳參數
+ **code**: (int)

#### 回傳結果
```json
// sample
// success
// normal 
{"code":0}
// fail
{"code":6100}
```

### 取得使用者狀態
#### 流程說明
會員登出。

    網址：/api/member/status

#### 回傳參數
+ **code**: (int)
+ **data**: (object)
    + **isLogined**: (int) user is login
    + **user**: (object) user data
        + **nickname** (string) user nickname
        + **account** (string) user account
        + **avatar** (string) user img
    
   
#### 回傳結果
```json
// sample
// success
// not login
{
  "code": 1,
  "data": {
    "isLogin": 0
  }
}
// normal is login
{
    "code": 1,
    "data": {
        "isLogined": 1,
        "user": {
            "account": "shuaib25@gmail.com",
            "avatar": "https://www.gravatar.com/avatar/c4e919002494d5e124c544e99e073308.jpg?s=64",
            "nickname": "暱稱"
        }
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}

// fail
```

## 聯絡我們

### 流程說明
透過 api 建立聯絡我們   

    網址：/contact/set

### 傳入 POST 參數
+ **nickname**: (string) 姓名
+ **email**: (string) Email
+ **phone**: (int) 電話 *option*
+ **message**: (string) 訊息內容

### 回傳參數
+ **code**: (string)  
+ **data**: (string) 使用者名稱  

### 回傳結果
```json
// sample
// success
{
    "code":0,
    "data":"\u73a9\u7c21\u5831"
}
// fail
{"code":8100}
{"code":9100}

```
## 錯誤清單

### 內部錯誤
+ **9100**: 新增失敗
+ **9101**: 修改失敗
+ **9102**: 刪除失敗
+ **9103**: 鍵值重複
+ **9104**: 遮罩尚未解鎖

+ **9200**: 檔案寫入失敗
+ **9201**: 檔案讀取失敗

### 輸入錯誤
+ **8100**: 缺少參數
+ **8101**: 參數值錯誤

+ **8200**: 驗證碼錯誤
+ **8201**: 密碼驗證失敗
+ **8202**: 電子郵件驗證失敗
+ **8203**: 電子郵件重複
+ ~~**8204**: 帳號 或 密碼錯誤~~
+ **8205**: 密碼格式錯誤
+ **8206**: 密碼與確認密碼不相同
+ **8207**: 帳號重複
+ **8208**: 帳號格式錯誤
+ **8209**: 電子郵件不存在
+ **8210**: slug 重複
+ **8211**: slug 禁止修改
+ **8212**: 你曾經用過這個密碼，請改用其他密碼
+ **8213**: 你最近用過這個密碼，請改用其他密碼

+ **8300**: 檔案格式錯誤
+ **8301**: 檔案大小錯誤

+ **8401**: 標題過長
+ **8402**: 類型錯誤
+ **8403**: 勾選項目過多
+ **8404**: 描述過長

+ **8501**: miss HTTP_CONTENT_DISPOSITION
+ **8502**: 類型錯誤

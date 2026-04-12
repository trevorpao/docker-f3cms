### 第 24 輪討論結果
1. 本輪正式完成 Step 3 的最小 runtime implementation 與 Docker 驗證：`www/tests/smoke/event_rule_engine/member_seen_task_done_reward.php` 已確認第一次寫入 `member_seen` 後，會建立唯一 truth row、把對應 task 轉為 `Done`，並在同一條 transaction 內完成 100 點 reward 入帳與 task / manaccount log 寫入。
2. 為了讓第一個 concrete scenario 不破壞 generic baseline，本輪同步補上 `MEMBER_SEEN_TARGET` evaluator、`member_seen_targets` context preload、`kDuty::completeTasksForSeenTarget()`、`fTask::markDone()`、`fManaccount::addPointsForMember()` 等 module-owned helper，並重新驗證 `basic_or_rule.php`、`member_context_preload.php`、`member_register_task_create.php` 都仍可通過。
3. 本輪中途抓到兩個實際 drift：其一是 `RuleParser` 在 leaf AST 中漏掉 `row_id`，已修正為完整保留 `MEMBER_SEEN_TARGET` 所需欄位；其二是 live test DB 殘留舊 duty 導致 reward 被重複發放，因此最後把 Step 3 smoke 改成會先清理自身 slug 前綴的舊測試資料，讓 Docker 驗證回到可重跑的 deterministic 基線。
4. 到這一步為止，`Member::Register -> task create -> member_seen -> task done + 100 點 reward` 已不再只是 spec，而是已有 live `target_db` runtime evidence；下一輪承接點應回到 `check` / review，判斷是否還要把 helper baseline 再收斂進真實 `rPress -> fMember` reaction path，或可直接進入 `(Optimization)` 前的收尾整理。

### 第 23 輪討論結果
1. 本輪沒有直接開始寫 Step 3 business logic，而是先清除其唯一明確 blocker：依 `.env` 指向的 Docker MariaDB，把 `document/sql/260412.sql` 套用到 live `target_db`。
2. 套用後已使用 `SHOW CREATE TABLE tbl_member_seen` 驗證 live schema，確認 `target/source varchar(32)`、`uniq_member_target_row`、`idx_target_row_member`、`idx_member_insert_ts` 等結構與 spec 一致；因此 repo schema 與 live schema 在 Step 3 前置上已重新對齊。
3. 這也代表前一輪文件中的 drift 已被關閉：`check.md` 不應再標示「live target_db 尚未套用新增的 tbl_member_seen DDL」，而應把下一步直接推進到 `member_seen -> task done + 100 點 reward transaction` 的最小 runtime 實作與 smoke。
4. 到這一步為止，目前 stage 仍屬 `(done)`，但 next step 已不再是 schema 套用，而是正式進入 Step 3 implementation。

### 第 22 輪討論結果
1. 本輪正式完成 Step 2 的最小 runtime 路徑，新增 `kDuty::createTasksForTrigger()`，讓上層可用 `Member::Register` 這類 trigger code 掃描 enabled duty claim，並只對含有 `task_template` 的定義建立 task。
2. 本輪同步補上 `fDuty::enabledClaimRows()` 與 `fTask::createForDutyAndMember()`；前者提供最小 duty claim 掃描入口，後者負責建立 `tbl_task` runtime row、寫入 `tbl_task_log` 的 `MEMBER_REGISTER_TRIGGER` audit，並透過 `uniq_duty_member` 維持 idempotent，不因重複 trigger 產生第二筆 task。
3. 本輪新增 Docker DB-backed smoke `www/tests/smoke/event_rule_engine/member_register_task_create.php`，實際驗證第一次 `Member::Register` trigger 會建立 `New` task、第二次 trigger 只會回用既有 task、且 task log 只留下單一建立紀錄；因此 Step 2 已不再只是 plan，而是有 live `target_db` runtime evidence。
4. 到這一步為止，下一輪最小工作已收斂為 Step 3：先把 repo 內已定義的 `tbl_member_seen` DDL 套用到 Docker live `target_db`，再實作 `member_seen -> task done + 100 點 reward` 的同 transaction 路徑。

### 第 21 輪討論結果
1. 本輪進一步收斂 `tbl_member_seen` 的時間語意：既然 first-hit truth 的成立時間就是後端 insert 當下，那 `first_seen_ts` 與 `insert_ts` 不應並存，否則只是為了假想的補寫 / 匯入情境保留重複欄位，屬於過度設計。
2. 因此本輪已把 schema / spec 改成只保留單一 `insert_ts`，讓它同時表示 first-hit truth 的成立時間；對目前業務語意而言，這已足夠，也更符合「第一次達標就永遠成立」的簡潔設計。
3. 本輪沒有改變既有 stage 與 next step：Step 2 仍是下一個最小工作，也就是 `Member::Register` 觸發後建立 task 的最小路徑與 smoke。

### 第 20 輪討論結果
1. 本輪補充確認 `tbl_member_seen.first_seen_ts` 的來源必須由後端決定，而不是信任前端回傳的事件時間；因為這個欄位代表的是 member-owned truth 的成立時間，應由真正執行 first-hit write 的 service 定錨。
2. 本輪同時刪除 `tbl_member_seen.threshold` 的規劃：對 seen 這張表而言，「第一次達標就永遠成立」才是核心語意，後續不再需要在 truth table 區分 70% seen 或 80% seen 這類商業邏輯版本。
3. `target` 與 `source` 的長度也在本輪收斂為 32，因為它們只承擔內容類型與事件入口的識別用途，不需要保留過大的欄位寬度。
4. 本輪屬於 schema baseline 微調，尚未改變當前承接點：下一輪最小工作仍是 Step 2，也就是 `Member::Register` 觸發後建立 task 的最小路徑與 smoke。

### 第 19 輪討論結果
1. 本輪正式從 `plan` 進入 `(done)` 的 Step 1，新增 concrete duty definition fixture `www/tests/fixtures/event_rule_engine/member_register_press_seen_claim.json`，內容即 `Member::Register` trigger 與 `task_template.factor/reward` 的最小 contract。
2. 本輪同步新增 DB-backed smoke `www/tests/smoke/event_rule_engine/member_register_duty_definition.php`，它會把這份 claim payload 寫入 `tbl_duty.claim`，再透過 `kDuty::loadRulePayload()` 與 `fDuty::oneBySlug()` 驗證 trigger、task_template、reward 等欄位可被正確回讀。
3. 實際驗證已使用 Docker `php-fpm` 容器執行 `/var/www/tests/smoke/event_rule_engine/member_register_duty_definition.php`，結果成功；因此 Step 1 不再只是文件 contract，而是已有 live `target_db` 的最小 runtime evidence。
4. 本輪沒有推進到 Step 2 或 Step 3：尚未實作 `Member::Register` 建 task，也尚未實作 `member_seen -> task done`。其中 Step 3 仍受限於 live `target_db` 尚未套用 `tbl_member_seen` DDL。
5. 到這一步為止，下一輪最小工作已收斂為 Step 2，也就是建立 `Member::Register` 觸發後的 task create 路徑與對應 smoke；完成後再決定是否先套 `tbl_member_seen` DDL，或先把 task create contract 穩定下來。

### 第 18 輪討論結果
1. 本輪承接第 17 輪留下的最小下一步，沒有直接進入 code，而是先把 `tbl_member_seen` 與 duty contract 回讀路徑補成正式 baseline：`plan.md` 現在已明確定義 `tbl_member_seen` 的最小欄位、索引與查詢方式，並把 `tbl_task.duty_id -> tbl_duty.claim.task_template` 的回讀路徑寫清楚。
2. 本輪同步把 daily SQL `document/sql/260412.sql` 補進 `tbl_member_seen` DDL，維持 task 主表只承接 runtime state，不新增 task JSON 載體；這代表 repo 端的 schema baseline 已前進，但 live `target_db` 尚未自動同步這批新增 DDL。
3. 本輪使用 Docker MariaDB 驗證目前 live `target_db` 尚未存在 `tbl_member_seen`；因此接下來若要進入 Step 3 的 runtime 驗證，前置條件是先套用這批新增 SQL，而不能把 repo 內的 daily SQL 誤當成 live schema 已完成。
4. `rPress` 與 `fMember` 的邊界也在本輪正式落檔：`rPress` 只做 seen 事件驗證與協調，實際 first-hit truth 寫入落在 member-owning service；`MEMBER_SEEN_TARGET` evaluator 則只讀 preload 後的 truth，不直接查 press log。
5. 到這一步為止，spec 的最小承接點可從 `plan` 進入 `(done)`：下一輪最小工作應是先做 Step 1，也就是建立 duty definition 與其最小 smoke / fixture；等 `tbl_member_seen` DDL 套用到 live DB 後，再推進 Step 3。

### 第 17 輪討論結果
1. 本輪修正上一輪文件中的兩個具體錯誤：第一，新的 Press seen 早期範例不能覆蓋掉舊的 generic AST 範例，後者仍須保留作為第二個測試標準；第二，`factor` / `reward` 這類 task contract 不應存進 `tbl_task`，而應留在 `tbl_duty` payload 中，由 task 透過 `duty_id` 回讀。
2. 因此 `idea.md` 已改回明確表述「task 主表維持輕量化」：`tbl_task` 只承接 `duty_id`、`member_id`、`status` 等主流程欄位，不新增 JSON 載體，避免 task 主表混入非數字 payload 而影響查詢與效能。
3. 本輪同步把 `plan.md` 與 `check.md` 的驗收口徑改一致：後續要驗證的是 duty 內 `task_template` 是否能承接 `factor` / `reward`，以及 task 是否可透過 `duty_id` 正確回讀 contract，而不是檢查不存在的 task JSON 欄位。
4. 目前 concrete scenario 與 generic AST 兩條路徑的角色已重新分清：前者是第一個 production scenario，後者是第二個測試標準，用來保證 shared engine 的通用性沒有被新情境吃掉。
5. 本輪仍是文件同步，沒有新增程式、SQL 或 smoke；下一輪若要進入實作，應以 `member_seen` 與 duty contract 的讀取 / 連接 baseline 為主，而不是去擴張 `tbl_task` schema。

### 第 16 輪討論結果
1. 本輪不是沿著上一輪的 generic `watched_video_codes` / `exam_scores` baseline 繼續往下寫 code，而是先處理需求方向漂移：使用者已把第一個 production scenario 收斂成 `Member::Register -> duty 建 task -> rPress seen -> fMember 寫 member_seen -> task done + 100 點 reward`，因此原本的 generic prototype 已不足以作為當前承接基準。
2. 本輪已正式確認 `member_seen` 的 ownership：seen 事件入口可以由 `rPress` 提供，但實際寫入必須落在 `fMember`，因為這是 member-owned truth；資料模型採 `target + row_id` 區分所有內容類型，且語意是「第一次達標就永遠成立」。
3. 本輪也正式確認不把 `press_id = 103` 之類的 business 條件硬寫在程式裡；相對地，必須由 duty claim payload 表達 `Member::Register` trigger 與 task_template，再由 duty 內 contract 表達 `factor` 與 `reward`，讓 task done 與加分可以依 `duty_id` 回讀 contract 運作。
4. 因為這個 concrete scenario 已改變了 payload 結構、資料落點與 integration 路徑，舊的 `check` 承接點不再成立；本輪已把 spec 的正式承接點回退到 `plan`，下一輪不應直接補 code，而應先把 `tbl_member_seen`、duty contract 回讀路徑、`rPress` seen endpoint 與 `fMember` 寫入邊界整理成正式 schema / query baseline。
5. 本輪只同步文件，沒有改動程式與 daily SQL；因為目前最重要的是先把新的 source of truth 寫回 spec，避免下一輪仍沿著舊 prototype 誤實作。
6. 最新討論的下一步選項：先只做一件事，補 `tbl_member_seen` 與 duty contract 回讀路徑的 schema / query baseline，並把 `MEMBER_SEEN_TARGET` 的 JSON Payload Contract 寫進 `plan.md`；之後再開始最小實作。

### 第 15 輪討論結果
1. 本輪承接第 14 輪留下的最小下一步，沒有先擴張去建立 `watched_video_codes` / `exam_scores` 的新 baseline，而是先在既有 module 邊界上新增單一上層 evaluation helper：`www/f3cms/modules/Duty/kit.php` 現在提供 `kDuty::evaluateForMember()`，把 `Duty` 的 payload source 與 `Member` 的 context preload 收斂成一個更接近 production 的入口。
2. 這個 helper 的責任仍維持清楚：`Duty` module 負責選擇 `claim` / `factor` 並建立 engine，`Member` module 負責建立 `EventRulePlayerContext`，shared `EventRuleEngine` 只做 evaluate；本輪沒有把 preload 或 writeback 拉回 `libs`，也沒有建立新的假 owning module。
3. 本輪同步新增 DB-backed smoke `www/tests/smoke/event_rule_engine/duty_member_evaluate.php`，它會暫時插入 member、heraldry relation、manaccount 與 duty 測試資料，直接經由 `kDuty::evaluateForMember()` 驗證整條 `Duty payload + Member context + EventRuleEngine` 路徑能在 live DB 中得到 `matched` 結果。
4. 因此 EventRuleEngine 現在不只已有分段 adapter，還已有第一個上層 orchestration 入口可把 payload source 與 context preload 串在一起；目前剩餘缺口更明確地收斂為 `watched_video_codes` / `exam_scores` 的真實來源與後續 writeback / reaction integration，而不是「如何把兩段最小 adapter 串起來」。
5. 本輪沒有修改 schema，也沒有追加新的 daily SQL；因為這次只是在既有 baseline 上補 orchestration helper 與 smoke，沒有新增資料表或 DBA 執行需求。
6. 最新討論的下一步選項：先只做一件事，盤點或建立 `watched_video_codes` / `exam_scores` 的真實 source baseline，讓 `contextOverrides` 不再只是暫時 bridge；之後再考慮 task / account writeback 的上層 integration。

### 第 14 輪討論結果
1. 本輪直接承接第 13 輪留下的最小下一步，已補上第一條 player context 的 module-facing preload contract：新增 `www/f3cms/modules/Member/kit.php`，由 `kMember::preloadEventRuleContext()` / `createEventRuleContext()` 組裝 `member_id`、`heraldry_codes`、`account_balance`、`account_status` 等 context 欄位，再交給 shared `EventRuleEngine` 使用。
2. 這次 context preload 刻意只承接目前 repo 與 live DB 已有 baseline 的資料來源：`Member`、`tbl_member_heraldry`、`tbl_heraldry` 與 `Manaccount`。對於目前 repo 尚未提供真實 baseline 的 `watched_video_codes` 與 `exam_scores`，本輪明確保留 override 注入口，而沒有憑空發明新的 table-backed source。
3. 為了讓 relation table 不只停留在 DDL，本輪也在 `www/f3cms/modules/Member/feed.php` 新增 `heraldryCodesByMemberId()`，正式把 `tbl_member_heraldry` -> `tbl_heraldry.slug` 的 preload 路徑落回 `Member` module 邊界，而不是讓 shared `libs` 或 smoke 直接手寫 join。
4. 本輪同步新增 DB-backed smoke `www/tests/smoke/event_rule_engine/member_context_preload.php`，它會暫時插入 `tbl_member`、`tbl_heraldry`、`tbl_member_heraldry`、`tbl_manaccount` 與 `tbl_duty` 測試資料，透過 `kMember` 組 context、`kDuty` 建 engine，驗證 badge path 能在 live DB 中得到 `matched` 結果，並確認帳戶與紋章資料確實有被 preload。
5. 因此 EventRuleEngine 現在已具備兩段可執行的最小 integration 基線：`Duty` 負責真實 payload source，`Member` 負責第一條 player context preload contract；目前剩餘缺口已收斂為 `watched_video_codes` / `exam_scores` 的真實來源、更多 owning module integration，以及更完整的上層 evaluation / writeback path。
6. 最新討論的下一步選項：先只做一件事，盤點或建立 `watched_video_codes` / `exam_scores` 的真實 source baseline，或在既有 module 邊界上增加單一上層 evaluation helper，把 `Duty payload + Member context` 收斂成更接近 production 的入口。

### 第 13 輪討論結果
1. 本輪直接承接第 12 輪留下的最小下一步，已在 `Duty` module 內建立第一條 module-facing payload source integration path：新增 `www/f3cms/modules/Duty/kit.php`，由 `kDuty::createRuleEngine()` 從 `tbl_duty.claim` / `factor` 載入 payload，再交給 shared `EventRuleEngine`。
2. 這次刻意沒有把 table-backed 載入責任塞回 `www/f3cms/libs/EventRuleEngine.php`；shared engine 仍只負責 parser、validator、registry 與 traversal，而 `Duty` module 自己負責從 `tbl_duty` 提供真實 payload，符合前幾輪已固定的 module / libs 邊界。
3. 本輪同時新增 DB-backed smoke `www/tests/smoke/event_rule_engine/duty_claim_loader.php`，它會在 live DB 中暫時插入一筆 `tbl_duty` 測試資料，透過 `kDuty::createRuleEngine()` 建立 engine，驗證 claim path 能得到 `matched` 結果後再清理該筆 duty row。
4. 因此 EventRuleEngine 第一條真實 payload source 路徑已不再只是 spec 規劃，而是已有可執行程式與 smoke 驗證承接；目前剩餘缺口已收斂為 player context 的真實組裝、更多 owning module integration，以及更多 edge-case smoke。
5. 本輪沒有擴張到 `Task`、`Manaccount` 或 `Member` 的 context preload，也沒有新增 reaction；範圍刻意只落在 `Duty` -> payload -> `EventRuleEngine` 這條最小 path。
6. 最新討論的下一步選項：先只做一件事，補 player context 的 module-facing preload contract，例如從 `Member`、`Manaccount`、`tbl_member_heraldry` 組裝出可直接餵給 engine 的 context payload，再擴充第二支 smoke。

### 第 12 輪討論結果
1. 本輪直接承接第 11 輪留下的下一步，先補上 `member_id` 的主表 / module 邊界：已在 `document/sql/260412.sql` 追加 `tbl_member`，並建立 `www/f3cms/modules/Member/feed.php`，讓 `member_id` 不再只是 spec 中的抽象外鍵。
2. 本輪也正式將 `document/sql/260412.sql` 套用到 Docker `mariadb` 的 `target_db`；依 `.env` 內的連線資訊實際驗證後，`tbl_member`、`tbl_duty`、`tbl_task`、`tbl_task_log`、`tbl_heraldry`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_manaccount_log` 均已存在於 live DB。
3. 因此前一輪 `check` 所記錄的兩個前置阻塞已在本輪解除：repo 內不再缺少最小 schema / module baseline，Docker live DB 也已完成 baseline schema 落地。
4. 這次仍未開始實作真正的 module-facing payload source 與 integration adapter；本輪的角色是把 integration 所需的最小落地基線補齊，讓下一輪可以開始處理 duty / claim / factor 的真實載入與 context 組裝，而不必再猜 schema 或主表邊界。
5. shared EventRuleEngine runtime 與既有 smoke contract 本輪未再變動；本輪驗證重點是 baseline schema 已成功套用，而不是 engine 行為回歸。
6. 最新討論的下一步選項：先只做一件事，從 `Duty` Feed 載入 `claim` 或 `factor` 的真實 payload，串起第一條 module-facing integration path，再視需要補更多 edge-case smoke。

### 第 11 輪討論結果
1. 本輪承接第 10 輪留下的下一步，直接在目前 repo 內建立最小 schema / module baseline，而不再停留在純阻塞描述：已於 `document/sql/260412.sql` 補上 `tbl_duty`、`tbl_task`、`tbl_task_log`、`tbl_heraldry`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_manaccount_log` 的第一版 DDL。
2. 本輪同時建立三個最小 owning module Feed：`www/f3cms/modules/Duty/feed.php`、`www/f3cms/modules/Task/feed.php`、`www/f3cms/modules/Manaccount/feed.php`，讓後續 payload source、task runtime state 與 manaccount audit 不再完全缺少 module 邊界承接點。
3. 這次 baseline 刻意沒有憑空建立 `Member` 主表或 `Member` module，因為目前 `idea.md` 僅定義 `member_id` 外鍵語意與 `tbl_member_heraldry` relation，尚未定義 `tbl_member` 主表；因此本輪只補到不會違反既有 spec 的最小可落地範圍。
4. 目前 drift 已從「repo 與 live DB 都沒有 baseline」縮小為「repo 已有 baseline，但 Docker / `.env` 指向的 live `target_db` 尚未套用今天的 SQL，且 member 主表邊界仍待明確」；因此 production integration 仍不能假設可直接讀寫這批資料表。
5. 本輪沒有改動 shared EventRuleEngine runtime，也尚未接上真實 payload source；最小有效輸出是先補齊 schema / module baseline，讓下一輪可以在此基礎上討論是否要套用 SQL、如何處理 member 主表，以及哪一條 module-facing integration 應先落地。
6. 最新討論的下一步選項：先只做一件事，決定是否將 `document/sql/260412.sql` 套用到 Docker `target_db`，並同步明確 `member_id` 對應的主表 / module 邊界，再開始第一條真正的 payload source integration。

### 第 10 輪討論結果
1. 本輪承接第 9 輪留下的 `check` 下一步，原本要選一條真實 owning module 路徑實作 integration adapter 與 payload source；但在實際盤點 repo 後，確認目前工作區內尚未出現對應 `tbl_duty`、`tbl_task`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_task_log`、`tbl_manaccount_log` 的 module 實作落點。
2. 本輪同時檢查了目前可見的 schema baseline 與 repo 內 module 程式：`conf/mysql/init.sql` 沒有這批資料表，`www/f3cms/modules/**` 也沒有對應 table-backed module / reaction 可承接 duty、task、member、manaccount 的真實 payload source 與 context preload。
3. 依 FDD / workspace 規則進一步使用 `.env` 內的 MariaDB 連線資訊，在 Docker 容器中的 `target_db` 實際執行 `SHOW TABLES LIKE ...` 驗證；結果 `tbl_duty`、`tbl_task`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_task_log`、`tbl_manaccount_log` 也都不存在於 live DB。
4. 因此目前真正的阻塞點已不再是 EventRuleEngine shared runtime 的位置，而是「此 repo 與其 live DB 都尚未提供可供接線的 owning module / table baseline」；在這個前提未補齊前，若硬做 production integration，反而會重新造出 spec 已明確禁止的假 module 或錯誤邊界。
5. 本輪沒有再修改 shared engine，也沒有新增假的 integration adapter；最小有效輸出是把這個已驗證的阻塞正式回寫到 `plan.md` 與 `check.md`，讓後續承接先補真實 owning module 落點或明確提供承接倉庫，而不是繼續在目前 repo 猜測設計。
6. 這不代表 EventRuleEngine 第一版骨架失效；目前已完成的 parser、validator、registry、traversal 與最小 smoke 仍是穩定基線，只是 `check` 的下一步必須先改成釐清真實 integration target，而不是直接宣告可以接到 duty / task 路徑。
7. 最新討論的下一步選項：先只做一件事，確認 `tbl_duty` / `tbl_task` / `tbl_manaccount` 等 owning module 與 schema 應存在於哪個 repo、分支或後續批次，再回來做 module-facing payload source 與 integration adapter。

### 第 9 輪討論結果
1. 本輪承接第 8 輪後的最新架構修正：`idea.md` 中的 `duty`、`task`、`member`、`manaccount` 實際分屬不同 module，因此上一輪把 runtime 收斂到單一 `www/f3cms/modules/EventRuleEngine/kit.php` 的假設不成立。
2. 依這個 owning-boundary 更正，本輪把 shared pure engine 改回比照 `WorkflowEngine` 的 shared library 形式，將 runtime 入口恢復為 `www/f3cms/libs/EventRuleEngine.php`，並保留 parser、validator、registry、traversal 與 pure evaluator 在 `libs`。
3. 同時，本輪刪除錯建的 `www/f3cms/modules/EventRuleEngine` runtime 入口，並把 smoke 改回直接走 `\F3CMS\EventRuleEngine`，避免繼續暗示存在一個新的 EventRuleEngine owning module。
4. 本輪同步把 `idea.md`、`plan.md` 與 `check.md` 的邊界修正為：table-backed payload source、context preload、reaction integration 與 state writeback 都必須由各 owning module 承接，而 shared engine 只做純規則解析與求值。
5. 這次修正並未改變第一版骨架的最小功能 contract；真正尚未完成的缺口仍是各 owning module 的 integration adapter、payload source 真實承接，以及更多 fixture / edge-case smoke。
6. 最新討論的下一步選項：先只做一件事，選定一條真實 owning module 路徑承接 payload source 與 integration adapter，再視結果擴充更多 rule types 或 smoke。

### 第 8 輪討論結果
1. 本輪承接第 7 輪留下的唯一下一步，已將 parser 以外的 EventRuleEngine 主體從 `www/f3cms/libs` 收斂回 `www/f3cms/modules/EventRuleEngine/kit.php`，把 runtime 入口改為 module-owned Kit，而不再由 `libs` 持有主流程。
2. 本輪同時把 `basic_or_rule.php` smoke 改為走 `kEventRuleEngine`，並移除 `libs` 中舊的 `EventRuleEngine`、validator、registry、evaluator、`PlayerContext` 與 result classes，讓 `libs` 只保留最小 JSON parser。
3. Docker 驗證顯示重構後 contract 仍維持穩定：`matched`、`not_matched`、`invalid_payload`、`missing_evaluator` 與 `context_error` 五條最小路徑都仍可通過，代表這次收斂沒有引入新的 runtime drift。
4. 因此第 7 輪辨識出的 architectural drift 已在本輪完成第一階段修正；目前 feature 仍位於 `check`，但下一輪的最小優先事項已改為 module / reaction integration adapter、payload source 承接，以及更多 fixture / edge-case smoke，而不再是 `libs` 邊界修正本身。
5. 本輪已同步更新 `plan.md` 與 `check.md`，把「parser 以外主體收斂回 module」改為已完成，避免後續承接時重複處理同一個 drift。
6. 最新討論的下一步選項：先只做一件事，補 module / reaction integration adapter 與 payload source 真實承接，再決定是否同輪擴充更多 edge-case smoke / fixture。

### 第 7 輪討論結果
1. 本輪承接目前 `check` 階段的架構邊界盤點，正式確認一條 F3CMS 規則：只要需求牽涉實體資料表，就必須以 module / reaction / service 承接主體實作，而不是把業務主流程長期放在 `libs`。
2. 依這條規則，EventRuleEngine 目前的第一版骨架雖已能通過最小 smoke，但現有 `www/f3cms/libs` 內的 `EventRuleEngine`、registry、evaluator、`PlayerContext` 等主體責任已超出 `libs` 應承接的範圍，因此這裡已形成新的 architectural drift。
3. 本輪同步收斂 `libs` 的正確邊界：`libs` 只保留最小的 EventRuleEngine JSON parser；凡是依賴 `tbl_duty`、`tbl_task`、`tbl_task_log`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_manaccount_log` 的 payload 載入、context preload、evaluator 組裝、module integration 與狀態寫回，都應回到 module 側。
4. 因此目前 feature 仍位於 `check`，但下一輪 `(done)` 的最小優先事項已改為先修正這個架構漂移，而不是直接沿著 `libs` 版骨架繼續補更多 integration 或 fixture。
5. 本輪尚未動程式，只先把這個新架構規則回寫到 `idea.md`、`plan.md`、`check.md` 與 `history.md`，避免後續承接時繼續把錯的責任邊界當成既定方向。
6. 最新討論的下一步選項：先只做一件事，把 parser 以外的 EventRuleEngine 主體從 `www/f3cms/libs` 收斂回 module 邊界，再決定是否同輪補 module integration adapter、payload source 承接與更多 edge-case smoke。

### 第 6 輪討論結果
1. 本輪承接第 5 輪留下的唯一下一步，已完成 EventRuleEngine 第一版最小程式骨架實作，新增了 `EventRuleEngine`、payload validator、parser、registry、`PlayerContext`、evaluation result 結構與三個第一版 evaluator，讓 Stage 3 / Stage 4 的規劃首次落成可執行程式。
2. 本輪同時新增 Docker 可跑的 `event_rule_engine_smoke.php`，並已使用專案既有容器環境完成驗證；目前 canonical 主路徑已切到 `www/tests/smoke/event_rule_engine/basic_or_rule.php`，且後續已在 TestMode 收尾輪完成 legacy wrapper retirement；smoke 已覆蓋 `matched`、`not_matched`、`invalid_payload`、`missing_evaluator` 與 `context_error` 五條最小驗證路徑。
3. 驗證結果顯示目前第一版骨架與 spec 收斂方向一致：validator 可阻擋非法 payload、registry 缺漏會 fail-closed、context 缺值不會默默補查資料，且三個第一版 rule types 已可被 traversal 正常 dispatch。
4. 因此目前 feature 已不再只是純 `plan` 狀態，而是已完成一輪 `(done)` 的最小骨架落地，接下來較合理的承接點是 `check`：盤點這輪骨架實作已完成與仍未完成的 integration / fixture 缺口。
5. 本輪當時尚未辨識到 `libs` 與 module 邊界的架構漂移；這個缺口已在第 7 輪補充修正。
6. 最新討論的下一步選項：先只做一件事，承接 `check`，確認第一版骨架的完成邊界與缺口，再決定下一輪 `(done)` 是補 module integration adapter、payload source 承接，還是補更多 fixture / edge-case smoke。

### 第 5 輪討論結果
1. 本輪承接第 4 輪留下的唯一下一步，已將 EventRuleEngine 的 Stage 4 正式收斂為第一版驗收與 fallback 基線，不再只停留在「需要 check」的抽象描述。
2. 本輪把第一版 acceptance 切成 payload 結構、engine 判斷、context 缺值 / preload、registry / integration 失配四個驗收切面，並明確規定非法 payload、缺 evaluator 與 context error 都必須 fail-closed，而不能偽裝成一般 business false。
3. 本輪也正式收斂了 context 缺值時的 default 原則、registry 與 validator 白名單不一致時的處置規則，以及 module integration 不可退化的邊界，避免後續實作把補查 DB、寫 log 或直接發獎勵塞回 engine。
4. 到這一輪為止，Stage 1 到 Stage 4 都已具備正式輸出：DSL contract、schema / query baseline、engine 骨架、驗收與 fallback 基線均已收斂，因此目前 feature 仍位於 `plan` 階段，但已接近可進入 `(done)` 的切入點。
5. 目前仍沒有程式 / 文件 drift，因為這個 feature 尚未有正式實作；本輪也不需要 Docker 驗證，因為尚未進入 smoke 或 runtime verification。
6. 最新討論的下一步選項：先只做一件事，承接 `(done)` 的最小實作切入點，先實作 payload validator、parser、registry 與 RuleEngine traversal 的第一版骨架，再決定同輪是否補三個 rule types 的最小 smoke / fixture。

### 第 4 輪討論結果
1. 本輪先辨識並處理文件 drift：`idea.md` 仍保留 PostgreSQL `JSONB` / GIN 與舊版 `tbl_manaccount_log` 欄位描述，但 `plan.md` / `history.md` 已收斂為 MariaDB 10.4.6、`parent_id` 型 module-owned log 與非全域 JSON 反查基線，因此本輪先把 `idea.md` 同步到目前穩定結論。
2. 在 drift 修正後，本輪承接第 3 輪留下的唯一下一步，已將 Stage 3 正式收斂成第一版 engine 執行骨架，包含 `DutyRuleLoader -> PayloadValidator -> RuleParser -> RuleEngine -> EvaluationResult` 的最小路徑。
3. 本輪同時明確化 evaluator registry contract、`PlayerContext` preload contract、validator / parser / engine 邊界，以及 fail-closed 的錯誤分類與回傳策略，避免後續實作時再把 DB query、context preload 或 task / account 寫回混回 RuleEngine 內部。
4. 另外，本輪也比照既有 WorkflowEngine 與 Press reaction 的責任切分，正式記錄 EventRuleEngine 應維持「module / reaction 準備 context，engine 純判斷，狀態寫回與 log 仍由 module 層負責」的整合邊界。
5. 目前沒有看到程式 / 文件 drift，因為此 feature 仍未進入實作；本輪也不需要 Docker 驗證，現階段仍屬 `plan` 階段下的 spec-only 收斂工作。
6. 最新討論的下一步選項：先只做一件事，承接 Stage 4，把 fail-closed 驗收點、context preload 缺值情境、registry 缺漏情境與 fallback 原則整理成更明確的 check / risk baseline，作為後續進入 `(done)` 前的最後規劃整理。

### 第 3 輪討論結果
1. 本輪承接第 2 輪留下的唯一下一步，已將 EventRuleEngine 的 Stage 2 正式收斂成 MariaDB 10.4.6 相容的 schema / index / query baseline，而不再停留於 `idea.md` 的抽象 schema 敘述。
2. 本輪明確確認第一版 DB 基線應以 MariaDB 行為為準，因此 `tbl_duty.claim`、`factor`、`next` 雖維持 JSON contract 語意，但規劃上不依賴 PostgreSQL `JSONB`、GIN 或通用 generated column 索引能力。
3. 本輪已將 `tbl_task`、`tbl_task_log`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_manaccount_log` 的最小欄位與最小索引正式寫入 `plan.md`，並同步將 reviewer 稽核查詢與 log 成長下的索引 / 歸檔風險一併明文化。
4. 其中 `tbl_task_log` 與 `tbl_manaccount_log` 均採 module-owned log 模式，使用 `parent_id` 指回主表，這樣可與 F3CMS 既有 `tbl_press_log` 慣例保持一致，避免重新引入共享 workflow runtime table 或混亂的 log 邊界。
5. 因此目前 feature 仍位於 `plan` 階段，但已從「只有 DSL 與資料邊界」推進到「已有第一版可實作的 DB baseline」；目前尚未進入 `(done)`，也尚未需要 Docker 驗證。
6. 最新討論的下一步選項：先只做一件事，承接 Stage 3，將 RuleEngine、Evaluator registry、PlayerContext preload contract、validator / parser 邊界與錯誤回傳策略收斂成最小可實作骨架。

### 第 2 輪討論結果
1. 本輪直接承接第 1 輪留下的唯一下一步，已將 `idea.md` 內既有的 JSON DSL、rule types、RuleEngine / PlayerContext / Evaluator 邊界、payload validator 與資料承接原則正式回寫到 `plan.md` 的 Stage 1 收斂結果。
2. 本輪同時把 `check.md` 中 Stage 1 對應的驗收項更新為已完成，包含 DSL contract、第一版 rule types、責任邊界、JSON 欄位型態、relation table、task / account log 最小欄位、短路求值、防禦策略與 JSON 全域反查為非範圍等內容。
3. 目前沒有看到文件與程式碼的 drift，因為此 feature 仍未進入實作階段；當前缺口集中在 schema / index / query 規劃，而不是程式回寫或 Docker 驗證。
4. 因此當前 flow stage 已可由單純 `idea` 承接點推進到 `plan` 階段：核心模型與邊界已有第一版正式輸出，但尚未進入 `(done)`。
5. 本輪不處理程式碼，也不做 Docker 驗證；本輪只完成 Stage 1 的 spec 收斂與 checklist 同步。
6. 最新討論的下一步選項：先只做一件事，承接 Stage 2，把 JSON 欄位、relation table、task / account log、history / reviewer 查詢與索引風險整理成更明確的 schema / query 規劃。

### 第 1 輪討論結果
1. 目前 `EventRuleEngine` spec 資料夾僅有 `idea.md`，尚未建立 `history.md`、`plan.md`、`check.md`，因此還不存在正式的 FDD 承接鏈。
2. 依 `idea.md` 現況判斷，這個 feature 已有相對完整的問題定義、目標、範圍、資料影響、限制與風險，但尚未正式拆成可執行 stage，也尚未形成驗收清單。
3. 因此當前 flow stage 應先落在 `idea` 後、準備進入 `(discuss)` / `plan` 的承接點，而不是直接進入實作或驗收。
4. 本輪不處理程式碼，也不做 Docker 驗證；本輪只完成 FDD 基本文件初始化，讓後續討論、規劃與 review 有正式落點。
5. 最新討論的下一步選項：先只做一件事，根據目前 `idea.md` 已收斂的內容，把 `plan.md` 補成第一版可執行 stage，並在 `check.md` 建立對應的驗收與風險清單。
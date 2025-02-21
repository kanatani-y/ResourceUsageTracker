<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?><?= isset($reservation) ? '予約編集' : '新規予約' ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check"></i> <?= isset($reservation) ? '予約編集' : '新規予約' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= isset($reservation) ? route_to('reservations.update', $reservation['id']) : route_to('reservations.store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="resource_id" class="form-label">リソース</label>
                            <select name="resource_id" id="resource_id" class="form-select" required <?= isset($reservation) ? 'disabled' : '' ?>>
                                <option value="">リソースを選択</option>
                                <?php foreach ($resources as $res): ?>
                                    <option value="<?= esc($res['id']) ?>"
                                        <?= ($res['id'] == $resource_id) ? 'selected' : '' ?>>
                                        <?= esc($res['name']) ?> (<?= esc($res['hostname'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- リソース変更不可のため、編集時は hidden input で `resource_id` を送信 -->
                            <?php if (isset($reservation)): ?>
                                <input type="hidden" name="resource_id" value="<?= esc($reservation['resource_id']) ?>">
                            <?php endif; ?>
                        </div>

                        <!-- アカウント選択 -->
                        <div class="mb-3">
                            <label for="account_id" class="form-label">アカウント</label>
                            <select name="account_id" id="account_id" class="form-select" <?= (isset($reservation) && $reservation['account_id'] == -1) ? '' : 'required' ?>>
                                <option value="">アカウントを選択</option>
                                <?php if (!empty($accounts) && is_array($accounts)): ?>
                                    <?php foreach ($accounts as $account): ?>
                                        <?php if (isset($account['id'])): // 'id' キーが存在するかチェック ?>
                                            <option value="<?= esc($account['id']) ?>"
                                                <?= ($account['id'] == ($reservation['account_id'] ?? $account_id)) ? 'selected' : '' ?>>
                                                <?= esc($account['username'] ?? 'なし') ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php
                        // 現在の日付（YYYY-MM-DD）
                        $today = date('Y-m-d');

                        // 時間の選択肢（9:00～18:00）
                        $timeOptions = [];
                        for ($hour = 9; $hour <= 18; $hour++) {
                            $timeOptions[] = sprintf('%02d:00', $hour);
                        }

                        // 現在の時刻から開始時刻を決定（30分未満切り捨て、30分以上切り上げ）
                        $currentHour = (int) date('H');
                        $currentMinutes = (int) date('i');
                        $defaultStartHour = max(9, ($currentMinutes < 30 ? $currentHour : $currentHour + 1));
                        $defaultStartHour = min($defaultStartHour, 18); // 上限を 18:00 に制限
                        $defaultStartTime = sprintf('%02d:00', $defaultStartHour);

                        // 終了時刻（開始時刻の1時間後）
                        $defaultEndHour = min($defaultStartHour + 1, 24);
                        $defaultEndTime = sprintf('%02d:00', $defaultEndHour);
                        ?>

                        <div class="mb-3">
                            <label for="reservation_date" class="form-label">予約日</label>
                            <input type="date" class="form-control" id="reservation_date" name="reservation_date"
                                value="<?= old('reservation_date', $selectedDate) ?>"
                                min="<?= date('Y-m-d') ?>" required>
                            <div id="dateError" class="text-danger mt-1" style="display: none;">
                                過去の日付は選択できません。
                            </div>
                        </div>

                        <div class="row">
                            <!-- 開始時刻 -->
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">開始時刻</label>
                                <select class="form-select" id="start_time" name="start_time" required>
                                    <?php foreach ($timeOptions as $option): ?>
                                        <option value="<?= $option ?>" <?= ($option === $time) ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- 終了時刻 -->
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">終了時刻</label>
                                <select class="form-select" id="end_time" name="end_time" required>
                                    <?php foreach ($timeOptions as $option): ?>
                                        <option value="<?= $option ?>" <?= ($option === $end_time) ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if (auth()->user()->inGroup('admin')): ?>
                            <div class="row">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">予約者</label>
                                    <select name="user_id" id="user_id" class="form-select">
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= esc($user->id) ?>"
                                                <?= ($user->id == ($reservation['user_id'] ?? auth()->user()->id)) ? 'selected' : '' ?>>
                                                <?= esc($user->fullname ?? $user->username) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="user_id" value="<?= auth()->user()->id ?>">
                            <p class="form-control-plaintext"><?= esc(auth()->user()->fullname ?? auth()->user()->username) ?></p>
                        <?php endif; ?>

                        <div class="row">
                            <!-- 使用目的 -->
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">使用目的</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" maxlength="100" required><?= old('purpose', $reservation['purpose'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- 予約ボタン -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('reservations') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 戻る
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> <?= isset($reservation) ? '更新' : '登録' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- 右側に予約状況を表示 -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> 予約状況
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush reservation-timeline">
                        <li class="list-group-item text-muted text-center">対象の予約はありません</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // **グローバルスコープに定義**
    const reservationDateInput = document.getElementById("reservation_date");
    const reservationList = document.querySelector(".reservation-timeline");
    const accounts = <?= json_encode($accounts, JSON_HEX_TAG) ?>;
    const resourceSelect = document.getElementById("resource_id");
    const accountSelect = document.getElementById("account_id");
    const selectedAccountId = "<?= esc($account_id ?? '') ?>";


    document.addEventListener("DOMContentLoaded", function () {
        // **アカウントリストを更新**
        function updateAccountSelection() {
            const resourceId = resourceSelect.value;
            accountSelect.innerHTML = '<option value="">アカウントを選択</option>';

            console.log("Selected resourceId:", resourceId);
            console.log("Available accounts:", accounts);
            if (accounts[resourceId] && accounts[resourceId].length > 0) {
                accountSelect.disabled = false;
                accountSelect.setAttribute('required', 'required'); // 必須にする
                accounts[resourceId].forEach(account => {
                    let option = document.createElement('option');
                    option.value = account.id;
                    option.textContent = account.username;
                    accountSelect.appendChild(option);
                    // **初期選択を適用**
                    if (String(option.value) === String(selectedAccountId)) {
                        option.selected = true;
                    }
                    accountSelect.appendChild(option);
                });
            } else {
                accountSelect.innerHTML = '<option value="-1" selected>なし</option>';
                accountSelect.disabled = true;
                accountSelect.removeAttribute('required'); // 必須を解除
            }
        }

        // **ページ読み込み時にアカウントリストを適用**
        setTimeout(updateAccountSelection, 100); // **短い遅延を設定して確実に適用**

        // **リソース変更時にアカウントリストを更新**
        resourceSelect.addEventListener("change", updateAccountSelection);

        // **予約情報を取得**
        function fetchReservations() {
            const selectedDate = reservationDateInput.value;
            const resourceId = resourceSelect.value;
            let accountId = accountSelect.value;

            if (!selectedDate || !resourceId) return;

            let url = `<?= site_url('reservations/getReservations') ?>?date=${selectedDate}&resource_id=${resourceId}`;
            if (accountId && accountId !== "-1") {
                url += `&account_id=${accountId}`;
            }

            reservationList.innerHTML = '<li class="list-group-item text-muted text-center">予約情報を取得中...</li>';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    reservationList.innerHTML = "";
                    if (data.length > 0) {
                        let lastDate = "";
                        data.forEach(res => {
                            let reservationDate = res.start_time.split(" ")[0];
                            let startTime = res.start_time.split(" ")[1].slice(0, 5);
                            let endTime = res.end_time.split(" ")[1].slice(0, 5);
                            let accountName = res.account_name ?? "なし";

                            if (lastDate !== reservationDate) {
                                lastDate = reservationDate;
                            }

                            reservationList.innerHTML += `
                                <li class="list-group-item">
                                    <span class="badge bg-primary">${startTime} - ${endTime}</span>
                                    <span class="reservation-name me-2">${res.user_name ?? '不明'}</span>
                                    <p class="mb-1"><small><i class="bi bi-key"></i> アカウント：${accountName}</small></p>
                                    <p class="mb-1"><small><i class="bi bi-briefcase"></i> 使用目的：${res.purpose ?? 'なし'}</small></p>
                                </li>
                            `;
                        });
                    } else {
                        reservationList.innerHTML = '<li class="list-group-item text-muted text-center">対象の予約はありません</li>';
                    }
                })
                .catch(error => {
                    console.error("予約情報の取得に失敗しました:", error);
                    reservationList.innerHTML = '<li class="list-group-item text-danger text-center">予約情報の取得に失敗しました</li>';
                });
        }

        // **ページ読み込み時に予約情報を取得**
        setTimeout(fetchReservations, 200); // **アカウントリスト更新後に予約情報を取得**

        // **予約日・リソース・アカウントが変更されたら、予約状況を更新**
        reservationDateInput.addEventListener("change", fetchReservations);
        resourceSelect.addEventListener("change", fetchReservations);
        accountSelect.addEventListener("change", fetchReservations);
    });

    document.getElementById("start_time").addEventListener("change", function() {
        let startTime = parseInt(this.value.split(":")[0]);
        let endTimeSelect = document.getElementById("end_time");

        // 終了時刻の選択肢を更新
        let options = endTimeSelect.options;
        for (let i = 0; i < options.length; i++) {
            let optionTime = parseInt(options[i].value.split(":")[0]);
            options[i].disabled = optionTime <= startTime;
        }

        // 終了時刻を開始時刻の1時間後に変更
        let newEndTime = Math.min(startTime + 1, 24) + ":00";
        endTimeSelect.value = newEndTime;
    });
</script>
<style>
.reservation-timeline .list-group-item {
    border-left: 4px solid #007bff;
    padding: 10px 15px;
    margin-bottom: 5px;
}
.reservation-timeline .badge {
    font-size: 14px;
    padding: 5px 10px;
}
.reservation-timeline {
    font-weight: bold;
    color: #333;
    min-width: 90px;
    text-align: left;
    display: inline-block;
}
.reservation-name {
    font-weight: bold;
    color: #333;
    min-width: 90px;
    text-align: left;
    display: inline-block;
    padding-left: 5px;
}
</style>
<?= $this->endSection() ?>

<?= $this->extend('layouts/layout') ?>

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
                    <form action="<?= isset($reservation) ? route_to('reservation.update', $reservation['id']) : route_to('reservation.store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="resource_id" class="form-label">リソース</label>
                            <select name="resource_id" id="resource_id" class="form-select" required>
                                <option value="">リソースを選択</option>
                                <?php foreach ($resources as $res): ?>
                                    <option value="<?= esc($res['id']) ?>" <?= (isset($resource_id) && $resource_id == $res['id']) ? 'selected' : '' ?>>
                                        <?= esc($res['name']) ?> (<?= esc($res['hostname'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- アカウント選択 -->
                        <div class="mb-3">
                            <label for="account_id" class="form-label">アカウント</label>
                            <select name="account_id" id="account_id" class="form-select" required <?= (isset($resource_id) && !empty($accounts[$resource_id])) ? '' : 'disabled' ?>>
                                <option value="">アカウントを選択</option>
                                <?php if (isset($resource_id) && isset($accounts[$resource_id]) && !empty($accounts[$resource_id])): ?>
                                    <?php foreach ($accounts[$resource_id] as $account): ?>
                                        <option value="<?= esc($account['id']) ?>"><?= esc($account['username']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php
                        // 現在の日付（YYYY-MM-DD）
                        $today = date('Y-m-d');

                        // 時間の選択肢（9:00～23:00）
                        $timeOptions = [];
                        for ($hour = 9; $hour <= 23; $hour++) {
                            $timeOptions[] = sprintf('%02d:00', $hour);
                        }

                        // 現在の時刻から開始時刻を決定（30分未満切り捨て、30分以上切り上げ）
                        $currentHour = (int) date('H');
                        $currentMinutes = (int) date('i');
                        $defaultStartHour = max(9, ($currentMinutes < 30 ? $currentHour : $currentHour + 1));
                        $defaultStartHour = min($defaultStartHour, 23); // 上限を 23:00 に制限
                        $defaultStartTime = sprintf('%02d:00', $defaultStartHour);

                        // 終了時刻（開始時刻の1時間後）
                        $defaultEndHour = min($defaultStartHour + 1, 24);
                        $defaultEndTime = sprintf('%02d:00', $defaultEndHour);
                        ?>

                        <div class="mb-3">
                            <label for="reservation_date" class="form-label">予約日</label>
                            <input type="date" class="form-control" id="reservation_date" name="reservation_date" value="<?= $today ?>" required>
                        </div>

                        <div class="row">
                            <!-- 開始時刻 -->
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">開始時刻</label>
                                <select class="form-select" id="start_time" name="start_time" required>
                                    <?php foreach ($timeOptions as $time): ?>
                                        <option value="<?= $time ?>" <?= ($time === $defaultStartTime) ? 'selected' : '' ?>><?= $time ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- 終了時刻 -->
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">終了時刻</label>
                                <select class="form-select" id="end_time" name="end_time" required>
                                    <?php foreach ($timeOptions as $time): ?>
                                        <option value="<?= $time ?>" <?= ($time === $defaultEndTime) ? 'selected' : '' ?>><?= $time ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- 予約ボタン -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('reservation') ?>" class="btn btn-secondary">
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
    </div>
</div>
<script>
    const accounts = <?= json_encode($accounts, JSON_HEX_TAG) ?>;
    const resourceSelect = document.getElementById('resource_id');
    const accountSelect = document.getElementById('account_id');

    resourceSelect.addEventListener('change', function() {
        const resourceId = this.value;
        accountSelect.innerHTML = '<option value="">アカウントを選択</option>';

        if (accounts[resourceId] && accounts[resourceId].length > 0) {
            accountSelect.disabled = false;
            accountSelect.setAttribute('required', 'required'); // 必須にする
            accounts[resourceId].forEach(account => {
                let option = document.createElement('option');
                option.value = account.id;
                option.textContent = account.username;
                accountSelect.appendChild(option);
            });
        } else {
            accountSelect.disabled = true;
            accountSelect.removeAttribute('required'); // 必須を解除
        }
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
<?= $this->endSection() ?>

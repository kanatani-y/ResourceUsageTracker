<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?><?= isset($reservation) ? '予約編集' : '新規予約' ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-<?= isset($reservation) ? 'check' : 'plus' ?>"></i>
                        <?= isset($reservation) ? '予約編集' : '新規予約' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= isset($reservation) ? route_to('reservation.update', $reservation['id']) : route_to('reservation.store') ?>" method="post">
                        <?= csrf_field() ?>
                        <?php if (isset($reservation)): ?>
                            <input type="hidden" name="_method" value="POST">
                        <?php endif; ?>

                        <div class="row">
                            <!-- リソース選択 -->
                            <div class="col-md-6 mb-3">
                                <label for="resource_id" class="form-label">リソース</label>
                                <select name="resource_id" id="resource_id" class="form-select" required>
                                    <option value="">リソースを選択</option>
                                    <?php foreach ($resources as $res): ?>
                                        <option value="<?= esc($res['id']) ?>"
                                            <?= (isset($reservation) && $reservation['resource_id'] == $res['id']) ? 'selected' : '' ?>>
                                            <?= esc($res['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- アカウント選択 -->
                            <div class="col-md-6 mb-3">
                                <label for="account_id" class="form-label">使用するアカウント</label>
                                <select name="account_id" id="account_id" class="form-select" required>
                                    <option value="">リソースを選択してください</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- 予約日 -->
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">予約日</label>
                                <input type="date" class="form-control" id="date" name="date" required
                                    value="<?= isset($reservation) ? esc(date('Y-m-d', strtotime($reservation['start_time']))) : '' ?>">
                            </div>

                            <!-- 予約時間 -->
                            <div class="col-md-6 mb-3">
                                <label for="time_slot" class="form-label">時間帯</label>
                                <select name="time_slot" id="time_slot" class="form-select" required>
                                    <option value="">時間帯を選択</option>
                                    <?php
                                    $timeSlots = [
                                        "9:00-10:00" => "9:00 - 10:00",
                                        "10:00-11:00" => "10:00 - 11:00",
                                        "11:00-12:00" => "11:00 - 12:00",
                                        "13:00-14:00" => "13:00 - 14:00",
                                        "14:00-15:00" => "14:00 - 15:00",
                                        "15:00-16:00" => "15:00 - 16:00",
                                        "16:00-17:00" => "16:00 - 17:00",
                                        "17:00-18:00" => "17:00 - 18:00",
                                        "morning" => "午前（9:00 - 12:00）",
                                        "afternoon" => "午後（13:00 - 18:00）",
                                        "full_day" => "終日（9:00 - 18:00）"
                                    ];
                                    foreach ($timeSlots as $key => $label):
                                    ?>
                                        <option value="<?= $key ?>"
                                            <?= (isset($reservation) && $reservation['time_slot'] == $key) ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- 使用目的 -->
                        <div class="mb-3">
                            <label for="purpose" class="form-label">使用目的</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="3"><?= old('purpose', $reservation['purpose'] ?? '') ?></textarea>
                        </div>

                        <!-- ボタン -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('reservation') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 戻る
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?= isset($reservation) ? '予約を更新' : '予約する' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const resourceSelect = document.getElementById("resource_id");
    const accountSelect = document.getElementById("account_id");

    function fetchAccounts(resourceId) {
        fetch("<?= site_url('api/accounts/') ?>" + resourceId)
            .then(response => response.json())
            .then(data => {
                accountSelect.innerHTML = '<option value="">アカウントを選択</option>';
                data.forEach(account => {
                    const option = document.createElement("option");
                    option.value = account.id;
                    option.textContent = account.username;
                    accountSelect.appendChild(option);
                });

                // 既存予約のアカウントをセット（編集時）
                <?php if (isset($reservation)): ?>
                    accountSelect.value = "<?= esc($reservation['account_id']) ?>";
                <?php endif; ?>
            })
            .catch(error => console.error("アカウントの取得に失敗しました:", error));
    }

    resourceSelect.addEventListener("change", function () {
        const resourceId = this.value;
        if (resourceId) {
            fetchAccounts(resourceId);
        } else {
            accountSelect.innerHTML = '<option value="">リソースを選択してください</option>';
        }
    });

    // 編集時、リソースが選択されていたらアカウントリストを更新
    <?php if (isset($reservation)): ?>
        fetchAccounts("<?= esc($reservation['resource_id']) ?>");
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>

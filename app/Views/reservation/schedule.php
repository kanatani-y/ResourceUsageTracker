<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>予約スケジュール（<?= esc($selectedDate) ?>）<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <h3 class="mb-3">予約スケジュール（<?= esc($selectedDate) ?>）</h3>

    <!-- 日付選択 -->
    <form method="get" class="mb-3">
        <label for="date" class="form-label">日付選択:</label>
        <input type="date" name="date" id="date" class="form-control d-inline-block w-auto" value="<?= esc($selectedDate) ?>">
        <button type="submit" class="btn btn-primary">表示</button>
    </form>

    <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>リソース</th>
                <th>アカウント</th>
                <?php for ($hour = 9; $hour <= 18; $hour++): ?>
                    <th><?= $hour ?>:00</th>
                <?php endfor; ?>
            </tr>
        </thead>
        
        <tbody>
        <?php foreach ($resources as $resource): ?>
            <?php 
            // デバッグ: リソース情報を出力
            echo "<!-- Debug: Processing Resource ID: {$resource['id']} - Name: {$resource['name']} -->";

            $resourceAccounts = isset($accounts[$resource['id']]) && count($accounts[$resource['id']]) > 0 
                ? $accounts[$resource['id']] 
                : [['id' => 0, 'username' => 'なし']]; // アカウントがない場合は "なし" を表示

            foreach ($resourceAccounts as $account): ?>
                <tr>
                    <td><?= esc($resource['name']) ?></td>
                    <td><?= esc($account['username']) ?></td>
                    <?php
                    $hourlySlots = array_fill(9, 10, '<td></td>'); // 9:00～18:00の枠を作成
                    
                    foreach ($reservations as $res) {
                        if ((int)$res['resource_id'] === (int)$resource['id'] && ((int)$res['account_id'] === (int)$account['id'] || (int)$res['account_id'] === 0)) {
                            $startHour = (int) date('H', strtotime($res['start_time']));
                            $endHour = (int) date('H', strtotime($res['end_time']));
                            $colspan = max(1, $endHour - $startHour);

                            // 予約時間のセルを適用
                            $hourlySlots[$startHour] = "<td colspan='$colspan' class='table-success text-white'>予約済</td>";

                            // 予約済み時間帯のセルをスキップ
                            for ($i = $startHour + 1; $i < $endHour; $i++) {
                                unset($hourlySlots[$i]);
                            }
                        }
                    }
                    ?>
                    <?= implode('', $hourlySlots) ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>

    </tbody>
    </table>
</div>

<style>
    .table-success {
        background-color: #28a745 !important;
        color: white;
        font-weight: bold;
    }
</style>

<?= $this->endSection() ?>

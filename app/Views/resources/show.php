<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>リソース詳細<?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="container-fluid d-flex justify-content-center p-2">
        <div class="card w-100 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-server"></i> リソース詳細
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th class="bg-light w-25">ID</th>
                            <td class="text-break"><?= esc($resource['id']) ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">リソース名</th>
                            <td class="text-break"><?= esc($resource['name']) ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">ホスト名</th>
                            <td class="text-break"><?= esc($resource['hostname']) ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">種類</th>
                            <td>
                                <?php
                                // リソースの種類に応じたアイコン設定
                                $resourceIcons = [
                                    'PC'      => '<i class="bi bi-laptop"></i> PC',
                                    'Server'  => '<i class="bi bi-hdd-rack"></i> サーバー',
                                    'Network' => '<i class="bi bi-router"></i> ネットワーク',
                                    'Storage' => '<i class="bi bi-hdd"></i> ストレージ',
                                    'Other'   => '<i class="bi bi-question-circle"></i> その他',
                                ];

                                // アイコンを取得、該当しない場合はデフォルトアイコンを設定
                                echo $resourceIcons[$resource['type']] ?? '<i class="bi bi-question-circle"></i> 不明';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">OS</th>
                            <td class="text-break"><?= esc($resource['os'] ?? 'なし') ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">IPアドレス</th>
                            <td class="text-break"><?= esc($resource['ip_address']) ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">CPU</th>
                            <td><?= esc($resource['cpu']) ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">メモリ</th>
                            <td><?= esc($resource['memory']) ?> GB</td>
                        </tr>
                        <tr>
                            <th class="bg-light">ストレージ</th>
                            <td><?= esc($resource['storage']) ?> GB</td>
                        </tr>
                        <tr>
                            <th class="bg-light">設置場所</th>
                            <td class="text-break"><?= esc($resource['location'] ?? 'なし') ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">説明</th>
                            <td class="text-break"><?= esc($resource['description'] ?? 'なし') ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">状態</th>
                            <td>
                                <?php if ($resource['deleted_at']) : ?>
                                    <span class="badge bg-secondary">削除済</span>
                                <?php else : ?>
                                    <?php
                                    switch ($resource['status']) {
                                        case 'available':
                                            echo '<span class="badge bg-info">利用可能</span>';
                                            break;
                                        case 'restricted':
                                            echo '<span class="badge bg-danger">利用禁止</span>';
                                            break;
                                        case 'retired':
                                            echo '<span class="badge bg-secondary">廃止</span>';
                                            break;
                                    }
                                    ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php $authUser = auth()->user(); ?>
                <!-- アカウント一覧 -->
                <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
                    <h5 class="mb-0"><i class="bi bi-key"></i> アカウント一覧</h5>
                    <?php if ($authUser->inGroup('admin')): ?>
                        <a href="<?= site_url('accounts/create/' . $resource['id']) ?>" 
                            class="btn btn-sm btn-success <?= $resource['deleted_at'] || $resource['status'] == 'retired' ? 'disabled' : '' ?>">
                            <i class="bi bi-plus-lg"></i> アカウント追加
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($accounts)) : ?>
                    <p class="text-muted">このリソースのアカウント情報はありません。</p>
                <?php else : ?>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>アカウント名</th>
                                <th>接続方式</th>
                                <th>ポート</th>
                                <th>状態</th>
                                <th>パスワード</th>
                                <th>備考</th>
                                
                                <?php if ($authUser->inGroup('admin')): ?>
                                <th>操作</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account) : ?>
                                <tr class="<?= $account['deleted_at'] ? 'text-muted' : '' ?>">
                                    <td><?= esc($account['account_name']) ?></td>
                                    <td>
                                        <?php
                                        $connectionLabels = [
                                            'SSH' => '<i class="bi bi-terminal"></i> SSH',
                                            'RDP' => '<i class="bi bi-windows"></i> RDP',
                                            'VNC' => '<i class="bi bi-display"></i> VNC',
                                            'OTH' => '<i class="bi bi-question-circle"></i> その他'
                                        ];
                                        echo $connectionLabels[$account['connection_type']] ?? '<i class="bi bi-question-circle"></i> 不明';
                                        ?>
                                    </td>
                                    <td><?= $account['port'] == -1 ? '-' : esc($account['port']) ?></td>
                                    <td>
                                        <?php
                                            switch ($account['status']) {
                                                case 'available':
                                                    echo '<span class="badge bg-info">有効</span>';
                                                    break;
                                                case 'restricted':
                                                    echo '<span class="badge bg-danger">無効</span>';
                                                    break;
                                                case 'retired':
                                                    echo '<span class="badge bg-secondary">廃止</span>';
                                                    break;
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <span id="password-<?= esc($account['id']) ?>" class="password-mask">••••••</span>
                                        <span id="password-plain-<?= esc($account['id']) ?>" class="d-none">
                                            <?= esc(base64url_decode($account['password'])) ?>
                                        </span>
                                        <button class="btn btn-sm" onclick="togglePassword(<?= esc($account['id']) ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                    <td><?= esc($account['description'] ?? '-') ?></td>
                                    
                                    <?php if ($authUser->inGroup('admin')): ?>
                                    <td>
                                    <a href="<?= site_url('accounts/edit/' . $account['id']) ?>" class="btn btn-sm btn-primary <?= $resource['deleted_at'] ? 'disabled' : '' ?>">
                                            <i class="bi bi-pencil-square"></i> 編集
                                        </a>
                                        
                                        <?php if ($account['status'] === 'retired') : ?>
                                            <form action="<?= site_url('accounts/delete/' . $account['id']) ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="button" class="btn btn-sm btn-danger <?= $account['deleted_at'] ? 'disabled' : '' ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmModal"
                                                    data-action="<?= site_url('accounts/delete/' . $account['id']) ?>"
                                                    data-title="削除確認"
                                                    data-message="本当にこのアカウントを削除しますか？">
                                                    <i class="bi bi-trash"></i> 削除
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>

                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php
                // リファラーを取得
                $referrer = $_SERVER['HTTP_REFERER'] ?? '';
                $defaultBackURL = site_url('resources');

                // リファラーが `reservations/schedule` を含む場合はそのページへ、それ以外は `resources.index`
                $backURL = (!empty($referrer) && strpos($referrer, site_url('reservations/schedule')) !== false)
                    ? site_url('reservations/schedule')
                    : $defaultBackURL;
                ?>

                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= esc($backURL) ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 戻る
                    </a>
                    <?php if ($authUser->inGroup('admin')): ?>
                        <a href="<?= site_url('resources/edit/' . $resource['id']) ?>"
                            class="btn btn-primary <?= $resource['deleted_at'] ? 'disabled' : '' ?>">
                            <i class="bi bi-pencil-square"></i> 編集
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<script>
    function togglePassword(accountId) {
        let masked = document.getElementById("password-" + accountId);
        let plain = document.getElementById("password-plain-" + accountId);
        let button = masked.previousElementSibling; // ボタン要素

        if (masked.classList.contains("d-none")) {
            masked.classList.remove("d-none");
            plain.classList.add("d-none");
            button.innerHTML = '<i class="bi bi-eye"></i> 表示';
        } else {
            masked.classList.add("d-none");
            plain.classList.remove("d-none");
            button.innerHTML = '<i class="bi bi-eye-slash"></i> 隠す';
        }
    }
</script>

<?= $this->endSection() ?>

<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>アカウント管理<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">アカウント一覧</h4>
        <a href="<?= isset($resource) ? site_url('accounts/create/' . $resource['id']) : site_url('accounts/create') ?>" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg"></i> 追加
        </a>
    </div>
    
    <div class="table-responsive" style="max-height: 74vh; overflow-y: auto;">
        <table class="table table-bordered table-hover">
            <thead class="table-light text-center sticky-top" style="z-index: 100;">
                <tr>
                    <th>リソース</th>
                    <th>アカウント名</th>
                    <th>接続方式</th>
                    <th>ポート</th>
                    <th>状態</th>
                    <th>備考</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $account) : ?>
                    <tr class="<?= $account['deleted_at'] ? 'text-muted' : '' ?>" style="<?= $account['deleted_at'] ? 'background-color: #EEEEEE;' : '' ?>">
                        <td>
                            <a href="<?= site_url('resources/show/' . $account['resource_id']) ?>" class="text-decoration-none">
                                <?php
                                $resourceIcons = [
                                    'PC'      => '<i class="bi bi-laptop"></i>',      // PC
                                    'Server'  => '<i class="bi bi-hdd-rack"></i>',   // サーバー
                                    'Network' => '<i class="bi bi-router"></i>',     // ネットワーク
                                    'Storage' => '<i class="bi bi-hdd"></i>',        // ストレージ
                                    'Other'   => '<i class="bi bi-question-circle"></i>', // その他
                                ];
                                echo $resourceIcons[$account['resource_type']] ?? '<i class="bi bi-question-circle"></i> ';
                                ?>
                                <?= esc($account['resource_name'] ?? '不明') ?>
                            </a>
                        </td>
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
                            <?php if ($account['deleted_at']) : ?>
                                <span class="badge bg-secondary">削除済</span>
                            <?php else : ?>
                                <?php if ($account['active'] == 1): ?>
                                    <span class="badge bg-info">有効</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">無効</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($account['description'] ?? '-') ?></td>
                        <td>
                            <?php if ($account['deleted_at']) : ?>
                                <!-- 復元リンク（モーダルで確認） -->
                                <a href="#" class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-method="get"
                                    data-action="<?= site_url('accounts/restore/' . $account['id']) ?>"
                                    data-title="復元確認"
                                    data-message="対象を復元します。よろしいですか？">
                                    <i class="bi bi-arrow-counterclockwise"></i> 復元
                                </a>
                            <?php else : ?>
                                <a href="<?= site_url('accounts/edit/' . $account['id']) ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> 編集
                                </a>
                                <?php if ($account['active'] == 0) : ?>
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
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
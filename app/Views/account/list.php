<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>アカウント管理<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">アカウント一覧</h4>
        <a href="<?= isset($resource) ? route_to('accounts.create', $resource['id']) : route_to('accounts.create_no_resource') ?>" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg"></i> 追加
        </a>
    </div>
    
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>リソース</th>
                <th>ユーザー名</th>
                <th>接続方式</th>
                <th>ポート</th>
                <th>状態</th>
                <th>備考</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account) : ?>
                <tr class="<?= $account['deleted_at'] ? 'text-muted' : '' ?>">
                    <td>
                        <a href="<?= route_to('resources.show', $account['resource_id']) ?>" class="text-decoration-none">
                            <i class="bi bi-server"></i> <?= esc($account['resource_name'] ?? '不明') ?>
                        </a>
                    </td>
                    <td><?= esc($account['username']) ?></td>
                    <td><?= esc($account['connection_type']) ?></td>
                    <td><?= $account['port'] == -1 ? '-' : esc($account['port']) ?></td>
                    <td>
                        <?php
                        switch ($account['status']) {
                            case 'available':
                                echo '<span class="badge bg-success">利用可能</span>';
                                break;
                            case 'restricted':
                                echo '<span class="badge bg-danger">利用禁止</span>';
                                break;
                            case 'retired':
                                echo '<span class="badge bg-secondary">廃止</span>';
                                break;
                        }
                        ?>
                    </td>
                    <td><?= esc($account['description'] ?? '-') ?></td>
                    <td>
                        <a href="<?= route_to('accounts.edit', $account['id']) ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square"></i> 編集
                        </a>
                        <form action="<?= route_to('accounts.delete', $account['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('本当に削除しますか？');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i> 削除
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>リソース管理<?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0">リソース管理</h4>
            <a href="<?= route_to('resource.create') ?>" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg"></i> 追加
            </a>
        </div>

        <?php if (session('message')) : ?>
            <div class="alert alert-success"><?= session('message') ?></div>
        <?php endif ?>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>リソース名</th>
                    <th>ホスト名</th>
                    <th>IPアドレス</th>
                    <th>種類</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource) : ?>
                    <tr class="<?= $resource['deleted_at'] ? 'text-muted' : '' ?>">
                        <td><?= $resource['id'] ?></td>
                        <td>
                            <a href="<?= route_to('resource.show', $resource['id']) ?>" class="text-decoration-none">
                                <?= esc($resource['name']) ?>
                            </a>
                        </td>
                        <td><?= esc($resource['hostname']) ?></td>
                        <td><?= esc($resource['ip_address']) ?></td>
                        <td><?= esc($resource['type']) ?></td>
                        <td>
                            <?php if ($resource['deleted_at']) : ?>
                                <span class="badge bg-danger">削除済</span>
                            <?php else : ?>
                                <?php
                                switch ($resource['status']) {
                                    case 'available':
                                        echo '<span class="badge bg-success">利用可能</span>';
                                        break;
                                    case 'in_use':
                                        echo '<span class="badge bg-warning">使用中</span>';
                                        break;
                                    case 'maintenance':
                                        echo '<span class="badge bg-info">メンテナンス中</span>';
                                        break;
                                    case 'retired':
                                        echo '<span class="badge bg-secondary">廃止</span>';
                                        break;
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-2">
                            

                            <div class="btn-group" role="group">
                                <a href="<?= route_to('resource.show', $resource['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> 詳細
                                </a>
                                <?php if ($resource['deleted_at']) : ?>
                                    <a href="<?= route_to('resource.restore', $resource['id']) ?>" class="btn btn-sm btn-warning" 
                                        onclick="return confirm('対象を復元します。よろしいですか？')">
                                        <i class="bi bi-arrow-counterclockwise"></i> 復元
                                    </a>
                                <?php else : ?>
                                    <a href="<?= route_to('resource.edit', $resource['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square"></i> 編集
                                    </a>
                                    <a href="<?= route_to('resource.delete', $resource['id']) ?>" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('対象を論理削除します。よろしいですか？')">
                                        <i class="bi bi-trash"></i> 削除
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?= $this->endSection() ?>

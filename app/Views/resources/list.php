<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>リソース管理<?= $this->endSection() ?>

<?= $this->section('main') ?>
    <?php $authUser = auth()->user(); ?>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0"><i class="bi bi-server"></i> リソース一覧</h4>
            <?php if ($authUser->inGroup('admin')): ?>
                <a href="<?= route_to('resources.create') ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg"></i> 追加
                </a>
            <?php endif; ?>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-light text-center">
                <tr>
                    <th>ID</th>
                    <th>リソース名</th>
                    <th>ホスト名</th>
                    <th>IPアドレス</th>
                    <th>種類</th>
                    <th>状態</th>
                    <?php if ($authUser->inGroup('admin')): ?>
                        <th>操作</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource) : ?>
                    <tr class="<?= $resource['deleted_at'] ? 'text-muted' : '' ?>">
                        <td><?= $resource['id'] ?></td>
                        <td class="text-decoration-none fw-bold">
                            <a href="<?= route_to('resources.show', $resource['id']) ?>" class="text-decoration-none">
                                <i class="bi bi-server"></i> <?= esc($resource['name']) ?>
                            </a>
                        </td>
                        <td><?= esc($resource['hostname']) ?></td>
                        <td><?= esc($resource['ip_address']) ?></td>
                        <td><?= esc($resource['type']) ?></td>
                        <td>
                            <?php if ($resource['deleted_at']) : ?>
                                <span class="badge bg-secondary">削除済</span>
                            <?php else : ?>
                                <?php
                                switch ($resource['status']) {
                                    case 'available':
                                        echo '<span class="badge bg-success">利用可能</span>';
                                        break;
                                    case 'in_use':
                                        echo '<span class="badge bg-danger">使用中</span>';
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
                        <?php if ($authUser->inGroup('admin')): ?>
                        <td class="d-flex gap-2">
                            <div>
                                <?php if ($resource['deleted_at']) : ?>
                                    <a href="<?= route_to('resources.restore', $resource['id']) ?>" class="btn btn-sm btn-warning" 
                                        onclick="return confirm('対象を復元します。よろしいですか？')">
                                        <i class="bi bi-arrow-counterclockwise"></i> 復元
                                    </a>
                                <?php else : ?>
                                    <a href="<?= route_to('resources.edit', $resource['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square"></i> 編集
                                    </a>
                                    <a href="<?= route_to('resources.delete', $resource['id']) ?>" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('対象を論理削除します。よろしいですか？')">
                                        <i class="bi bi-trash"></i> 削除
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?= $this->endSection() ?>

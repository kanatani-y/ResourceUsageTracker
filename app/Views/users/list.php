<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>ユーザー一覧<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">ユーザー一覧</h4>
        <a href="<?= site_url('admin/users/register') ?>" class="btn btn-sm btn-success float-end">
            <i class="bi bi-plus-lg"></i> 追加
        </a>
    </div>

    <div class="table-responsive" style="max-height: 74vh; overflow-y: auto;">
        <table class="table table-bordered table-hover">
            <thead class="table-light text-center sticky-top" style="z-index: 100;">
                <tr>
                    <th>ID</th>
                    <th>ユーザー名</th>
                    <th>氏名</th>
                    <th>種別</th>
                    <th>状態</th>
                    <th>最終アクティブ日時</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="<?= $user->deleted_at ? 'text-muted' : '' ?>">
                        <td><?= esc($user->id) ?></td>
                        <td><?= esc($user->username) ?></td>
                        <td><?= esc($user->fullname ?? '-') ?></td>
                        <td>
                            <?php 
                            $groups = $user->getGroups() ?? [];
                            $groupLabel = '一般';
    
                            if (in_array('admin', $groups, true)) {
                                $groupLabel = '管理者';
                            } elseif (in_array('guest', $groups, true)) {
                                $groupLabel = 'ゲスト';
                            }
    
                            echo esc($groupLabel);
                            ?>
                        </td>
                        <td>
                            <?php if ($user->deleted_at): ?>
                                <span class="badge bg-secondary">削除済</span>
                            <?php else: ?>
                                <?= $user->active ? '<span class="badge bg-info">有効</span>' : '<span class="badge bg-danger">無効</span>' ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $user->last_active ? $user->last_active->format('Y-m-d H:i:s') : '未アクティブ' ?></td>
                        <td class="text-nowrap" style="width: 150px;">
                            <div>
                                <?php if ($user->deleted_at) : ?>
                                    <!-- 復元リンク（モーダルで確認） -->
                                    <a href="#" class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmModal"
                                        data-method="get"
                                        data-action="<?= site_url('admin/users/restore/' . $user->id) ?>"
                                        data-title="復元確認"
                                        data-message="対象を復元します。よろしいですか？">
                                        <i class="bi bi-arrow-counterclockwise"></i> 復元
                                    </a>
                                <?php else : ?>
                                    <?php if (!in_array('guest', $groups, true)): // ゲストでない場合のみ編集可能 ?>
                                        <a href="<?= site_url('admin/users/edit/' . $user->id) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i> 編集
                                        </a>
                                        <?php if ($user->active == 0 && $user->id !== auth()->user()->id) : ?>
                                            <form action="<?= site_url('admin/users/delete/' . $user->id) ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmModal"
                                                    data-action="<?= site_url('admin/users/delete/' . $user->id) ?>"
                                                    data-title="削除確認"
                                                    data-message="本当にこのユーザーを削除しますか？">
                                                    <i class="bi bi-trash"></i> 削除
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">変更不可</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

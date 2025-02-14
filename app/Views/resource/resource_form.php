<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?><?= isset($resource) ? 'リソース編集' : '新規リソース登録' ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi <?= isset($resource) ? 'bi-pencil-square' : 'bi-plus-square' ?>"></i>
                        <?= isset($resource) ? 'リソース編集' : '新規リソース登録' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= isset($resource) ? route_to('resource.update', $resource['id']) : route_to('resource.store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <!-- リソース名 -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">リソース名</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?= old('name', $resource['name'] ?? '') ?>" required>
                            </div>

                            <!-- ホスト名 -->
                            <div class="col-md-6 mb-3">
                                <label for="hostname" class="form-label">ホスト名</label>
                                <input type="text" class="form-control" id="hostname" name="hostname" 
                                    value="<?= old('hostname', $resource['hostname'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- 種類 -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">種類</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="PC" <?= old('type', $resource['type'] ?? '') === 'PC' ? 'selected' : '' ?>>PC</option>
                                    <option value="Server" <?= old('type', $resource['type'] ?? '') === 'Server' ? 'selected' : '' ?>>Server</option>
                                    <option value="Network" <?= old('type', $resource['type'] ?? '') === 'Network' ? 'selected' : '' ?>>Network</option>
                                    <option value="Storage" <?= old('type', $resource['type'] ?? '') === 'Storage' ? 'selected' : '' ?>>Storage</option>
                                    <option value="Other" <?= old('type', $resource['type'] ?? 'Other') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <!-- OS -->
                            <div class="col-md-6 mb-3">
                                <label for="os" class="form-label">OS</label>
                                <input type="text" class="form-control" id="os" name="os" 
                                    value="<?= old('os', $resource['os'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- IPアドレス -->
                            <div class="col-md-6 mb-3">
                                <label for="ip_address" class="form-label">IPアドレス</label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address" 
                                    value="<?= old('ip_address', $resource['ip_address'] ?? '') ?>">
                            </div>

                            <!-- CPU -->
                            <div class="col-md-6 mb-3">
                                <label for="cpu" class="form-label">CPU</label>
                                <input type="text" class="form-control" id="cpu" name="cpu" 
                                    value="<?= old('cpu', $resource['cpu'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- メモリ -->
                            <div class="col-md-6 mb-3">
                                <label for="memory" class="form-label">メモリ</label>
                                <input type="text" class="form-control" id="memory" name="memory" 
                                    value="<?= old('memory', $resource['memory'] ?? '') ?>">
                            </div>

                            <!-- ストレージ -->
                            <div class="col-md-6 mb-3">
                                <label for="storage" class="form-label">ストレージ</label>
                                <input type="text" class="form-control" id="storage" name="storage" 
                                    value="<?= old('storage', $resource['storage'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- ステータス -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">ステータス</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="available" <?= old('status', $resource['status'] ?? '') === 'available' ? 'selected' : '' ?>>利用可能</option>
                                    <option value="in_use" <?= old('status', $resource['status'] ?? '') === 'in_use' ? 'selected' : '' ?>>使用中</option>
                                    <option value="maintenance" <?= old('status', $resource['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>メンテナンス中</option>
                                    <option value="retired" <?= old('status', $resource['status'] ?? '') === 'retired' ? 'selected' : '' ?>>廃止</option>
                                </select>
                            </div>

                            <!-- 設置場所 -->
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">設置場所</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                    value="<?= old('location', $resource['location'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- 説明 -->
                        <div class="mb-3">
                            <label for="description" class="form-label">説明</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= old('description', $resource['description'] ?? '') ?></textarea>
                        </div>

                        <!-- ボタン -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('resource') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 戻る
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> <?= isset($resource) ? '更新' : '登録' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

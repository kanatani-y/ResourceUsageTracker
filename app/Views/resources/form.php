<?= $this->extend('layouts/common') ?>

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
                    <form action="<?= isset($resource) ? site_url('resources/update/' . $resource['id']) : site_url('resources/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <!-- リソース名 -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">リソース名</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?= old('name', $resource['name'] ?? '') ?>" required maxlength="50">
                            </div>

                            <!-- ホスト名 -->
                            <div class="col-md-6 mb-3">
                                <label for="hostname" class="form-label">ホスト名</label>
                                <input type="text" class="form-control" id="hostname" name="hostname" 
                                    pattern="^[a-zA-Z0-9._-]+$" title="半角英数字、ドット、ハイフン、アンダースコアのみ利用可能"
                                    value="<?= old('hostname', $resource['hostname'] ?? '') ?>" required maxlength="50">
                            </div>
                        </div>

                        <div class="row">
                            <!-- 種類 -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">種類</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="PC" <?= old('type', $resource['type'] ?? 'PC') === 'PC' ? 'selected' : '' ?>>PC</option>
                                    <option value="Server" <?= old('type', $resource['type'] ?? 'PC') === 'Server' ? 'selected' : '' ?>>Server</option>
                                    <option value="Network" <?= old('type', $resource['type'] ?? 'PC') === 'Network' ? 'selected' : '' ?>>Network</option>
                                    <option value="Storage" <?= old('type', $resource['type'] ?? 'PC') === 'Storage' ? 'selected' : '' ?>>Storage</option>
                                    <option value="Other" <?= old('type', $resource['type'] ?? 'PC') === 'Other' ? 'selected' : '' ?>>その他</option>
                                </select>
                            </div>

                            <!-- OS -->
                            <div class="col-md-6 mb-3">
                                <label for="os" class="form-label">OS</label>
                                <input type="text" class="form-control" id="os" name="os" 
                                    value="<?= old('os', $resource['os'] ?? '') ?>" maxlength="100">
                            </div>
                        </div>

                        <div class="row">
                            <!-- IPアドレス -->
                            <div class="col-md-6 mb-3">
                                <label for="ip_address" class="form-label">IPアドレス</label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address" 
                                    value="<?= old('ip_address', $resource['ip_address'] ?? '') ?>"
                                    pattern="^(\d{1,3}\.){3}\d{1,3}$" title="IPv4形式 (例: 192.168.1.1) を入力してください"
                                    maxlength="15">
                            </div>

                            <!-- CPU コア数-->
                            <div class="col-md-6 mb-3">
                                <label for="cpu" class="form-label">CPU</label>
                                <input type="text" class="form-control" id="cpu" name="cpu" min="0"
                                    value="<?= old('cpu', $resource['cpu'] ?? '') ?>" inputmode="numeric">
                            </div>
                        </div>

                        <div class="row">
                            <!-- メモリ -->
                            <div class="col-md-6 mb-3">
                                <label for="memory" class="form-label">メモリ容量</label>
                                <input type="text" class="form-control" id="memory" name="memory" min="0"
                                    value="<?= old('memory', $resource['memory'] ?? '') ?>" inputmode="numeric">
                            </div>

                            <!-- ストレージ -->
                            <div class="col-md-6 mb-3">
                                <label for="storage" class="form-label">ストレージ容量</label>
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
                                    <option value="restricted" <?= old('status', $resource['status'] ?? '') === 'restricted' ? 'selected' : '' ?>>利用禁止</option>
                                    <option value="retired" <?= old('status', $resource['status'] ?? '') === 'retired' ? 'selected' : '' ?>>廃止</option>
                                </select>
                            </div>

                            <!-- 設置場所 -->
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">設置場所</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                    value="<?= old('location', $resource['location'] ?? '') ?>" maxlength="255">
                            </div>
                        </div>

                        <!-- 説明 -->
                        <div class="mb-3">
                            <label for="description" class="form-label">説明</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= old('description', $resource['description'] ?? '') ?></textarea>
                        </div>

                        <?php
                        // リファラーを取得
                        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
                        $showResourceURL = isset($resource['id']) ? site_url('resources/show/' . $resource['id']) : site_url('resources');
                        $defaultBackURL = site_url('resources'); // デフォルトはリソース一覧

                        // リファラーが `resources/show/` を含む場合はリソース詳細に戻る
                        $backURL = (strpos($referrer, 'resources/show/') !== false) ? $showResourceURL : $defaultBackURL;
                        ?>
                        <!-- ボタン -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= esc($backURL) ?>" class="btn btn-secondary">
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

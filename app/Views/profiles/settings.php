<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>ユーザー設定<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> ユーザー設定
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (session('errors')) : ?>
                        <div class="alert alert-danger">
                            <?php foreach (session('errors') as $error) : ?>
                                <div><?= esc($error) ?></div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>

                    <form action="<?= site_url('profiles/update') ?>" method="post" id="userSettingsForm">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fullname" class="form-label">氏名</label>
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    value="<?= old('fullname', $user->fullname ?? '') ?>" required
                                    maxlength="20" pattern="^[^\s ]+[ ][^\s ]+$"
                                    title="姓と名の間に半角スペースを1つ入れてください">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= old('email', $user->email ?? '') ?>" required maxlength="255">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="current_password" class="form-label">現在のパスワード</label>
                                <input type="password" class="form-control" id="current_password"
                                    name="current_password" required minlength="4">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">新しいパスワード（変更しない場合は空欄）</label>
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                    minlength="4" onkeyup="validatePassword()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_password_confirm" class="form-label">新しいパスワード（確認）</label>
                                <input type="password" class="form-control" id="new_password_confirm"
                                    name="new_password_confirm" onkeyup="validatePassword()">
                                <div id="password-error" class="text-danger mt-1" style="display: none;">
                                    パスワード（確認）が一致しません。
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('/') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 戻る
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> 更新
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function validatePassword() {
        const newPassword = document.getElementById("new_password").value;
        const confirmPassword = document.getElementById("new_password_confirm").value;
        const errorDiv = document.getElementById("password-error");

        if (confirmPassword === "") {
            errorDiv.style.display = "none";
        } else if (newPassword !== confirmPassword) {
            errorDiv.style.display = "block";
        } else {
            errorDiv.style.display = "none";
        }
    }

    document.getElementById("userSettingsForm").addEventListener("submit", function(event) {
        if (document.getElementById("password-error").style.display === "block") {
            event.preventDefault();
            alert("パスワード（確認）が一致していません。");
        }
    });
</script>

<?= $this->endSection() ?>

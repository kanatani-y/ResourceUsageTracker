<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?> | Resource Usage Tracker</title>

    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico') ?>">

    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/icons/bootstrap-icons.css') ?>">

    <style>
        body {
            padding-top: 56px;
        }
    </style>

    <?= $this->renderSection('pageStyles') ?>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-secondary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-light" href="<?= site_url('/') ?>">
                <i class="bi bi-hdd-stack"></i> Resource Usage Tracker
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (auth()->loggedIn()): ?>
                    <?php $authUser = auth()->user(); ?>
                    <ul class="navbar-nav ms-auto">
                        <!-- リソース予約 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-calendar-check"></i> 予約
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('reservations/schedule') ?>">
                                        <i class="bi bi-list-check"></i> 予約スケジュール
                                    </a>
                                </li>
                                <li>
                                    <?php if (!$authUser->inGroup('guest')): ?> 
                                        <a class="dropdown-item" href="<?= site_url('reservations/create') ?>">
                                            <i class="bi bi-calendar-plus"></i> 予約登録
                                        </a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </li>

                        <!-- リソース管理 (ゲスト以外) -->
                        <?php if (!$authUser->inGroup('guest')): ?> 
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-hdd-network"></i> リソース管理
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('resources') ?>">
                                        <i class="bi bi-server"></i> リソース一覧
                                    </a>
                                </li>
                                <?php if ($authUser->inGroup('admin')): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('resources/create') ?>">
                                        <i class="bi bi-plus-square"></i> リソース登録
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if ($authUser->inGroup('admin')): ?>
                        <!-- アカウント管理 (管理者のみ) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-key"></i> アカウント管理
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('accounts') ?>">
                                        <i class="bi bi-list-ul"></i> アカウント一覧
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- ユーザー管理 (管理者のみ) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-people"></i> ユーザー管理
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('admin/users') ?>">
                                        <i class="bi bi-list-ul"></i> ユーザ一覧
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= site_url('admin/users/register') ?>">
                                        <i class="bi bi-person-plus"></i> ユーザ登録
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- 設定 -->
                        <li class="nav-item dropdown ms-5">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2"></i>
                                <?= esc($authUser->fullname ?? $authUser->username) ?> さん
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (!$authUser->inGroup('guest')): ?> 
                                    <li>
                                        <a class="dropdown-item" href="<?= site_url('profiles/settings/') ?>">
                                            <i class="bi bi-gear"></i> 設定
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <form id="logoutForm" action="<?= site_url('logout') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right"></i> ログアウト
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main role="main" class="container mt-3">
        <?php if (session()->getFlashdata('message')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('message')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <?= esc($error) ?><br>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('main') ?>

        <!-- 汎用確認モーダル -->
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="confirmModalLabel">確認</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                    </div>
                    <div class="modal-body" id="confirmModalMessage">
                        <!-- メッセージが動的に入る -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        
                        <!-- GETリクエスト時のボタン -->
                        <button id="confirmOkButton" class="btn btn-primary" style="display: none;">OK</button>
                        
                        <!-- POSTリクエスト時のフォーム -->
                        <form id="confirmForm" action="" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary">OK</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var confirmModal = document.getElementById("confirmModal");
            var confirmForm = document.getElementById("confirmForm");
            var confirmModalTitle = document.getElementById("confirmModalLabel");
            var confirmModalMessage = document.getElementById("confirmModalMessage");
            var confirmOkButton = document.getElementById("confirmOkButton");

            confirmModal.addEventListener("show.bs.modal", function(event) {
                var button = event.relatedTarget;
                if (!button.hasAttribute("data-action")) return;

                var action = button.getAttribute("data-action");
                var method = button.getAttribute("data-method") || "post"; // デフォルトはPOST
                var title = button.getAttribute("data-title");
                var message = button.getAttribute("data-message");

                // モーダルのタイトルとメッセージを更新
                confirmModalTitle.textContent = title;
                confirmModalMessage.textContent = message;

                if (method.toLowerCase() === "get") {
                    // GETリクエストの場合、直接遷移
                    confirmForm.style.display = "none";
                    confirmOkButton.style.display = "block";
                    confirmOkButton.onclick = function() {
                        window.location.href = action;
                    };
                } else {
                    // POSTリクエストの場合、フォームを使用
                    confirmForm.style.display = "block";
                    confirmOkButton.style.display = "none";
                    confirmForm.action = action;
                }
            });
        });
        </script>

    </main>

    <?= $this->renderSection('pageScripts') ?>

    <!-- Bootstrap JavaScript -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <style>
        /* フェードインの基本スタイル */
        body {
            opacity: 0;
            animation: fadeIn 0.5s ease-in forwards;
        }

        /* アニメーション定義 */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</body>
</html>

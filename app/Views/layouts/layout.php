<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?> | Device Usage Tracker</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        /* ナビバーの高さを考慮して main に余白を追加 */
        body {
            padding-top: 56px; /* navbar の高さ分 */
        }
    </style>

    <?= $this->renderSection('pageStyles') ?>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-body-tertiary bg-secondary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-light" href="<?= site_url('/') ?>">Device Usage Tracker</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- ログイン時のみメニューを表示 -->
                <?php if (auth()->loggedIn()): ?>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">デバイス予約</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= route_to('admin.users.index') ?>">予約状況確認</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">デバイス管理</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= route_to('admin.users.index') ?>">デバイス一覧</a></li>
                                <li><a class="dropdown-item" href="<?= route_to('admin.register') ?>">デバイス登録</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">ユーザー管理</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= route_to('admin.users.index') ?>">ユーザ一覧</a></li>
                                <li><a class="dropdown-item" href="<?= route_to('admin.register') ?>">ユーザ登録</a></li>
                            </ul>
                        </li>

                        <!-- 氏名 -->
                        <li class="nav-item dropdown ms-5">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i> 
                                <?= esc(auth()->user()->fullname ?? auth()->user()->username) ?> さん</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:confirmLogout();">ログアウト</a></li>
                            </ul>
                            <form id="logoutForm" action="<?= route_to('logout') ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                            </form>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main role="main" class="container mt-3">
        <?= $this->renderSection('main') ?>
    </main>

    <?= $this->renderSection('pageScripts') ?>

    <!-- Bootstrap JavaScript (動作しない場合はこの行が必要) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script>
        function confirmLogout() {
            if (confirm('ログアウトしますか？')) {
                document.getElementById('logoutForm').submit();
            }
        }
    </script>
</body>
</html>

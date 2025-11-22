<header class="admin-header">
    <div class="header-content">
        <div class="logo">
            <h1><a href="index.php">Naturistický kemp - Admin</a></h1>
        </div>
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="pages.php">Stránky</a></li>
                <li><a href="posts.php">Články</a></li>
                <li><a href="settings.php">Nastavení</a></li>
                <li><a href="../" target="_blank">Zobrazit web</a></li>
                <li><a href="logout.php">Odhlásit se</a></li>
            </ul>
        </nav>
        <div class="user-info">
            Přihlášen jako: <strong><?= $_SESSION['admin_username'] ?></strong>
        </div>
    </div>
</header>

<nav class="navbar">
    <div class="logo">ProLink</div>

    <ul class="nav-links">
        <li><a href="/prolink/View/home.php">Accueil</a></li>
        <li><a href="#">Réseau</a></li>
        <li><a href="#">Projets</a></li>
        <li><a href="#">Événements</a></li>
    </ul>

     <div class="auth">
        <a href="/prolink/View/login.php" class="btn login">Login</a>
        <a href="/prolink/View/register.php" class="btn register">Register</a>
    </div>
</nav>

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 50px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.logo {
    font-size: 22px;
    font-weight: bold;
    color: #0073b1;
}

.nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
}

.nav-links a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
}

.nav-links a:hover {
    color: #0073b1;
}

.auth .btn {
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    margin-left: 10px;
}

.login {
    border: 1px solid #0073b1;
    color: #0073b1;
}

.register {
    background: #0073b1;
    color: white;
}
</style>
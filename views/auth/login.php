<?php
/**
 * Tela de entrada. Desenhada dentro de views/template/layout-login.php,
 * que nao tem menu lateral: quem ainda nao entrou nao usaria nenhum
 * daqueles atalhos.
 */
?>
<h1 class="h4 mb-4">Entrar</h1>

<form method="post" action="<?= url('auth/login') ?>">
    <?= campo_csrf() ?>
    <div class="mb-3">
        <label class="form-label" for="email">E-mail</label>
        <input class="form-control" id="email" type="email" name="email" autocomplete="email" value="<?= e(antigo('email')) ?>" required>
    </div>
    <div class="mb-4">
        <label class="form-label" for="senha">Senha</label>
        <input class="form-control" id="senha" type="password" name="senha" autocomplete="current-password" required>
    </div>
    <button class="btn btn-primary w-100" type="submit">Entrar</button>
</form>

<p class="text-center mt-4 mb-0">
    <a href="<?= url('auth/registrar') ?>">Criar uma conta</a>
</p>

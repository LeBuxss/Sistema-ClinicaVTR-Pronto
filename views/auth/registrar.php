<?php
/**
 * Tela de cadastro. Assim como a de entrada, e desenhada dentro de
 * views/template/layout-login.php, sem o menu lateral.
 */
?>
<h1 class="h4 mb-4">Criar conta</h1>

<form method="post" action="<?= url('auth/registrar') ?>">
    <?= campo_csrf() ?>
    <div class="mb-3">
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control <?= tem_erro('nome') ? 'is-invalid' : '' ?>" id="nome" type="text" name="nome" autocomplete="name" value="<?= e(antigo('nome')) ?>" required>
        <?php if ($mensagem = erro_de('nome')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>

    <div class="mb-3">
        <label class="form-label" for="email">E-mail</label>
        <input class="form-control <?= tem_erro('email') ? 'is-invalid' : '' ?>" id="email" type="email" name="email" autocomplete="email" value="<?= e(antigo('email')) ?>" required>
        <?php if ($mensagem = erro_de('email')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="mb-4">
        <label class="form-label" for="senha">Senha</label>
        <input class="form-control <?= tem_erro('senha') ? 'is-invalid' : '' ?>" id="senha" type="password" name="senha" autocomplete="new-password" minlength="6" required>
        <?php if ($mensagem = erro_de('senha')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <button class="btn btn-primary w-100" type="submit">Criar conta</button>
</form>

<p class="text-center mt-4 mb-0">
    <a href="<?= url('auth/login') ?>">Ja tenho uma conta</a>
</p>

<div class="mb-4">
    <h1 class="h3 mb-1"><?= e($titulo) ?></h1>
    <p class="text-secondary mb-0">Preencha os dados abaixo.</p>
</div>

<form class="card border-0 shadow-sm p-4" method="post" action="<?= url('clinicavtr/' . ($registro ? 'atualizar/' . $registro['id'] : 'salvar')) ?>">
    <?= campo_csrf() ?>
    <div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control <?= tem_erro('nome') ? 'is-invalid' : '' ?>" id="nome" type="text" name="nome" value="<?= e(antigo('nome', $registro['nome'] ?? '')) ?>" required>
        <?php if ($mensagem = erro_de('nome')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="cpf">CPF</label>
        <input class="form-control <?= tem_erro('cpf') ? 'is-invalid' : '' ?>" id="cpf" type="text" name="cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" value="<?= e(antigo('cpf', $registro['cpf'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('cpf')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">email</label>
        <input class="form-control <?= tem_erro('email') ? 'is-invalid' : '' ?>" id="email" type="email" name="email" value="<?= e(antigo('email', $registro['email'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('email')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="senha">senha</label>
        <input class="form-control <?= tem_erro('senha') ? 'is-invalid' : '' ?>" id="senha" type="password" name="senha" autocomplete="new-password" minlength="6">
        <?php if ($registro): ?><div class="form-text">Deixe em branco para manter a senha atual.</div><?php endif ?>
        <?php if ($mensagem = erro_de('senha')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-outline-secondary" href="<?= url('clinicavtr') ?>">Cancelar</a>
    </div>
</form>

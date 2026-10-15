<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0"><?= e($titulo) ?></h1>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= url('cadastro') ?>">Voltar</a>
        <a class="btn btn-primary" href="<?= url('cadastro/editar/' . $registro['id']) ?>">Editar</a>
        <form method="post" action="<?= url('cadastro/excluir/' . $registro['id']) ?>" onsubmit="return confirm('Excluir este registro?')">
            <?= campo_csrf() ?>
            <button class="btn btn-outline-danger" type="submit">Excluir</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <dl class="row g-0 mb-0 p-4">
        <dt class="col-sm-3">Id</dt>
        <dd class="col-sm-9"><?= e($registro['id']) ?></dd>
        <dt class="col-sm-3">Nome</dt>
        <dd class="col-sm-9"><?= e($registro['nome'] ?? '') ?></dd>
        <dt class="col-sm-3">Email</dt>
        <dd class="col-sm-9"><?= e($registro['email'] ?? '') ?></dd>
        <dt class="col-sm-3">Especie</dt>
        <dd class="col-sm-9"><?= e($registro['especie'] ?? '') ?></dd>
        <dt class="col-sm-3">Tutor</dt>
        <dd class="col-sm-9"><?= e($registro['tutor'] ?? '') ?></dd>
        <dt class="col-sm-3">Veterinario</dt>
        <dd class="col-sm-9"><?= e($registro['veterinario'] ?? '') ?></dd>
        <dt class="col-sm-3">Serviço</dt>
        <dd class="col-sm-9"><?= e($registro['procedimento'] ?? '') ?></dd>
    </dl>
</div>

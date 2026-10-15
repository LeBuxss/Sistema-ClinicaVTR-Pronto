<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0"><?= e($titulo) ?></h1>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= url('consulta') ?>">Voltar</a>
        <a class="btn btn-primary" href="<?= url('consulta/editar/' . $registro['id']) ?>">Editar</a>
        <form method="post" action="<?= url('consulta/excluir/' . $registro['id']) ?>" onsubmit="return confirm('Excluir este registro?')">
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
        <dt class="col-sm-3">Raça</dt>
        <dd class="col-sm-9"><?= e($registro['raca'] ?? '') ?></dd>
        <dt class="col-sm-3">Data de nascimento</dt>
        <dd class="col-sm-9"><?= e($registro['datanascimente'] ?? '') ?></dd>
        <dt class="col-sm-3">Data da consulta</dt>
        <dd class="col-sm-9"><?= e($registro['data_consulta'] ?? '') ?></dd>
        <dt class="col-sm-3">Sexo</dt>
        <dd class="col-sm-9"><?= e($registro['sexo'] ?? '') ?></dd>
        <dt class="col-sm-3">Peso</dt>
        <dd class="col-sm-9"><?= e($registro['peso'] ?? '') ?></dd>
        <dt class="col-sm-3">Tutor</dt>
        <dd class="col-sm-9"><?= e($registro['tutor'] ?? '') ?></dd>
        <dt class="col-sm-3">Telefone do tutor</dt>
        <dd class="col-sm-9"><?= e($registro['telefone_tutor'] ?? '') ?></dd>
        <dt class="col-sm-3">Queixa principal</dt>
        <dd class="col-sm-9"><?= e($registro['queixa_principal'] ?? '') ?></dd>
        <dt class="col-sm-3">Observações clínicas</dt>
        <dd class="col-sm-9"><?= nl2br(e($registro['observacoes_clinicas'] ?? '')) ?></dd>
        <dt class="col-sm-3">Horário da consulta</dt>
        <dd class="col-sm-9"><?= e($registro['horario_disponivel'] ?? '') ?></dd>
        <dt class="col-sm-3">Veterinário disponível</dt>
        <dd class="col-sm-9"><?= e($registro['veterinario_disponivel'] ?? '') ?></dd>
    </dl>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Cadastro</h1>
        <p class="text-secondary mb-0">Gerencie os registros cadastrados.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="<?= url('clinicavtr/relatorio') ?>">Relatório PDF</a>
        <a class="btn btn-primary" href="<?= url('clinicavtr/criar') ?>">Novo registro</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>E-mail</th>
                <th class="text-end">Acoes</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($registros as $registro): ?>
            <tr>
                <td><a href="<?= url('clinicavtr/ver/' . $registro['id']) ?>"><?= e($registro['id']) ?></a></td>
                <td><?= e($registro['nome'] ?? '') ?></td>
                <td><?= e($registro['cpf'] ?? '') ?></td>
                <td><?= e($registro['email'] ?? '') ?></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('clinicavtr/editar/' . $registro['id']) ?>">Editar</a>
                    <form class="d-inline" method="post" action="<?= url('clinicavtr/excluir/' . $registro['id']) ?>" onsubmit="return confirm('Excluir este registro?')">
                        <?= campo_csrf() ?>
                        <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                    </form>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if ($registros === []): ?>
            <tr><td colspan="5" class="text-center text-secondary py-4">Nenhum registro cadastrado.</td></tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

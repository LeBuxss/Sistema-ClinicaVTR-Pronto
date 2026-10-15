<div class="mb-4">
    <h1 class="h3 mb-1"><?= e($titulo) ?></h1>
    <p class="text-secondary mb-0">Preencha os dados abaixo.</p>
</div>

<form class="card border-0 shadow-sm p-4" method="post" action="<?= url('cadastro/' . ($registro ? 'atualizar/' . $registro['id'] : 'salvar')) ?>">
    <?= campo_csrf() ?>
    <div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control <?= tem_erro('nome') ? 'is-invalid' : '' ?>" id="nome" type="text" name="nome" value="<?= e(antigo('nome', $registro['nome'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('nome')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input class="form-control <?= tem_erro('email') ? 'is-invalid' : '' ?>" id="email" type="text" name="email" value="<?= e(antigo('email', $registro['email'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('email')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="especie">Especie</label>
        <input class="form-control <?= tem_erro('especie') ? 'is-invalid' : '' ?>" id="especie" type="text" name="especie" value="<?= e(antigo('especie', $registro['especie'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('especie')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="tutor">Tutor</label>
        <select class="form-select <?= tem_erro('tutor') ? 'is-invalid' : '' ?>" id="tutor" name="tutor">
            <option value="">Selecione um tutor</option>
            <?php foreach (\Modelos\Cadastro::TUTORES_DISPONIVEIS as $tutor): ?>
                <option value="<?= e($tutor) ?>" <?= antigo('tutor', $registro['tutor'] ?? '') === $tutor ? 'selected' : '' ?>><?= e($tutor) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($mensagem = erro_de('tutor')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="veterinario">Veterinario</label>
        <select class="form-select <?= tem_erro('veterinario') ? 'is-invalid' : '' ?>" id="veterinario" name="veterinario">
            <option value="">Selecione um veterinario</option>
            <?php foreach (\Modelos\Cadastro::VETERINARIOS_DISPONIVEIS as $veterinario): ?>
                <option value="<?= e($veterinario) ?>" <?= antigo('veterinario', $registro['veterinario'] ?? '') === $veterinario ? 'selected' : '' ?>><?= e($veterinario) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($mensagem = erro_de('veterinario')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="procedimento">Serviço</label>
        <select class="form-select <?= tem_erro('procedimento') ? 'is-invalid' : '' ?>" id="procedimento" name="procedimento">
            <option value="">Selecione um serviço</option>
            <?php foreach (\Modelos\Cadastro::SERVICOS_DISPONIVEIS as $servico => $valor): ?>
                <option value="<?= e($servico) ?>" <?= antigo('procedimento', $registro['procedimento'] ?? '') === $servico ? 'selected' : '' ?>>
                    <?= e($servico . ' - R$ ' . number_format($valor, 2, ',', '.')) ?>
                </option>
            <?php endforeach ?>
        </select>
        <div class="mt-2 small text-secondary" id="valor-servico">
            <?php
                $servicoAtual = antigo('procedimento', $registro['procedimento'] ?? '');
                if ($servicoAtual !== '' && isset(\Modelos\Cadastro::SERVICOS_DISPONIVEIS[$servicoAtual])) {
                    echo 'Valor do serviço: R$ ' . number_format(\Modelos\Cadastro::SERVICOS_DISPONIVEIS[$servicoAtual], 2, ',', '.');
                }
            ?>
        </div>
        <?php if ($mensagem = erro_de('procedimento')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <button class="btn btn-success" type="submit" name="finalizar" value="1">Finalizar</button>
        <a class="btn btn-outline-secondary" href="<?= url('cadastro') ?>">Cancelar</a>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('procedimento');
    const valor = document.getElementById('valor-servico');
    const precos = {
        'Banho': 'R$ 100,00',
        'Tosa': 'R$ 150,00',
        'Vacina': 'R$ 130,00',
        'Consulta Completa': 'R$ 380,00'
    };

    function atualizarValor() {
        const servico = select.value;
        valor.textContent = servico && precos[servico]
            ? 'Valor do serviço: ' + precos[servico]
            : '';
    }

    select.addEventListener('change', atualizarValor);
    atualizarValor();
});
</script>

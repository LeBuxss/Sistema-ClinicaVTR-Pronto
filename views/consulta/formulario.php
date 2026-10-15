<div class="mb-4">
    <h1 class="h3 mb-1"><?= e($titulo) ?></h1>
    <p class="text-secondary mb-0">Dados do paciente, do tutor e da consulta.</p>
</div>

<form class="card border-0 shadow-sm p-4 formulario-clinica" method="post" action="<?= url('consulta/' . ($registro ? 'atualizar/' . $registro['id'] : 'salvar')) ?>">
    <?= campo_csrf() ?>
    <div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control <?= tem_erro('nome') ? 'is-invalid' : '' ?>" id="nome" type="text" name="nome" value="<?= e(antigo('nome', $registro['nome'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('nome')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="raca">Raça</label>
        <input class="form-control <?= tem_erro('raca') ? 'is-invalid' : '' ?>" id="raca" type="text" name="raca" maxlength="120" value="<?= e(antigo('raca', $registro['raca'] ?? '')) ?>" placeholder="Ex.: SRD, Labrador">
        <?php if ($mensagem = erro_de('raca')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="datanascimente">Data de nascimento</label>
        <input class="form-control <?= tem_erro('datanascimente') ? 'is-invalid' : '' ?>" id="datanascimente" type="date" name="datanascimente" value="<?= e(antigo('datanascimente', $registro['datanascimente'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('datanascimente')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="data_consulta">Data da consulta</label>
        <input class="form-control <?= tem_erro('data_consulta') ? 'is-invalid' : '' ?>" id="data_consulta" type="date" name="data_consulta" value="<?= e(antigo('data_consulta', $registro['data_consulta'] ?? '')) ?>" min="<?= e(date('Y-m-d')) ?>" data-data-agendamento required>
        <?php if ($mensagem = erro_de('data_consulta')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="sexo">Sexo</label>
        <input class="form-control <?= tem_erro('sexo') ? 'is-invalid' : '' ?>" id="sexo" type="text" name="sexo" value="<?= e(antigo('sexo', $registro['sexo'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('sexo')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="peso">Peso (kg)</label>
        <input class="form-control <?= tem_erro('peso') ? 'is-invalid' : '' ?>" id="peso" type="number" step="0.01" name="peso" value="<?= e(antigo('peso', $registro['peso'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('peso')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="tutor">Tutor</label>
        <select class="form-select <?= tem_erro('tutor') ? 'is-invalid' : '' ?>" id="tutor" name="tutor">
            <option value="">Selecione um tutor</option>
            <?php foreach (\Modelos\Consulta::TUTORES_DISPONIVEIS as $tutor): ?>
                <option value="<?= e($tutor) ?>" <?= antigo('tutor', $registro['tutor'] ?? '') === $tutor ? 'selected' : '' ?>><?= e($tutor) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($mensagem = erro_de('tutor')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="telefone_tutor">Telefone do tutor</label>
        <input class="form-control <?= tem_erro('telefone_tutor') ? 'is-invalid' : '' ?>" id="telefone_tutor" type="tel" name="telefone_tutor" maxlength="30" autocomplete="tel" inputmode="tel" value="<?= e(antigo('telefone_tutor', $registro['telefone_tutor'] ?? '')) ?>" placeholder="(00) 00000-0000">
        <?php if ($mensagem = erro_de('telefone_tutor')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-12">
        <label class="form-label" for="queixa_principal">Queixa principal</label>
        <input class="form-control <?= tem_erro('queixa_principal') ? 'is-invalid' : '' ?>" id="queixa_principal" type="text" name="queixa_principal" maxlength="500" value="<?= e(antigo('queixa_principal', $registro['queixa_principal'] ?? '')) ?>" placeholder="Motivo da consulta ou sintomas relatados">
        <?php if ($mensagem = erro_de('queixa_principal')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-12">
        <label class="form-label" for="observacoes_clinicas">Observações clínicas</label>
        <textarea class="form-control <?= tem_erro('observacoes_clinicas') ? 'is-invalid' : '' ?>" id="observacoes_clinicas" name="observacoes_clinicas" rows="4" maxlength="10000" placeholder="Histórico relevante, alergias, diagnóstico ou recomendações"><?= e(antigo('observacoes_clinicas', $registro['observacoes_clinicas'] ?? '')) ?></textarea>
        <?php if ($mensagem = erro_de('observacoes_clinicas')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="horario_disponivel">Horário da consulta</label>
        <input class="form-control <?= tem_erro('horario_disponivel') ? 'is-invalid' : '' ?>" id="horario_disponivel" type="time" name="horario_disponivel" value="<?= e(antigo('horario_disponivel', $registro['horario_disponivel'] ?? '')) ?>" data-horario-agendamento required>
        <?php if ($mensagem = erro_de('horario_disponivel')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="veterinario_disponivel">Veterinário da consulta</label>
        <select class="form-select <?= tem_erro('veterinario_disponivel') ? 'is-invalid' : '' ?>" id="veterinario_disponivel" name="veterinario_disponivel" required>
            <option value="">Selecione um médico</option>
            <?php foreach (['Dr. Eclis', 'Dra. Bruna', 'Dra. Creuza'] as $medico): ?>
                <option value="<?= e($medico) ?>" <?= antigo('veterinario_disponivel', $registro['veterinario_disponivel'] ?? '') === $medico ? 'selected' : '' ?>><?= e($medico) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($mensagem = erro_de('veterinario_disponivel')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <button class="btn btn-success" type="submit" name="finalizar" value="1">Finalizar</button>
        <a class="btn btn-outline-secondary" href="<?= url('consulta') ?>">Cancelar</a>
    </div>
</form>

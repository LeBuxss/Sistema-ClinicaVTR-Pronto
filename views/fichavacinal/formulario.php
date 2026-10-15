<div class="mb-4">
    <h1 class="h3 mb-1"><?= e($titulo) ?></h1>
    <p class="text-secondary mb-0">Preencha os dados abaixo.</p>
</div>

<form class="card border-0 shadow-sm p-4" method="post" action="<?= url('fichavacinal/' . ($registro ? 'atualizar/' . $registro['id'] : 'salvar')) ?>">
    <?= campo_csrf() ?>
    <div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control <?= tem_erro('nome') ? 'is-invalid' : '' ?>" id="nome" type="text" name="nome" value="<?= e(antigo('nome', $registro['nome'] ?? '')) ?>" required>
        <?php if ($mensagem = erro_de('nome')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="vacinaaplica">Tipo de Vacina</label>
        <select class="form-select <?= tem_erro('vacinaaplica') ? 'is-invalid' : '' ?>" id="vacinaaplica" name="vacinaaplica" required>
            <option value="">Selecione o tipo de vacina</option>
            <?php foreach (\Modelos\Fichavacinal::TIPOS_VACINA as $tipoVacina): ?>
                <option value="<?= e($tipoVacina) ?>" <?= antigo('vacinaaplica', $registro['vacinaaplica'] ?? '') === $tipoVacina ? 'selected' : '' ?>><?= e($tipoVacina) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($mensagem = erro_de('vacinaaplica')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="lote">lote</label>
        <input class="form-control <?= tem_erro('lote') ? 'is-invalid' : '' ?>" id="lote" type="text" name="lote" value="<?= e(antigo('lote', $registro['lote'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('lote')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dataaplicacao">Data Aplicada</label>
        <input class="form-control <?= tem_erro('dataaplicacao') ? 'is-invalid' : '' ?>" id="dataaplicacao" type="date" name="dataaplicacao" value="<?= e(antigo('dataaplicacao', $registro['dataaplicacao'] ?? '')) ?>">
        <?php if ($mensagem = erro_de('dataaplicacao')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dataretorno">Retorno</label>
        <input class="form-control <?= tem_erro('dataretorno') ? 'is-invalid' : '' ?>" id="dataretorno" type="date" name="dataretorno" value="<?= e(antigo('dataretorno', $registro['dataretorno'] ?? '')) ?>" data-retorno-vacinal>
        <?php if ($mensagem = erro_de('dataretorno')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
        <p class="form-text fst-italic d-none mb-0" data-retorno-vacinal-mensagem aria-live="polite">Retorno vacinal daqui 30 dias.</p>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="veterinarioresponsalve">Veterinario</label>
        <select class="form-select <?= tem_erro('veterinarioresponsalve') ? 'is-invalid' : '' ?>" id="veterinarioresponsalve" name="veterinarioresponsalve">
            <option value="">Selecione um veterinario</option>
            <?php foreach (\Modelos\Fichavacinal::VETERINARIOS_DISPONIVEIS as $veterinario): ?>
                <option value="<?= e($veterinario) ?>" <?= antigo('veterinarioresponsalve', $registro['veterinarioresponsalve'] ?? '') === $veterinario ? 'selected' : '' ?>><?= e($veterinario) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($mensagem = erro_de('veterinarioresponsalve')): ?><div class="invalid-feedback d-block"><?= e($mensagem) ?></div><?php endif ?>
    </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <button class="btn btn-success" type="submit" name="finalizar" value="1">Finalizar</button>
        <a class="btn btn-outline-secondary" href="<?= url('fichavacinal') ?>">Cancelar</a>
    </div>
</form>

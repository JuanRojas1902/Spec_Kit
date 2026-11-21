<?php /** DETALLE: ficha de lectura, incluido el detalle maestro de grupos. */ ?>
<h1 class="h3 mb-3">Detalle · <?= escapar($config['titulo']) ?></h1><div class="card"><div class="card-body"><dl class="row mb-0">
<?php foreach (array_unique(array_merge($config['pk'],array_keys($config['campos']))) as $k): ?><dt class="col-sm-4"><?= escapar($config['campos'][$k]['etiqueta']??$k) ?></dt><dd class="col-sm-8"><?= escapar($valores[$k]??'—') ?></dd><?php endforeach; ?></dl>
<?php if ($config['detalle']??false): ?><hr><h2 class="h5">Semilleros</h2><ul><?php foreach (($valores['detalle']??[]) as $s): ?><li><?= escapar($s['nombre']??'') ?></li><?php endforeach; ?></ul><?php endif; ?></div></div>
<a class="btn btn-outline-secondary mt-3" href="<?= escapar(destino($vista)) ?>">Volver</a>

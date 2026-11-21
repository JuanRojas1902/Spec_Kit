<?php /** ERROR: muestra un mensaje controlado sin revelar detalles internos. */ ?>
<div class="alert alert-warning" role="alert"><?= escapar($mensaje) ?></div><a class="btn btn-outline-primary" href="<?= escapar(destino('inicio')) ?>">Volver al panel</a>

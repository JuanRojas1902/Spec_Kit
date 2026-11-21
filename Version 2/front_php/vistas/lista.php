<?php /** LISTADO: presenta los datos de la API y botones según los permisos. */
$columnas=array_unique(array_merge($config['pk'],array_keys($config['campos'])));
// Solo se muestran campos expresamente configurados; no se exponen datos internos.
?>
<div class="d-flex justify-content-between flex-wrap gap-2 mb-3"><h1 class="h3"><?= escapar($config['titulo']) ?></h1><?php if (es_admin()&&in_array('crear',$config['operaciones'],true)): ?><a class="btn btn-primary" href="<?= escapar(destino($vista,['accion'=>'crear'])) ?>">Nuevo registro</a><?php endif; ?></div>
<a class="btn btn-outline-secondary btn-sm mb-3" href="<?= escapar(destino('inicio')) ?>">Volver al panel</a>
<?php if (isset($config['nota'])): ?><p class="text-muted"><?= escapar($config['nota']) ?></p><?php endif; ?>
<?php if ($flash): ?><div class="alert alert-<?= $flash['ok']?'success':'danger' ?>"><?= escapar($flash['mensaje']) ?></div><?php endif; ?>
<?php if (!$res['ok']): ?><div class="alert alert-danger"><?= escapar($res['mensaje']) ?> (HTTP <?= escapar($res['estado']) ?>)</div>
<?php elseif ($res['filas']===[]): ?><div class="alert alert-info">No hay registros disponibles.</div>
<?php else: ?><p class="text-muted"><?= count($res['filas']) ?> registros recibidos. Total informado: <?= escapar($res['total']) ?>.</p>
<div class="table-responsive"><table class="table table-striped table-hover align-middle bg-white"><thead class="table-dark"><tr>
<?php foreach ($columnas as $k): ?><th scope="col"><?= escapar($config['campos'][$k]['etiqueta']??$k) ?></th><?php endforeach; ?><th scope="col">Acciones</th></tr></thead><tbody>
<?php foreach ($res['filas'] as $fila): $ids=claves_de($config,$fila); ?><tr>
<?php foreach ($columnas as $k): $v=$fila[$k]??null; ?><td><?= escapar($v===null?'—':($opciones[$k][(string)(is_scalar($v)?$v:'')]??$v)) ?></td><?php endforeach; ?>
<td><div class="d-flex flex-wrap gap-2"><?php if ($ids!==null): ?>
<?php if (count($config['pk'])===1 && $vista!=='semillero'): ?><a class="btn btn-sm btn-outline-secondary" href="<?= escapar(destino($vista,['accion'=>'ver']+$ids)) ?>">Ver</a><?php endif; ?>
<?php if (es_admin()&&in_array('editar',$config['operaciones'],true)): ?><a class="btn btn-sm btn-outline-primary" href="<?= escapar(destino($vista,['accion'=>'editar']+$ids)) ?>">Editar</a><?php endif; ?>
<?php if (es_admin()&&in_array('retirar',$config['operaciones'],true)): ?><form method="post" action="<?= escapar(destino($vista,['accion'=>'retirar']+$ids)) ?>" onsubmit="return confirm('¿Deseas retirar este registro?');"><input type="hidden" name="csrf" value="<?= escapar(csrf()) ?>"><button type="submit" class="btn btn-sm btn-outline-danger">Retirar</button></form><?php endif; ?>
<?php endif; ?></div></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>

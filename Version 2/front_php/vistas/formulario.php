<?php /** FORMULARIO: convierte las claves foráneas en select y los demás campos en input. */ ?>
<div class="row justify-content-center"><div class="col-lg-8"><h1 class="h3 mb-3"><?= $edicion?'Editar':'Registrar' ?> · <?= escapar($config['titulo']) ?></h1>
<?php if ($errores!==[]||$avisos!==[]): ?><div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach (array_merge($errores,$avisos) as $e): ?><li><?= escapar($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card"><div class="card-body p-4"><form method="post" action="<?= escapar(destino($vista,['accion'=>$edicion?'editar':'crear']+$claves)) ?>">
<input type="hidden" name="csrf" value="<?= escapar(csrf()) ?>">
<?php foreach ($config['campos'] as $k=>$campo): $v=(string)($valores[$k]??''); $fijo=$edicion&&in_array($k,$config['pk'],true); ?>
<div class="mb-3"><label class="form-label" for="<?= escapar($k) ?>"><?= escapar($campo['etiqueta']) ?><?= $campo['requerido']?' *':'' ?></label>
<?php if ($campo['fk']!==null): ?>
<select class="form-select" id="<?= escapar($k) ?>" name="<?= escapar($k) ?>" <?= $campo['requerido']?'required':'' ?> <?= isset($avisos[$k])?'disabled':'' ?>>
<option value="">Selecciona una opción</option>
<?php foreach (($opciones[$k]??[]) as $id=>$texto): ?><option value="<?= escapar($id) ?>" <?= (string)$id===$v?'selected':'' ?>><?= escapar($texto) ?></option><?php endforeach; ?>
</select><div class="form-text">Se muestra el nombre, pero se envía la clave primaria relacionada.</div>
<?php elseif ($campo['tipo']==='textarea'): ?><textarea class="form-control" id="<?= escapar($k) ?>" name="<?= escapar($k) ?>" <?= $campo['requerido']?'required':'' ?>><?= escapar($v) ?></textarea>
<?php else: ?><input class="form-control" id="<?= escapar($k) ?>" name="<?= escapar($k) ?>" type="<?= $campo['tipo']==='int'?'number':'text' ?>" value="<?= escapar($v) ?>" <?= $campo['requerido']?'required':'' ?> <?= $fijo?'readonly':'' ?>><?php endif; ?></div>
<?php endforeach; ?>
<?php if ($config['detalle']??false): $detalle=is_array($valores['detalle']??null)?$valores['detalle']:[]; ?>
<hr><h2 class="h5">Semilleros del grupo</h2><p class="text-muted">Los renglones vacíos no se enviarán. Comprueba el efecto del reemplazo en la API antes de editar datos reales.</p>
<?php for ($i=0;$i<max(5,count($detalle)+3);$i++): ?><div class="mb-2"><label class="visually-hidden" for="sem<?= $i ?>">Semillero <?= $i+1 ?></label><input id="sem<?= $i ?>" class="form-control" name="detalle_nombre[]" placeholder="Semillero <?= $i+1 ?>" value="<?= escapar($detalle[$i]['nombre']??'') ?>"></div><?php endfor; ?>
<?php endif; ?>
<div class="d-flex gap-2 mt-4"><button class="btn btn-primary" type="submit" <?= $avisos!==[]?'disabled':'' ?>>Guardar</button><a class="btn btn-outline-secondary" href="<?= escapar(destino($vista)) ?>">Cancelar</a></div></form></div></div></div></div>

<?php
/**
 * Archivo: puentes_lista.php
 * Capa: Front-End / Vista
 * 
 * Qué es: Vista genérica para listar las parejas de una tabla puente.
 * Por qué está aquí: Muestra las relaciones asignadas y ofrece un botón de retiro («Retirar») por pareja.
 * Qué NO hace: No incluye opciones de edición, ya que las parejas puente se retiran y se asignan de nuevo.
 */

declare(strict_types=1);

$tituloModulo = ucwords(str_replace('_', ' ', $recursoPuente));
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Asignaciones: <?= htmlspecialchars($tituloModulo, ENT_QUOTES, 'UTF-8') ?></h2>
    <a href="/<?= $recursoPuente ?>/nueva" class="btn btn-primary">Asignar Nueva Pareja</a>
</div>

<?php if (!empty($mensajeAlerta)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($mensajeAlerta, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (empty($parejas)): ?>
    <div class="alert alert-info" role="alert">
        Todavía no hay datos de asignaciones en esta sección.
    </div>
<?php else: ?>
    <div class="table-responsive bg-white p-3 rounded shadow-sm border">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <?php 
                    $claves = array_keys($parejas[0]);
                    $clave1 = $claves[0] ?? 'clave1';
                    $clave2 = $claves[1] ?? 'clave2';
                    ?>
                    <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $clave1)), ENT_QUOTES, 'UTF-8') ?></th>
                    <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $clave2)), ENT_QUOTES, 'UTF-8') ?></th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parejas as $p): ?>
                    <?php 
                    $val1 = $p[$clave1] ?? '';
                    $val2 = $p[$clave2] ?? '';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$val1, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)$val2, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end">
                            <form action="/<?= $recursoPuente ?>/<?= $val1 ?>/<?= $val2 ?>/retirar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de retirar esta asignación?');">
                                <button type="submit" class="btn btn-sm btn-danger">Retirar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
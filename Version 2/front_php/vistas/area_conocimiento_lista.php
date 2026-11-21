<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * Vista del listado de areas de conocimiento.
 * Los datos llegan desde la API a traves del
 * controlador del frontend.
 *
 * No ejecuta SQL ni administra tokens JWT.
 */

$filas = is_array($registros ?? null)
    ? $registros
    : [];

$esAdmin = function_exists('frontEsAdminV3')
    && frontEsAdminV3();

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 mb-1">Listado de areas de conocimiento</h2>
        <p class="text-muted mb-0">
            Registros obtenidos desde la API de Investigacion.
        </p>
    </div>

    <?php if ($esAdmin): ?>
        <a href="/area_conocimiento/nuevo"
           class="btn btn-primary">
            Nueva area
        </a>
    <?php endif; ?>
</div>

<?php if ($filas === []): ?>

    <div class="alert alert-info">
        <?php if (!empty($erroresValidacion)): ?>
            No se pudieron mostrar los registros.
            Revisa el mensaje de error de la pagina.
        <?php else: ?>
            No hay areas de conocimiento para mostrar.
        <?php endif; ?>
    </div>

<?php else: ?>

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-striped table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Gran area</th>
                    <th>Area</th>
                    <th>Disciplina</th>

                    <?php if ($esAdmin): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($filas as $fila): ?>
                    <?php
                    if (!is_array($fila)) {
                        continue;
                    }

                    $id = (int) ($fila['id'] ?? 0);
                    ?>

                    <tr>
                        <td>
                            <?= htmlspecialchars(
                                (string) ($fila['id'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                (string) ($fila['gran_area'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                (string) ($fila['area'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                (string) ($fila['disciplina'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <?php if ($esAdmin): ?>
                            <td>
                                <?php if ($id > 0): ?>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="/area_conocimiento/<?= $id ?>/editar">
                                        Editar
                                    </a>

                                    <form
                                        method="POST"
                                        action="/area_conocimiento/<?= $id ?>/retirar"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Retirar esta area?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= htmlspecialchars(
                                                (string) ($csrf ?? ''),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger">
                                            Retirar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>
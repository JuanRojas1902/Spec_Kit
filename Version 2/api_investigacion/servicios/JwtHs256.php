<?php

declare(strict_types=1);

/*
=====================================================
PROYECTO DE INVESTIGACION - VERSION 3
ARCHIVO: servicios/JwtHs256.php

QUE ES:
Componente para emitir y verificar tokens JWT HS256.

POR QUE:
La API debe identificar de manera segura al usuario
autenticado mediante Authorization: Bearer.

QUE NO HACE:
- No consulta MariaDB.
- No comprueba contraseñas.
- No decide si el usuario es administrador.
- No sustituye la autorización de cada ruta.
=====================================================
*/

final class JwtHs256
{
    private const DURACION = 3600; // 60 minutos

    private static function secreto(): string
    {
        $secreto = getenv('JWT_SECRETO');

        if (
            !is_string($secreto) ||
            strlen($secreto) < 32
        ) {
            throw new RuntimeException(
                'JWT_SECRETO no está configurado correctamente.'
            );
        }

        return $secreto;
    }

    private static function codificar(string $datos): string
    {
        return rtrim(
            strtr(base64_encode($datos), '+/', '-_'),
            '='
        );
    }

    private static function decodificar(string $datos): ?string
    {
        if (
            $datos === '' ||
            preg_match('/^[A-Za-z0-9_-]+$/D', $datos) !== 1
        ) {
            return null;
        }

        $relleno = str_repeat(
            '=',
            (4 - strlen($datos) % 4) % 4
        );

        $resultado = base64_decode(
            strtr($datos, '-_', '+/') . $relleno,
            true
        );

        return $resultado === false ? null : $resultado;
    }

    public static function emitir(int $usuarioId): string
    {
        if ($usuarioId <= 0) {
            throw new InvalidArgumentException(
                'El identificador del usuario es inválido.'
            );
        }

        $ahora = time();

        $cabecera = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $contenido = [
            'sub' => (string) $usuarioId,
            'iat' => $ahora,
            'exp' => $ahora + self::DURACION
        ];

        $parte1 = self::codificar(
            json_encode($cabecera, JSON_THROW_ON_ERROR)
        );

        $parte2 = self::codificar(
            json_encode($contenido, JSON_THROW_ON_ERROR)
        );

        $mensaje = $parte1 . '.' . $parte2;

        $firma = hash_hmac(
            'sha256',
            $mensaje,
            self::secreto(),
            true
        );

        return $mensaje . '.' . self::codificar($firma);
    }

    public static function verificar(string $token): ?array
    {
        if ($token === '' || strlen($token) > 4096) {
            return null;
        }

        $partes = explode('.', $token);

        if (count($partes) !== 3) {
            return null;
        }

        [$parte1, $parte2, $parteFirma] = $partes;

        $cabeceraJson = self::decodificar($parte1);
        $contenidoJson = self::decodificar($parte2);
        $firmaRecibida = self::decodificar($parteFirma);

        if (
            $cabeceraJson === null ||
            $contenidoJson === null ||
            $firmaRecibida === null
        ) {
            return null;
        }

        $cabecera = json_decode($cabeceraJson, true);
        $contenido = json_decode($contenidoJson, true);

        if (
            !is_array($cabecera) ||
            !is_array($contenido) ||
            ($cabecera['alg'] ?? null) !== 'HS256' ||
            ($cabecera['typ'] ?? null) !== 'JWT'
        ) {
            return null;
        }

        $firmaEsperada = hash_hmac(
            'sha256',
            $parte1 . '.' . $parte2,
            self::secreto(),
            true
        );

        if (!hash_equals($firmaEsperada, $firmaRecibida)) {
            return null;
        }

        $ahora = time();

        if (
            !isset($contenido['sub'], $contenido['iat'], $contenido['exp']) ||
            !is_string($contenido['sub']) ||
            !is_int($contenido['iat']) ||
            !is_int($contenido['exp']) ||
            $contenido['exp'] <= $ahora ||
            $contenido['iat'] > $ahora + 30 ||
            $contenido['exp'] <= $contenido['iat']
        ) {
            return null;
        }

        $usuarioId = filter_var(
            $contenido['sub'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($usuarioId === false) {
            return null;
        }

        return $contenido;
    }
}

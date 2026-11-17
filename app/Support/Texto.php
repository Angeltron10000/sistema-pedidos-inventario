<?php

namespace App\Support;

use Normalizer;

/**
 * Ayudas para comparar nombres escritos por personas.
 *
 * Problema que resuelve (ejercicio 15 de las pruebas):
 * un texto copiado y pegado puede verse igual a uno tecleado, pero traer
 * caracteres invisibles distintos. Por ejemplo:
 *   - un "espacio especial" (espacio duro, U+00A0) en lugar del espacio normal;
 *   - caracteres de ancho cero (U+200B, U+FEFF) que no se ven;
 *   - una "é" armada en dos piezas ("e" + acento) en lugar de una sola letra.
 * Además, "Café americano" y "café americano" deben considerarse el mismo nombre.
 */
class Texto
{
    /**
     * Deja el texto "limpio" para guardarlo:
     * acentos unificados, espacios especiales convertidos en espacios normales,
     * caracteres invisibles eliminados, espacios repetidos reducidos a uno
     * y sin espacios al principio ni al final.
     *
     * Ejemplo: "  Café\u{00A0}\u{00A0}americano " -> "Café americano"
     */
    public static function limpiar(?string $texto): string
    {
        $texto = (string) $texto;

        // 1) Unificar acentos: "e" + "´" (dos piezas) se vuelve "é" (una sola letra)
        if (class_exists(Normalizer::class)) {
            $texto = Normalizer::normalize($texto, Normalizer::FORM_C) ?: $texto;
        }

        // 2) Quitar caracteres invisibles de ancho cero
        $texto = preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}\x{00AD}]/u', '', $texto) ?? $texto;

        // 3) Cualquier tipo de espacio (normal, duro, tabulador, salto de línea...) se vuelve un espacio normal,
        //    y varios espacios seguidos se reducen a uno
        $texto = preg_replace('/[\p{Z}\s]+/u', ' ', $texto) ?? $texto;

        // 4) Sin espacios al principio ni al final
        return trim($texto);
    }

    /**
     * "Clave" para comparar dos nombres: el texto limpio y en minúsculas.
     * Dos nombres con la misma clave se consideran el mismo nombre.
     *
     * Ejemplo: "CAFÉ  Americano" y "café americano" tienen la misma clave.
     */
    public static function clave(?string $texto): string
    {
        return mb_strtolower(self::limpiar($texto), 'UTF-8');
    }

    /**
     * Deja solo los números de un teléfono, para compararlo.
     * Ejemplo: "(55) 1234-5678" -> "5512345678"
     */
    public static function soloDigitos(?string $telefono): string
    {
        return preg_replace('/\D+/', '', (string) $telefono) ?? '';
    }
}

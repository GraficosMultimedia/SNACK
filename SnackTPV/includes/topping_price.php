<?php
declare(strict_types=1);

/**
 * Precio operativo de un topping sin alterar la base de datos.
 *
 * Prioridad:
 * 1) toppings.precio si es mayor que cero.
 * 2) último precio positivo registrado históricamente en venta_toppings.
 * 3) catálogo de precios establecido en install.sql / catálogo vigente del TPV.
 */
function topping_effective_price(PDO $pdo, array $topping): float
{
    $master = (float)($topping['precio'] ?? 0);
    if ($master > 0) {
        return $master;
    }

    $id = (int)($topping['id'] ?? 0);
    if ($id > 0) {
        try {
            $q = $pdo->prepare('SELECT MAX(precio) FROM venta_toppings WHERE topping_id=? AND precio>0');
            $q->execute([$id]);
            $historical = (float)($q->fetchColumn() ?? 0);
            if ($historical > 0) {
                return $historical;
            }
        } catch (Throwable $e) {
            // Si la tabla histórica no está disponible, continuar con el catálogo fijo.
        }
    }

    $catalog = [
        'oreo' => 10.00,
        'nuez' => 10.00,
        'bombones' => 10.00,
        'chispas' => 10.00,
        'lunetas' => 10.00,
        'almendra' => 15.00,
        'nutella' => 15.00,
        'granola' => 10.00,
        'lechera' => 10.00,
        'granillo de chocolate' => 10.00,
        'granillo de colores' => 10.00,
        'kinder delice' => 20.00,
        'pay de queso' => 20.00,
    ];

    $name = function_exists('mb_strtolower')
        ? mb_strtolower(trim((string)($topping['nombre'] ?? '')), 'UTF-8')
        : strtolower(trim((string)($topping['nombre'] ?? '')));

    return (float)($catalog[$name] ?? 0);
}

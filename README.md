# Symfony: tienda ficticia

Tienda de practica con Symfony 7.4 (LTS), Twig, Doctrine y SQLite. Todo es ficticio y con fines educativos:
no hay pagos reales (solo "pago contra entrega simulado") y no se envia ningun dato a ningun lado.

## Correrla (puerto 8087)

```powershell
cd symfony-tienda
php bin/console doctrine:migrations:migrate --no-interaction   # crea las tablas (var/data_dev.db)
php bin/console app:cargar-catalogo                            # carga los 16 productos de datos/productos.json
php -S localhost:8087 -t public                                # http://localhost:8087
```

El comando `app:cargar-catalogo` es idempotente: se puede repetir sin duplicar productos.
Para empezar de cero, borra `var/data_dev.db` y repite los dos primeros pasos.

## Pruebas

```powershell
php bin/phpunit        # 13 pruebas: unitarias del carrito y recorrido completo de la tienda
```

## Capas (igual que en los otros stacks)

`Controller` (solo delega) -> `Service` (logica) -> `Repository` (Doctrine, unico que habla con la BD).

| Capa | Archivos |
|---|---|
| Controladores | `CatalogoController`, `CarritoController`, `PedidoController` |
| Servicios | `CatalogoService`, `CarritoService` (carrito en sesion), `PedidoService` (transaccion + stock), `CatalogoImportador` |
| Entidades | `Categoria`, `Producto`, `Pedido`, `PedidoItem` (copia de lo comprado) |

## Decisiones que vale la pena mirar

- **CSRF** en los formularios que modifican datos (anadir/quitar del carrito) y en el de pedido.
- **El precio nunca viene del navegador:** el carrito solo guarda ids y cantidades; el precio se lee de la BD.
- **La confirmacion del pedido solo se ve en la sesion que lo hizo** (los ids no se pueden adivinar en la URL).
- **DTO con propiedades nulables:** un campo vacio llega como `null`; con un `string` a secas el formulario
  daba un error 500. Hay una prueba de regresion (`testElFormularioVacioMuestraErroresYNoUnError500`).
- Precios en pesos colombianos sin decimales; la extension `FormatoExtension` (`|cop`) los muestra como `$129.000`.

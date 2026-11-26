# Feature: Catálogo de flyers

> Diseño del 26 nov 2026. No implementar hasta cerrar las preguntas abiertas. La home no se rehace.

## Qué se necesita

Galun quiere vender los flyers que ya arma, aparte del portfolio. Cada semana carga unos cinco o seis. Cada uno se muestra con una imagen para que se entienda cuál es, y el archivo de Photoshop (PSD) es lo que se compra para reeditar.

Hay dos formas de pagar: uno suelto, o una membresía mensual más conveniente. El pago es Mercado Pago.

La idea de «meter Photoshop en la página» es para que alguien que no es diseñador pueda cambiar el flyer y exportarlo sin instalar nada. Eso no se construye como un editor propio. Si entra, entra después, embebido, y solo si un PSD real de Galun se puede editar ahí de forma usable.

## Quién lo usa

- Público: mira el catálogo sin cuenta.
- Comprador: cuenta propia (email y contraseña). No es el usuario admin.
- Admin: el mismo login de siempre. Sube la muestra y el PSD, publica, oculta, ve ventas. Sin roles nuevos.

## Pantallas / flujos

1. La home queda igual. En el nav (y en el menú móvil) se suma un link a una página nueva. El nombre visible queda en «Catálogo» hasta que él elija otro.
2. `catalogo.php`: grilla de flyers publicados. Cada tarjeta es la imagen de muestra, el título y el precio. No es una sección de la home.
3. Ficha del flyer: muestra grande, precio, «Comprar» y, si la membresía está activa, «Descargar».
4. Cuenta: registro, entrada, «Mis compras».
5. Compra suelta: un flyer por checkout. Sale a Mercado Pago y vuelve. Cuando el pago está acreditado, puede descargar el PSD las veces que quiera.
6. Membresía: un plan mensual. Mientras el cobro está al día, descarga cualquiera de los publicados, incluidos los que él cargue después. Al caer la membresía, las descargas nuevas se cortan. Lo que ya bajó queda en su máquina.
7. Admin: listado y alta de flyers. Muestra (JPG o WEBP) y PSD por separado. Publicado sí/no. Orden manual, igual que el resto del sitio. Precio de ese flyer.
8. Admin: precio del plan mensual.
9. Admin, solo lectura: personas registradas y cobros. Nombre, mail, fecha de alta y si la membresía está al día. Cada cobro muestra fecha, persona, si fue un flyer o el mes, el monto y si se acreditó. Arriba, la suma de lo acreditado en el mes. No calcula porcentajes ni reparte la plata.

## Reglas de negocio

Confirmado por él, en sustancia:

- La home no se mezcla con la tienda.
- Vende de a un flyer, y también una membresía mensual que conviene más que comprar de a uno.
- Él carga los archivos. El ritmo es semanal, unos cinco o seis. No es un pack obligatorio.
- El comprador se lleva el PSD para reeditarlo.
- El pago lo resuelve Mercado Pago.
- Primera entrega: vender y descargar el PSD. El editor queda para una etapa posterior, y solo si la prueba con un archivo real cierra.
- La membresía al día permite descargar cualquier flyer publicado, también los que se carguen después. No hay cupo diario.
- Los precios se publican en pesos (ARS). Los carga el admin: cada flyer tiene el suyo y la membresía tiene el suyo. No hay un número fijo en el código. Lo que ya se cobró queda en la venta.
- El comprador puede usar el flyer en sus fiestas. No puede revender el PSD como plantilla.
- La ficha muestra el flyer con marca de agua. El archivo limpio no se descarga sin pago.
- No hay días de gracia. Si el mes no se cobró, ese día se apagan las descargas. El mes que sí se cobró sigue hasta el día que cubre. Si el comprador cancela, no se le cobra el mes siguiente y usa lo que ya pagó hasta que vence.

Propuesta técnica:

- Un checkout, un flyer. No hay carrito de varios en esta versión.
- La muestra con marca de agua se ve sin pagar, para que alguien pueda elegir qué comprar. El PSD no tiene URL pública: lo entrega PHP solo si hay una compra acreditada de ese flyer o una membresía vigente.
- La suscripción la autoriza el comprador una sola vez en Mercado Pago. El sitio no guarda la tarjeta y Galun no manda un link cada mes. Mercado Pago avisa el cobro; si no está acreditado, la cuenta deja de ver las descargas.
- No se guarda la versión que el comprador edite. Si hay editor, la exportación la baja el navegador.
- El panel de cobros es de consulta. No se borra ni se edita un cobro acreditado. Un porcentaje para el estudio, si existe, se acuerda y se liquida afuera: el sistema no lo guarda ni lo descuenta.

## Datos

Tablas nuevas. No se tocan `works`, `visuals` ni `clients`. Motor InnoDB, `utf8mb4_unicode_ci`. Plata en `DECIMAL(12,2)`.

```sql
CREATE TABLE buyers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_buyers_email (email)
);

CREATE TABLE flyers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  preview_path VARCHAR(255) NOT NULL,
  source_path VARCHAR(255) NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'ARS',
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_flyers_public (is_published, sort_order, id)
);

CREATE TABLE plans (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL,
  name VARCHAR(80) NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'ARS',
  interval_unit VARCHAR(16) NOT NULL DEFAULT 'month',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_plans_code (code)
);

CREATE TABLE orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  buyer_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'pending',
  total DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'ARS',
  mp_preference_id VARCHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_orders_buyer (buyer_id, created_at),
  CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES buyers (id) ON DELETE RESTRICT
);

CREATE TABLE order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  flyer_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_order_items_flyer (order_id, flyer_id),
  KEY idx_order_items_flyer (flyer_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_flyer FOREIGN KEY (flyer_id) REFERENCES flyers (id) ON DELETE RESTRICT
);

CREATE TABLE subscriptions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  buyer_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'pending',
  price DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'ARS',
  mp_preapproval_id VARCHAR(64) NULL,
  current_period_end DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_subscriptions_mp (mp_preapproval_id),
  KEY idx_subscriptions_buyer (buyer_id, status),
  CONSTRAINT fk_subscriptions_buyer FOREIGN KEY (buyer_id) REFERENCES buyers (id) ON DELETE RESTRICT,
  CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans (id) ON DELETE RESTRICT
);

CREATE TABLE payments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NULL,
  subscription_id BIGINT UNSIGNED NULL,
  mp_payment_id VARCHAR(64) NOT NULL,
  status VARCHAR(32) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'ARS',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payments_mp (mp_payment_id),
  KEY idx_payments_order (order_id),
  KEY idx_payments_subscription (subscription_id),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE RESTRICT,
  CONSTRAINT fk_payments_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE RESTRICT
);
```

`orders.status`: `pending`, `paid`, `failed`, `refunded`. El monto de `payments` no se edita: un reembolso es otra fila, cuando exista esa operación.

Puede descargar el flyer si tiene un `order_items` de un pedido `paid` de ese flyer, o una `subscriptions` `active` con `current_period_end` en el futuro.

Migración: `sql/updates/` nuevo, más el reflejo en `sql/schema.sql`. No hay datos viejos que mover. Dump de producción antes de aplicarla.

## Endpoints / integraciones

- `api/mp-webhook.php` — notificación de Mercado Pago. Verifica la firma, busca el pago, y si ese `mp_payment_id` ya existe no lo aplica de nuevo. Recién ahí pasa el pedido a `paid` o actualiza la membresía. Responde JSON.
- Checkout Pro de Mercado Pago para la compra suelta (el comprador paga en Mercado Pago, no en un formulario nuestro).
- Suscripción de Mercado Pago (preapproval) para el mes. El token vive en `config.local.php`, nunca en el repo.
- Descarga: un PHP autenticado que lee el PSD y lo manda. La carpeta del PSD tiene `Require all denied`.
- No hay email transaccional en esta versión: «Mis compras» es donde vuelve a bajar el archivo.

## Editor (fase posterior)

No se incrusta Adobe Photoshop. Una persona sin cuenta de Adobe no puede editar ahí.

Photopea abre un PSD en el navegador, deja editar capas y puede devolver JPG, PNG o PSD. Se embebería en la ficha, recién después de comprobar que ese comprador puede usar ese flyer. El archivo no se manda a un servidor nuestro al exportar.

Límite honesto: la pantalla de Photopea es la de un programa de diseño. No es un formulario de «cambiá la fecha y el DJ». Alguien que nunca abrió capas puede trabarse igual. Por eso no es la primera entrega. Antes de prometerlo hay que probar con un PSD real de Galun y leer las condiciones actuales de Photopea para un sitio que cobra.

Si esa prueba no cierra, el producto sigue en pie: se venden los PSD y los abre en su Photoshop quien sepa.

## Módulos, en orden

1. **Catálogo visible.** Página nueva, link en el nav, admin para subir muestra + PSD, publicar y ordenar. Sin cobro. Sirve para cargar los primeros y ver la página.
2. **Compra suelta.** Cuenta de comprador, Checkout Pro, webhook, descarga del PSD.
3. **Membresía.** Plan mensual, suscripción de Mercado Pago, acceso a todo el catálogo publicado mientras esté al día.
4. **Editor.** Solo si la prueba con Photopea cierra. No bloquea 1–3.

## Fuera de alcance

- Rehacer la home, la intro o los carruseles.
- Un editor de capas hecho por nosotros.
- Cupo de descargas por día.
- Carrito de varios flyers.
- Pack semanal con precio propio, hasta que él lo pida con un número.
- Factura de AFIP.
- Calcular o descontar un porcentaje del estudio. La suma del mes está a la vista para hacerlo a mano.
- Guardar el flyer ya editado por el comprador.
- Mover el sitio al servidor dedicado. Sigue no crítico, en Hostinger.

## Preguntas abiertas

- ¿De qué peso salen los PSD? El admin ya vio un corte del servidor en una subida de unos 2 MB; un PSD grande hay que probarlo en el hosting antes de prometer la carga semanal.
- Nombre del botón, cuando lo tenga. Mientras tanto: Catálogo.

## Checklist de cierre

- [x] Preguntas de precio y de acceso cerradas
- [ ] Implementado siguiendo la skill del stack
- [ ] Casos borde probados (skill `testing`)
- [ ] Revisión pasada (skill `review`)
- [ ] `project.md` actualizado

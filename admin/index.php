<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';

admin_head('Panel');
?>
<p>Desde acá se cargan los trabajos, los visuales, los clientes y los flyers del catálogo. El archivo tiene que venir ya exportado: el sitio no lo comprime.</p>
<p><a class="button" href="works.php">Trabajos</a> <a class="button quiet" href="visuals.php">Visuales</a> <a class="button quiet" href="clients.php">Clientes</a> <a class="button quiet" href="flyers.php">Flyers</a> <a class="button quiet" href="sales.php">Cobros</a></p>
<?php admin_foot();
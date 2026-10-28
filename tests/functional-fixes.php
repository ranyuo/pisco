<?php
declare(strict_types=1);
require dirname(__DIR__).'/portal-private/bootstrap.php';
$path = getenv('PORTAL_CONFIG_FILE');
if (!$path || !str_ends_with($path, 'config.test.php')) throw new RuntimeException('Solo config.test.php.');
$config = require $path;
if (!str_ends_with($config['db_name'], '_test')) throw new RuntimeException('Solo base _test.');
$app = portal_app();
function verify(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
    echo "OK $message\n";
}
$fixture = ['tupa' => [
    ['id'=>'a','codigo'=>'4.1','categoria'=>'12.-ALQUILERES','denominacion'=>'4.1 ATRÍCULA REGULAR','monto'=>10],
    ['id'=>'b','codigo'=>'X','categoria'=>'6.- CONSTANCIA Y CERTIFICADOS','denominacion'=>'Concepto personalizado','monto'=>20],
    ['id'=>'c','codigo'=>'4.1','categoria'=>'Categoría propia','denominacion'=>'Título personalizado','monto'=>30],
]];
$normalized = PortalContent::normalize($fixture);
verify($normalized['tupa'][0]['denominacion'] === '4.1 MATRÍCULA REGULAR', 'Corrección de errata heredada');
verify(array_column($normalized['tupa'],'categoria') === ['12.- ALQUILERES','6.- CONSTANCIAS Y CERTIFICADOS','Categoría propia'], 'Categorías canónicas conservan las personalizadas');
verify(array_column($normalized['tupa'],'id') === array_column($fixture['tupa'],'id') && array_column($normalized['tupa'],'monto') === [10,20,30], 'Normalización conserva identificadores y montos');
verify($normalized['tupa'][2]['denominacion'] === 'Título personalizado', 'No se reemplazan títulos editados');
verify(PortalContent::normalize($normalized) === $normalized, 'Normalización repetible');
// Prueba de actualización sobre la base desechable preparada por parity.php.
$db = $app->db();
$row = $db->query('SELECT data FROM pisco_state WHERE id=1')->fetchColumn();
$data = json_decode($row, true, flags:JSON_THROW_ON_ERROR);
foreach ($data['tupa'] as &$concept) {
    if ($concept['codigo'] === '4.1') $concept['denominacion'] = '4.1 ATRÍCULA REGULAR';
    if ($concept['categoria'] === '12.- ALQUILERES') $concept['categoria'] = '12.-ALQUILERES';
    if ($concept['categoria'] === '6.- CONSTANCIAS Y CERTIFICADOS') $concept['categoria'] = '6.- CONSTANCIA Y CERTIFICADOS';
} unset($concept);
$data['academico']['publicados'] = []; // Reproduce el mapa vacío recibido desde PHP.
$db->prepare('UPDATE pisco_state SET data=?,revision=revision+1 WHERE id=1')->execute([json_encode($data, JSON_THROW_ON_ERROR)]);
$expected = PortalContent::normalize($data);
PortalSchema::apply($db);
$first = $db->query('SELECT data,revision FROM pisco_state WHERE id=1')->fetch();
verify(json_decode($first['data'],true,flags:JSON_THROW_ON_ERROR) === $expected, 'Actualización corrige datos guardados sin reemplazar el resto del estado');
PortalSchema::apply($db);
verify($db->query('SELECT data,revision FROM pisco_state WHERE id=1')->fetch() === $first, 'Actualizar dos veces conserva contenido y revisión');

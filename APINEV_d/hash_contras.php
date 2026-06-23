<?php
// generar_sql.php — Colócalo en la raíz del proyecto
// Ábrelo en: http://localhost/sintesu/generar_sql.php
// BÓRRALO después de usarlo

$usuarios = [
    ['id' => 1001, 'pass' => 'Admin123'],
    ['id' => 2001, 'pass' => 'Docente123'],
    ['id' => 2002, 'pass' => 'Docente123'],
    ['id' => 3001, 'pass' => 'Est123'],
    ['id' => 3002, 'pass' => 'Est123'],
    ['id' => 3003, 'pass' => 'Est123'],
    ['id' => 4001, 'pass' => 'Coord123'],
];

echo "<pre style='font-family:monospace; font-size:13px; background:#1e1e1e; color:#d4d4d4; padding:20px;'>";
echo "-- ==============================\n";
echo "-- ACTUALIZAR CONTRASEÑAS A BCRYPT\n";
echo "-- No borra ningún dato, solo actualiza las contraseñas\n";
echo "-- ==============================\n\n";

echo "USE sintesu;\n\n";

foreach ($usuarios as $u) {
    $hash = password_hash($u['pass'], PASSWORD_BCRYPT);
    echo "UPDATE Usuarios SET contrasenia = '$hash' WHERE idUsuario = {$u['id']};\n\n";
}

echo "-- ==============================\n";
echo "-- VERIFICA\n";
echo "-- ==============================\n";
echo "SELECT idUsuario, nombres, apPaterno, idRol FROM Usuarios;\n";
echo "</pre>";

echo "<br><strong style='color:green;'>✔ Copia el SQL de arriba, pégalo en phpMyAdmin → pestaña SQL → Ejecutar</strong>";
echo "<br><strong style='color:red;'>⚠ Borra este archivo del servidor después de usarlo</strong>";

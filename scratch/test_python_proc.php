<?php
$pythonExe = 'C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\.venv\\Scripts\\python.exe';
$script = 'C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\11_modelos_guardados\\verificar_modelos.py';

echo "Testing proc_open in PHP...\n";
$cmd = '"' . $pythonExe . '" "' . $script . '"';
$fullCmd = 'cmd /c "' . $cmd . '"';
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];
$process = proc_open($fullCmd, $descriptors, $pipes);
if (is_resource($process)) {
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $ret = proc_close($process);
    echo "Process returned $ret\n";
    echo "Stdout length: " . strlen($stdout) . "\n";
    echo "First 200 chars of stdout:\n" . substr($stdout, 0, 200) . "\n";
    if (!empty($stderr)) {
        echo "Stderr: $stderr\n";
    }
} else {
    echo "proc_open failed!\n";
}

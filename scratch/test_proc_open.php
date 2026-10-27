<?php
require_once __DIR__ . '/../app/config/config.php';

$pythonBin = PYTHON_BIN;
$enginePath = ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . 'inferencia_engine.py';
$inputFile = ROOT_PATH . 'scratch' . DIRECTORY_SEPARATOR . 'sample_input.json';

echo "Testing proc_open with array:\n";
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];

$cmd = [$pythonBin, $enginePath, '--file', $inputFile];
$proc = @proc_open($cmd, $descriptors, $pipes);
if (is_resource($proc)) {
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($proc);
    echo "Array mode: Exit code = $code\n";
    echo "Output: " . substr($out, 0, 150) . "\n";
    if ($err) echo "Err: $err\n";
} else {
    echo "Array mode failed to open.\n";
}

echo "\nTesting cmd with outer quotes:\n";
$cmdStr = 'cmd /c ""' . $pythonBin . '" "' . $enginePath . '" --file "' . $inputFile . '""';
$proc2 = proc_open($cmdStr, $descriptors, $pipes2);
if (is_resource($proc2)) {
    fclose($pipes2[0]);
    $out2 = stream_get_contents($pipes2[1]);
    $err2 = stream_get_contents($pipes2[2]);
    fclose($pipes2[1]);
    fclose($pipes2[2]);
    $code2 = proc_close($proc2);
    echo "Cmd string mode: Exit code = $code2\n";
    echo "Output: " . substr($out2, 0, 150) . "\n";
    if ($err2) echo "Err: $err2\n";
}

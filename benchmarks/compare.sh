#!/bin/sh
# Runs the same benchmark against Cycle, Eloquent and Arcano and prints the speedups.
set -e
cd "$(dirname "$0")"
PHP="nice -n 10 php -d opcache.enable_cli=1 -d opcache.jit=tracing -d opcache.jit_buffer_size=64M -d zend.assertions=1"
[ "$1" = "--arcano-only" ] || $PHP bench-cycle/bench.php > /dev/null
[ "$1" = "--arcano-only" ] || $PHP bench-eloquent/bench.php > /dev/null
$PHP arcano/benchmarks/bench.php > /dev/null
php -r '
$c = json_decode(file_get_contents("bench-cycle/results.json"), true);
$e = json_decode(file_get_contents("bench-eloquent/results.json"), true);
$a = json_decode(file_get_contents("arcano/benchmarks/results.json"), true);
printf("%-15s %7s %8s %7s %6s %9s %9s %8s %10s %10s\n", "scenario", "cycle", "eloquent", "arcano", "pdo", "vs cycle", "vs eloq", "target", "ovh cycle", "ovh eloq");
foreach ($c as $k => [$cm, $pm]) {
    [$em, $ep] = $e[$k]; [$am, $ap] = $a[$k];
    // overhead = ORM time minus the hand-written PDO time of the same run
    $ao = max($am - $ap, 0.01);
    printf("%-15s %7.1f %8.1f %7.1f %6.1f %8.2fx %8.2fx %8.1f %9.2fx %9.2fx\n", $k, $cm, $em, $am, $pm, $cm / $am, $em / $am, min($cm, $em) / 5, ($cm - $pm) / $ao, ($em - $ep) / $ao);
}'

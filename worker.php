<?php declare(strict_types=1);

use React\ChildProcess\Process;
use React\EventLoop\Loop;

require __DIR__ . '/vendor/autoload.php';

function worker_process(string $command, ?callable $after = NULL): void {
  print "Running $command" . PHP_EOL;
  $process = new Process($command);
  $process->start();
  $process->stdout->on('data', static fn ($chunk) => print $chunk . PHP_EOL);
  $process->stderr->on('data', static fn ($chunk) => print $chunk . PHP_EOL);
  $process->on('exit', static function (?int $exitCode, $termSignal) use ($command, $after): void {
    if ($exitCode === NULL) {
      print "command '$command' terminated with signal: $termSignal";
    }
    elseif ($after !== NULL) {
      $after();
    }
  });
}

Loop::addTimer(1.0, static function () {
  worker_process(__DIR__ . '/vendor/bin/drush updb --yes',
    static fn () => worker_process(__DIR__ . '/vendor/bin/drush cim --yes')
  );
});
Loop::addPeriodicTimer(600.0, static function () {
  worker_process(__DIR__ . '/vendor/bin/drush cron');
});

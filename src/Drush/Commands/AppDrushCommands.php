<?php declare(strict_types=1);

namespace App\Drush\Commands;

use Consolidation\SiteProcess\Util\Tty;
use Drupal\Component\Utility\Crypt;
use Drush\Attributes as CLI;
use Drush\Boot\DrupalBootLevels;
use Drush\Commands\DrushCommands;
use Drush\Drush;

final class AppDrushCommands extends DrushCommands {

  /**
   * @param array{sqlite: bool} $options
   */
  #[CLI\Command(name: 'app:env:generate', aliases: ['env:generate'])]
  #[CLI\Option(name: 'sqlite', description: 'Set the DB_CONNECTION to SQLite by default')]
  // @phpstan-ignore-next-line
  #[CLI\Bootstrap(level: DrupalBootLevels::NONE)]
  public function envGenerate(array $options = ['sqlite' => false]): void {
    $boot_manager = Drush::bootstrapManager();
    $app_path = $boot_manager->getComposerRoot();

    $envPath = $app_path . '/.env';
    if (!file_exists($envPath)) {
      copy($app_path . '/.env.example', $envPath);
    }

    $key = Crypt::randomBytesBase64(55);
    $existingKey = $_ENV['DRUPAL_HASH_SALT'] ?? '';
    $escapedHashSalt = preg_quote('=' . $existingKey, '/');

    $patterns = ["/^DRUPAL_HASH_SALT{$escapedHashSalt}/m"];
    $replacements = ['DRUPAL_HASH_SALT='.$key];

    if ($options['sqlite']) {
      $patterns[] = "/^DB_CONNECTION=mysql/m";
      $replacements[] = 'DB_CONNECTION=sqlite';
    }

    $envContents = file_get_contents($envPath);
    assert(is_string($envContents));
    file_put_contents($envPath, preg_replace(
      $patterns,
      $replacements,
      $envContents
    ));
  }

}

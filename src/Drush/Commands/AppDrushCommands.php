<?php declare(strict_types=1);

namespace App\Drush\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\Component\Utility\Crypt;
use Drush\Attributes as CLI;
use Drush\Boot\DrupalBootLevels;
use Drush\Commands\DrushCommands;
use Drush\Drush;

/**
 * @phpcs:disable Squiz.WhiteSpace.FunctionSpacing.Before
 */
final class AppDrushCommands extends DrushCommands {

  /**
   * @param array{sqlite: bool} $options
   *   The options.
   */
  #[CLI\Command(name: 'app:env:generate', aliases: ['env:generate'])]
  #[CLI\Option(name: 'sqlite', description: 'Set the DB_CONNECTION to SQLite by default')]
  #[CLI\Bootstrap(level: DrupalBootLevels::NONE)]
  public function envGenerate(array $options = ['sqlite' => FALSE]): void {
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
    $replacements = ['DRUPAL_HASH_SALT=' . $key];

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

  #[CLI\Command(name: 'app:content:generate')]
  #[CLI\Bootstrap(level: DrupalBootLevels::FULL)]
  public function contentGenerate(): CommandResult {
    // @phpstan-ignore-next-line
    $entity_type_manager = \Drupal::entityTypeManager();

    $distillery_storage = $entity_type_manager->getStorage('distillery');
    $distillery_count = $distillery_storage->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();
    if ($distillery_count > 0) {
      return CommandResult::dataWithExitCode('Distilleries exist, not generating content.', 1);
    }
    $whiskey_storage = $entity_type_manager->getStorage('whiskey');
    $whiskey_count = $whiskey_storage->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();
    if ($whiskey_count === 0) {
      return CommandResult::dataWithExitCode('Whiskies exist, not generating content.', 1);
    }

    $data = [
      [
        [
          'name' => 'Woodford Reserve',
          'phone' => '859.879.1812',
          'website' => 'www.woodfordreserve.com',
          'address' => [
            'country_code' => 'US',
            'locality' => 'Versailles',
            'administrative_area' => 'KY',
            'postal_code' => '40383',
            'address_line1' => '7855 McCracken Pike',
          ],
        ],
        [
          'Woodford Reserve Straight Bourbon Whiskey',
          'Woodford Reserve Double Oaked',
          'Woodford Reserve Malt Whiskey',
          'Woodford Reserve Rye Whiskey',
          'Woodford Reserve Wheat Whiskey',
          'Woodford Reserve Baccarat Edition',
          'Woodford Reserve Distillery Series',
        ],
      ],
      [
        [
          'name' => 'Old Rip Van Vinkle',
          'website' => 'https://www.oldripvanwinkle.com/',
        ],
        [
          'Old Rip Van Winkle 10 Year',
          'Van Winkle Special Reserve',
          "Pappy Van Winkle's Family Reserve 15 Year",
          "Pappy Van Winkle's Family Reserve 20 Year",
          "Pappy Van Winkle's Family Reserve 23 Year",
          "Van Winkle Family Reserve Rye",
        ],
      ],
    ];
    foreach ($data as $item) {
      $distillery = $distillery_storage->create($item[0]);
      $distillery->save();
      foreach ($item[1] as $name) {
        $whiskey_storage->create([
          'name' => $name,
          'distillery' => $distillery->id(),
        ])->save();
      }
    }

    return CommandResult::dataWithExitCode('Added sample data', 0);
  }

}

<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Asset;

use Drupal\Core\Asset\CssOptimizer as CoreCssOptimizer;

final class CssOptimizer extends CoreCssOptimizer {

  /**
   * @todo this needs refactor when color removed in drupal core release.
   */
  public function rewriteFileURI($matches) {
    // Prefix with base and remove '../' segments where possible.
    $path = $this->rewriteFileURIBasePath . $matches[1];
    $last = '';
    while ($path != $last) {
      $last = $path;
      $path = preg_replace('`(^|/)(?!\.\./)([^/]+)/\.\./`', '$1', $path);
    }
    // Switched generateString to generateAbsoluteString.
    return 'url(' . $this->fileUrlGenerator->generateAbsoluteString($path) . ')';
  }

}

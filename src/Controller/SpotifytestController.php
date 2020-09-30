<?php
/**
 * @file
 * Contains \Drupal\spotifytest\Controller\SpotifytestController.
 */
namespace Drupal\spotifytest\Controller;

class SpotifytestController {
  public function content($spotifyid) {
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello, Spotify!' . $spotifyid),
    );
  }
}
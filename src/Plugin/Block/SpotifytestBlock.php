<?php

namespace Drupal\spotifytest\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Eventslideshow' Block.
 *
 * @Block(
 *   id = "spotifytest_block",
 *   admin_label = @Translation("SpotifyTest  Block"),
 *   category = @Translation("Spotify test"),
 * )
 */
class SpotifytestBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#theme' => 'spotifytest_artistslist_block',
      '#nodes_build' => $this->getNodesBuild(),
    );
  }

  /**
   * This function return max 20 artist data.
   * @return array
   */
  protected function getNodesBuild() {

    $output = [];
    $spotify_token = "MDIxYjY0ODRjNjgxNDRkZDliNmFhNjMyZjQwNzRkMjg6NjFjYzQzNzY3MDU1NDFhMGExMWJiZWFiYzE2YzA2OTg=";
    $config = $this->getConfiguration();
    $quantity = isset($config['spotifytest_block']) && $config['spotifytest_block'] >= 1 && $config['spotifytest_block'] <= 20 ? $config['spotifytest_block'] : 20;


    // Download the artists from Spotify
    // Get the token
    $endpoint = "https://accounts.spotify.com/api/token";
    $options = [
      'connect_timeout' => 30,
      'debug' => false,
      'headers' => array(
        'Authorization' => "Basic $spotify_token"
      ),
      'form_params' => [
        'grant_type' => 'client_credentials',
      ],
      'verify' => true,
    ];


    try {
      $client = \Drupal::httpClient();
      $request = $client->request('POST', $endpoint, $options);
    }
    catch (RequestException $e) {
      // Log the error.
      watchdog_exception('custom_modulename', $e);
    }

    $responseStatus = $request->getStatusCode();
    $body = $request->getBody()->getContents();

    $response = json_decode($body);
    $access_token = $response->access_token;

    if (!empty($access_token)) {
      // Get the author list
      $endpoint = "https://api.spotify.com/v1/search?q=a&type=artist&limit=$quantity";
      $options = [
        'connect_timeout' => 30,
        'debug' => false,
        'headers' => array(
          'Authorization' => "Bearer  $access_token",
          'Content-Type' => "application/json",
          'Accept' => "application/json",
        ),
        'verify' => true,
      ];

      try {
        $client = \Drupal::httpClient();
        $request = $client->request('GET', $endpoint, $options);
      }
      catch (RequestException $e) {
        // Log the error.
        watchdog_exception('custom_modulename', $e);
      }

      $responseStatus = $request->getStatusCode();
      $body = $request->getBody()->getContents();

      $response = json_decode($body);
      $artists = $response->artists;


      // Collect the artists' names and ids
      if (!empty($artists->items)) {
        foreach ($artists->items as $key => $artist) {
          $output[] = ["id" => $artist->id, "name" => $artist->name];
        }
      }
    }
    else {
      $output = ["error" => t("Access token is missing.")];
    }

    return ($output);
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['spotifytest_block'] = array(
      '#type' => 'number',
      '#title' => t('Number of Artits'),
      '#description' => t('The number should be between 0 and 20.'),
      '#min' => 1,
      '#max' => 20,
      '#step' => 1,
      '#default_value' => isset($config['spotifytest_block']) ? $config['spotifytest_block'] : '',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['spotifytest_block'] = $values['spotifytest_block'];
  }

}

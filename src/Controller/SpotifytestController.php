<?php

/**
 * @file
 * Contains \Drupal\spotifytest\Controller\SpotifytestController.
 */

namespace Drupal\spotifytest\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;

class SpotifytestController {

  public function content($spotifyid) {

    if (!empty($spotifyid)) {

      // Check_Plain
      $string = new FormattableMarkup($spotifyid, []);
      $spotifyid = Html::escape($string);

      // Spotify crendentials
      $spotify_token = "MDIxYjY0ODRjNjgxNDRkZDliNmFhNjMyZjQwNzRkMjg6NjFjYzQzNzY3MDU1NDFhMGExMWJiZWFiYzE2YzA2OTg=";


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
        $endpoint = "https://api.spotify.com/v1/artists/$spotifyid";
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

        // Collect the artist's data
        if (!empty($response->name)) {

          $output = ["name" => $response->name,
            "external_url" => $response->external_urls->spotify,
            "popularity" => $response->popularity,
            "error" => "",
          ];
        }
        else {
          $output = ["error" => t("Author's data is missing.")];
        }
      }
      else {
        $output = ["error" => t("Access token is missing.")];
      }
    }
    else {
      $output = ["error" => t("Spotify id is missing.")];
    }

    return array(
      '#theme' => 'spotifytest_artist_page',
      '#artist' => $output,
    );
  }

}

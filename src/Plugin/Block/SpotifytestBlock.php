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

    $config = $this->getConfiguration();
    $quantity = isset($config['spotifytest_block']) ? $config['spotifytest_block'] : 0;

    return ("Max number: " . $quantity);
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
      '#min' => 0,
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

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (is_int($form_state->getValue('spotifytest_block'))) {
      $form_state->setErrorByName('spotifytest_block', $this->t('Please use integer numbers'));
    }
  }

}

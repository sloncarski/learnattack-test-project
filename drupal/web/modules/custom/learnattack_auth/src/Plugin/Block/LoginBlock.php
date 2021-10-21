<?php

namespace Drupal\learnattack_auth\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block with a list of Articles within same series.
 *
 * @Block(
 *   id = "login_block",
 *   admin_label = @Translation("Login Block"),
 * )
 */
class LoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $account;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('current_user')
      );
  }

  /**
   * Builds and returns the renderable array for this block plugin.
   *
   * If a block should not be rendered because it has no content, then this
   * method must also ensure to return no content: it must then only return an
   * empty array, or an empty array with #cache set (with cacheability metadata
   * indicating the circumstances for it being empty).
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   * @see \Drupal\block\BlockViewBuilder
   */
  public function build() {
    $logoutUrl = Url::fromRoute('user.logout')->setAbsolute(TRUE)->toString();
    $loginUrl = Url::fromRoute('learnattack_auth.login_page')->setAbsolute(TRUE)->toString();

    $markup = $this->account->isAuthenticated()
      ? $this->t('<a href="'.$logoutUrl.'">Log out</a>')
      : $this->t('<a href="'.$loginUrl.'">Log in</a>');

    return [
      '#markup' => $markup
    ];
  }
}
<?php

namespace Drupal\learnattack_auth\Form;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\learnattack_auth\Services\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoginForm extends FormBase {

  protected $userService;
  protected $messenger;

  public function __construct(UserService $userService, MessengerInterface $messenger) {
    $this->userService = $userService;
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('learnattack_auth.user_service'),
      $container->get('messenger')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'learnattack_login_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['auth_code'] = array(
      '#type' => 'textfield',
      '#title' => t('Code:'),
      '#required' => TRUE,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Login'),
      '#button_type' => 'primary',
    );
    $form['#cache']['max-age'] = 0;
    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param FormStateInterface $form_state
   *   The current state of the form.
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $isUserLoggedIn = $this->userService->login($form_state->getValue('auth_code'));
    if ($isUserLoggedIn) {
      $form_state->setRedirectUrl(Url::fromRoute('entity.node.canonical', ['node' => 2]));
    } else {
      $this->messenger->addError('Please, enter the correct code');
    }
  }
}
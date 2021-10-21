<?php

namespace Drupal\learnattack_auth\Form;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\learnattack_auth\Services\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RegisterForm extends FormBase {

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
    return 'learnattack_register_form';
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
    $form['register_email'] = array(
      '#type' => 'email',
      '#title' => t('Email address:'),
      '#required' => TRUE,
    );
    $form['register_username'] = array(
      '#type' => 'textfield',
      '#title' => t('Username:'),
      '#required' => TRUE,
    );
    $form['register_password_confirm'] = array(
      '#type' => 'password_confirm',
      '#required' => TRUE,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Register'),
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
   * @throws EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $formStateArr = [
      'register_email' => $form_state->getValue('register_email'),
      'register_username' => $form_state->getValue('register_username'),
      'register_password_confirm' => $form_state->getValue('register_password_confirm')
    ];
    $registeredUser = $this->userService->register($formStateArr);
    if ($registeredUser['result']) {
      $this->messenger->addMessage('The auth code is sent to the email address');
    } else {
      $this->messenger->addError('Unable to register user, please contact our administrators');
    }
  }
}
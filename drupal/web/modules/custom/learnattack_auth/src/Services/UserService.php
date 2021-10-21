<?php

namespace Drupal\learnattack_auth\Services;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Mail\MailManagerInterface;

class UserService {

  protected $entityTypeManager;
  protected $mailManager;

  public function __construct(EntityTypeManager $entityTypeManager, MailManagerInterface $mailManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->mailManager = $mailManager;
  }

  public function login($authCode) {
    $users = $this->entityTypeManager->getStorage('user')
                  ->loadByProperties(['field_auth_code' => $authCode]);
    $user = reset($users);
    if ($user) {
      user_login_finalize($user);
      return true;
    }
    return false;
  }

  public function register($formStateArr) {
    $userCode = uniqid();
    $emailAddress = $formStateArr['register_email'];
    $this->entityTypeManager->getStorage('user')->create([
      'email' => $emailAddress,
      'name' => $formStateArr['register_username'],
      'pass' => $formStateArr['register_password_confirm'],
      'status' => 1,
      'field_auth_code' => $userCode
    ])->save();
    $params['body'] = 'Code: ' . $userCode;
    return $this->mailManager
                ->mail('learnattack_auth', 'key', $emailAddress, 'en', $params);
  }
}
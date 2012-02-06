<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EEEmailService
 *
 * @author georgeee
 */
class LEmailService extends EAuthServiceBase implements IAuthService {

    const ERROR_NONE = 0;
    const ERROR_AUTH_FAILED = 1;
    const ERROR_ACTIVATION_MAIL_SENT = 2;
    const ERROR_ACTIVATION_MAIL_FAILED = 3;
    const ERROR_UNRECOGNIZED = 4;

    protected $name = 'email';
    protected $title = 'E-mail';
    protected $type = 'email';
    public $email;
    public $password;
    public $errorCode;
    
    public $user = null;
    
    public function authenticate() {
        $email = $this->email;
        $password = $this->password;
        $this->authenticated = false;

        if (!isset($email) || !isset($password)) {
            return false;
        }
        $account = LAccount::model()->findByAttributes(array('service' => 'email', 'id' => $email));
        if (!isset($account)) { //Performing the registration
            $mixed = LilyModule::instance()->accountManager->performRegistration($email, $password, null, null, $this->user);
            if (LilyModule::instance()->accountManager->activate) {
                if (LilyModule::instance()->accountManager->error_code == 0) {
                    $this->errorCode = self::ERROR_ACTIVATION_MAIL_SENT;
                } else {
                    $this->errorCode = self::ERROR_ACTIVATION_MAIL_FAILED;
                }
            } else {
                if (!isset($mixed))
                    $this->errorCode = self::ERROR_UNRECOGNIZED;
                else {
                    $this->errorCode = self::ERROR_NONE;
                    $this->attributes['displayId'] = $this->id = $email;
                    $this->authenticated = true;
                }
            }
        } else {
            $password_hash = LilyModule::instance()->hash($password);
            if ($password_hash == $account->data->password) {
                $this->attributes['id'] = $email;
                $this->authenticated = true;
                $this->errorCode = self::ERROR_NONE;
            } else {
                $this->errorCode = self::ERROR_AUTH_FAILED;
            Yii::log("LEmailService auth failed: $password_hash is not {$account->data->password}.", 'info', 'lily.LEmailService.info');
            }
        }
            Yii::log("LEmailService auth resulted with code $this->errorCode.", 'info', 'lily.LEmailService.info');
        return $this->authenticated;
    }
    protected function fetchAttributes() {
        parent::fetchAttributes();
    }
}

?>

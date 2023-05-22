<?php

namespace App\Models;

use App\Auth;
use App\Flash;
use App\Mail;
use App\Mail2;
use App\Token;
use Core\View;
use PDO;

class User extends \Core\Model 
{
    // errors messages
    public $errors = [];
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    public function validate()
    {
        // Name
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }

        // Email address
        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'Email already taken';
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }

        // if ($this->password != $this->password_confirmation) {
        //     $this->errors = 'Password must match confirmation';
        // }

        // Password
        if (isset($this->password)) {

            if (strlen($this->password) < 6) {
                $this->errors[] = 'Please enter at least 6 characters for the password';
            }

            if (preg_match('/.*[a-z]+.*/i',$this->password) == 0) {
                $this->errors[] = 'Password needs at least one letter';
            }
            
            if (preg_match('/.*\d+.*/i',$this->password) == 0) {
                $this->errors[] = 'Password needs at least one number';
            }
        }
    }

    // See if a user record already exists with the specified email
    // $ignore_id Return false anyway if the record found has this ID
    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            } 
        }
        return false;
    }

    // Find a user by email address
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());  // 'App\Models\User' - Gets the name of the class dinamically

        $stmt->execute();

        return $stmt->fetch();
    }

    // Find a user model by ID
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();
        return $stmt->fetch();
    }
    
    // Authenticating a user by email and passwords
    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user && $user->is_active) {
            if (password_verify($password,$user->password_hash)) {
                return $user;
            }
        }
        return false;
    }

    // Save the user model with the current property values
    public function save()
    {
        $this->validate();

        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();  // For sending activation email link

            $sql = 'INSERT INTO users (name, email, password_hash, activation_hash) 
                    VALUES (:name, :email, :password_hash, :activation_hash)';
                    
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    // Send an email to the user containing activation link
    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        // Mail::send($this->email, 'Account activation', $text, $html);

        Mail2::send2($this->email, 'Account activation', $text, $html);
    }

    // Activate the user account with the specified activation token
    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }

     // Find a user model by activation token hash and expiry
     public static function findByActivationToken($token)
     {
        $token = new Token($token);
        $hashed_token = $token->getHash();
        //var_dump($hashed_token);

        $sql = 'SELECT * FROM users WHERE activation_hash = :activation_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user =  $stmt->fetch();

        //var_dump($user->activation_hash);

        if ($user) {
            // Check account activation token is n't null
            if (($user->activation_hash) !== null) {
                return $user;
            }
        }
     }

    // Remember the login by inserting a new unique token into the remembered_logins table 
    // for this user record
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiryTimestamp = time() + 60 *  60 * 24 * 30; // 30 days from now
        $this->expiry_timestamp = date('Y-m-d H:i:s', $this->expiryTimestamp);

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';
                
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':expires_at', $this->expiry_timestamp, PDO::PARAM_STR);

        return $stmt->execute();

    }

    // Send password reset instructions to the user specificied
    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);

        if ($user) {

            if ($user->startPasswordReset()) {

                $user->sendPasswordResetEmail();
            }
        } else {
            View::renderTemplate('Password/email_not_found.html');
            exit;
        }
    }

    // Start the password reset process by generating a new token and expiry
    protected function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();

        $expiryTimeStamp = time() + 60 * 60 * 2; // 2 hours from now
        $expiry_timestamp = date('Y-m-d H:i:s', $expiryTimeStamp);

        $sql = 'UPDATE users 
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', $expiry_timestamp);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();

    }

    // Send password reset instructions in an email to the user
    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        //Mail::send($this->email, 'Password reset', $text, $html);

        Mail2::send2($this->email, 'Password reset', $text, $html);
    }

    // Find a user model by password reset token and expiry
    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user =  $stmt->fetch();

        if ($user) {
            // Check password reset token hasn't expired
            if (strtotime($user->password_reset_expires_at) > time()) {
                return $user;
            }
        }
    }

    // Reset the password
    public function resetPassword($password)
    {
        $this->password = $password;
        $this->validate();

        if (empty($this->errors)) {
            
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password_hash = :password_hash,
                        password_reset_hash = :reset_hash,
                        password_reset_expires_at = :expires_at
                    WHERE id = :id';
            
            $db = static::getDB();

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':reset_hash',NULL, PDO::PARAM_STR);
            $stmt->bindValue(':expires_at',NULL, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    // Update the user's profile
    public function updateProfile($data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->errors)) {
            $sql = 'UPDATE users
                    SET name = :name,
                        email = :email';
            
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";
                        
            
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            // Add password if it's set
            if (isset($this->password)) {
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            }
            
            return $stmt->execute();
        }

        return false;

    }

    // Delete the user's profile
    public function deleteProfile($data)
    {
        $user = Auth::getUser();
        $this->password = $data['password'];
        $this->validate();

        if (empty($this->errors)) {
            
            // $user = Auth::getUser();

            if (password_verify($this->password, $user->password_hash)) {
                
                $sql = 'DELETE a, b FROM users AS a
                        LEFT OUTER JOIN remembered_logins AS b ON a.id = b.user_id
                        WHERE id = :id';

                $db = static::getDB();
                $stmt = $db->prepare($sql);

                $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
                return $stmt->execute();

            } else {
                Flash::addMessage('Enter the correct password', Flash::WARNING);
            } 
        }
    }
}

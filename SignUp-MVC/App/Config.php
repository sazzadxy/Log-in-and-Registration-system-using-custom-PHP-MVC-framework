<?php

namespace App;
// Application configuration
class Config  
{
    const DB_HOST = 'localhost';
    const DB_NAME = 'mvclogin';
    const DB_USER = 'root';
    const DB_PASSWORD = '';

    // Show or hide error messages on screen
    const SHOW_ERRORS = false; // true; // 

    // Secret key for hashing from https://randomkeygen.com/  CodeIgniter Encryption Keys
    const SECRET_KEY = 'YOUR-SECRET_KEY';

    // Mailgun API key
    const MAILGUN_API_KEY = 'YOUR-MAILGUN-API-KEY';     

    // Mailgun domain
    const MAILGUN_DOMAIN = 'YOUR-MAILGUN-DOMAIN';     


    // PHPMailer credentials
    const MAIL_HOST = 'smtp.gmail.com';
    const MAIL_USERNAME = 'YOUR-GMAIL-ADDRESS';
    const MAIL_PASSWORD = 'YOUR-APP-PASSWORD'; // App password by google 2-step verification
    const MAIL_SMTPSECURE = 'tls'; //or use 'ssl'
    const MAIL_PORT = "587"; // or use "465" for 'ssl'

}   

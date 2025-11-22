<?php
// FILE: /app/core/CSRF.php

class CSRF {
    private static $session;

    public static function init() {
        self::$session = Session::getInstance();
    }

    public static function generateToken() {
        if (self::$session === null) {
            self::init();
        }

        $token = bin2hex(random_bytes(32));
        self::$session->set('csrf_token', $token);
        return $token;
    }

    public static function getToken() {
        if (self::$session === null) {
            self::init();
        }

        $token = self::$session->get('csrf_token');
        if (!$token) {
            $token = self::generateToken();
        }
        return $token;
    }

    public static function validateToken($token) {
        if (self::$session === null) {
            self::init();
        }

        $sessionToken = self::$session->get('csrf_token');
        return $sessionToken && hash_equals($sessionToken, $token);
    }

    public static function field() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify() {
        $request = new Request();

        if ($request->isPost() || $request->isPut() || $request->isDelete()) {
            $token = $request->input('csrf_token');

            if (!$token || !self::validateToken($token)) {
                http_response_code(403);
                die('CSRF token validation failed.');
            }
        }

        return true;
    }
}

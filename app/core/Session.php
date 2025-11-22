<?php
// FILE: /app/core/Session.php

class Session {
    private static $instance = null;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }

    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function flash($key, $value = null) {
        if ($value === null) {
            // Get and remove
            $data = $this->get($key);
            $this->remove($key);
            return $data;
        } else {
            // Set
            $this->set($key, $value);
        }
    }

    public function setFlash($key, $value) {
        $this->set('_flash_' . $key, $value);
    }

    public function getFlash($key, $default = null) {
        $value = $this->get('_flash_' . $key, $default);
        $this->remove('_flash_' . $key);
        return $value;
    }

    public function hasFlash($key) {
        return $this->has('_flash_' . $key);
    }

    public function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    public function regenerate() {
        session_regenerate_id(true);
    }
}

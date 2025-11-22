<?php
// FILE: /app/core/Request.php

class Request {
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isGet() {
        return $this->method() === 'GET';
    }

    public function isPost() {
        return $this->method() === 'POST';
    }

    public function isPut() {
        return $this->method() === 'PUT';
    }

    public function isDelete() {
        return $this->method() === 'DELETE';
    }

    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    public function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    public function input($key = null, $default = null) {
        $input = array_merge($_GET, $_POST);
        if ($key === null) {
            return $input;
        }
        return isset($input[$key]) ? $input[$key] : $default;
    }

    public function file($key) {
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    public function hasFile($key) {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function all() {
        return $this->input();
    }

    public function only($keys) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }
        return $result;
    }

    public function except($keys) {
        $result = $this->input();
        foreach ($keys as $key) {
            unset($result[$key]);
        }
        return $result;
    }

    public function has($key) {
        return $this->input($key) !== null;
    }

    public function ip() {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public function userAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    public function path() {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function header($key, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }
}

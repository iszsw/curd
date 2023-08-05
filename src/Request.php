<?php

namespace curd;

/**
 * Class Request
 *
 * @package curd
 */
class Request
{

    protected array $input;

    protected array $get = [];

    protected array $post = [];

    protected array $server = [];

    protected array $params = [];

    protected string $method;

    public function __construct()
    {
        parse_str(file_get_contents('php://input'), $input);
        $this->input = $input;
        $this->get = $_GET;
        $this->post = $_POST ?: $this->input;
        $this->server = $_SERVER;
        $this->params = array_merge($this->get, $this->post, $this->input);
    }

    public function param(?string $name = null, $default = '')
    {
        if (null === $name) {
            return $this->params;
        }
        return $this->params[$name] ?? $default;
    }

    public function get(?string $name = null, $default = '')
    {
        if (null === $name) {
            return $this->get;
        }
        return $this->get[$name] ?? $default;
    }

    public function post(?string $name = null, $default = '')
    {
        if (null === $name) {
            return $this->post;
        }
        return $this->post[$name] ?? $default;
    }

    /**
     * 获取server参数
     *
     * @param  string  $name
     * @param  string  $default
     *
     * @return mixed
     */
    public function server(string $name = '', string $default = ''): mixed
    {
        if (empty($name)) {
            return $this->server;
        }

        return $this->server[strtoupper($name)] ?? $default;
    }

    public function method(): string
    {
        if ( ! isset($this->method)) {
            if ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            } else {
                $this->method = $this->server('REQUEST_METHOD') ?: 'GET';
            }
        }

        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isAjax(): bool
    {
        return 'xmlhttprequest' === strtolower($this->server('HTTP_X_REQUESTED_WITH'));
    }

    public function success($data = [], $msg = 'success'): array
    {
        return [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
        ];
    }

    public function error($msg = '', $data = [], $code = 1): array
    {
        return [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];
    }

}


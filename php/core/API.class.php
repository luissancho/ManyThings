<?php

namespace ManyThings\Core;

use Requests;

class API
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    const METHOD_PATCH = 'PATCH';

    protected $host;
    protected $headers;
    protected $options;

    public function __construct($host, $headers, $options)
    {
        $options['verify'] = false;

        $this->host = $host;
        $this->headers = $headers;
        $this->options = $options;
    }

    protected function error($error, $query)
    {
        throw new AppException($error, $query);
    }

    public function query($query, $params = [], $method = null)
    {
        if (!$method) {
            $method = self::METHOD_GET;
        }

        $headers = $this->headers;
        $options = $this->options;
        if ($method == self::METHOD_POST || $method == self::METHOD_PUT) {
            $headers['Content-Type'] = 'application/json';
            $headers['Accept'] = 'application/json';
            $params = json_encode($params);
        }

        $url = $this->host . '/' . $query;

        $response = Requests::request($url, $headers, $params, $method, $options);

        if (!$response->success) {
            $this->error($response->body, $query);
        }

        $result = $response->body;
        if (Utils::isJson($result)) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    public function insert($query, $params = [], $method = null)
    {
        if (!$method) {
            $method = self::METHOD_POST;
        }

        return $this->query($query, $params, $method);
    }

    public function update($query, $params = [], $method = null)
    {
        if (!$method) {
            $method = self::METHOD_PUT;
        }

        $this->query($query, $params, $method);

        return true;
    }

    public function delete($query, $params = [], $method = null)
    {
        if (!$method) {
            $method = self::METHOD_DELETE;
        }

        $this->query($query, $params, $method);

        return true;
    }

    public function getResults($query, $params = [], $method = null)
    {
        return $this->query($query, $params, $method);
    }

    public function getRow($query, $params = [], $method = null)
    {
        return $this->query($query, $params, $method);
    }

    public function getVar($query, $params = [], $method = null)
    {
        return $this->query($query, $params, $method);
    }

    public function getColumns($source)
    {
        $columns = [];

        $query = $source . '/schema';
        $results = $this->query($query, [], self::METHOD_GET);

        if ($results) {
            foreach ($results as $key => $row) {
                $columns[] = $key;
            }
        }

        return $columns;
    }

    public function escape($value)
    {
        return $value ? addslashes(strval($value)) : '';
    }
}

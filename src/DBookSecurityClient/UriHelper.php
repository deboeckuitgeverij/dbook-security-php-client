<?php
namespace DBookSecurityClient;

class UriHelper
{
    public static function buildUri($parts)
    {
        $parts = array_merge(
            array(
                'scheme' => 'http',
                'host' => '',
                'path' => '',
                'query' => '',
            ),
            $parts
        );

        if ($parts['query']) {
            $parts['query'] = '?' . $parts['query'];
        }

        return sprintf(
            '%s://%s%s%s',
            $parts['scheme'],
            $parts['host'],
            $parts['path'],
            $parts['query']
        );
    }

    public static function addPath($uri, $path)
    {
        $parts = array_merge(
            array(
                'path' => '',
                'query' => '',
            ),
            parse_url($uri)
        );

        $parts['path'] .= $path;

        return self::buildUri($parts);
    }

    public static function setQuery($uri, $data)
    {
        $parts = parse_url($uri);
        $parts['query'] = is_string($data) ? $data: self::buildQuery($data);

        return self::buildUri($parts);
    }

    public static function buildQuery($data)
    {
        $merged = array();
        foreach (func_get_args() as $arg) {
            $merged = array_merge($merged, $arg);
        }

        return http_build_query($merged);
    }

    public static function getCurrentUrl($data = array(), $removeParameters = array())
    {
        $parts  = array_merge(
            array(
                'path' => '',
                'query' => ''
            ),
            parse_url(self::getOriginalUrl())
        );

        if ($parts['query'] != '') {
            $fields = array();
            parse_str($parts['query'], $fields);
            $data = array_merge(
                array_diff_key($fields, array_flip($removeParameters)),
                $data
            );
        }

        $parts['query'] = http_build_query($data);

        return self::buildUri($parts);
    }

    public static function getOriginalUrl()
    {
        return self::buildUri([
            'scheme' => $_SERVER['HTTPS'] ? 'https': 'http',
            'host' => $_SERVER['HTTP_HOST'],
            'path' => $_SERVER['REQUEST_URI'],
            'query' => $_SERVER['QUERY_STRING'],
        ]);
    }
}
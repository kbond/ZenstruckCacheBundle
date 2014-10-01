<?php

namespace Zenstruck\CacheBundle\Http;

use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Response extends BaseResponse
{
    private $url;

    /**
     * @param string $url
     * @param string $content
     * @param int    $status
     * @param array  $headers
     */
    public function __construct($url, $content = '', $status = 200, $headers = array())
    {
        $this->url = $url;

        parent::__construct($content, $status, $headers);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $code
     *
     * @return string
     */
    public static function getStatusText($code)
    {
        return array_key_exists($code, self::$statusTexts) ? self::$statusTexts[$code] : 'Unknown';
    }
}

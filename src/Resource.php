<?php
namespace Allegro\REST;

class Resource
{

    /**
     * Resource constructor.
     * @param string $id
     * @param Resource $parent
     */
    public function __construct($id, Resource $parent)
    {
        $this->id = $id;
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->parent->getAccessToken();
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->parent->getApiKey();
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->parent->getUri() . '/' . $this->id;
    }

    /**
     * @return Commands
     */
    public function commands()
    {
        return new Commands($this);
    }

    /**
     * @param null|array $data
     * @return bool|string
     */
    public function get($data = null)
    {
        //$uri = $this->getUri();
        $uri = 'https://api.allegro.pl/sale/categories';
        if ($data !== null) {
            $uri .= '?';
            $uri .= http_build_query($data);
        }
        return $this->sendApiRequest($uri, 'GET');
    }

    /**
     * @param $categoryId
     * @return bool|string
     */
    public function getAttributeByCategory($categoryId){
        $uri = 'https://api.allegro.pl/sale/categories';
        if(!empty($categoryId)){
            $uri.= '/'.$categoryId.'/parameters';
        }
        return $this->sendApiRequest($uri, 'GET');
    }
    public function getDeliveryMethod($uri){
        return $this->sendApiRequest($uri, 'GET');
    }
    /**
     * @param null $data
     * @return bool|string
     */
    public function getOrders($data = null){
        $uri = 'https://api.allegro.pl/order';
        if ($data !== null) {
            $uri .= '/'.$data;
        }
        return $this->sendRequest($uri, 'GET');
    }
    /**
     * @param array $data
     * @return bool|string
     */
    public function put($data,$uri)
    {
        return $this->sendApiRequest($uri, 'PUT', $data);
    }

    /**
     * @param $data
     * @param $url
     * @return bool|string
     */
    public function post($data,$uri)
    {
        if($uri == 'https://api.allegro.pl/sale/product-proposals'){
            return $this->sendRequest($uri, 'POST', $data);
        } else {
            return $this->sendApiRequest($uri, 'POST', $data);
        }
//        return $this->sendApiRequest($uri, 'POST', $data);
    }

    /**
     * @param null|array $data
     * @return bool|string
     */
    public function delete($data = null)
    {
        $uri = $this->getUri();

        if ($data !== null) {
            $uri .= '?';
            $uri .= http_build_query($data);
        }

        return $this->sendApiRequest($uri, 'DELETE');
    }

    public function __get($name)
    {
        return new Resource($name, $this);
    }

    public function __call($name, $args)
    {
        $id = array_shift($args);
        $collection = new Resource($name, $this);
        return new Resource($id, $collection);
    }

    protected function sendRequest($url, $method, $data = array())
    {
        $token = $this->getAccessToken();
        $key = $this->getApiKey();

        $headers = array(
            "Authorization: Bearer $token",
            "Api-Key: $key",
            "Content-Type: application/vnd.allegro.beta.v1+json",
            "Accept: application/vnd.allegro.beta.v1+json"
        );
        $data = json_encode($data);
        return $this->sendHttpRequest($url, $method, $headers, $data);
    }
    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @return bool|string
     */
    protected function sendApiRequest($url, $method, $data = array())
    {
        $token = $this->getAccessToken();
        $key = $this->getApiKey();

        $headers = array(
            "Authorization: Bearer $token",
            "Api-Key: $key",
            "Content-Type: application/vnd.allegro.public.v1+json",
            "Accept: application/vnd.allegro.public.v1+json"
        );
        $data = json_encode($data);
        return $this->sendHttpRequest($url, $method, $headers, $data);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param string $data
     * @return bool|string
     */
    protected function sendHttpRequest($url, $method, $headers = array(), $data = '')
    {
        $options = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $data,
                'ignore_errors' => true
            )
        );
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

    /**
     * @var string
     */
    private $id;

    /**
     * @var Resource
     */
    private $parent;
}

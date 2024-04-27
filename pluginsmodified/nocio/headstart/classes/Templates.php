<?php

namespace Nocio\Headstart\Classes;

use GuzzleHttp\Client;
use Yaml;
use File;

class Templates
{

    /**
     * @var string
     */
    protected $url;
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    protected $index;

    protected $schema;

    /**
     * Client constructor.
     *
     * @param string $url
     */
    public function __construct($url = null)
    {
        $this->url = $url ? $url : 'https://api.github.com';
        $this->guzzle = new Client();
    }

    protected function url($path = null) {
        return $this->url . $path;
    }

    protected function raw($url, $headers = [])
    {
        return $this->guzzle->request('GET', $this->url($url), [
            'headers' => $headers
        ]);
    }

    protected function json($url, $headers = [])
    {
        $response = $this->raw($url, $headers);
        return json_decode($response->getBody()->getContents(), false);
    }

    protected function file($filepath) {
        return base64_decode($this->json('/repos/nocio/headstart/contents/templates/' . $filepath)->content);
    }

    protected function load() {
        if (!is_null($this->index)) {
            return true;
        }

        $this->index = Yaml::parse($this->file('index.yaml'));

        return true;
    }

    public function get($code = null) {
        $this->load();

        if (is_null($code)) {
            return $this->index;
        }

        return $this->index[$code];
    }

    public function collisionFreeName($filename, $ext = null) {
        if (is_null($ext)) {
            $ext = '.' . File::extension($filename);
        }
        if (File::exists($this->schema->getPath() . '/' . $filename)) {
            if (substr($filename, - strlen($ext)) === $ext) {
                $filename = substr($filename, 0, strlen($filename) - strlen($ext));
            }
            $filename .= '.' . str_random(3) . $ext;
        }

        return $filename;
    }

    public function makePath($path) {
        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
    }

    public function download_file($file, $target = null) {
        if (is_null($target)) {
            $target = $file;
        }
        $filename = $this->collisionFreeName($target);
        $filepath = join_paths($this->schema->getPath(), $filename);
        $this->makePath(dirname($filepath));

        File::put($filepath, $this->file($file));

        return $filename;
    }

    public function download($code) {
        $config = $this->get($code);
        $code_path = str_replace('.', '_', $code);

        if (is_null($this->schema)) {
            $this->schema = Schema::load('headstart');
        }

        $schema_file = $code_path . '.htm';

        if (isset($config['includes'])) {
            // download includes
            foreach ($config['includes'] as $include) {
                $this->download_file($include, str_replace($code_path, '', $include));
            }

            $schema_file = $code_path . '/schema.htm';
        }

        return substr($this->download_file($schema_file, 'graphs/' . $config['filename']), strlen('graphs/'));
    }

}

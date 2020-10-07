<?php


namespace WiziYousignClient;


/**
 * Class WiziSignFile
 * @package WiziYousignClient
 */
class WiziSignFile
{
    protected $base64;
    protected $filename;

    /**
     * Choose between filepath || fileContent || base64
     * @param null|string $filepath
     * @param null|string $fileContent
     * @param null|string $base64
     * @param null|string $filename Filename of the file (mandatory if $filepath is not set)
     */
    public function __construct($filepath = null, $fileContent = null, $base64 = null, $filename = null)
    {
        if (!is_null($filepath)) {
            $fileContent = file_get_contents($filepath);
            if (is_null($filename)) {
                $names = explode('/', $filepath);
                $filename = end($names);
            }
        }

        if (!is_null($fileContent)) {
            $base64 = base64_encode($fileContent);
        }

        $this->base64 = $base64;

        $this->filename = $filename;
    }

    public function getBase64()
    {
        return $this->base64;
    }

    public function getFilename()
    {
        return $this->filename;
    }
}

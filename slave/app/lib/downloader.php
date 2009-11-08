<?php

//require_once 'PEAR.php';
require_once 'HTTP/Request2.php';

class HTTP_Request2_Observer_Download implements SplObserver
{
    protected $dir;

    protected $fp;
    
    public $content_length;
    public $downloaded_size;

    public function __construct($target_file, $status_handler)
    {
        /*if (!is_dir($dir)) {
            throw new Exception("'{$dir}' is not a directory");
        }*/
        //$this->dir = $dir;
				$this->target_file = $target_file;
				$this->status_handler = $status_handler;
    }

    public function update(SplSubject $subject)
    {
        $event = $subject->getLastEvent();

        switch ($event['name']) {
        case 'receivedHeaders':
            $this->content_length = (@intval($event['data']->getHeader('content-length')) > 0) ? intval($event['data']->getHeader('content-length')) : 0;
            
            /*if ($disposition = $event['data']->getHeader('content-disposition')
                && 0 == strpos($disposition, 'attachment')
                && preg_match('/filename="([^"]+)"/', $disposition, $m)
            ) {
                $filename = basename($m[1]);
            } else {
                $filename = basename($subject->getUrl()->getPath());
            }*/
            //$target = $this->dir . DIRECTORY_SEPARATOR . $filename;
            if (!($this->fp = @fopen($this->target_file, 'wb'))) {
                throw new Exception("Cannot open target file '{$target}'");
            }
            break;

        case 'receivedBodyPart':
        case 'receivedEncodedBodyPart':
            fwrite($this->fp, $event['data']);
            
            $this->downloaded_size += strlen($event['data']);
            
            //echo round(($this->downloaded_size / $this->content_length) * 100) . "\n";
						call_user_func_array($this->status_handler, array($this->downloaded_size, $this->content_length));
            break;

        case 'receivedBody':
            fclose($this->fp);
        }
    }
}
<?php

namespace Leven\Router\Messages;

class AttachmentResponse extends Response
{

    public array $headers = [
        'Content-Description' => 'File Transfer',
        'Content-Type' => 'application/octet-stream',
        'Content-Transfer-Encoding' => 'binary',
    ];

    public function __construct(
        public string $body,
        string $filename,
        public int    $status = 200
    )
    {
        $this->setHeader('Content-Disposition', "attachment; filename=\"$filename\"");
        $this->setHeader('Content-Length', strlen($this->body));
    }

}
<?php

namespace Leven\Router\Messages;

class AttachmentResponse extends Response
{

    public array $headers = [
        'Content-Description' => 'File Transfer',
        'Content-Type' => 'application/octet-stream',
        'Content-Disposition' => 'File Transfer',
        'Content-Transfer-Encoding' => 'binary',
    ];

    public function __construct(
        public string $body,
        string $filename,
        public int    $status = 200
    )
    {
        $this->addHeader('Content-Disposition', "attachment; filename=\"$filename\"");
        $this->addHeader('Content-Length', strlen($this->body));
    }

}
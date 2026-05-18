<?php
namespace KoelmanLabs\Component\Planjeagenda\Site\Service\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;

class IcsResponseFactory
{
    public function create(string $ics): ResponseInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $ics);
        rewind($stream);

        $response = new Response();

        return $response
            ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->withHeader('Content-Disposition', 'inline; filename="calendar.ics"')
            ->withBody(new Stream($stream));
    }
}
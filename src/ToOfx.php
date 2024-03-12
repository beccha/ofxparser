<?php

declare(strict_types=1);

namespace Beccha\OfxParser;

use Beccha\OfxParser\Entity\BankAccount;
use Beccha\OfxParser\Entity\SignOn;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class ToOfx
{
    private BankAccount $banque;
    private SignOn $signOn;

    public function __construct(SignOn $signOn, BankAccount $banque)
    {
        $this->banque = $banque;
        $this->signOn = $signOn;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function generate(): string
    {
        $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
        $twig = new Environment($loader, ['debug' => true]);
        $twig->addExtension(new DebugExtension());

        return $twig->render('ofx.sgml.twig', [
            'banque' => $this->banque,
            'signOn' => $this->signOn,
        ]);
    }
}

<?php

namespace App\Mail;

use App\Config;
use App\Entities\PasswordReset;
use App\SignedUrl;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

class PasswordResetEmail
{
    public function __construct(
        private readonly MailerInterface       $mailer,
        private readonly BodyRendererInterface $renderer,
        private readonly Config                $config,
        private readonly SignedUrl             $signedUrl,
    )
    {
    }

    public function send(PasswordReset $passwordReset)
    {
        $resetLink = $this->signedUrl->createFrom(
            'reset-password',
            ['token' => $passwordReset->getToken()],
            $passwordReset->getExpiration()
        );

        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($passwordReset->getEmail())
            ->subject('Your Expennies Reset Password Token')
            ->htmlTemplate('emails/password_reset.twig')
            ->context(compact('resetLink'));

        $this->renderer->render($message);

        $this->mailer->send($message);
    }
}
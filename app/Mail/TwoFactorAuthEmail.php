<?php

namespace App\Mail;

use App\Config;
use App\Entities\User;
use App\Entities\UserLoginCode;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

class TwoFactorAuthEmail
{
    public function __construct(
        private readonly MailerInterface       $mailer,
        private readonly Config                $config,
        private readonly BodyRendererInterface $bodyRenderer,
    )
    {
    }

    public function send(UserLoginCode $userLoginCode): void
    {
        $to = $userLoginCode->getUser()->getEmail();
        $code = $userLoginCode->getCode();

        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($to)
            ->subject('Expennies authentication code')
            ->htmlTemplate('emails/two_factor_login.twig')
            ->context([
                'code' => $code
            ]);

        $this->bodyRenderer->render($message);

        $this->mailer->send($message);
    }
}
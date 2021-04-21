<?php
/**
 * Created by PhpStorm.
 * User: c.gazaniol
 * Date: 24/04/2019
 * Time: 10:00
 */

namespace App\Service;


use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as Twig_Environment;

class MailerService
{

    /**
     * @var Twig_Environment
     */
    private $templating;
    private $mailer;


    public function __construct(Twig_Environment $templating, MailerInterface $mailer)
    {
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    public function sendMail($subject, $content, $to)
    {

        //protection dev
        if (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] == 'dev') {

            $content .= '<p>DESTINATAIRES : ';
            if (!is_array($to)) {
                $content .= $to;
            } else {
                foreach($to as $dest) {
                    $content .= $dest . ', ';
                }
            }
            $content .= '</p>';
            $to = 'test@wiilog.fr';
        }

        $this->mailer->send((new Email())
            ->from("noreply@wiilog.fr")
            ->to($to)
            ->subject($subject)
            ->html($content));
    }
}

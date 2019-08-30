<?php


namespace AppBundle\Services;

use AppBundle\Entity\Inclusion;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;

class SendMail
{
    const SUBJECT_DEFAULT = "Arcoffice Email";
    const EMAILS_ADMIN = ["folken70@hotmail.com", "herve.perrier@infirmerie-protestante.com", "recherche-clinique@infirmerie-protestante.com"];

    protected $mailer;
    protected $templating;
    protected $container;

    public function __construct(Swift_Mailer $mailer, EngineInterface $templating, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->container = $container;
    }

    public function sendEmail($render = "default", $variables = [], $to = self::EMAILS_ADMIN)
    {
        if (!is_array($to)) {
            $to = [$to];
        }

        // Ajout de l'email du medecin responsable de l'inclusion si renseigné
        if (isset($variables["inclusion"])) {
            /** @var $inclusion Inclusion */
            $inclusion = $variables["inclusion"];
            $medecin = $inclusion->getMedecin();
            if ($medecin) {
                $emailMedecin = $medecin->getEmail();
                if (filter_var($emailMedecin, FILTER_VALIDATE_EMAIL)) {
                    $to[] = $emailMedecin;
                }
            }
        }

        $to = array_unique(array_merge($to, self::EMAILS_ADMIN));
        $twigTemplate = "emails/${render}.html.twig";

        $message = (new Swift_Message($variables["sujet"] ?? self::SUBJECT_DEFAULT))
            ->setFrom($this->container->getParameter("mailer_user"))
            ->setTo($to)
            ->setBody(
                $this->templating->render($twigTemplate, $variables)
            )
            ->setContentType('text/html');

        $this->mailer->send($message);
    }
}
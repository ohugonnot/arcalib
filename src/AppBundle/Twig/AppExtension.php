<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Visite;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $generator;

    private $requestStack;

    public function __construct(UrlGeneratorInterface $generator, RequestStack $requestStack)
    {
        $this->generator = $generator;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('path_context', [$this, 'getPathContext'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
        ];
    }

    public function getFilters()
    {

        return [
            new TwigFilter('values', [$this, 'values']),
            new TwigFilter('age', [$this, 'getAge']),
            new TwigFilter('nbInclusions', [$this, 'nbInclusions']),
            new TwigFilter('visiteClass', [$this, 'visiteClass']),
        ];
    }

    /**
     * @param $array
     * @return array
     */
    public function values(array $array): array
    {

        return array_values($array);
    }

    /**
     * @param DateTime|null $date
     * @param DateTime|null $deces
     * @return int|null
     * @throws Exception
     */
    public function getAge(?DateTime $date, ?DateTime $deces): ?int
    {
        if (!$date instanceof DateTime) {
            return null;
        }

        if ($deces instanceof DateTime) {
            $referenceDate = $deces->format("d-m-Y");
        } else {
            $referenceDate = date('d-m-Y');
        }

        $referenceDateTimeObject = new DateTime($referenceDate);

        $diff = $referenceDateTimeObject->diff($date);

        return $diff->y;
    }

    /**
     * @param Essais[] $array
     * @return int
     */
    public function nbInclusions($array): int
    {
        $sum = 0;
        foreach ($array as $essai) {
            $sum += count($essai->getInclusions());
        }

        return $sum;
    }

    /**
     * @param Visite $visite
     * @return string
     * @throws Exception
     */
    public function visiteClass(Visite $visite): string
    {
        $date = $visite->getDate();
        if ($date == null) {
            return "";
        }

        $now = new DateTime();
        $interval = $now->diff($date);
        if ($interval->format('%R%a') > 30) {
            return "more-30-days";
        }
        if ($interval->format('%R%a') < 30 && $interval->format('%R%a') > 0) {
            return "less-30-days";
        } else {
            return "past";
        }
    }

    public function getPathContext($name, $parameters = [], $relative = false)
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request)
            $parameters = $parameters + $request->query->all();
        return $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function isUrlGenerationSafe(\Twig\Node\Node $argsNode)
    {
        // support named arguments
        $paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : (
        $argsNode->hasNode(1) ? $argsNode->getNode(1) : null
        );

        if (null === $paramsNode || $paramsNode instanceof ArrayExpression && \count($paramsNode) <= 2 &&
            (!$paramsNode->hasNode(1) || $paramsNode->getNode(1) instanceof ConstantExpression)
        ) {
            return ['html'];
        }

        return [];
    }
}
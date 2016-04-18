<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Controller;

use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchDSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\FilterManagerBundle\Filter\ViewData;
use ONGR\FilterManagerBundle\Search\SearchResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used for displaying translations.
 */
class ListController extends Controller
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * Injects elasticsearch repository for listing actions.
     *
     * @param Repository $repository Elasticsearch repository.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Renders view with filter manager response.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        /** @var SearchResponse $fmr */
        $fmr = $this->get('ongr_translations.filter_manager')->handleRequest($request);
        $data = iterator_to_array($fmr->getResult());
        return $this->render(
            'ONGRTranslationsBundle:List:list.html.twig',
            [
                'data' => iterator_to_array($fmr->getResult()),
                'locales' => $this->buildLocalesList($fmr->getFilters()['locale']),
                'filters_manager' => $fmr,
            ]
        );
    }

    /**
     * Creates locales list.
     *
     * @param ViewData\ChoicesAwareViewData $filter
     *
     * @return array
     */
    private function buildLocalesList($filter)
    {
        $list = [];

        foreach ($this->getParameter('ongr_translations.managed_locales') as $value) {
            $list[$value] = true;
        }
        ksort($list);

        $activeLocales = [];

        if ($filter->getState()->isActive()) {
            foreach ($filter->getChoices() as $choice) {
                $activeLocales[$choice->getLabel()] = $choice->isActive();
            }
            $list = array_merge($list, $activeLocales);
        }

        return $list;
    }

    /**
     * Renders out a page with all the info about a translation
     *
     * @param Request $request
     * @param String $translation
     *
     * @return Response
     */
    public function translationAction(Request $request, $translation)
    {
        $fmr = $this->get('ongr_translations.filter_manager')->handleRequest($request);
        foreach ($this->repository->findBy(['key' => $translation]) as $translation) {
            $translation = $translation;
        }
        return $this->render('ONGRTranslationsBundle:List:translation.html.twig', [
            'translation' => $translation,
            'filters_manager' => $fmr,
            'locales' => $this->buildLocalesList($fmr->getFilters()['locale']),
        ]);
    }
}

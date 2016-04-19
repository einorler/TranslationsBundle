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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used for working with individual translation
 * in edit view
 */
class TranslationController extends Controller
{
    /**
     * Add a tag action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addTagAction(Request $request)
    {
        $response = [];
        $cache = $this->get('es.cache_engine');
        try {
            $this->get('ongr_translations.translation_manager')
                ->add($this->remakeRequest($request));
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }
        !isset($response['error']) ?
            $response['success'] = 'Tag successfully added' :
            $response['success'] = false;
        $cache->save('translations_edit', $response);
        return new RedirectResponse(
            $this->generateUrl(
                'ongr_translations_translation_page',
                [
                    'translation' => $request->request->get('key'),
                ]
            )
        );
    }

    /**
     * Remakes a request to have json content
     *
     * @param Request $request
     *
     * @return Request
     */
    private function remakeRequest(Request $request)
    {
        $content['name'] = $request->request->get('name');
        $content['properties'] = $request->request->get('properties');
        $content['id'] = $request->request->get('id');
        $content['findBy'] = $request->request->get('findBy');
        $content = json_encode($content);
        return new Request([], [], [], [], [], [], $content);
    }
}
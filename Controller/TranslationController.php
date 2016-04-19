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
     * Add a tag action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        $response = [];
        $cache = $this->get('es.cache_engine');
        $requests = $this->remakeRequest($request);
        try {
            foreach ($requests as $messageRequest) {
                $this->get('ongr_translations.translation_manager')
                    ->edit($messageRequest);
            }
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }
        !isset($response['error']) ?
            $response['success'] = 'Messages updated successfully' :
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
     * of a single object. If there is a number of
     * locales associated with a request it returns an
     * array of new requests
     *
     * @param Request $request
     *
     * @return mixed
     */
    private function remakeRequest(Request $request)
    {
        $content = [];
        $content['name'] = $request->request->get('name');
        $content['properties'] = $request->request->get('properties');
        $content['id'] = $request->request->get('id');
        $content['findBy'] = $request->request->get('findBy');
        if ($request->request->has('locales')) {
            return $this->turnToArray($request, $content);
        }
        $content = json_encode($content);
        return new Request([], [], [], [], [], [], $content);
    }

    /**
     * Turns a request to an array of requests with json content
     *
     * @param Request $request
     * @param array $content
     *
     * @return array
     */
    private function turnToArray(Request $request, array $content)
    {
        $requests = [];
        $locales = $request->request->get('locales');
        $messages = $request->request->get('messages');
        $statuses = $request->request->get('statuses');
        $findBy = $request->request->get('findBy');
        foreach ($locales as $locale) {
            if ($messages[$locale] == '') {
                break;
            }
            $content['properties']['locale'] = $locale;
            $content['properties']['message'] = $messages[$locale];
            $content['properties']['status'] = $statuses[$locale];
            $content['findBy'] = $findBy[$locale];
            $requests[] = new Request([], [], [], [], [], [], json_encode($content));
        }
        return $requests;
    }
}

<?php

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Quote;
use App\Entity\Template;
use App\Repository\DestinationRepository;
use App\Repository\SiteRepository;

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        try{
            $templateData = $this->prepareTemplateData($data);
        }catch (\InvalidArgumentException $e){
            exit($e->getMessage());
        }



        $replaced = clone($tpl);

        foreach ($templateData as $key => $value) {
            $replaced->subject = preg_replace('/\[' . $key . '\]/', $value, $replaced->subject);
            $replaced->content = preg_replace('/\[' . $key . '\]/', $value, $replaced->content);
        }
        return $replaced;
    }

    /**
     * Prepare template data
     * @param $data array
     * @return array
     */
    private function prepareTemplateData($data)
    {

        $quote = $this->getQuote($data['quote']);

        if ($quote === null) {
            throw new \InvalidArgumentException('Quote infos missed !');
        }

        $site = $this->getSite($quote->siteId);
        $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
        $user = $this->getUser(isset($data['user']) ? $data['user'] : null);

        $templateData = [
            'quote:summary_html' => Quote::renderHtml($quote),
            'quote:summary' => Quote::renderText($quote),
            'quote:destination_name' => $destination->countryName,
            'quote:destination_link' => $site->url . '/' . $destination->countryName . '/quote/' . $quote->id
        ];
        if ($user) {
            $templateData['user:first_name'] = ucfirst(mb_strtolower($user->firstname));
        }


        return $templateData;

    }

    /**
     * Get a Site instance from repos or Context App
     * @param $siteId int
     * @return Site
     */
    private function getSite($siteId)
    {
        $site = SiteRepository::getInstance()->getById($siteId);

        if (empty($site)) {
            $site = ApplicationContext::getInstance()->getCurrentSite();
        }

        return $site;
    }

    /**
     * Check Quote instance or get it from repos
     * @param $quote
     * @return null|Quote
     */
    private function getQuote($quote)
    {
        if (isset($quote) && $quote instanceof Quote) {
            return $quote;
        } elseif (is_int($quote)) {
            return QuoteRepository::getInstance()->getById($quote);
        }
        return null;
    }

    /**
     * Check User instance or get it from  Context App
     * @param $user
     * @return User
     */
    private function getUser($user)
    {
        if ($user instanceof User) {
            return $user;
        }
        return ApplicationContext::getInstance()->getCurrentUser();
    }


}

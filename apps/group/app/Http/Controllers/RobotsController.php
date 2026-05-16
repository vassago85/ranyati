<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Serve a host-aware robots.txt with:
     *  - explicit disallows for every admin / auth-only prefix that exists or
     *    could plausibly exist on this app,
     *  - Cloudflare Content-Signal directives (search yes, ai-train no,
     *    ai-input yes),
     *  - the correct Sitemap line for the current production host.
     *
     * Only the public marketing pages should ever appear in search engines or
     * be ingested by AI assistants; everything else stays out of the crawl.
     */
    public function __invoke(Request $request): Response
    {
        $host = $request->getHost();

        if (str_starts_with($host, 'motivations.')) {
            $body = $this->motivationsBody();
        } elseif (str_starts_with($host, 'storage.')) {
            $body = $this->storageBody();
        } elseif (str_starts_with($host, 'arms.')) {
            $body = $this->armsBody();
        } else {
            $body = $this->apexBody();
        }

        return response($body, 200, [
            'Content-Type'  => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function motivationsBody(): string
    {
        return <<<TXT
# Ranyati Motivations — professional firearm licence motivation letters
# Operated by Ranyati Firearm Motivations (Pty) Ltd / Ranyati Group
# https://motivations.ranyati.co.za

User-agent: *
Disallow: /admin
Disallow: /admin/
Disallow: /dashboard
Disallow: /owner
Disallow: /developer
Disallow: /dev
Disallow: /email
Disallow: /settings
Disallow: /messages
Disallow: /documents
Disallow: /applications
Disallow: /payments
Disallow: /verify
Disallow: /up
Disallow: /enquire/send-otp
Disallow: /arms/send-otp
Disallow: /listing/

# Content usage signals (Cloudflare Content-Signal proposal)
# Public marketing pages may be crawled by search engines and read by AI
# assistants when answering user questions, but must NOT be used to train AI
# models. Admin and operational endpoints above remain disallowed for all
# crawlers and agents.
Content-Signal: ai-train=no, search=yes, ai-input=yes

# Machine-readable summary for AI agents
# See https://motivations.ranyati.co.za/llms.txt
Sitemap: https://motivations.ranyati.co.za/sitemap.xml
TXT;
    }

    private function storageBody(): string
    {
        return <<<TXT
# Ranyati Storage — secure firearm storage, Pretoria
# Operated by Ranyati Firearm Motivations (Pty) Ltd / Ranyati Group
# https://storage.ranyati.co.za

User-agent: *
Disallow: /admin
Disallow: /admin/
Disallow: /dashboard
Disallow: /owner
Disallow: /developer
Disallow: /dev
Disallow: /email
Disallow: /settings
Disallow: /messages
Disallow: /documents
Disallow: /verify
Disallow: /up

Content-Signal: ai-train=no, search=yes, ai-input=yes

Sitemap: https://storage.ranyati.co.za/sitemap.xml
TXT;
    }

    private function armsBody(): string
    {
        return <<<TXT
# Ranyati Arms — quality used firearms for sale
# Operated by Ranyati Firearm Motivations (Pty) Ltd / Ranyati Group
# https://arms.ranyati.co.za

User-agent: *
Disallow: /admin
Disallow: /admin/
Disallow: /dashboard
Disallow: /owner
Disallow: /developer
Disallow: /dev
Disallow: /email
Disallow: /settings
Disallow: /messages
Disallow: /documents
Disallow: /verify
Disallow: /up
Disallow: /arms/send-otp
Disallow: /listing/

Content-Signal: ai-train=no, search=yes, ai-input=yes

Sitemap: https://arms.ranyati.co.za/sitemap.xml
TXT;
    }

    private function apexBody(): string
    {
        return <<<TXT
# Ranyati Group — parent brand
# Operated by Ranyati Firearm Motivations (Pty) Ltd
# https://ranyati.co.za

User-agent: *
Disallow: /admin
Disallow: /admin/
Disallow: /dashboard
Disallow: /owner
Disallow: /developer
Disallow: /dev
Disallow: /email
Disallow: /settings
Disallow: /messages
Disallow: /documents
Disallow: /verify
Disallow: /up

Content-Signal: ai-train=no, search=yes, ai-input=yes

Sitemap: https://ranyati.co.za/sitemap.xml
TXT;
    }
}

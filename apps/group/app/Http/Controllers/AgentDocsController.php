<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AgentDocsController extends Controller
{
    /**
     * GET /llms.txt — machine-readable overview for AI assistants and agents.
     * Motivations host only.
     */
    public function llms(Request $request): SymfonyResponse
    {
        $this->ensureMotivationsHost($request);

        $body = view('agent.llms')->render();

        return response($body, 200, [
            'Content-Type'  => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * GET /about.md — markdown twin of the public About content.
     * Motivations host only.
     */
    public function aboutMd(Request $request): SymfonyResponse
    {
        $this->ensureMotivationsHost($request);

        $body = view('agent.about-md')->render();

        return response($body, 200, [
            'Content-Type'  => 'text/markdown; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * GET /services.md — markdown summary of motivation services offered.
     * Motivations host only.
     */
    public function servicesMd(Request $request): SymfonyResponse
    {
        $this->ensureMotivationsHost($request);

        $body = view('agent.services-md')->render();

        return response($body, 200, [
            'Content-Type'  => 'text/markdown; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * GET /faq.md — markdown twin of the public FAQ (host-only, no invention).
     * Motivations host only.
     */
    public function faqMd(Request $request): SymfonyResponse
    {
        $this->ensureMotivationsHost($request);

        $body = view('agent.faq-md')->render();

        return response($body, 200, [
            'Content-Type'  => 'text/markdown; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * Agent documents are scoped to the Motivations host so that
     * motivations-specific copy never leaks onto a sister-brand subdomain.
     * In local development the host is `localhost`, so we allow it through
     * to make `php artisan serve` + curl smoke testing possible.
     */
    private function ensureMotivationsHost(Request $request): void
    {
        $host = $request->getHost();

        $allowed = str_starts_with($host, 'motivations.')
            || app()->environment('local');

        abort_unless($allowed, 404);
    }
}

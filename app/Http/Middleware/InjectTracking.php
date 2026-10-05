<?php

namespace App\Http\Middleware;

use App\Models\Integration;
use App\Support\TrackingSnippets;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class InjectTracking
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($request->is('admin', 'admin/*') || ! $response instanceof \Illuminate\Http\Response || $response->getStatusCode() !== 200 || ! str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            return $response;
        }
        $html = $response->getContent();
        if (! preg_match('/<head\b[^>]*>/i', $html) || ! preg_match('/<body\b[^>]*>/i', $html)) {
            return $response;
        }
        // No HTML/config cache: browsers and proxies must request the current configuration.
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->remove('ETag');
        $response->headers->remove('Last-Modified');
        if (! Schema::hasTable('integrations')) {
            return $response;
        }
        $head = $body = '';
        foreach (Integration::where('account_id', config('integrations.account_id'))->where('status', 'active')->orderBy('id')->get() as $integration) {
            $marker = '<!-- adxon-tracking:'.$integration->integration_type.' -->';
            if (str_contains($html, $marker)) {
                continue;
            }
            try {
                $codes = app(TrackingSnippets::class)->validate($integration->integration_type, $integration->head_code, $integration->body_code ?? '');
            } catch (ValidationException $exception) {
                continue;
            }
            $head .= $marker."\n".$codes['head']."\n";
            if ($codes['body'] !== '') {
                $body .= $marker."\n".$codes['body']."\n";
            }
        }
        $html = preg_replace_callback('/<head\b[^>]*>/i', fn ($match) => $match[0]."\n".$head, $html, 1);
        $html = preg_replace_callback('/<body\b[^>]*>/i', fn ($match) => $match[0]."\n".$body, $html, 1);
        $response->setContent($html);
        $response->headers->remove('Content-Length');

        return $response;
    }
}

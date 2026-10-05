<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use Illuminate\Validation\ValidationException;

class TrackingSnippets
{
    public const TYPES = ['ga4' => 'Google Analytics 4', 'gtm' => 'Google Tag Manager', 'meta_pixel' => 'Meta Pixel'];

    public function validate(string $type, string $head, string $body = ''): array
    {
        $pattern = match ($type) {
            'ga4' => '/\bG-[A-Z0-9]{4,20}\b/',
            'gtm' => '/\bGTM-[A-Z0-9]{4,20}\b/',
            'meta_pixel' => '/fbq\(\s*[\'"]init[\'"]\s*,\s*[\'"](\d{5,25})[\'"]\s*\)/',
        };
        preg_match_all($pattern, $head, $matches);
        $ids = array_unique($type === 'meta_pixel' ? $matches[1] : $matches[0]);
        if (count($ids) !== 1) {
            $this->invalid('head_code');
        }
        $codes = $this->codes($type, array_values($ids)[0]);
        $expectedHead = $codes['head'];
        // Meta's standard pasted snippet includes a noscript image; HTML requires it in body.
        $validHead = $this->signature($head) === $this->signature($expectedHead);
        if ($type === 'meta_pixel') {
            $validHead = $validHead || $this->signature($head) === $this->signature($expectedHead.$codes['body']);
        }
        if (! $validHead) {
            $this->invalid('head_code');
        }
        if ($type === 'gtm' && $this->signature($body) !== $this->signature($codes['body'])) {
            $this->invalid('body_code');
        }
        if ($type !== 'gtm' && trim($body) !== '') {
            $this->invalid('body_code');
        }

        return $codes;
    }

    private function invalid(string $field): never
    {
        throw ValidationException::withMessages([$field => 'Paste the complete standard provider installation snippet with one valid ID. GTM head and body IDs must match. Custom scripts and additional events are not accepted.']);
    }

    private function signature(string $html): string
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!doctype html><html><body><div id="snippet-root">'.$html.'</div></body></html>', LIBXML_NONET);
            $walk = function ($node) use (&$walk): array {
                if ($node->nodeType === XML_COMMENT_NODE || ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) === '')) {
                    return [];
                }
                if (! $node instanceof DOMElement) {
                    return ['text', preg_replace('/\s+/', '', str_replace('"', "'", $node->textContent))];
                }
                $attributes = [];
                foreach ($node->attributes as $attribute) {
                    $attributes[$attribute->name] = $attribute->name === 'style' ? preg_replace('/\s+/', '', $attribute->value) : $attribute->value;
                }
                ksort($attributes);
                $children = [];
                foreach ($node->childNodes as $child) {
                    if ($value = $walk($child)) {
                        $children[] = $value;
                    }
                }

                return [$node->nodeName, $attributes, $children];
            };

            return json_encode($walk($document->documentElement), JSON_THROW_ON_ERROR);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function codes(string $type, string $id): array
    {
        return match ($type) {
            'ga4' => ['head' => <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$id}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$id}');
</script>
HTML, 'body' => ''],
            'gtm' => ['head' => <<<HTML
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$id}');</script>
HTML, 'body' => '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.$id.'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>'],
            'meta_pixel' => ['head' => <<<HTML
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$id}');
fbq('track', 'PageView');
</script>
HTML, 'body' => '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.$id.'&ev=PageView&noscript=1" /></noscript>'],
        };
    }
}

<?php
function soko_icon($name) {
    $paths = [
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'wallet' => '<path d="M20 8H4V5a2 2 0 0 1 2-2h13M4 8v12h17V8Zm11 4h6v5h-6Z"/><path d="M17 14h1"/>',
        'user' => '<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2Z"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'menu' => '<path d="M3 5h18M3 12h18M3 19h18"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'bolt' => '<path d="m13 2-9 12h7l-1 8 10-12h-8Z"/>',
        'support' => '<path d="M4 14v-3a8 8 0 0 1 16 0v3M20 17v2a2 2 0 0 1-2 2h-5"/><rect x="3" y="10" width="4" height="8" rx="2"/><rect x="17" y="10" width="4" height="8" rx="2"/>',
    ];
    return '<svg class="ss-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['grid']) . '</svg>';
}

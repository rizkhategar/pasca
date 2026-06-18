@if (!empty($initialNewsPayload))
    <script>
        /*
         * The News index page starts its API fetch immediately after this script.
         * On repeat visits, serve the cached page-one payload once so cards render
         * immediately, then the next navigation/request refreshes from the API.
         */
        (function () {
            const cachedFirstPage = @json($initialNewsPayload);
            const originalFetch = window.fetch.bind(window);
            let hasServedCachedFirstPage = false;

            window.fetch = function (resource, options) {
                const requestUrl = typeof resource === 'string' ? resource : resource?.url;

                if (!hasServedCachedFirstPage
                    && requestUrl
                    && requestUrl.includes('/berita/search')
                    && requestUrl.includes('page=1')
                    && requestUrl.includes('paginate=9')
                    && requestUrl.includes('sort=desc')
                    && !requestUrl.includes('category_id=')
                    && !requestUrl.includes('&q=')) {
                    hasServedCachedFirstPage = true;

                    return Promise.resolve(new Response(JSON.stringify(cachedFirstPage), {
                        status: 200,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-News-Source': 'page-cache',
                        },
                    }));
                }

                return originalFetch(resource, options);
            };
        })();
    </script>
@endif

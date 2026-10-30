/**
 * topographyCode.js — "C" (sub-site) autocomplete on the patient form.
 *
 * When the primary site changes, fetches its sub-sites from
 * patient/topography-codes and fills the <datalist> behind #tumour-topography_code.
 *   C00.9 -> C00.0 ... C00.8
 * Sites without sub-sites (e.g. C01.9, or a specific code like C22.0) make the field read-only.
 */
(function ($) {
    const $site = $('#tumour-primary_site');
    const $code = $('#tumour-topography_code');
    const $list = $('#topography-code-options');

    if (!$site.length || !$code.length) {
        return;
    }

    // readOnly rather than disabled: disabled inputs are not submitted, so a
    // cleared value would never reach the server on update
    function setState(enabled, placeholder) {
        $code.prop('readOnly', !enabled)
            .toggleClass('bg-surface-dim cursor-not-allowed', !enabled)
            .attr('placeholder', placeholder);
    }

    /**
     * @param {boolean} keepValue  true on page load, so a saved (or invalid) value
     *                             stays visible for the server-side error message
     */
    function loadSubsites(keepValue) {
        const site = $site.val();
        $list.empty();

        if (!site) {
            if (!keepValue) $code.val('');
            setState(false, 'Select primary site first');
            return;
        }

        $.getJSON($code.data('url'), { primarySite: site })
            .done(function (items) {
                items.forEach(function (item) {
                    $list.append($('<option>').attr('value', item.code).text(item.description));
                });

                const current = ($code.val() || '').toUpperCase();
                const known = items.some(function (item) { return item.code === current; });

                if (!known && !keepValue) $code.val('');

                if (items.length) {
                    setState(true, 'Type or pick, e.g. ' + items[0].code);
                } else {
                    setState(false, 'No sub-sites for this primary site');
                }
            })
            .fail(function () {
                // Offline or server error: allow free entry; the server validates on submit
                setState(true, 'Enter sub-site code, e.g. ' + site.slice(0, 3) + '.0');
            });
    }

    $site.on('change', function () { loadSubsites(false); });
    $code.on('change', function () { $code.val(($code.val() || '').trim().toUpperCase()); });

    loadSubsites(true);
})(jQuery);

/**
 * topographyCode.js — Primary site and "C" (sub-site) autocomplete on the patient form.
 *
 * Primary site (#tumour-primary_site) is a text input backed by
 * <datalist id="primary-site-options">: type a code (C50) or a site name (breast).
 * The matching site name is shown under the input. Once the value is a listed code,
 * the dependent fields are refreshed:
 *   - Histology options   (patient/morphology-options)
 *   - Laterality options  (patient/laterality-options)
 *   - C sub-sites         (patient/topography-codes), e.g. C00.9 -> C00.0 ... C00.8
 * Partial input (e.g. "C5") does not trigger lookups.
 *
 * C (#tumour-topography_code) is read-only when the primary site has no sub-sites
 * (e.g. C01.9, or a specific code like C22.0).
 */
(function ($) {
    const $site = $('#tumour-primary_site');
    const $code = $('#tumour-topography_code');
    const $codeList = $('#topography-code-options');
    const siteList = document.getElementById('primary-site-options');

    if (!$site.length) {
        return;
    }

    /* ── Primary site ─────────────────────────────────────────────────────── */

    // Site description for a listed code, or null when the value is not in the list
    function siteName(value) {
        if (!siteList || !value) return null;
        const match = Array.from(siteList.options).find(function (o) { return o.value === value; });
        return match ? match.textContent : null;
    }

    function siteHint() {
        let $hint = $site.siblings('.primary-site-name');
        if (!$hint.length) {
            $hint = $('<div class="primary-site-name text-xs mt-1"></div>').insertAfter($site);
        }
        return $hint;
    }

    function showSiteName() {
        const value = $site.val();
        const name = siteName(value);
        const $hint = siteHint();

        if (!value) {
            $hint.text('').removeClass('text-error');
        } else if (name) {
            $hint.text(name).removeClass('text-error').addClass('text-on-surface-variant');
        } else {
            $hint.text('Not a listed primary site, pick one from the suggestions').removeClass('text-on-surface-variant').addClass('text-error');
        }
    }

    // Refresh histology and laterality; an empty site resets them to their prompts
    function refreshDependents(site) {
        $.get($site.data('morphology-url'), { topographyCode: site }, function (html) {
            $('select#tumour-histology').html(html);
        });
        $.get($site.data('laterality-url'), { topographyCode: site }, function (html) {
            $('select#tumour-laterality').html(html);
        });
    }

    let lastSite = $site.val();

    function onSiteChange() {
        const value = $site.val();
        const valid = value === '' || siteName(value) !== null;

        showSiteName();

        if (!valid || value === lastSite) return;

        lastSite = value;
        refreshDependents(value);
        loadSubsites(false);
    }

    // Uppercase as the user types ("c50.9" -> "C50.9"), keeping the caret in place
    $site.on('input', function () {
        const el = this;
        const upper = el.value.toUpperCase();
        if (el.value !== upper && /^c/i.test(el.value)) {
            const pos = el.selectionStart;
            el.value = upper;
            el.setSelectionRange(pos, pos);
        }
        showSiteName();
        // Picking from the datalist fires "input" with the full code: refresh right away
        if (siteName(el.value) !== null) onSiteChange();
    });
    $site.on('change', onSiteChange);

    /* ── C (sub-site) ─────────────────────────────────────────────────────── */

    // readOnly rather than disabled: disabled inputs are not submitted, so a
    // cleared value would never reach the server on update
    function setCodeState(enabled, placeholder) {
        $code.prop('readOnly', !enabled)
            .toggleClass('bg-surface-dim cursor-not-allowed', !enabled)
            .attr('placeholder', placeholder);
    }

    /**
     * @param {boolean} keepValue  true on page load, so a saved (or invalid) value
     *                             stays visible for the server-side error message
     */
    function loadSubsites(keepValue) {
        if (!$code.length) return;

        const site = $site.val();
        $codeList.empty();

        if (!site || siteName(site) === null) {
            if (!keepValue) $code.val('');
            setCodeState(false, 'Select primary site first');
            return;
        }

        $.getJSON($code.data('url'), { primarySite: site })
            .done(function (items) {
                items.forEach(function (item) {
                    $codeList.append($('<option>').attr('value', item.code).text(item.description));
                });

                const current = ($code.val() || '').toUpperCase();
                const known = items.some(function (item) { return item.code === current; });

                if (!known && !keepValue) $code.val('');

                if (items.length) {
                    setCodeState(true, 'Type or pick, e.g. ' + items[0].code);
                } else {
                    setCodeState(false, 'No sub-sites for this primary site');
                }
            })
            .fail(function () {
                // Offline or server error: allow free entry; the server validates on submit
                setCodeState(true, 'Enter sub-site code, e.g. ' + site.slice(0, 3) + '.0');
            });
    }

    $code.on('change', function () { $code.val(($code.val() || '').trim().toUpperCase()); });

    // Initial state (update form, or create form re-rendered after a validation error)
    showSiteName();
    loadSubsites(true);
})(jQuery);

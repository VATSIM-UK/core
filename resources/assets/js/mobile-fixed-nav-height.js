const MOBILE_NAV_MAX_WIDTH = 992;
const MOBILE_NAV_MEDIA = `(max-width: ${MOBILE_NAV_MAX_WIDTH}px)`;

function isMobileNavLayout() {
    return window.matchMedia(MOBILE_NAV_MEDIA).matches;
}

function isNavMenuExpanded() {
    const collapse = document.getElementById("nav-inner");

    return (
        collapse?.classList.contains("in") ||
        collapse?.classList.contains("collapsing")
    );
}

function navClearanceBufferPx() {
    const value = getComputedStyle(document.documentElement)
        .getPropertyValue("--nav-clearance-buffer")
        .trim();

    const parsed = parseInt(value, 10);

    return Number.isFinite(parsed) ? parsed : 0;
}

function measureMobileFixedNavClearancePx(nav) {
    // Use bottom edge from viewport top so safe-area padding and fixed offsets
    // are included (height alone misses inset when the nav is offset from top: 0).
    const { bottom } = nav.getBoundingClientRect();

    return Math.ceil(bottom) + navClearanceBufferPx();
}

function updateMobileFixedNavHeight() {
    const nav = document.getElementById("nav");

    if (!nav || !isMobileNavLayout() || isNavMenuExpanded()) {
        return;
    }

    document.documentElement.style.setProperty(
        "--mobile-fixed-nav-height",
        `${measureMobileFixedNavClearancePx(nav)}px`,
    );
}

function clearMobileFixedNavHeight() {
    document.documentElement.style.removeProperty("--mobile-fixed-nav-height");
}

function syncMobileFixedNavHeight() {
    if (isMobileNavLayout()) {
        updateMobileFixedNavHeight();
    } else {
        clearMobileFixedNavHeight();
    }
}

function initMobileFixedNavHeight() {
    syncMobileFixedNavHeight();

    window.addEventListener("resize", syncMobileFixedNavHeight);
    window.addEventListener("load", updateMobileFixedNavHeight);
    window.addEventListener("orientationchange", updateMobileFixedNavHeight);

    if (window.visualViewport) {
        window.visualViewport.addEventListener(
            "resize",
            updateMobileFixedNavHeight,
        );
    }

    if (document.fonts?.ready) {
        document.fonts.ready.then(updateMobileFixedNavHeight);
    }

    const nav = document.getElementById("nav");
    const collapse = document.getElementById("nav-inner");

    if (collapse) {
        $(collapse).on("hidden.bs.collapse", updateMobileFixedNavHeight);
    }

    if (nav && typeof ResizeObserver !== "undefined") {
        const observer = new ResizeObserver(() => {
            if (!isNavMenuExpanded()) {
                updateMobileFixedNavHeight();
            }
        });

        observer.observe(nav);
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMobileFixedNavHeight);
} else {
    initMobileFixedNavHeight();
}

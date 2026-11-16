{{--
    Small corrections to Filament's own chrome.

    Loaded through a render hook at the end of <head>, so these rules come
    after the panel's stylesheet and win on equal specificity. !important is
    used anyway on the handful that fight Tailwind utilities baked into
    Filament's markup — worth the bluntness for four cosmetic lines, and it
    keeps the whole override in one file instead of a compiled theme with a
    build step behind it.

    Rules that do not match anything are simply ignored, so a future Filament
    release can rename a class without breaking the panel — the sidebar just
    returns to its default spacing.
--}}
<style>
    :root {
        /* The one number worth tuning. Filament ships 1.5rem. */
        --admin-sidebar-gutter: .55rem;
    }

    /* The indent of the whole menu column. */
    .fi-sidebar-nav {
        padding-inline: var(--admin-sidebar-gutter) !important;
    }

    /* Each item's own padding, and the gap between icon and label. */
    .fi-sidebar-item-button {
        padding-inline-start: .55rem !important;
        padding-inline-end: .55rem !important;
        gap: .6rem !important;
    }

    /* Group wrappers add a second indent of their own on some releases. */
    .fi-sidebar-group-items,
    .fi-sidebar-nav-groups {
        padding-inline-start: 0 !important;
    }

    /* The brand line at the top, so it sits on the same left edge as the menu. */
    .fi-sidebar-header {
        padding-inline-start: calc(var(--admin-sidebar-gutter) + .55rem) !important;
    }

    /* Group headings ("Content" and the like) line up with the items. */
    .fi-sidebar-group-label {
        padding-inline-start: .55rem !important;
    }
</style>
